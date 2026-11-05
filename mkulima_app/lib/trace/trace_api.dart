import '../services/api_service.dart';
import 'trace_sync.dart';

/// Reads from /api/v1/trace. Each read refreshes the copy kept on the phone,
/// so the same screen still has something to show offline.
class TraceApi {
  final ApiService api;
  TraceApi(this.api);

  TraceSync get _store => TraceSync.instance;

  static List<Map<String, dynamic>> _rows(Object? body) {
    final data = body is Map ? body['data'] : null;
    return data is List
        ? data
              .whereType<Map>()
              .map((m) => Map<String, dynamic>.from(m))
              .toList()
        : const [];
  }

  static Map<String, dynamic> _data(Object? body) => ApiService.v1Data(body);

  /// My participant profile (null if not registered) and how many hand-offs wait for me.
  Future<({Map<String, dynamic>? participant, int pending})> me() async {
    final r = await api.get('/v1/trace/me');
    final data = _data(r.data);
    final p = data['participant'];
    final participant = p is Map ? Map<String, dynamic>.from(p) : null;
    await _store.remember('me', participant);
    await _store.remember('pending_count', data['pending_confirmations']);
    return (
      participant: participant,
      pending: (data['pending_confirmations'] as num?)?.toInt() ?? 0,
    );
  }

  Future<List<Map<String, dynamic>>> lots({bool history = false}) async {
    final r = await api.get(
      '/v1/trace/lots',
      queryParameters: {'scope': history ? 'history' : 'current'},
    );
    final rows = _rows(r.data);
    await _store.remember(history ? 'history' : 'lots', rows);
    return rows;
  }

  Future<List<Map<String, dynamic>>> plots() async {
    final r = await api.get('/v1/trace/plots');
    final rows = _rows(r.data);
    await _store.remember('plots', rows);
    return rows;
  }

  Future<List<Map<String, dynamic>>> pending() async {
    final r = await api.get('/v1/trace/pending');
    final rows = _rows(r.data);
    await _store.remember('pending', rows);
    return rows;
  }

  Future<Map<String, dynamic>> lot(String uuid) async {
    final r = await api.get('/v1/trace/lots/$uuid');
    final lot = _data(r.data);
    await _store.rememberLot(lot);
    return lot;
  }

  /// What a code leads to: {kind: lot|plot|participant, ...}.
  Future<Map<String, dynamic>> scan(String code) async {
    final r = await api.get('/v1/trace/scan/$code');
    final data = _data(r.data);
    if (data['kind'] == 'lot' && data['lot'] is Map) {
      await _store.rememberLot(Map<String, dynamic>.from(data['lot'] as Map));
    }
    return data;
  }

  Future<Map<String, dynamic>> participant(String code) async {
    final r = await api.get('/v1/trace/participants/$code');
    return _data(r.data);
  }
}
