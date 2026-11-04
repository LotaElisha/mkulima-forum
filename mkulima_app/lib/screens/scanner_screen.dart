import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import '../core/format.dart';
import '../core/theme.dart';
import '../providers/auth_provider.dart';
import '../providers/connectivity_provider.dart';
import '../services/api_service.dart';
import '../services/scan_queue.dart';
import 'agronomists_screen.dart';
import 'disease_library_screen.dart';
import 'marketplace_screen.dart';
import '../widgets/mk_illustrations.dart';

/// Standalone page so the scanner can be pushed from anywhere, as well as
/// being the centre tab.
class ScannerPage extends StatelessWidget {
  const ScannerPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Mkulima AI – Scan Mmea')),
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

  /// Server crop key, or null for "not sure". Sent with the photo.
  String? _crop;

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
        cropType: _crop,
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
    final saved = await ScanQueue.add(_imageBytes!, _imageName, crop: _crop);
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
        final result = await api.scanDisease(
          scan.bytes,
          scan.name,
          cropType: scan.crop,
        );
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
      padding: EdgeInsets.zero,
      children: [
        _Viewfinder(imageBytes: _imageBytes, online: online),
        // The white sheet overlaps the viewfinder's bottom edge.
        Transform.translate(
          offset: const Offset(0, -20),
          child: Container(
            padding: const EdgeInsets.fromLTRB(16, 20, 16, 0),
            decoration: const BoxDecoration(
              color: MkColors.surface,
              borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: _SourceTile(
                        icon: Icons.photo_camera_outlined,
                        title: 'Piga Picha',
                        subtitle: '(Camera)',
                        primary: true,
                        onTap: _isScanning
                            ? null
                            : () => _pickImage(ImageSource.camera),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: _SourceTile(
                        icon: Icons.image_outlined,
                        title: 'Pakia Picha',
                        subtitle: '(Kutoka ghalari)',
                        primary: false,
                        onTap: _isScanning
                            ? null
                            : () => _pickImage(ImageSource.gallery),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                const Text('Chagua zao (hiari)', style: MkText.label),
                const SizedBox(height: 6),
                DropdownButtonFormField<String?>(
                  initialValue: _crop,
                  isExpanded: true,
                  onChanged: _isScanning
                      ? null
                      : (v) => setState(() => _crop = v),
                  decoration: const InputDecoration(
                    prefixIcon: Icon(
                      Icons.spa_outlined,
                      color: MkColors.primary,
                    ),
                  ),
                  items: [
                    for (final (key, label) in _crops)
                      DropdownMenuItem(value: key, child: Text(label)),
                  ],
                ),
                const SizedBox(height: 16),
                FilledButton.icon(
                  onPressed: _isScanning
                      ? null
                      : _imageBytes == null
                      ? () => _pickImage(ImageSource.camera)
                      : _diagnose,
                  icon: Icon(
                    _imageBytes != null && !online
                        ? Icons.save_alt_outlined
                        : Icons.document_scanner_outlined,
                  ),
                  label: Text(
                    _isScanning
                        ? 'AI inachambua...'
                        : _imageBytes != null && !online
                        ? 'Hifadhi, kagua nikirudi mtandaoni'
                        : 'Chunguza Mmea',
                    textAlign: TextAlign.center,
                  ),
                  style: FilledButton.styleFrom(
                    backgroundColor: MkColors.cta,
                    foregroundColor: Colors.white,
                    minimumSize: const Size.fromHeight(56),
                    textStyle: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.w800,
                    ),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(16),
                    ),
                  ),
                ),
                if (_imageBytes == null)
                  const Padding(
                    padding: EdgeInsets.only(top: 6),
                    child: Text(
                      'Piga au pakia picha ya jani lenye dalili, kwa mwanga mzuri.',
                      style: MkText.caption,
                      textAlign: TextAlign.center,
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
                    onInputs: () =>
                        _push(const MarketplaceScreen(standalone: true)),
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
                const _OfflineCard(),
                const SizedBox(height: 12),
                // Own Card (own Material): a bare ListTile paints its border
                // on the Scaffold, which left it behind when content grew.
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
                const SizedBox(height: 8),
              ],
            ),
          ),
        ),
      ],
    );
  }
}

/// Crops the server accepts as `crop_type`, with Swahili names.
const _crops = <(String?, String)>[
  (null, 'Sina uhakika / zao lingine'),
  ('maize', 'Mahindi'),
  ('beans', 'Maharage'),
  ('rice', 'Mpunga'),
  ('cassava', 'Muhogo'),
  ('banana', 'Ndizi'),
  ('coffee', 'Kahawa'),
  ('tea', 'Chai'),
  ('tomato', 'Nyanya'),
  ('onion', 'Vitunguu'),
  ('potato', 'Viazi'),
];

/// The photo (or a painted leaf until there is one) with viewfinder corners
/// and a status pill that states only what is true right now.
class _Viewfinder extends StatelessWidget {
  final Uint8List? imageBytes;
  final bool online;
  const _Viewfinder({required this.imageBytes, required this.online});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 300,
      child: Stack(
        fit: StackFit.expand,
        children: [
          if (imageBytes != null)
            Image.memory(imageBytes!, fit: BoxFit.cover)
          else
            const MkSpottedLeaf(),
          const Padding(
            padding: EdgeInsets.fromLTRB(36, 24, 36, 88),
            child: MkScanCorners(inset: 0, length: 44, stroke: 5),
          ),
          Positioned(
            left: 16,
            right: 16,
            bottom: 34,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              decoration: BoxDecoration(
                color: const Color(0xCC0B2A16),
                borderRadius: BorderRadius.circular(999),
                border: Border.all(color: Colors.white38),
              ),
              child: Row(
                children: [
                  Icon(
                    online ? Icons.cloud_done_outlined : Icons.wifi_off_rounded,
                    color: const Color(0xFF86EFAC),
                    size: 20,
                  ),
                  const SizedBox(width: 6),
                  Expanded(
                    child: Text(
                      online
                          ? 'Mtandaoni: AI iko tayari'
                          : 'Bila mtandao: picha huhifadhiwa',
                      style: const TextStyle(color: Colors.white, fontSize: 13),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  Container(
                    width: 1,
                    height: 22,
                    margin: const EdgeInsets.symmetric(horizontal: 8),
                    color: Colors.white38,
                  ),
                  const Icon(
                    Icons.menu_book_outlined,
                    color: Colors.white,
                    size: 18,
                  ),
                  const SizedBox(width: 4),
                  const Text(
                    'Maktaba (Offline)',
                    style: TextStyle(color: Colors.white, fontSize: 13),
                  ),
                  // An icon, not "✓": Roboto has no tick glyph and drew a box.
                  const Icon(Icons.check, color: Colors.white, size: 16),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _SourceTile extends StatelessWidget {
  final IconData icon;
  final String title;
  final String subtitle;
  final bool primary;
  final VoidCallback? onTap;
  const _SourceTile({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.primary,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Material(
      color: primary ? MkColors.leafPale : MkColors.surfaceMuted,
      borderRadius: BorderRadius.circular(16),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Container(
          constraints: const BoxConstraints(minHeight: 112),
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 12),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(16),
            border: Border.all(
              color: primary ? const Color(0xFFBFDCC8) : MkColors.border,
            ),
          ),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, size: 36, color: MkColors.primary),
              const SizedBox(height: 6),
              Text(
                title,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w700,
                  color: MkColors.ink,
                ),
              ),
              Text(
                subtitle,
                textAlign: TextAlign.center,
                style: MkText.caption,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

/// What works without a connection, stated exactly. There is no on-device
/// model, so this must never say the AI diagnoses offline.
class _OfflineCard extends StatelessWidget {
  const _OfflineCard();

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: MkColors.leafPale,
        borderRadius: BorderRadius.circular(MkRadii.card),
        border: Border.all(color: const Color(0xFFBFDCC8)),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Icon(Icons.bolt_rounded, color: MkColors.primary, size: 24),
                    SizedBox(width: 4),
                    Flexible(
                      child: Text(
                        'Inafanya kazi bila mtandao',
                        style: TextStyle(
                          fontSize: 17,
                          fontWeight: FontWeight.w800,
                          color: MkColors.primaryDark,
                        ),
                      ),
                    ),
                  ],
                ),
                SizedBox(height: 6),
                Text(
                  'Bila mtandao, piga picha kama kawaida. Huhifadhiwa kwenye '
                  'simu na kukaguliwa na AI moja kwa moja ukirudi mtandaoni.',
                  style: TextStyle(
                    fontSize: 14,
                    height: 1.4,
                    color: MkColors.ink,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 10),
          SizedBox(
            width: 64,
            height: 72,
            child: Stack(
              children: [
                const Positioned.fill(
                  child: Icon(
                    Icons.smartphone_outlined,
                    size: 64,
                    color: MkColors.primaryDark,
                  ),
                ),
                const Positioned(
                  left: 20,
                  top: 18,
                  child: Icon(Icons.eco, size: 24, color: MkColors.primary),
                ),
                Positioned(
                  right: 0,
                  bottom: 0,
                  child: Container(
                    padding: const EdgeInsets.all(3),
                    decoration: const BoxDecoration(
                      color: MkColors.cta,
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(
                      Icons.wifi_off_rounded,
                      size: 16,
                      color: Colors.white,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
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
