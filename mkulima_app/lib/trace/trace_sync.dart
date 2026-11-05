import 'dart:convert';

import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../core/api_error.dart';
import '../services/api_service.dart';

/// One write waiting to reach the server.
///
/// Every body carries UUIDs made on the phone, so sending the same op twice
/// (a retry after a dropped connection) returns the row already written
/// instead of a second one.
class TraceOp {
  final String id;
  final String
  kind; // register, plot, season, harvest, event, handoff, split, merge, process, confirm, dispute
  final String path;
  final Map<String, dynamic> body;
  final String label;
  final String? lotUuid;
  final String? lotCode;
  final DateTime createdAt;
  String status; // pending | failed | synced
  String? error;
  Map<String, dynamic>? result;

  TraceOp({
    required this.id,
    required this.kind,
    required this.path,
    required this.body,
    required this.label,
    this.lotUuid,
    this.lotCode,
    DateTime? createdAt,
    this.status = 'pending',
    this.error,
    this.result,
  }) : createdAt = createdAt ?? DateTime.now();

  bool get isPending => status == 'pending';
  bool get isFailed => status == 'failed';
  bool get isSynced => status == 'synced';

  Map<String, dynamic> toJson() => {
    'id': id,
    'kind': kind,
    'path': path,
    'body': body,
    'label': label,
    'lot_uuid': lotUuid,
    'lot_code': lotCode,
    'created_at': createdAt.toIso8601String(),
    'status': status,
    'error': error,
  };

  static TraceOp? tryDecode(Object? raw) {
    try {
      final m = Map<String, dynamic>.from(raw as Map);
      return TraceOp(
        id: m['id'] as String,
        kind: m['kind'] as String,
        path: m['path'] as String,
        body: Map<String, dynamic>.from(m['body'] as Map),
        label: m['label'] as String? ?? '',
        lotUuid: m['lot_uuid'] as String?,
        lotCode: m['lot_code'] as String?,
        createdAt: DateTime.tryParse(m['created_at'] as String? ?? ''),
        status: m['status'] as String? ?? 'pending',
        error: m['error'] as String?,
      );
    } catch (_) {
      return null;
    }
  }
}

/// [ApiError] for a Mkulima Trace call. The app's general mapping hides a
/// 403's text (Laravel's default one is English and unhelpful), but the trace
/// API writes its refusals for people, in Swahili: "only the holder can
/// record this", "your registration is waiting for an admin". Keep those.
ApiError traceError(Object e) {
  final err = ApiError.from(e);
  final cause = err.cause;
  if (err.statusCode == 403 && cause is DioException) {
    final body = cause.response?.data;
    final message = body is Map ? body['message'] : null;
    if (message is String && message.trim().isNotEmpty) {
      return ApiError(
        message: message,
        statusCode: 403,
        requestId: err.requestId,
        cause: cause,
      );
    }
  }
  return err;
}

/// What happened to a write the user just made.
enum TraceOutcome { synced, queued }

class TraceSubmit {
  final TraceOutcome outcome;
  final Map<String, dynamic>? data;
  const TraceSubmit(this.outcome, [this.data]);
  bool get synced => outcome == TraceOutcome.synced;
}

/// Sends a POST and returns the decoded body. Swappable for tests.
typedef TracePoster =
    Future<Map<String, dynamic>> Function(
      String path,
      Map<String, dynamic> body,
    );

TracePoster posterFor(ApiService api) => (path, body) async {
  final r = await api.post(path, data: body);
  return r.data is Map ? Map<String, dynamic>.from(r.data as Map) : {};
};

/// The offline queue and the local copy of what the server last said.
///
/// Writes are sent in the order they were made, so a hand-off recorded
/// offline after a harvest reaches the server after the harvest. A network
/// failure or a server error stops the run and leaves the rest pending; a
/// refusal (4xx) marks that op failed with the server's message and moves on.
class TraceSync extends ChangeNotifier {
  TraceSync._();
  static final TraceSync instance = TraceSync._();

  static const _opsKey = 'trace_ops_v1';
  static const _cacheKey = 'trace_cache_v1';
  static const _keepSynced = 30;
  static const _keepDetails = 80;

  final List<TraceOp> _ops = [];
  Map<String, dynamic> _cache = {};
  bool _loaded = false;
  bool _syncing = false;
  DateTime? lastSyncedAt;

  List<TraceOp> get ops => List.unmodifiable(_ops);
  List<TraceOp> get unsent => _ops.where((o) => !o.isSynced).toList();
  int get pendingCount => _ops.where((o) => o.isPending).length;
  int get failedCount => _ops.where((o) => o.isFailed).length;
  bool get syncing => _syncing;

  Future<void> load() async {
    if (_loaded) return;
    final prefs = await SharedPreferences.getInstance();
    _ops
      ..clear()
      ..addAll(
        (prefs.getStringList(_opsKey) ?? const [])
            .map((s) => TraceOp.tryDecode(_decode(s)))
            .whereType<TraceOp>(),
      );
    final raw = prefs.getString(_cacheKey);
    _cache = raw == null
        ? {}
        : Map<String, dynamic>.from(_decode(raw) as Map? ?? {});
    _loaded = true;
    notifyListeners();
  }

  @visibleForTesting
  void resetForTest() {
    _ops.clear();
    _cache = {};
    _loaded = false;
    _syncing = false;
  }

  static Object? _decode(String s) {
    try {
      return jsonDecode(s);
    } catch (_) {
      return null;
    }
  }

  Future<void> _saveOps() async {
    final prefs = await SharedPreferences.getInstance();
    // Keep a short history of what was sent, so the farmer can see it went.
    final synced = _ops.where((o) => o.isSynced).toList();
    if (synced.length > _keepSynced) {
      final drop = synced.take(synced.length - _keepSynced).toSet();
      _ops.removeWhere(drop.contains);
    }
    await prefs.setStringList(
      _opsKey,
      _ops.map((o) => jsonEncode(o.toJson())).toList(),
    );
  }

  Future<void> _saveCache() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_cacheKey, jsonEncode(_cache));
  }

  // ── Writes ────────────────────────────────────────────────────────

  /// Try to send now. Offline or on a server error the op is queued and
  /// [TraceOutcome.queued] comes back. A refusal is thrown as [ApiError]
  /// so the form can show it and nothing is queued.
  Future<TraceSubmit> submit(
    TracePoster post,
    TraceOp op, {
    required bool online,
  }) async {
    await load();
    if (!online) {
      await _enqueue(op);
      return const TraceSubmit(TraceOutcome.queued);
    }
    // Earlier writes go first, or this one could reach the server before
    // the harvest it depends on.
    if (pendingCount > 0) {
      await _enqueue(op);
      await syncNow(post);
      final sent = _ops.firstWhere((o) => o.id == op.id, orElse: () => op);
      if (sent.isFailed) {
        throw ApiError(
          message: sent.error ?? 'Imeshindikana.',
          statusCode: 422,
        );
      }
      return sent.isSynced
          ? TraceSubmit(TraceOutcome.synced, sent.result)
          : const TraceSubmit(TraceOutcome.queued);
    }
    try {
      final body = await post(op.path, op.body);
      op
        ..status = 'synced'
        ..result = body;
      _ops.add(op);
      await _saveOps();
      lastSyncedAt = DateTime.now();
      notifyListeners();
      return TraceSubmit(TraceOutcome.synced, body);
    } catch (e) {
      final err = traceError(e);
      if (_shouldRetry(err)) {
        await _enqueue(op);
        return const TraceSubmit(TraceOutcome.queued);
      }
      throw err;
    }
  }

  Future<void> _enqueue(TraceOp op) async {
    op.status = 'pending';
    _ops.add(op);
    await _saveOps();
    notifyListeners();
  }

  static bool _shouldRetry(ApiError e) =>
      e.isOffline ||
      e.statusCode == 408 ||
      e.statusCode == 429 ||
      (e.statusCode ?? 0) >= 500;

  /// Send everything still pending, oldest first.
  Future<void> syncNow(TracePoster post, {bool includeFailed = false}) async {
    await load();
    if (_syncing) return;
    _syncing = true;
    notifyListeners();
    try {
      for (final op in List.of(_ops)) {
        if (op.isSynced || (op.isFailed && !includeFailed)) continue;
        try {
          op.result = await post(op.path, op.body);
          op
            ..status = 'synced'
            ..error = null;
        } catch (e) {
          final err = traceError(e);
          if (_shouldRetry(err)) {
            op.status = 'pending';
            break;
          }
          op
            ..status = 'failed'
            ..error = err.message;
        }
        await _saveOps();
      }
      lastSyncedAt = DateTime.now();
    } finally {
      _syncing = false;
      await _saveOps();
      notifyListeners();
    }
  }

  /// Drop a write the server refused, after the farmer has read why.
  Future<void> discard(String id) async {
    _ops.removeWhere((o) => o.id == id && !o.isPending);
    await _saveOps();
    notifyListeners();
  }

  /// Unsent writes that touch this lot, newest last.
  List<TraceOp> unsentFor({String? uuid, String? code}) => _ops
      .where(
        (o) =>
            !o.isSynced &&
            ((uuid != null && o.lotUuid == uuid) ||
                (code != null && o.lotCode == code)),
      )
      .toList();

  // ── Local copy ────────────────────────────────────────────────────

  Map<String, dynamic>? get me => _map(_cache['me']);
  List<Map<String, dynamic>> get lots => _list(_cache['lots']);
  List<Map<String, dynamic>> get history => _list(_cache['history']);
  List<Map<String, dynamic>> get plots => _list(_cache['plots']);
  List<Map<String, dynamic>> get pending => _list(_cache['pending']);

  /// A lot's full record as last fetched, by code or UUID.
  Map<String, dynamic>? lot(String key) {
    final details = _map(_cache['details']) ?? {};
    final hit =
        details[key] ??
        details.values.cast<Object?>().firstWhere(
          (d) => d is Map && (d['uuid'] == key || d['code'] == key),
          orElse: () => null,
        );
    return _map(hit);
  }

  Future<void> remember(String slot, Object? value) async {
    await load();
    _cache[slot] = value;
    await _saveCache();
    notifyListeners();
  }

  Future<void> rememberLot(Map<String, dynamic> lot) async {
    await load();
    final code = lot['code'] as String?;
    if (code == null) return;
    final details = Map<String, dynamic>.from(_map(_cache['details']) ?? {});
    details.remove(code);
    details[code] = lot;
    while (details.length > _keepDetails) {
      details.remove(details.keys.first);
    }
    _cache['details'] = details;
    await _saveCache();
    notifyListeners();
  }

  static Map<String, dynamic>? _map(Object? v) =>
      v is Map ? Map<String, dynamic>.from(v) : null;

  static List<Map<String, dynamic>> _list(Object? v) => v is List
      ? v.whereType<Map>().map((m) => Map<String, dynamic>.from(m)).toList()
      : const [];
}
