import 'dart:math';

import '../providers/auth_provider.dart' show kApiBaseUrl;

/// Identifiers for Mkulima Trace, made on the phone so a record can be
/// created offline and synced later without being written twice.
///
/// A QR code holds only a [code]: ten characters from an alphabet without
/// look-alikes (no 0/O, 1/I/L). It never holds a name, a phone number or
/// farm coordinates. The server uses the same alphabet and refuses a code
/// that is already taken.
class TraceIds {
  TraceIds._();

  static const alphabet = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';
  static const codeLength = 10;
  static final _random = Random.secure();
  static final _pattern = RegExp(r'^[23456789ABCDEFGHJKMNPQRSTUVWXYZ]{10}$');
  static final _link = RegExp(r'/t/([23456789A-Za-z]{10})(?:[/?#]|$)');

  /// A random (version 4) UUID.
  static String uuid() {
    final b = List<int>.generate(16, (_) => _random.nextInt(256));
    b[6] = (b[6] & 0x0f) | 0x40;
    b[8] = (b[8] & 0x3f) | 0x80;
    final h = b.map((x) => x.toRadixString(16).padLeft(2, '0')).join();
    return '${h.substring(0, 8)}-${h.substring(8, 12)}-${h.substring(12, 16)}-'
        '${h.substring(16, 20)}-${h.substring(20)}';
  }

  static String code() => List.generate(
    codeLength,
    (_) => alphabet[_random.nextInt(alphabet.length)],
  ).join();

  static bool isCode(String? value) =>
      value != null && _pattern.hasMatch(value);

  /// Accepts what a camera read (a link ending in /t/{code}) or what a
  /// person typed (the bare code, any case, spaces ignored).
  static String? fromScan(String raw) {
    final text = raw.trim();
    final link = _link.firstMatch(text);
    if (link != null) return link.group(1)!.toUpperCase();
    final bare = text.replaceAll(RegExp(r'[\s-]'), '').toUpperCase();
    return isCode(bare) ? bare : null;
  }

  /// The site that serves /t/{code}: the API host without its /api suffix.
  static String get webBase => kApiBaseUrl.replaceFirst(RegExp(r'/api/?$'), '');

  /// What the QR code encodes.
  static String link(String code) => '$webBase/t/$code';

  /// A printable label with the QR code, the lot code and the crop.
  static String labelLink(String code) => '$webBase/t/$code/label';
}
