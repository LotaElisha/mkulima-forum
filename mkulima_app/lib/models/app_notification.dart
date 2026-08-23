import 'package:flutter/foundation.dart';

/// One item in the notification feed.
///
/// Written because the app crashed on this screen with
///
///   type 'List<dynamic>' is not a subtype of type
///   'FutureOr<Map<String, dynamic>>'
///
/// The service declared `Future<Map<String, dynamic>>` and then returned
/// `response.data['notifications']`, which is a List. Nothing in the type
/// system caught it because the value crossed the boundary as `dynamic`.
/// Parsing into a real class means a shape change fails here, on one line,
/// with the field name in the message - not somewhere deep in a widget build.
@immutable
class AppNotification {
  final String id;
  final String title;
  final String message;
  final String type;
  final bool read;
  final DateTime? createdAt;
  final Map<String, dynamic>? data;

  const AppNotification({
    required this.id,
    required this.title,
    required this.message,
    required this.type,
    required this.read,
    this.createdAt,
    this.data,
  });

  factory AppNotification.fromJson(Map<String, dynamic> json) {
    return AppNotification(
      id: _string(json['id']),
      title: _string(json['title']),
      message: _string(json['message']),
      type: _string(json['type'], fallback: 'system'),
      read: _bool(json['read']),
      createdAt: _dateTime(json['created_at']),
      data: json['data'] is Map ? Map<String, dynamic>.from(json['data']) : null,
    );
  }

  AppNotification copyWith({bool? read}) => AppNotification(
        id: id,
        title: title,
        message: message,
        type: type,
        read: read ?? this.read,
        createdAt: createdAt,
        data: data,
      );

  static String _string(dynamic value, {String fallback = ''}) {
    if (value == null) return fallback;
    if (value is String) return value.isEmpty ? fallback : value;
    return '$value';
  }

  /// Laravel is not consistent about booleans across drivers: MySQL returns
  /// tinyint 1/0, SQLite true/false, and a JSON cast can produce "1".
  static bool _bool(dynamic value) {
    if (value is bool) return value;
    if (value is num) return value != 0;
    if (value is String) return value == '1' || value.toLowerCase() == 'true';
    return false;
  }

  static DateTime? _dateTime(dynamic value) {
    if (value is! String || value.isEmpty) return null;
    return DateTime.tryParse(value)?.toLocal();
  }
}

/// The whole `/notifications` response.
///
/// The endpoint answers `{"notifications": [...], "unread_count": n}`. It also
/// accepts a bare list, because an endpoint that grows pagination later will
/// wrap itself in `{"data": [...]}` and this screen should not crash the day
/// that happens.
@immutable
class NotificationFeed {
  final List<AppNotification> items;
  final int unreadCount;

  const NotificationFeed({required this.items, required this.unreadCount});

  static const NotificationFeed empty =
      NotificationFeed(items: [], unreadCount: 0);

  bool get isEmpty => items.isEmpty;

  factory NotificationFeed.fromResponse(dynamic body) {
    final list = _extractList(body);

    final items = <AppNotification>[];
    for (final entry in list) {
      if (entry is Map) {
        items.add(AppNotification.fromJson(Map<String, dynamic>.from(entry)));
      }
    }

    // Trust the server's count when it sends one - it is computed before any
    // client-side filtering. Fall back to counting what arrived.
    int unread;
    if (body is Map && body['unread_count'] is num) {
      unread = (body['unread_count'] as num).toInt();
    } else {
      unread = items.where((n) => !n.read).length;
    }

    return NotificationFeed(items: items, unreadCount: unread);
  }

  static List<dynamic> _extractList(dynamic body) {
    if (body is List) return body;
    if (body is Map) {
      for (final key in const ['notifications', 'data', 'items']) {
        final value = body[key];
        if (value is List) return value;
        // Laravel's paginator nests the rows one level deeper.
        if (value is Map && value['data'] is List) return value['data'] as List;
      }
    }
    return const [];
  }
}
