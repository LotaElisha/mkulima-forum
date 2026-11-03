import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import '../core/format.dart';
import '../core/strings.dart';
import '../core/theme.dart';
import '../providers/auth_provider.dart';
import '../providers/connectivity_provider.dart';
import '../services/api_service.dart';
import '../services/scan_queue.dart';
import 'agronomists_screen.dart';
import 'disease_library_screen.dart';
import 'marketplace_screen.dart';

/// Standalone page so the scanner can be pushed from anywhere, as well as
/// being the centre tab.
class ScannerPage extends StatelessWidget {
  const ScannerPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text(MkStrings.scannerBrand)),
      body: const ScannerScreen(),
    );
  }
}

/// AI Plant Scanner.
///
/// What runs where, stated plainly because the UI must not claim more:
///   * Diagnosis is Gemini Vision on the server (POST /scanner/scan). It
///     needs a connection and an AI key configured in admin; when either is
///     missing the server answers "analysis unavailable" and so do we.
///   * Offline, the photo is kept on the phone (ScanQueue) and diagnosed
///     automatically when the connection returns.
///   * The disease library is bundled and readable offline.
///   * There is no on-device model. Nothing here names a disease without
///     the server having looked at the photo.
class ScannerScreen extends StatefulWidget {
  const ScannerScreen({super.key});

  @override
  State<ScannerScreen> createState() => _ScannerScreenState();
}

class _ScannerScreenState extends State<ScannerScreen> {
  Uint8List? _imageBytes;
  String _imageName = 'plant.jpg';
  bool _isScanning = false;
  bool _syncing = false;
  bool _wasOnline = true;
  Map<String, dynamic>? _result;
  String? _unavailable;
  List<PendingScan> _pending = [];

  @override
  void initState() {
    super.initState();
    _loadPending();
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final online = context.watch<ConnectivityProvider>().isOnline;
    if (online && !_wasOnline) _syncPending();
    _wasOnline = online;
  }

  Future<void> _loadPending() async {
    final pending = await ScanQueue.all();
    if (mounted) setState(() => _pending = pending);
  }

  Future<void> _pickImage(ImageSource source) async {
    final picked = await ImagePicker().pickImage(
      source: source,
      maxWidth: 1024,
      imageQuality: 85,
    );
    if (picked == null) return;
    final bytes = await picked.readAsBytes();
    if (!mounted) return;
    setState(() {
      _imageBytes = bytes;
      _imageName = picked.name;
      _result = null;
      _unavailable = null;
    });
  }

  Future<void> _diagnose() async {
    if (_imageBytes == null) return;
    if (!context.read<ConnectivityProvider>().isOnline) {
      await _saveForLater();
      return;
    }
    final ok = await AuthProvider.requireAuth(
      context,
      action: 'kukagua ugonjwa wa mmea',
    );
    if (!ok || !mounted) return;
    setState(() {
      _isScanning = true;
      _unavailable = null;
    });
    try {
      final result = await context.read<ApiService>().scanDisease(
        _imageBytes!,
        _imageName,
      );
      if (!mounted) return;
      setState(() {
        _result = result;
        _isScanning = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isScanning = false;
        _unavailable = ApiService.formatError(e);
      });
    }
  }

  Future<void> _saveForLater() async {
    final saved = await ScanQueue.add(_imageBytes!, _imageName);
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          saved
              ? 'Picha imehifadhiwa. Itakaguliwa ukirudi mtandaoni.'
              : 'Picha ${ScanQueue.maxItems} zinasubiri tayari. Rudi mtandaoni kwanza.',
        ),
      ),
    );
    if (saved) {
      setState(() {
        _imageBytes = null;
        _result = null;
        _unavailable = null;
      });
      _loadPending();
    }
  }

  Future<void> _syncPending() async {
    if (_syncing || _pending.isEmpty) return;
    if (!context.read<AuthProvider>().isAuthenticated) return;
    setState(() => _syncing = true);
    final api = context.read<ApiService>();
    var done = 0;
    for (final scan in List.of(_pending)) {
      try {
        final result = await api.scanDisease(scan.bytes, scan.name);
        await ScanQueue.remove(scan.id);
        done++;
        if (!mounted) return;
        setState(() {
          _imageBytes = scan.bytes;
          _result = result;
        });
      } catch (_) {
        break; // Keep the rest queued; retry on the next reconnect.
      }
    }
    await _loadPending();
    if (!mounted) return;
    setState(() => _syncing = false);
    if (done > 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Picha $done zilizosubiri zimekaguliwa.')),
      );
    }
  }

  void _push(Widget screen) =>
      Navigator.of(context).push(MaterialPageRoute(builder: (_) => screen));

  @override
  Widget build(BuildContext context) {
    final online = context.watch<ConnectivityProvider>().isOnline;
    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 24),
      children: [
        Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: online ? MkColors.leafPale : MkColors.accentSoft,
            borderRadius: BorderRadius.circular(MkRadii.card),
          ),
          child: Text(
            online
                ? 'Uko mtandaoni: picha itakaguliwa na AI papo hapo.'
                : 'Hakuna mtandao. Piga picha sasa; itahifadhiwa na kukaguliwa ukirudi '
                      'mtandaoni. Maktaba ya magonjwa inafanya kazi bila mtandao.',
            style: MkText.body,
          ),
        ),
        const SizedBox(height: 12),
        if (_imageBytes != null)
          ClipRRect(
            borderRadius: BorderRadius.circular(MkRadii.card),
            child: AspectRatio(
              aspectRatio: 4 / 3,
              child: Image.memory(_imageBytes!, fit: BoxFit.cover),
            ),
          ),
        const SizedBox(height: 12),
        FilledButton.icon(
          onPressed: _isScanning
              ? null
              : _imageBytes == null
              ? () => _pickImage(ImageSource.camera)
              : _diagnose,
          icon: Icon(
            _imageBytes == null
                ? Icons.camera_alt_rounded
                : online
                ? Icons.biotech_outlined
                : Icons.save_alt_outlined,
          ),
          label: Text(
            _isScanning
                ? 'AI inachambua...'
                : _imageBytes == null
                ? 'Piga picha ya mmea'
                : online
                ? 'Kagua kwa AI'
                : 'Hifadhi, kagua nikirudi mtandaoni',
          ),
          style: FilledButton.styleFrom(minimumSize: const Size.fromHeight(52)),
        ),
        const SizedBox(height: 8),
        OutlinedButton.icon(
          onPressed: _isScanning ? null : () => _pickImage(ImageSource.gallery),
          icon: const Icon(Icons.photo_library_outlined),
          label: Text(
            _imageBytes == null
                ? 'Chagua kutoka ghalari'
                : 'Chagua picha nyingine',
          ),
          style: OutlinedButton.styleFrom(
            minimumSize: const Size.fromHeight(48),
            foregroundColor: MkColors.ink,
          ),
        ),
        if (_unavailable != null) ...[
          const SizedBox(height: 12),
          Text(
            'Uchunguzi haukufanyika: $_unavailable',
            style: MkText.bodyMuted,
          ),
          TextButton(
            onPressed: _saveForLater,
            child: const Text('Hifadhi, jaribu baadaye'),
          ),
        ],
        if (_result != null) ...[
          const SizedBox(height: 16),
          _ResultCard(
            result: _result!,
            onAgronomist: () => _push(const AgronomistsScreen()),
            onInputs: () => _push(const MarketplaceScreen(standalone: true)),
          ),
        ],
        if (_pending.isNotEmpty) ...[
          const SizedBox(height: 16),
          Card(
            margin: EdgeInsets.zero,
            color: MkColors.surfaceMuted,
            child: ListTile(
              leading: const Icon(Icons.schedule_outlined),
              title: Text(
                _syncing
                    ? 'Inakagua picha zilizosubiri...'
                    : 'Picha ${_pending.length} zinasubiri kukaguliwa',
              ),
              trailing: online && !_syncing
                  ? TextButton(
                      onPressed: _syncPending,
                      child: const Text('Kagua sasa'),
                    )
                  : null,
            ),
          ),
        ],
        const SizedBox(height: 16),
        // Own Card (own Material): a bare ListTile paints its border on the
        // Scaffold, which left the border behind when the banner above grew.
        Card(
          margin: EdgeInsets.zero,
          child: ListTile(
            leading: const Icon(
              Icons.menu_book_outlined,
              color: MkColors.primary,
            ),
            title: const Text('Maktaba ya magonjwa'),
            subtitle: const Text(
              'Dalili, tiba na kinga. Inafanya kazi bila mtandao.',
            ),
            trailing: const Icon(Icons.chevron_right),
            onTap: () => _push(const DiseaseLibraryScreen()),
          ),
        ),
      ],
    );
  }
}

class _ResultCard extends StatelessWidget {
  final Map<String, dynamic> result;
  final VoidCallback onAgronomist;
  final VoidCallback onInputs;
  const _ResultCard({
    required this.result,
    required this.onAgronomist,
    required this.onInputs,
  });

  @override
  Widget build(BuildContext context) {
    final confidence = parseNum(
      result['confidence'] ?? result['confidence_score'],
    );
    String? text(String k) {
      final v = result[k];
      return v == null || v.toString().trim().isEmpty ? null : v.toString();
    }

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        border: Border.all(color: MkColors.border),
        borderRadius: BorderRadius.circular(MkRadii.card),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Matokeo ya uchunguzi', style: MkText.caption),
          Text(text('disease_name') ?? 'Haijulikani', style: MkText.page),
          if (confidence != null && confidence > 0)
            Text(
              'Uhakika wa AI: ${(confidence <= 1 ? confidence * 100 : confidence).toStringAsFixed(0)}%',
              style: MkText.label.copyWith(color: MkColors.primary),
            ),
          for (final (label, key) in const [
            ('Maelezo', 'description'),
            ('Dalili', 'symptoms'),
            ('Tiba', 'treatment'),
            ('Tiba', 'treatment_recommendation'),
            ('Kinga', 'prevention'),
          ])
            if (text(key) case final value?) ...[
              const SizedBox(height: 10),
              Text(label, style: MkText.label),
              Text(value, style: MkText.body),
            ],
          const SizedBox(height: 12),
          const Text(
            'AI inaweza kukosea. Thibitisha na mtaalamu kabla ya kununua dawa; tumia dawa '
            'zilizosajiliwa na TPHPA.',
            style: MkText.caption,
          ),
          const SizedBox(height: 8),
          Wrap(
            spacing: 8,
            children: [
              FilledButton(
                onPressed: onAgronomist,
                child: const Text('Ongea na mtaalamu'),
              ),
              OutlinedButton(
                onPressed: onInputs,
                child: const Text('Tafuta pembejeo'),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
