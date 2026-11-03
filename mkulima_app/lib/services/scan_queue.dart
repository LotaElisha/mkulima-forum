import 'dart:convert';
import 'dart:typed_data';
import 'package:shared_preferences/shared_preferences.dart';

/// Photos taken with no connection, waiting to be diagnosed.
///
/// Diagnosis runs on the server, so offline the app keeps the photo and
/// sends it when the connection returns. Capped at [maxItems] so a phone
/// with little storage is not filled with images.
class ScanQueue {
  static const _key = 'pending_plant_scans_v1';
  static const maxItems = 5;

  static Future<List<PendingScan>> all() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getStringList(_key) ?? const [];
    return [
      for (final item in raw)
        if (PendingScan.tryDecode(item) case final scan?) scan,
    ];
  }

  /// False when the queue is full.
  static Future<bool> add(Uint8List bytes, String name) async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getStringList(_key) ?? <String>[];
    if (raw.length >= maxItems) return false;
    raw.add(jsonEncode({
      'id': DateTime.now().microsecondsSinceEpoch.toString(),
      'name': name,
      'at': DateTime.now().toIso8601String(),
      'image': base64Encode(bytes),
    }));
    await prefs.setStringList(_key, raw);
    return true;
  }

  static Future<void> remove(String id) async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getStringList(_key) ?? <String>[];
    raw.removeWhere((item) => PendingScan.tryDecode(item)?.id == id);
    await prefs.setStringList(_key, raw);
  }
}

class PendingScan {
  final String id;
  final String name;
  final DateTime at;
  final Uint8List bytes;

  const PendingScan({required this.id, required this.name, required this.at, required this.bytes});

  static PendingScan? tryDecode(String raw) {
    try {
      final m = jsonDecode(raw) as Map<String, dynamic>;
      return PendingScan(
        id: m['id'] as String,
        name: m['name'] as String,
        at: DateTime.parse(m['at'] as String),
        bytes: base64Decode(m['image'] as String),
      );
    } catch (_) {
      return null;
    }
  }
}
