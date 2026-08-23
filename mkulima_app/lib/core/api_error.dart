import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';

/// One place that turns any failure into something a farmer can read.
///
/// The app was showing this on screen, verbatim, in production:
///
///   DioException [bad response]: This exception was thrown because the
///   response has a status code of 500 and RequestOptions.validateStatus was
///   configured to throw for this status code. ... Read more about status
///   codes at https://developer.mozilla.org/en-US/docs/Web/HTTP/Status
///
/// A farmer in Njombe cannot act on that, and it tells anyone holding the
/// phone more about our stack than they need to know. The detail is still
/// worth having — it just belongs in a log, keyed by an id the user can read
/// back to support, not in the interface.
@immutable
class ApiError implements Exception {
  /// What to put on screen. Swahili, plain, actionable.
  final String message;

  /// HTTP status, when there was a response at all.
  final int? statusCode;

  /// Field-level validation errors from Laravel, keyed by field name.
  final Map<String, List<String>> fieldErrors;

  /// Correlation id from the server (`X-Request-Id`), so a report from a user
  /// can be matched to a server-side stack trace.
  final String? requestId;

  /// The underlying failure. Logged, never displayed.
  final Object? cause;

  const ApiError({
    required this.message,
    this.statusCode,
    this.fieldErrors = const {},
    this.requestId,
    this.cause,
  });

  bool get isUnauthorized => statusCode == 401;
  bool get isForbidden => statusCode == 403;
  bool get isValidation => statusCode == 422;
  bool get isOffline => statusCode == null;

  /// True when trying again in a moment is a sensible thing to offer.
  bool get isRetryable =>
      isOffline || statusCode == 429 || (statusCode != null && statusCode! >= 500);

  /// The first validation message for a field, if there is one.
  String? fieldError(String field) => fieldErrors[field]?.firstOrNull;

  static const String _offline =
      'Hakikisha una intaneti kisha ujaribu tena.';
  static const String _serverDown =
      'Huduma haipatikani kwa sasa. Tafadhali jaribu tena baada ya muda mfupi.';
  static const String _serverError =
      'Kuna tatizo la mfumo. Tafadhali jaribu tena.';
  static const String _forbidden =
      'Huna ruhusa ya kutumia huduma hii.';
  static const String _unauthorized =
      'Muda wa kuingia umeisha. Tafadhali ingia tena.';
  static const String _notFound =
      'Hatukupata kile ulichokitafuta.';
  static const String _tooMany =
      'Umejaribu mara nyingi mno. Subiri kidogo kisha ujaribu tena.';
  static const String _unknown =
      'Imeshindikana kukamilisha ombi. Tafadhali jaribu tena.';

  factory ApiError.from(Object error, [StackTrace? stack]) {
    if (error is ApiError) return error;

    if (error is! DioException) {
      _log('unexpected', error, stack);
      return ApiError(message: _unknown, cause: error);
    }

    final response = error.response;
    final status = response?.statusCode;
    final requestId = response?.headers.value('x-request-id');

    // No response at all: DNS, TLS, timeout, aeroplane mode. The distinction
    // between "no signal" and "server unreachable" is not one the user can act
    // on differently, so both get the same sentence.
    if (response == null) {
      _log('network', error, stack);
      return ApiError(
        message: error.type == DioExceptionType.cancel ? _unknown : _offline,
        cause: error,
      );
    }

    final body = response.data;
    final serverMessage = _serverMessage(body);
    final fields = _fieldErrors(body);

    // Laravel's own message is preferred where the server wrote it for a
    // person: validation failures, "phone already linked", "OTP disabled".
    // It is already translated per the user's preferred_language. It is NOT
    // preferred for 5xx, where the body is a stack trace or an HTML error
    // page and shows the user our internals.
    final useServerMessage = serverMessage != null &&
        status != null &&
        status < 500 &&
        status != 401 &&
        status != 403;

    final String message;
    if (useServerMessage) {
      message = serverMessage;
    } else {
      message = switch (status) {
        401 => _unauthorized,
        403 => _forbidden,
        404 => _notFound,
        429 => _tooMany,
        503 || 502 || 504 => _serverDown,
        _ when status != null && status >= 500 => _serverError,
        _ => _unknown,
      };
    }

    _log('http $status', error, stack, requestId: requestId);

    return ApiError(
      message: message,
      statusCode: status,
      fieldErrors: fields,
      requestId: requestId,
      cause: error,
    );
  }

  /// A readable server message, or null when the body is not one.
  ///
  /// Guards against an HTML error page or a stack trace being pushed to the
  /// screen: an Nginx 503 page is a String, not a Map, and a Laravel debug
  /// response carries `exception` and `trace` keys we must never surface.
  static String? _serverMessage(dynamic body) {
    if (body is! Map) return null;
    if (body.containsKey('exception') || body.containsKey('trace')) return null;

    final raw = body['message'];
    if (raw is! String) return null;

    final message = raw.trim();
    if (message.isEmpty) return null;
    if (message.length > 200) return null;
    // "Server Error", "Service Unavailable" and friends are Laravel's own
    // English placeholders, not something written for this user.
    const placeholders = {
      'server error',
      'service unavailable',
      'internal server error',
      'bad gateway',
      'unauthenticated.',
      'unauthenticated',
    };
    if (placeholders.contains(message.toLowerCase())) return null;
    if (message.startsWith('<')) return null;

    return message;
  }

  static Map<String, List<String>> _fieldErrors(dynamic body) {
    if (body is! Map) return const {};
    final errors = body['errors'];
    if (errors is! Map) return const {};

    final result = <String, List<String>>{};
    errors.forEach((key, value) {
      if (value is List) {
        result['$key'] = value.map((e) => '$e').toList();
      } else if (value is String) {
        result['$key'] = [value];
      }
    });
    return result;
  }

  /// Detail goes here, never to the screen.
  ///
  /// In debug this prints. In release it is the hook for whatever error
  /// tracker gets wired up - see GO_LIVE_CHECKLIST.md, which still lists
  /// error tracking as unconfigured.
  static void _log(String kind, Object error, StackTrace? stack,
      {String? requestId}) {
    if (!kDebugMode) return;
    final id = requestId == null ? '' : ' [request $requestId]';
    debugPrint('ApiError($kind)$id: $error');
    if (stack != null) debugPrint(stack.toString());
  }

  @override
  String toString() => message;
}

extension _FirstOrNull<E> on List<E> {
  E? get firstOrNull => isEmpty ? null : first;
}
