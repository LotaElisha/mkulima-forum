import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/regions.dart';
import '../../core/theme.dart';
import '../../providers/connectivity_provider.dart';
import '../../services/api_service.dart';
import '../../services/location_service.dart';
import '../../trace/trace_api.dart';
import '../../trace/trace_ids.dart';
import '../../trace/trace_strings.dart';
import '../../trace/trace_sync.dart';
import 'trace_lot_screen.dart';
import 'trace_widgets.dart';

/// A farmer's plots and their seasons. Each plot has its own QR code; a
/// season is what a harvest is recorded against.
class TraceFarmScreen extends StatefulWidget {
  const TraceFarmScreen({super.key});

  @override
  State<TraceFarmScreen> createState() => _TraceFarmScreenState();
}

class _TraceFarmScreenState extends State<TraceFarmScreen> {
  final _store = TraceSync.instance;

  @override
  void initState() {
    super.initState();
    _store.addListener(_changed);
    _reload();
  }

  @override
  void dispose() {
    _store.removeListener(_changed);
    super.dispose();
  }

  void _changed() {
    if (mounted) setState(() {});
  }

  Future<void> _reload() async {
    try {
      await TraceApi(context.read<ApiService>()).plots();
    } catch (_) {
      // Offline: the copy on the phone is shown.
    }
  }

  Future<void> _open(Widget screen) async {
    await Navigator.of(context).push(MaterialPageRoute(builder: (_) => screen));
    if (mounted) _reload();
  }

  @override
  Widget build(BuildContext context) {
    final plots = _store.plots;
    final localPlots = _store.unsent.where((o) => o.kind == 'plot').toList();
    return Scaffold(
      appBar: AppBar(title: const Text('Mashamba yangu')),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _open(const _PlotForm()),
        icon: const Icon(Icons.add),
        label: const Text('Ongeza shamba'),
      ),
      body: RefreshIndicator(
        onRefresh: _reload,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 96),
          children: [
            if (plots.isEmpty && localPlots.isEmpty)
              const Padding(
                padding: EdgeInsets.symmetric(vertical: 32),
                child: Text(
                  'Bado hujasajili shamba. Ongeza shamba, kisha msimu wa zao, ndipo urekodi mavuno.',
                  textAlign: TextAlign.center,
                  style: TextStyle(color: MkColors.muted, height: 1.45),
                ),
              ),
            for (final op in localPlots)
              Card(
                child: ListTile(
                  leading: const Icon(Icons.phone_android),
                  title: Text(op.label),
                  subtitle: Align(
                    alignment: Alignment.centerLeft,
                    child: TraceOpChip(op),
                  ),
                ),
              ),
            for (final plot in plots) _PlotCard(plot: plot, onOpen: _open),
          ],
        ),
      ),
    );
  }
}

class _PlotCard extends StatelessWidget {
  final Map<String, dynamic> plot;
  final Future<void> Function(Widget) onOpen;
  const _PlotCard({required this.plot, required this.onOpen});

  @override
  Widget build(BuildContext context) {
    final seasons = (plot['seasons'] as List? ?? const [])
        .whereType<Map>()
        .map((m) => Map<String, dynamic>.from(m))
        .toList();
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        '${plot['name']}',
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      Text(
                        [plot['village'], plot['district'], plot['region']]
                            .whereType<String>()
                            .where((s) => s.isNotEmpty)
                            .join(', '),
                        style: const TextStyle(color: MkColors.muted),
                      ),
                      if (plot['area_acres'] != null)
                        Text(
                          'Ekari ${plot['area_acres']}',
                          style: const TextStyle(color: MkColors.muted),
                        ),
                    ],
                  ),
                ),
                IconButton(
                  tooltip: 'QR ya shamba',
                  icon: const Icon(Icons.qr_code_2),
                  onPressed: () => showModalBottomSheet<void>(
                    context: context,
                    showDragHandle: true,
                    builder: (_) => Padding(
                      padding: const EdgeInsets.fromLTRB(24, 0, 24, 32),
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          TraceQr(plot['code'] as String, size: 220),
                          const SizedBox(height: 8),
                          SelectableText(
                            '${plot['code']}',
                            style: const TextStyle(
                              fontSize: 22,
                              fontWeight: FontWeight.w800,
                              letterSpacing: 3,
                            ),
                          ),
                          const SizedBox(height: 8),
                          const Text(
                            'QR hii haina jina lako wala mahali shamba lilipo.',
                            textAlign: TextAlign.center,
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),
            const Divider(height: 24),
            if (seasons.isEmpty)
              const Text(
                'Hakuna msimu bado.',
                style: TextStyle(color: MkColors.muted),
              ),
            for (final s in seasons)
              ListTile(
                contentPadding: EdgeInsets.zero,
                title: Text(
                  '${s['crop']} · ${s['season_label']}',
                  style: const TextStyle(fontWeight: FontWeight.w600),
                ),
                subtitle: Text(
                  (s['observations'] as num? ?? 0) > 0
                      ? 'Uchunguzi wa mimea ${s['observations']} umeambatanishwa'
                      : 'Hakuna uchunguzi wa mimea ulioambatanishwa',
                ),
                trailing: PopupMenuButton<String>(
                  tooltip: 'Chaguo',
                  onSelected: (v) {
                    if (v == 'harvest') {
                      onOpen(
                        TraceHarvestScreen(seasonUuid: s['uuid'] as String),
                      );
                    }
                    if (v == 'scan') _linkScan(context, s);
                  },
                  itemBuilder: (_) => const [
                    PopupMenuItem(
                      value: 'harvest',
                      child: Text('Rekodi mavuno'),
                    ),
                    PopupMenuItem(
                      value: 'scan',
                      child: Text('Ambatanisha uchunguzi wa mmea'),
                    ),
                  ],
                ),
              ),
            const SizedBox(height: 8),
            OutlinedButton.icon(
              onPressed: () => onOpen(_SeasonForm(plot: plot)),
              icon: const Icon(Icons.add),
              label: const Text('Ongeza msimu'),
              style: OutlinedButton.styleFrom(
                minimumSize: const Size.fromHeight(44),
              ),
            ),
          ],
        ),
      ),
    );
  }

  /// Attach a Plant AI scan to a season. Only the farmer's own scans, and
  /// only after they tick the consent box.
  Future<void> _linkScan(
    BuildContext context,
    Map<String, dynamic> season,
  ) async {
    final api = context.read<ApiService>();
    final messenger = ScaffoldMessenger.of(context);
    List<dynamic> scans;
    try {
      scans = await api.getDiseaseHistory();
    } catch (e) {
      messenger.showSnackBar(SnackBar(content: Text(traceError(e).message)));
      return;
    }
    if (!context.mounted) return;
    if (scans.isEmpty) {
      messenger.showSnackBar(
        const SnackBar(content: Text('Hujafanya uchunguzi wa mmea bado.')),
      );
      return;
    }
    await showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      showDragHandle: true,
      builder: (_) => _LinkScanSheet(api: api, season: season, scans: scans),
    );
  }
}

class _LinkScanSheet extends StatefulWidget {
  final ApiService api;
  final Map<String, dynamic> season;
  final List<dynamic> scans;
  const _LinkScanSheet({
    required this.api,
    required this.season,
    required this.scans,
  });

  @override
  State<_LinkScanSheet> createState() => _LinkScanSheetState();
}

class _LinkScanSheetState extends State<_LinkScanSheet> {
  String? _scan;
  bool _consent = false;
  bool _busy = false;

  Future<void> _save() async {
    setState(() => _busy = true);
    final messenger = ScaffoldMessenger.of(context);
    final nav = Navigator.of(context);
    try {
      await widget.api.post(
        '/v1/trace/seasons/${widget.season['uuid']}/observations',
        data: {'scan_uuid': _scan, 'consent': _consent},
      );
      messenger.showSnackBar(
        const SnackBar(content: Text('Uchunguzi umeambatanishwa na msimu.')),
      );
      nav.pop();
    } catch (e) {
      messenger.showSnackBar(SnackBar(content: Text(traceError(e).message)));
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.fromLTRB(
        16,
        0,
        16,
        MediaQuery.paddingOf(context).bottom + 16,
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(
            'Msimu: ${widget.season['crop']} · ${widget.season['season_label']}',
            style: const TextStyle(fontWeight: FontWeight.w700),
          ),
          const SizedBox(height: 8),
          ConstrainedBox(
            constraints: BoxConstraints(
              maxHeight: MediaQuery.sizeOf(context).height * .4,
            ),
            child: RadioGroup<String>(
              groupValue: _scan,
              onChanged: (v) => setState(() => _scan = v),
              child: ListView(
                shrinkWrap: true,
                children: [
                  for (final s in widget.scans.whereType<Map>())
                    RadioListTile<String>(
                      value: '${s['uuid']}',
                      title: Text(
                        '${s['disease_name'] ?? 'Uchunguzi wa mmea'}',
                      ),
                      subtitle: Text(traceDate(s['created_at'])),
                    ),
                ],
              ),
            ),
          ),
          CheckboxListTile(
            value: _consent,
            contentPadding: EdgeInsets.zero,
            onChanged: (v) => setState(() => _consent = v ?? false),
            title: const Text(
              'Nakubali uchunguzi huu uonekane kwenye historia ya msimu huu kwa walio kwenye mnyororo wa mzigo.',
            ),
          ),
          FilledButton(
            onPressed: _busy || _scan == null || !_consent ? null : _save,
            style: FilledButton.styleFrom(
              minimumSize: const Size.fromHeight(48),
            ),
            child: const Text('Ambatanisha'),
          ),
        ],
      ),
    );
  }
}

class _PlotForm extends StatefulWidget {
  const _PlotForm();

  @override
  State<_PlotForm> createState() => _PlotFormState();
}

class _PlotFormState extends State<_PlotForm> {
  final _form = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _acres = TextEditingController();
  final _district = TextEditingController();
  final _village = TextEditingController();
  String? _region;
  ({double lat, double lng})? _point;
  bool _sharePoint = false;
  bool _busy = false;

  @override
  void dispose() {
    for (final c in [_name, _acres, _district, _village]) {
      c.dispose();
    }
    super.dispose();
  }

  Future<void> _locate() async {
    final p = await LocationService.approximate();
    if (!mounted) return;
    setState(() => _point = p);
    if (p == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Mahali hapakupatikana. Unaweza kuendelea bila hapo.'),
        ),
      );
    }
  }

  Future<void> _submit() async {
    if (!_form.currentState!.validate()) return;
    setState(() => _busy = true);
    final name = _name.text.trim();
    final op = TraceOp(
      id: TraceIds.uuid(),
      kind: 'plot',
      path: '/v1/trace/plots',
      label: 'Shamba: $name',
      body: {
        'uuid': TraceIds.uuid(),
        'name': name,
        'region': _region,
        if (traceNumber(_acres) != null) 'area_acres': traceNumber(_acres),
        if (traceText(_district) != null) 'district': traceText(_district),
        if (traceText(_village) != null) 'village': traceText(_village),
        if (_point != null) 'lat': _point!.lat,
        if (_point != null) 'lng': _point!.lng,
        'share_location_with_chain': _sharePoint,
      },
    );
    final result = await traceSubmit(context, op);
    if (!mounted) return;
    setState(() => _busy = false);
    if (result != null) Navigator.of(context).pop();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Ongeza shamba')),
      body: TraceFormBody(
        formKey: _form,
        busy: _busy,
        submitLabel: 'Hifadhi shamba',
        onSubmit: _submit,
        children: [
          TextFormField(
            controller: _name,
            decoration: const InputDecoration(labelText: 'Jina la shamba'),
            validator: (v) =>
                (v ?? '').trim().isEmpty ? 'Andika jina la shamba' : null,
          ),
          const SizedBox(height: 12),
          TextFormField(
            controller: _acres,
            keyboardType: const TextInputType.numberWithOptions(decimal: true),
            decoration: const InputDecoration(
              labelText: 'Ukubwa kwa ekari (si lazima)',
            ),
          ),
          const SizedBox(height: 12),
          DropdownButtonFormField<String>(
            initialValue: _region,
            isExpanded: true,
            decoration: const InputDecoration(labelText: 'Mkoa'),
            items: [
              for (final r in tanzaniaRegions)
                DropdownMenuItem(value: r, child: Text(r)),
            ],
            onChanged: (v) => setState(() => _region = v),
            validator: (v) => v == null ? 'Chagua mkoa' : null,
          ),
          const SizedBox(height: 12),
          TextFormField(
            controller: _district,
            decoration: const InputDecoration(labelText: 'Wilaya (si lazima)'),
          ),
          const SizedBox(height: 12),
          TextFormField(
            controller: _village,
            decoration: const InputDecoration(labelText: 'Kijiji (si lazima)'),
          ),
          const SizedBox(height: 12),
          OutlinedButton.icon(
            onPressed: _locate,
            icon: const Icon(Icons.my_location),
            label: Text(
              _point == null
                  ? 'Ongeza mahali shamba lilipo (si lazima)'
                  : 'Mahali pameongezwa',
            ),
            style: OutlinedButton.styleFrom(
              minimumSize: const Size.fromHeight(48),
            ),
          ),
          if (_point != null)
            SwitchListTile(
              value: _sharePoint,
              contentPadding: EdgeInsets.zero,
              onChanged: (v) => setState(() => _sharePoint = v),
              title: const Text(
                'Ruhusu walio kwenye mnyororo wa mzigo kuona mahali shamba lilipo',
              ),
              subtitle: const Text(
                'Bila ruhusa hii wanaona mkoa na wilaya tu. QR haina mahali shamba lilipo.',
              ),
            ),
        ],
      ),
    );
  }
}

class _SeasonForm extends StatefulWidget {
  final Map<String, dynamic> plot;
  const _SeasonForm({required this.plot});

  @override
  State<_SeasonForm> createState() => _SeasonFormState();
}

class _SeasonFormState extends State<_SeasonForm> {
  final _form = GlobalKey<FormState>();
  final _crop = TextEditingController();
  final _label = TextEditingController(text: 'Masika ${DateTime.now().year}');
  DateTime? _planted;
  bool _busy = false;

  @override
  void dispose() {
    _crop.dispose();
    _label.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_form.currentState!.validate()) return;
    setState(() => _busy = true);
    final op = TraceOp(
      id: TraceIds.uuid(),
      kind: 'season',
      path: '/v1/trace/plots/${widget.plot['uuid']}/seasons',
      label: 'Msimu: ${_crop.text.trim()}',
      body: {
        'uuid': TraceIds.uuid(),
        'crop': _crop.text.trim().toLowerCase(),
        'season_label': _label.text.trim(),
        if (_planted != null)
          'planted_on': _planted!.toIso8601String().substring(0, 10),
      },
    );
    final result = await traceSubmit(context, op);
    if (!mounted) return;
    setState(() => _busy = false);
    if (result != null) Navigator.of(context).pop();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Msimu mpya · ${widget.plot['name']}')),
      body: TraceFormBody(
        formKey: _form,
        busy: _busy,
        submitLabel: 'Hifadhi msimu',
        onSubmit: _submit,
        children: [
          TextFormField(
            controller: _crop,
            decoration: const InputDecoration(
              labelText: 'Zao (mf. mahindi, mpunga, kahawa)',
            ),
            validator: (v) => (v ?? '').trim().isEmpty ? 'Andika zao' : null,
          ),
          const SizedBox(height: 12),
          TextFormField(
            controller: _label,
            decoration: const InputDecoration(labelText: 'Jina la msimu'),
            validator: (v) =>
                (v ?? '').trim().isEmpty ? 'Andika jina la msimu' : null,
          ),
          const SizedBox(height: 12),
          OutlinedButton.icon(
            onPressed: () async {
              final d = await showDatePicker(
                context: context,
                firstDate: DateTime(DateTime.now().year - 2),
                lastDate: DateTime.now(),
                initialDate: _planted ?? DateTime.now(),
              );
              if (d != null) setState(() => _planted = d);
            },
            icon: const Icon(Icons.event),
            label: Text(
              _planted == null
                  ? 'Tarehe ya kupanda (si lazima)'
                  : 'Ilipandwa ${_planted!.day}/${_planted!.month}/${_planted!.year}',
            ),
            style: OutlinedButton.styleFrom(
              minimumSize: const Size.fromHeight(48),
            ),
          ),
        ],
      ),
    );
  }
}

/// Record a harvest: it becomes a new lot with its own QR code, held by
/// the farmer. Works offline; the code is made on the phone.
class TraceHarvestScreen extends StatefulWidget {
  final String? seasonUuid;
  const TraceHarvestScreen({super.key, this.seasonUuid});

  @override
  State<TraceHarvestScreen> createState() => _TraceHarvestScreenState();
}

class _TraceHarvestScreenState extends State<TraceHarvestScreen> {
  final _form = GlobalKey<FormState>();
  final _qty = TextEditingController();
  final _grade = TextEditingController();
  final _moisture = TextEditingController();
  final _notes = TextEditingController();
  String _unit = 'bag_90kg';
  String? _season;
  DateTime _date = DateTime.now();
  bool _busy = false;

  @override
  void initState() {
    super.initState();
    _season = widget.seasonUuid;
  }

  @override
  void dispose() {
    for (final c in [_qty, _grade, _moisture, _notes]) {
      c.dispose();
    }
    super.dispose();
  }

  List<(String, String)> get _seasons => [
    for (final plot in TraceSync.instance.plots)
      for (final s in (plot['seasons'] as List? ?? const []).whereType<Map>())
        (
          '${s['uuid']}',
          '${s['crop']} · ${s['season_label']} · ${plot['name']}',
        ),
  ];

  Future<void> _submit() async {
    if (!_form.currentState!.validate()) return;
    setState(() => _busy = true);
    final crop = _seasons
        .firstWhere((s) => s.$1 == _season, orElse: () => ('', 'Mavuno'))
        .$2
        .split(' · ')
        .first;
    final lotUuid = TraceIds.uuid();
    final code = TraceIds.code();
    final op = TraceOp(
      id: TraceIds.uuid(),
      kind: 'harvest',
      path: '/v1/trace/lots',
      label: 'Mavuno: $crop ${_qty.text.trim()} ${TraceStrings.unit(_unit)}',
      lotUuid: lotUuid,
      lotCode: code,
      body: {
        'season_uuid': _season,
        'uuid': lotUuid,
        'event_uuid': TraceIds.uuid(),
        'code': code,
        'quantity': traceNumber(_qty),
        'unit': _unit,
        'harvested_on': _date.toIso8601String().substring(0, 10),
        'occurred_at': DateTime.now().toUtc().toIso8601String(),
        if (traceText(_grade) != null) 'grade': traceText(_grade),
        if (traceNumber(_moisture) != null)
          'moisture_percent': traceNumber(_moisture),
        if (traceText(_notes) != null) 'notes': traceText(_notes),
      },
    );
    final result = await traceSubmit(context, op);
    if (!mounted) return;
    setState(() => _busy = false);
    if (result == null) return;
    if (result.data != null) {
      await TraceSync.instance.rememberLot(ApiService.v1Data(result.data));
    }
    if (!mounted) return;
    Navigator.of(context).pushReplacement(
      MaterialPageRoute(
        builder: (_) => TraceLotScreen(uuid: lotUuid, code: code),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final seasons = _seasons;
    final online = context.watch<ConnectivityProvider>().isOnline;
    return Scaffold(
      appBar: AppBar(title: const Text('Rekodi mavuno')),
      body: seasons.isEmpty
          ? Padding(
              padding: const EdgeInsets.all(24),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Text(
                    'Kwanza ongeza shamba na msimu wa zao kwenye "Mashamba yangu".',
                    textAlign: TextAlign.center,
                    style: TextStyle(height: 1.45),
                  ),
                  const SizedBox(height: 16),
                  FilledButton(
                    onPressed: () => Navigator.of(context).pushReplacement(
                      MaterialPageRoute(
                        builder: (_) => const TraceFarmScreen(),
                      ),
                    ),
                    style: FilledButton.styleFrom(
                      minimumSize: const Size.fromHeight(48),
                    ),
                    child: const Text('Mashamba yangu'),
                  ),
                ],
              ),
            )
          : TraceFormBody(
              formKey: _form,
              busy: _busy,
              submitLabel: online ? 'Hifadhi mavuno' : 'Hifadhi kwenye simu',
              onSubmit: _submit,
              children: [
                DropdownButtonFormField<String>(
                  initialValue: seasons.any((s) => s.$1 == _season)
                      ? _season
                      : null,
                  isExpanded: true,
                  decoration: const InputDecoration(labelText: 'Msimu'),
                  items: [
                    for (final s in seasons)
                      DropdownMenuItem(
                        value: s.$1,
                        child: Text(s.$2, overflow: TextOverflow.ellipsis),
                      ),
                  ],
                  onChanged: (v) => setState(() => _season = v),
                  validator: (v) => v == null ? 'Chagua msimu' : null,
                ),
                const SizedBox(height: 12),
                TraceQuantityInput(
                  controller: _qty,
                  unit: _unit,
                  onUnit: (u) => setState(() => _unit = u),
                ),
                const SizedBox(height: 12),
                OutlinedButton.icon(
                  onPressed: () async {
                    final d = await showDatePicker(
                      context: context,
                      firstDate: DateTime(DateTime.now().year - 1),
                      lastDate: DateTime.now(),
                      initialDate: _date,
                    );
                    if (d != null) setState(() => _date = d);
                  },
                  icon: const Icon(Icons.event),
                  label: Text(
                    'Tarehe ya kuvuna: ${_date.day}/${_date.month}/${_date.year}',
                  ),
                  style: OutlinedButton.styleFrom(
                    minimumSize: const Size.fromHeight(48),
                  ),
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _grade,
                  decoration: const InputDecoration(
                    labelText: 'Daraja (si lazima)',
                  ),
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _moisture,
                  keyboardType: const TextInputType.numberWithOptions(
                    decimal: true,
                  ),
                  decoration: const InputDecoration(
                    labelText: 'Unyevu % (si lazima)',
                  ),
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _notes,
                  maxLines: 3,
                  decoration: const InputDecoration(
                    labelText: 'Maelezo (si lazima)',
                  ),
                ),
                if (!online)
                  const Padding(
                    padding: EdgeInsets.only(top: 12),
                    child: TraceChip.warning(
                      TraceStrings.savedOffline,
                      icon: Icons.cloud_off,
                    ),
                  ),
              ],
            ),
    );
  }
}
