import 'dart:async';
import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import '../core/theme.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import 'login_modal.dart';

/// Kagua Dawa — counterfeit agri-input detection.
/// Three honest tools: registry lookup, AI label check cross-referenced
/// against the registry, and admin-confirmed community alerts per region.
class KaguaDawaScreen extends StatelessWidget {
  const KaguaDawaScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 3,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('Kagua Dawa'),
          backgroundColor: MkColors.primary,
          foregroundColor: Colors.white,
          actions: [
            IconButton(
              tooltip: 'Orodha ya ukaguzi',
              icon: const Icon(Icons.checklist),
              onPressed: () => _showChecklist(context),
            ),
          ],
          bottom: const TabBar(
            indicatorColor: MkColors.accent,
            labelColor: Colors.white,
            unselectedLabelColor: Colors.white70,
            tabs: [
              Tab(icon: Icon(Icons.search), text: 'Tafuta'),
              Tab(icon: Icon(Icons.photo_camera), text: 'Piga Lebo'),
              Tab(icon: Icon(Icons.campaign), text: 'Tahadhari'),
            ],
          ),
        ),
        body: const TabBarView(
          children: [
            _RegistrySearchTab(),
            _LabelCheckTab(),
            _AlertsTab(),
          ],
        ),
      ),
    );
  }

  Future<void> _showChecklist(BuildContext context) async {
    final api = context.read<ApiService>();
    Map<String, dynamic>? data;
    try {
      final response = await api.get('/inputs/checklist');
      data = response.data;
    } catch (_) {}
    if (!context.mounted) return;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) => DraggableScrollableSheet(
        expand: false,
        initialChildSize: 0.75,
        builder: (context, controller) => ListView(
          controller: controller,
          padding: const EdgeInsets.all(20),
          children: [
            Text(
              data?['title'] ?? 'Kagua Dawa Kabla ya Kununua',
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 12),
            ...((data?['items'] as List?) ?? []).map((item) => ListTile(
                  dense: true,
                  leading: Icon(
                    item['weight'] == 'high'
                        ? Icons.priority_high
                        : Icons.check_circle_outline,
                    color: item['weight'] == 'high'
                        ? MkColors.danger
                        : MkColors.primary,
                  ),
                  title: Text('${item['text']}',
                      style: const TextStyle(fontSize: 14)),
                )),
            if (data?['advice'] != null)
              Card(
                color: Colors.amber[50],
                child: Padding(
                  padding: const EdgeInsets.all(12),
                  child: Text('${data!['advice']}',
                      style: const TextStyle(fontSize: 13)),
                ),
              ),
          ],
        ),
      ),
    );
  }
}

/* ======================= TAB 1: Registry search ======================= */

class _RegistrySearchTab extends StatefulWidget {
  const _RegistrySearchTab();

  @override
  State<_RegistrySearchTab> createState() => _RegistrySearchTabState();
}

class _RegistrySearchTabState extends State<_RegistrySearchTab>
    with AutomaticKeepAliveClientMixin {
  final _controller = TextEditingController();
  Timer? _debounce;
  Map<String, dynamic>? _data;
  bool _isLoading = false;

  @override
  bool get wantKeepAlive => true;

  @override
  void dispose() {
    _debounce?.cancel();
    _controller.dispose();
    super.dispose();
  }

  void _onChanged(String value) {
    _debounce?.cancel();
    _debounce = Timer(const Duration(milliseconds: 400), () async {
      final query = value.trim();
      if (query.length < 2) {
        setState(() => _data = null);
        return;
      }
      setState(() => _isLoading = true);
      try {
        final api = context.read<ApiService>();
        final response =
            await api.get('/inputs/verify', queryParameters: {'q': query});
        if (mounted) {
          setState(() {
            _data = response.data;
            _isLoading = false;
          });
        }
      } catch (_) {
        if (mounted) setState(() => _isLoading = false);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    super.build(context);
    final matches = (_data?['matches'] as List?) ?? [];
    final alerts = (_data?['related_alerts'] as List?) ?? [];

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        TextField(
          controller: _controller,
          onChanged: _onChanged,
          decoration: const InputDecoration(
            labelText: 'Jina la dawa au namba ya usajili (TPRI/TFRA)',
            prefixIcon: Icon(Icons.search),
            border: OutlineInputBorder(),
          ),
        ),
        const SizedBox(height: 16),
        if (_isLoading) const Center(child: CircularProgressIndicator()),
        if (_data != null && !_isLoading) ...[
          _GuidanceCard(text: '${_data!['guidance']}'),
          const SizedBox(height: 12),
          if (alerts.isNotEmpty) ...[
            const Text('Tahadhari Zilizothibitishwa',
                style: TextStyle(fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            ...alerts.map((a) => _AlertCard(alert: a)),
            const SizedBox(height: 12),
          ],
          if (matches.isNotEmpty) ...[
            const Text('Kwenye Orodha ya Usajili',
                style: TextStyle(fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            ...matches.map((m) => Card(
                  margin: const EdgeInsets.only(bottom: 8),
                  child: ListTile(
                    leading: _statusIcon('${m['status']}'),
                    title: Text('${m['name']}'),
                    subtitle: Text([
                      if (m['registration_number'] != null)
                        'Usajili: ${m['registration_number']}',
                      if (m['manufacturer'] != null) '${m['manufacturer']}',
                      'Chanzo: ${m['source']}',
                    ].join(' · ')),
                    trailing: _statusChip('${m['status']}'),
                  ),
                )),
          ],
        ],
        if (_data == null && !_isLoading)
          Padding(
            padding: const EdgeInsets.only(top: 48),
            child: Column(
              children: [
                const Icon(Icons.verified_user_outlined,
                    size: 56, color: Colors.grey),
                const SizedBox(height: 12),
                Text(
                  'Andika jina la dawa, mbolea au namba ya usajili '
                  'kuangalia kama imesajiliwa rasmi.',
                  textAlign: TextAlign.center,
                  style: TextStyle(color: Colors.grey[600]),
                ),
              ],
            ),
          ),
      ],
    );
  }
}

/* ======================= TAB 2: Label photo check ===================== */

class _LabelCheckTab extends StatefulWidget {
  const _LabelCheckTab();

  @override
  State<_LabelCheckTab> createState() => _LabelCheckTabState();
}

class _LabelCheckTabState extends State<_LabelCheckTab>
    with AutomaticKeepAliveClientMixin {
  Uint8List? _imageBytes;
  String _imageName = 'lebo.jpg';
  bool _isChecking = false;
  Map<String, dynamic>? _result;

  @override
  bool get wantKeepAlive => true;

  Future<void> _pick(ImageSource source) async {
    final picked =
        await ImagePicker().pickImage(source: source, maxWidth: 1280);
    if (picked != null) {
      final bytes = await picked.readAsBytes();
      if (!mounted) return;
      setState(() {
        _imageBytes = bytes;
        _imageName = picked.name;
        _result = null;
      });
    }
  }

  Future<void> _check() async {
    if (_imageBytes == null) return;
    setState(() => _isChecking = true);
    try {
      final api = context.read<ApiService>();
      final response = await api.postMultipart(
        '/inputs/check-label',
        fileField: 'image',
        fileBytes: _imageBytes!,
        filename: _imageName,
      );
      if (!mounted) return;
      setState(() {
        _result = response.data;
        _isChecking = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() => _isChecking = false);
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text(
              'Huduma ya kusoma lebo haipatikani kwa sasa. Tumia "Tafuta".')));
    }
  }

  @override
  Widget build(BuildContext context) {
    super.build(context);
    final auth = Provider.of<AuthProvider>(context);

    if (!auth.isAuthenticated) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.lock_outline, size: 56, color: Colors.grey),
            const SizedBox(height: 12),
            const Text('Ingia ili kukagua lebo kwa picha'),
            const SizedBox(height: 12),
            ElevatedButton(
              onPressed: () => LoginModal.show(context),
              child: const Text('Ingia'),
            ),
          ],
        ),
      );
    }

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        if (_imageBytes != null)
          ClipRRect(
            borderRadius: BorderRadius.circular(14),
            child: Image.memory(_imageBytes!,
                height: 220, width: double.infinity, fit: BoxFit.cover),
          )
        else
          Container(
            height: 220,
            decoration: BoxDecoration(
              color: Colors.grey[200],
              borderRadius: BorderRadius.circular(14),
            ),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.receipt_long, size: 52, color: Colors.grey[400]),
                const SizedBox(height: 8),
                Text('Piga picha ya LEBO ya dawa/mbolea kwa karibu',
                    style: TextStyle(color: Colors.grey[600])),
              ],
            ),
          ),
        const SizedBox(height: 14),
        Row(
          children: [
            Expanded(
              child: ElevatedButton.icon(
                onPressed: () => _pick(ImageSource.camera),
                icon: const Icon(Icons.camera_alt),
                label: const Text('Kamera'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: MkColors.primary,
                  foregroundColor: Colors.white,
                ),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: ElevatedButton.icon(
                onPressed: () => _pick(ImageSource.gallery),
                icon: const Icon(Icons.photo_library),
                label: const Text('Ghalari'),
              ),
            ),
          ],
        ),
        if (_imageBytes != null) ...[
          const SizedBox(height: 12),
          SizedBox(
            height: 48,
            child: ElevatedButton.icon(
              onPressed: _isChecking ? null : _check,
              icon: _isChecking
                  ? const SizedBox(
                      width: 18,
                      height: 18,
                      child: CircularProgressIndicator(
                          strokeWidth: 2, color: Colors.white))
                  : const Icon(Icons.verified_user),
              label: const Text('Kagua Lebo Sasa'),
              style: ElevatedButton.styleFrom(
                backgroundColor: MkColors.accent,
                foregroundColor: MkColors.primaryDark,
              ),
            ),
          ),
        ],
        if (_result != null) ...[
          const SizedBox(height: 16),
          if (_result!['readable'] == false)
            _GuidanceCard(text: '${_result!['message']}')
          else ...[
            _VerdictBanner(verdict: '${_result!['verdict']}'),
            const SizedBox(height: 10),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(14),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Kilichosomwa kwenye Lebo',
                        style: TextStyle(fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    _kv('Jina', _result!['extracted']?['product_name']),
                    _kv('Namba ya usajili',
                        _result!['extracted']?['registration_number']),
                    _kv('Mtengenezaji',
                        _result!['extracted']?['manufacturer']),
                    ...((_result!['extracted']?['label_warnings'] as List?) ??
                            [])
                        .map((w) => Padding(
                              padding: const EdgeInsets.only(top: 4),
                              child: Row(children: [
                                const Icon(Icons.warning_amber,
                                    size: 16, color: Colors.orange),
                                const SizedBox(width: 6),
                                Expanded(
                                    child: Text('$w',
                                        style: const TextStyle(fontSize: 13))),
                              ]),
                            )),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 10),
            _GuidanceCard(text: '${_result!['guidance']}'),
          ],
        ],
      ],
    );
  }

  Widget _kv(String label, dynamic value) => Padding(
        padding: const EdgeInsets.only(bottom: 4),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            SizedBox(
                width: 120,
                child: Text('$label:',
                    style: const TextStyle(fontWeight: FontWeight.w600))),
            Expanded(child: Text(value?.toString() ?? '—')),
          ],
        ),
      );
}

/* ======================= TAB 3: Regional alerts ======================= */

class _AlertsTab extends StatefulWidget {
  const _AlertsTab();

  @override
  State<_AlertsTab> createState() => _AlertsTabState();
}

class _AlertsTabState extends State<_AlertsTab>
    with AutomaticKeepAliveClientMixin {
  List<dynamic> _alerts = [];
  String? _region;
  bool _isLoading = true;

  static const _regions = [
    'Dar es Salaam', 'Arusha', 'Dodoma', 'Mwanza', 'Mbeya', 'Morogoro',
    'Tanga', 'Iringa', 'Kigoma', 'Mtwara', 'Tabora', 'Kilimanjaro',
  ];

  @override
  bool get wantKeepAlive => true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _isLoading = true);
    try {
      final api = context.read<ApiService>();
      final response = await api.get('/inputs/alerts',
          queryParameters: {if (_region != null) 'region': _region});
      if (!mounted) return;
      setState(() {
        _alerts = response.data['data'] ?? [];
        _isLoading = false;
      });
    } catch (_) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    super.build(context);
    final auth = Provider.of<AuthProvider>(context);

    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 0),
          child: Row(
            children: [
              Expanded(
                child: DropdownButtonFormField<String?>(
                  initialValue: _region,
                  isExpanded: true,
                  decoration: const InputDecoration(
                    labelText: 'Mkoa',
                    border: OutlineInputBorder(),
                    contentPadding:
                        EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  ),
                  items: [
                    const DropdownMenuItem(value: null, child: Text('Mikoa yote')),
                    ..._regions.map(
                        (r) => DropdownMenuItem(value: r, child: Text(r))),
                  ],
                  onChanged: (value) {
                    setState(() => _region = value);
                    _load();
                  },
                ),
              ),
              const SizedBox(width: 10),
              ElevatedButton.icon(
                onPressed: () {
                  if (!auth.isAuthenticated) {
                    LoginModal.show(context);
                    return;
                  }
                  _showReportForm();
                },
                icon: const Icon(Icons.flag),
                label: const Text('Ripoti'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: MkColors.danger,
                  foregroundColor: Colors.white,
                  padding:
                      const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
                ),
              ),
            ],
          ),
        ),
        Expanded(
          child: _isLoading
              ? const Center(child: CircularProgressIndicator())
              : RefreshIndicator(
                  onRefresh: _load,
                  child: _alerts.isEmpty
                      ? ListView(
                          children: [
                            const SizedBox(height: 80),
                            const Icon(Icons.shield_outlined,
                                size: 56, color: Colors.grey),
                            const SizedBox(height: 12),
                            Center(
                              child: Text(
                                _region == null
                                    ? 'Hakuna tahadhari zilizothibitishwa kwa sasa.'
                                    : 'Hakuna tahadhari za $_region kwa sasa.',
                                style: TextStyle(color: Colors.grey[600]),
                              ),
                            ),
                          ],
                        )
                      : ListView.builder(
                          padding: const EdgeInsets.all(16),
                          itemCount: _alerts.length,
                          itemBuilder: (context, index) =>
                              _AlertCard(alert: _alerts[index]),
                        ),
                ),
        ),
      ],
    );
  }

  void _showReportForm() {
    final formKey = GlobalKey<FormState>();
    final name = TextEditingController();
    final dealer = TextEditingController();
    final description = TextEditingController();
    String region = _region ?? _regions.first;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (sheetContext) => Padding(
        padding: EdgeInsets.only(
          bottom: MediaQuery.of(sheetContext).viewInsets.bottom + 16,
          left: 16,
          right: 16,
          top: 16,
        ),
        child: Form(
          key: formKey,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Text('Ripoti Dawa Feki',
                  style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold)),
              const SizedBox(height: 12),
              TextFormField(
                controller: name,
                decoration: const InputDecoration(
                    labelText: 'Jina la bidhaa', border: OutlineInputBorder()),
                validator: (v) =>
                    (v?.trim().isEmpty ?? true) ? 'Tafadhali jaza' : null,
              ),
              const SizedBox(height: 10),
              TextFormField(
                controller: dealer,
                decoration: const InputDecoration(
                    labelText: 'Duka/agrovet (hiari)',
                    border: OutlineInputBorder()),
              ),
              const SizedBox(height: 10),
              DropdownButtonFormField<String>(
                initialValue: region,
                decoration: const InputDecoration(
                    labelText: 'Mkoa', border: OutlineInputBorder()),
                items: _regions
                    .map((r) => DropdownMenuItem(value: r, child: Text(r)))
                    .toList(),
                onChanged: (v) => region = v ?? region,
              ),
              const SizedBox(height: 10),
              TextFormField(
                controller: description,
                maxLines: 3,
                decoration: const InputDecoration(
                    labelText: 'Eleza dalili za ubandia ulizoziona',
                    border: OutlineInputBorder()),
                validator: (v) => (v?.trim().length ?? 0) < 10
                    ? 'Eleza kwa ufupi (angalau herufi 10)'
                    : null,
              ),
              const SizedBox(height: 14),
              SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton(
                  onPressed: () async {
                    if (!(formKey.currentState?.validate() ?? false)) return;
                    try {
                      final api = context.read<ApiService>();
                      final response = await api.post('/inputs/report', data: {
                        'product_name': name.text.trim(),
                        'dealer_name': dealer.text.trim().isEmpty
                            ? null
                            : dealer.text.trim(),
                        'region': region,
                        'description': description.text.trim(),
                      });
                      if (!sheetContext.mounted) return;
                      Navigator.pop(sheetContext);
                      if (!mounted) return;
                      // ignore: use_build_context_synchronously
                      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                          content: Text(
                              '${response.data['message'] ?? 'Ripoti imepokelewa.'}')));
                    } catch (_) {
                      if (!sheetContext.mounted) return;
                      ScaffoldMessenger.of(sheetContext).showSnackBar(
                          const SnackBar(
                              content:
                                  Text('Imeshindikana. Jaribu tena.')));
                    }
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: MkColors.danger,
                    foregroundColor: Colors.white,
                  ),
                  child: const Text('Wasilisha Ripoti'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

/* ======================= Shared widgets ======================= */

Widget _statusIcon(String status) => switch (status) {
      'registered' => const Icon(Icons.verified, color: Colors.green),
      'banned' => const Icon(Icons.dangerous, color: Colors.red),
      _ => const Icon(Icons.remove_circle_outline, color: Colors.orange),
    };

Widget _statusChip(String status) {
  final (label, color) = switch (status) {
    'registered' => ('IMESAJILIWA', Colors.green),
    'banned' => ('MARUFUKU', Colors.red),
    _ => ('IMEONDOLEWA', Colors.orange),
  };
  return Chip(
    label: Text(label,
        style: const TextStyle(color: Colors.white, fontSize: 10)),
    backgroundColor: color,
    padding: EdgeInsets.zero,
  );
}

class _VerdictBanner extends StatelessWidget {
  final String verdict;

  const _VerdictBanner({required this.verdict});

  @override
  Widget build(BuildContext context) {
    final (text, color, icon) = switch (verdict) {
      'found_registered' => (
          'IMEPATIKANA KWENYE ORODHA',
          Colors.green,
          Icons.verified
        ),
      'banned' => ('IMEPIGWA MARUFUKU', Colors.red, Icons.dangerous),
      'withdrawn' => (
          'USAJILI UMEONDOLEWA',
          Colors.orange,
          Icons.remove_circle
        ),
      'registry_empty' => (
          'ORODHA BADO INAJAZWA',
          Colors.blueGrey,
          Icons.hourglass_top
        ),
      _ => ('HAIKUPATIKANA — DALILI YA HATARI', Colors.red, Icons.warning),
    };

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: color.withValues(alpha: .12),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color),
      ),
      child: Row(
        children: [
          Icon(icon, color: color),
          const SizedBox(width: 10),
          Expanded(
            child: Text(text,
                style: TextStyle(fontWeight: FontWeight.bold, color: color)),
          ),
        ],
      ),
    );
  }
}

class _GuidanceCard extends StatelessWidget {
  final String text;

  const _GuidanceCard({required this.text});

  @override
  Widget build(BuildContext context) {
    return Card(
      color: Colors.amber[50],
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Icon(Icons.info_outline, color: Colors.amber[900], size: 20),
            const SizedBox(width: 8),
            Expanded(
                child: Text(text,
                    style: const TextStyle(fontSize: 13, height: 1.4))),
          ],
        ),
      ),
    );
  }
}

class _AlertCard extends StatelessWidget {
  final dynamic alert;

  const _AlertCard({required this.alert});

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 10),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: BorderSide(color: Colors.red.withValues(alpha: .35)),
      ),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                const Icon(Icons.campaign, color: Colors.red, size: 18),
                const SizedBox(width: 6),
                Expanded(
                  child: Text('${alert['product_name']}',
                      style: const TextStyle(fontWeight: FontWeight.bold)),
                ),
              ],
            ),
            const SizedBox(height: 4),
            Text(
              [
                '${alert['region']}',
                if (alert['district'] != null) '${alert['district']}',
                if (alert['dealer_name'] != null)
                  'Duka: ${alert['dealer_name']}',
              ].join(' · '),
              style: TextStyle(fontSize: 12, color: Colors.grey[600]),
            ),
            const SizedBox(height: 6),
            Text('${alert['description']}',
                style: const TextStyle(fontSize: 13)),
          ],
        ),
      ),
    );
  }
}
