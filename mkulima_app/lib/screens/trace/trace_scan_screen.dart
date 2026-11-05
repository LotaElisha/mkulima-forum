import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:provider/provider.dart';

import '../../core/api_error.dart';
import '../../core/theme.dart';
import '../../providers/connectivity_provider.dart';
import '../../services/api_service.dart';
import '../../trace/trace_api.dart';
import '../../trace/trace_ids.dart';
import '../../trace/trace_strings.dart';
import '../../trace/trace_sync.dart';
import 'trace_lot_screen.dart';
import 'trace_widgets.dart';

/// Scan a Mkulima Trace QR code with the camera, or type the code.
///
/// Decoding happens on the phone. With [pickCode] the screen just returns
/// the code (to name the receiver of a hand-off). Otherwise it opens what
/// the code points to; offline, it opens the copy kept on the phone if the
/// lot was seen before.
class TraceScanScreen extends StatefulWidget {
  final bool pickCode;
  const TraceScanScreen({super.key, this.pickCode = false});

  @override
  State<TraceScanScreen> createState() => _TraceScanScreenState();
}

class _TraceScanScreenState extends State<TraceScanScreen> {
  final _controller = MobileScannerController(
    detectionSpeed: DetectionSpeed.noDuplicates,
    formats: const [BarcodeFormat.qrCode],
  );
  final _manual = TextEditingController();
  bool _busy = false;
  String? _message;

  @override
  void dispose() {
    _controller.dispose();
    _manual.dispose();
    super.dispose();
  }

  void _onDetect(BarcodeCapture capture) {
    final raw = capture.barcodes
        .map((b) => b.rawValue)
        .whereType<String>()
        .firstOrNull;
    if (raw != null) _handle(raw);
  }

  Future<void> _handle(String raw) async {
    if (_busy) return;
    final code = TraceIds.fromScan(raw);
    if (code == null) {
      setState(() => _message = 'Hii si QR ya Mkulima Trace.');
      return;
    }
    if (widget.pickCode) {
      Navigator.of(context).pop(code);
      return;
    }
    setState(() {
      _busy = true;
      _message = null;
    });
    final nav = Navigator.of(context);
    final online = context.read<ConnectivityProvider>().isOnline;
    try {
      if (!online) throw const ApiError(message: 'offline');
      final result = await TraceApi(context.read<ApiService>()).scan(code);
      if (!mounted) return;
      switch (result['kind']) {
        case 'lot':
          final lot = Map<String, dynamic>.from(result['lot'] as Map);
          await nav.pushReplacement(
            MaterialPageRoute(
              builder: (_) =>
                  TraceLotScreen(uuid: '${lot['uuid'] ?? ''}', code: code),
            ),
          );
        // A public view has no uuid; the lot screen shows the cached summary.
        case 'participant':
          final p = Map<String, dynamic>.from(result['participant'] as Map);
          setState(
            () => _message =
                'Msimbo wa mshiriki: ${TraceStrings.type(p['type'] as String?)}'
                '${p['name'] != null ? ' · ${p['name']}' : ''}'
                '${p['verified'] == true ? ' · amethibitishwa' : ''}',
          );
        case 'plot':
          final p = Map<String, dynamic>.from(result['plot'] as Map);
          setState(
            () => _message =
                'Shamba la ${p['region']} · mazao: ${(p['crops'] as List? ?? const []).join(', ')}',
          );
      }
    } catch (e) {
      final cached = TraceSync.instance.lot(code);
      if (cached != null && mounted) {
        await nav.pushReplacement(
          MaterialPageRoute(
            builder: (_) =>
                TraceLotScreen(uuid: '${cached['uuid'] ?? code}', code: code),
          ),
        );
      } else if (mounted) {
        final err = traceError(e);
        setState(
          () => _message = !online || err.isOffline
              ? 'Hakuna mtandao, na mzigo huu haujawahi kufunguliwa kwenye simu hii. Msimbo: $code'
              : (err.statusCode == 404
                    ? 'Msimbo $code haupo kwenye Mkulima Trace.'
                    : err.message),
        );
      }
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          widget.pickCode ? 'Changanua msimbo wa mpokeaji' : 'Changanua QR',
        ),
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          ClipRRect(
            borderRadius: BorderRadius.circular(16),
            child: SizedBox(
              height: 300,
              child: MobileScanner(
                controller: _controller,
                onDetect: _onDetect,
                errorBuilder: (context, error, _) => Container(
                  color: MkColors.surfaceMuted,
                  padding: const EdgeInsets.all(24),
                  alignment: Alignment.center,
                  child: Text(
                    error.errorCode == MobileScannerErrorCode.permissionDenied
                        ? 'Ruhusu kamera kuchanganua QR, au andika msimbo hapa chini.'
                        : 'Kamera haipatikani. Andika msimbo hapa chini.',
                    textAlign: TextAlign.center,
                  ),
                ),
              ),
            ),
          ),
          const SizedBox(height: 16),
          if (_busy) const LinearProgressIndicator(),
          if (_message != null)
            Padding(
              padding: const EdgeInsets.only(bottom: 12),
              child: Text(
                _message!,
                style: const TextStyle(fontWeight: FontWeight.w600),
              ),
            ),
          const Text(
            'Au andika msimbo ulio chini ya QR',
            style: TextStyle(fontWeight: FontWeight.w600),
          ),
          const SizedBox(height: 8),
          TextField(
            controller: _manual,
            textCapitalization: TextCapitalization.characters,
            decoration: const InputDecoration(
              labelText: 'Msimbo (herufi na namba 10)',
            ),
            onSubmitted: _handle,
          ),
          const SizedBox(height: 12),
          FilledButton(
            onPressed: _busy ? null : () => _handle(_manual.text),
            style: FilledButton.styleFrom(
              minimumSize: const Size.fromHeight(48),
            ),
            child: const Text('Fungua'),
          ),
          if (!widget.pickCode) const TraceNotice(),
        ],
      ),
    );
  }
}
