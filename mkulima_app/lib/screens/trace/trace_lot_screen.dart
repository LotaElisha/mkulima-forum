import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../core/theme.dart';
import '../../services/api_service.dart';
import '../../trace/trace_api.dart';
import '../../trace/trace_ids.dart';
import '../../trace/trace_strings.dart';
import '../../trace/trace_sync.dart';
import '../../widgets/mk_error_state.dart';
import '../../widgets/mk_offline_banner.dart';
import '../../widgets/mk_skeleton.dart';
import 'trace_confirm_screen.dart';
import 'trace_forms.dart';
import 'trace_widgets.dart';

/// One lot: its QR code, who holds it, where it came from and went, and
/// every recorded step. What is shown depends on who is looking; the server
/// decides that, and the phone shows the last copy it was given when offline.
class TraceLotScreen extends StatefulWidget {
  final String uuid;
  final String? code;
  const TraceLotScreen({super.key, required this.uuid, this.code});

  @override
  State<TraceLotScreen> createState() => _TraceLotScreenState();
}

class _TraceLotScreenState extends State<TraceLotScreen> {
  final _store = TraceSync.instance;
  Map<String, dynamic>? _lot;
  bool _loading = true;
  bool _fromCache = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _store.addListener(_changed);
    _load();
  }

  @override
  void dispose() {
    _store.removeListener(_changed);
    super.dispose();
  }

  void _changed() {
    if (mounted) setState(() {});
  }

  Future<void> _load() async {
    final api = context.read<ApiService>();
    await _store.load();
    _lot = _store.lot(widget.code ?? widget.uuid) ?? _store.lot(widget.uuid);
    if (mounted) setState(() => _loading = _lot == null);
    try {
      final trace = TraceApi(api);
      if (widget.uuid.isEmpty && widget.code != null) {
        // Opened from a scan with only the public summary: ask by code.
        final scanned = await trace.scan(widget.code!);
        _lot = scanned['lot'] is Map
            ? Map<String, dynamic>.from(scanned['lot'] as Map)
            : _lot;
      } else {
        _lot = await trace.lot(widget.uuid);
      }
      _fromCache = false;
      _error = null;
    } catch (e) {
      _fromCache = _lot != null;
      final err = traceError(e);
      // 404 for a lot made offline means it has not reached the server yet.
      if (_lot == null && !(err.statusCode == 404 && _localOps.isNotEmpty)) {
        _error = err.message;
      }
    }
    if (mounted) setState(() => _loading = false);
  }

  List<TraceOp> get _localOps =>
      _store.unsentFor(uuid: widget.uuid, code: widget.code);

  Future<void> _open(Widget screen) async {
    await Navigator.of(context).push(MaterialPageRoute(builder: (_) => screen));
    if (mounted) _load();
  }

  @override
  Widget build(BuildContext context) {
    final lot = _lot;
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.code ?? lot?['code'] as String? ?? 'Mzigo'),
      ),
      body: Column(
        children: [
          const MkOfflineBanner(),
          Expanded(
            child: _loading
                ? const MkListSkeleton()
                : lot == null
                ? (_localOps.isNotEmpty
                      ? _LocalLot(ops: _localOps, code: widget.code)
                      : MkErrorState(message: _error, onRetry: _load))
                : RefreshIndicator(onRefresh: _load, child: _details(lot)),
          ),
        ],
      ),
    );
  }

  Widget _details(Map<String, dynamic> lot) {
    final code = lot['code'] as String;
    final full = lot['tier'] == 'full';
    final isPublic = lot['tier'] == 'public';
    final title = (lot['product'] as String?)?.isNotEmpty == true
        ? lot['product'] as String
        : '${lot['crop']}';
    final canAct = lot['i_hold'] == true && lot['status'] == 'active';
    final timeline = (lot['timeline'] as List? ?? const [])
        .whereType<Map>()
        .map((m) => Map<String, dynamic>.from(m))
        .toList();
    final parents = (lot['parents'] as List? ?? const [])
        .whereType<Map>()
        .toList();
    final children = (lot['children'] as List? ?? const [])
        .whereType<Map>()
        .toList();
    final local = _localOps;

    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
      children: [
        if (_fromCache)
          const TraceChip.info(
            'Taarifa zilizohifadhiwa kwenye simu',
            icon: Icons.history,
          ),
        const SizedBox(height: 8),
        Text(
          title,
          style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w800),
        ),
        const SizedBox(height: 6),
        Wrap(
          spacing: 8,
          runSpacing: 6,
          children: [
            TraceChip(
              TraceStrings.lotStatus[lot['status']] ?? '${lot['status']}',
            ),
            if (lot['awaiting_me'] == true)
              const TraceChip.warning('Inasubiri uthibitisho wako'),
            if (lot['anomalies'] is Map &&
                (lot['anomalies'] as Map)['reviewed'] != true)
              const TraceChip.danger('Inakaguliwa na msimamizi'),
          ],
        ),
        const SizedBox(height: 16),
        Center(child: TraceQr(code)),
        Center(
          child: SelectableText(
            code,
            style: const TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.w800,
              letterSpacing: 3,
            ),
          ),
        ),
        const SizedBox(height: 8),
        OutlinedButton.icon(
          onPressed: () => launchUrl(
            Uri.parse(TraceIds.labelLink(code)),
            mode: LaunchMode.externalApplication,
          ),
          icon: const Icon(Icons.print_outlined),
          label: const Text('Chapisha au pakua lebo ya QR'),
          style: OutlinedButton.styleFrom(
            minimumSize: const Size.fromHeight(48),
          ),
        ),
        const SizedBox(height: 16),
        if (isPublic)
          ..._publicSummary(lot)
        else ...[
          TraceField(
            'Kiasi',
            '${lot['quantity']} ${TraceStrings.unit(lot['unit'] as String?)} (${traceKg(lot['quantity_kg'])})',
          ),
          TraceField('Daraja', lot['grade'] as String?),
          TraceField('Tarehe ya kuvuna', lot['harvested_on'] as String?),
          TraceField(
            'Msimu',
            (lot['season'] as Map?)?['season_label'] as String?,
          ),
          TraceField('Mkoa', (lot['plot'] as Map?)?['region'] as String?),
          TraceField('Wilaya', (lot['plot'] as Map?)?['district'] as String?),
          TraceField('Kijiji', (lot['plot'] as Map?)?['village'] as String?),
          TraceField('Mkulima', _who(lot['grower'])),
          TraceField(
            'Simu ya mkulima',
            (lot['grower'] as Map?)?['phone'] as String?,
          ),
          TraceField('Anayeshikilia sasa', _who(lot['holder'])),
          TraceField('Anasubiri kupokea', _who(lot['pending_holder'])),
        ],
        if (local.isNotEmpty) ...[
          const SizedBox(height: 12),
          const Text(
            'Bado kwenye simu',
            style: TextStyle(fontWeight: FontWeight.w700),
          ),
          for (final op in local)
            ListTile(
              contentPadding: EdgeInsets.zero,
              title: Text(op.label),
              subtitle: Align(
                alignment: Alignment.centerLeft,
                child: TraceOpChip(op),
              ),
            ),
        ],
        if (lot['awaiting_me'] == true) ...[
          const SizedBox(height: 12),
          FilledButton.icon(
            onPressed: () => _open(const TraceConfirmScreen()),
            icon: const Icon(Icons.fact_check_outlined),
            label: const Text('Thibitisha au pinga makabidhiano'),
            style: FilledButton.styleFrom(
              minimumSize: const Size.fromHeight(48),
            ),
          ),
        ],
        if (canAct) ...[
          const SizedBox(height: 16),
          FilledButton.icon(
            onPressed: () => _open(TraceHandoffScreen(lot: lot)),
            icon: const Icon(Icons.swap_horiz),
            label: const Text('Kabidhi mzigo'),
            style: FilledButton.styleFrom(
              minimumSize: const Size.fromHeight(48),
            ),
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              Expanded(
                child: OutlinedButton(
                  onPressed: () => _open(TraceEventScreen(lot: lot)),
                  style: OutlinedButton.styleFrom(
                    minimumSize: const Size.fromHeight(48),
                  ),
                  child: const Text('Rekodi hatua'),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: OutlinedButton(
                  onPressed: () => _open(TraceSplitScreen(lot: lot)),
                  style: OutlinedButton.styleFrom(
                    minimumSize: const Size.fromHeight(48),
                  ),
                  child: const Text('Gawanya'),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: OutlinedButton(
                  onPressed: () => _open(TraceProcessScreen(lot: lot)),
                  style: OutlinedButton.styleFrom(
                    minimumSize: const Size.fromHeight(48),
                  ),
                  child: const Text('Sindika'),
                ),
              ),
            ],
          ),
        ] else if (lot['i_hold'] != true &&
            !isPublic &&
            lot['status'] == 'active') ...[
          const SizedBox(height: 12),
          OutlinedButton.icon(
            onPressed: () =>
                _open(TraceHandoffScreen(lot: lot, asReceiver: true)),
            icon: const Icon(Icons.call_received),
            label: const Text('Nimepokea mzigo huu'),
            style: OutlinedButton.styleFrom(
              minimumSize: const Size.fromHeight(48),
            ),
          ),
        ],
        if (parents.isNotEmpty || children.isNotEmpty) ...[
          const SizedBox(height: 20),
          const Text(
            'Asili na mgawanyo',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700),
          ),
          for (final p in parents)
            _LinkTile(link: p, from: true, onOpen: _open),
          for (final c in children)
            _LinkTile(link: c, from: false, onOpen: _open),
        ],
        if (timeline.isNotEmpty) ...[
          const SizedBox(height: 20),
          const Text(
            'Historia',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700),
          ),
          const SizedBox(height: 8),
          for (final e in timeline) _EventTile(event: e, full: full),
        ],
        const TraceNotice(),
      ],
    );
  }

  List<Widget> _publicSummary(Map<String, dynamic> lot) => [
    TraceField('Zao', lot['crop'] as String?),
    TraceField('Daraja', lot['grade'] as String?),
    TraceField('Mkoa', lot['region'] as String?),
    TraceField('Mwezi wa mavuno', lot['harvest_month'] as String?),
    TraceField('Hatua zilizorekodiwa', '${lot['steps'] ?? 0}'),
    TraceField(
      'Makabidhiano yaliyothibitishwa',
      '${lot['handoffs_confirmed'] ?? 0} / ${lot['handoffs'] ?? 0}',
    ),
    const Padding(
      padding: EdgeInsets.only(top: 8),
      child: Text(
        'Unaona muhtasari tu. Walio kwenye mnyororo wa mzigo huu wanaona historia kamili.',
        style: TextStyle(color: MkColors.muted),
      ),
    ),
  ];
}

String? _who(Object? party) {
  if (party is! Map) return null;
  final type = TraceStrings.type(party['type'] as String?);
  final name = party['name'] as String?;
  final verified = party['verified'] == true ? ' ✓' : '';
  return name == null || name.isEmpty
      ? '$type$verified'
      : '$name ($type)$verified';
}

class _LinkTile extends StatelessWidget {
  final Map link;
  final bool from;
  final Future<void> Function(Widget) onOpen;
  const _LinkTile({
    required this.link,
    required this.from,
    required this.onOpen,
  });

  @override
  Widget build(BuildContext context) {
    final relation = TraceStrings.event(link['relation'] as String?);
    return ListTile(
      contentPadding: EdgeInsets.zero,
      leading: Icon(
        from ? Icons.call_merge : Icons.call_split,
        color: MkColors.primary,
      ),
      title: Text(
        '${from ? 'Kutoka' : 'Kwenda'} ${link['code']} · ${link['product'] ?? link['crop']}',
      ),
      subtitle: Text(
        '$relation · ${traceKg(link['link_kg'] ?? link['quantity_kg'])}',
      ),
      trailing: const Icon(Icons.chevron_right),
      onTap: () => onOpen(
        TraceLotScreen(uuid: '${link['uuid']}', code: link['code'] as String?),
      ),
    );
  }
}

class _EventTile extends StatelessWidget {
  final Map<String, dynamic> event;
  final bool full;
  const _EventTile({required this.event, required this.full});

  @override
  Widget build(BuildContext context) {
    final type = event['type'] as String?;
    final status = event['confirmation_status'] as String?;
    final payment = event['payment'] as Map?;
    final lines = <String>[
      if (event['from'] != null && event['to'] != null)
        '${_who(event['from'])} → ${_who(event['to'])}'
      else if (event['recorded_by'] != null)
        'Na: ${_who(event['recorded_by'])}',
      if (event['transporter'] != null)
        'Msafirishaji: ${_who(event['transporter'])}',
      if (event['quantity_kg'] != null)
        'Kiasi: ${traceKg(event['quantity_kg'])}',
      if (event['received_kg'] != null && type == 'handoff')
        'Kilichopokelewa: ${traceKg(event['received_kg'])}',
      if (event['loss_kg'] != null)
        'Upotevu: ${traceKg(event['loss_kg'])}${event['loss_reason'] != null ? ' (${event['loss_reason']})' : ''}',
      if (event['discrepancy_kg'] != null)
        'Tofauti isiyoelezwa: ${traceKg(event['discrepancy_kg'])}',
      if (event['grade'] != null) 'Daraja: ${event['grade']}',
      if (event['moisture_percent'] != null)
        'Unyevu: ${event['moisture_percent']}%',
      if (event['quality_notes'] != null) 'Ubora: ${event['quality_notes']}',
      if (event['location_label'] != null) 'Mahali: ${event['location_label']}',
      if (event['price_per_kg'] != null)
        'Bei: ${event['price_per_kg']} ${event['currency'] ?? ''} kwa kilo',
      if (payment != null)
        'Malipo: ${TraceStrings.paymentMethods[payment['method']] ?? payment['method'] ?? ''} ${payment['reference'] ?? ''}'
            .trim(),
      if (event['notes'] != null) '${event['notes']}',
      if (event['dispute_reason'] != null)
        'Sababu ya kupinga: ${event['dispute_reason']}',
    ];
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        border: Border.all(color: MkColors.border),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  '${TraceStrings.event(type)}${event['inherited'] == true ? ' · ${event['lot_code']}' : ''}',
                  style: const TextStyle(
                    fontWeight: FontWeight.w700,
                    fontSize: 16,
                  ),
                ),
              ),
              Text(
                traceDate(event['occurred_at']),
                style: const TextStyle(color: MkColors.muted, fontSize: 13),
              ),
            ],
          ),
          const SizedBox(height: 4),
          for (final l in lines) Text(l, style: const TextStyle(height: 1.4)),
          if (payment != null)
            const Text(
              TraceStrings.paymentNote,
              style: TextStyle(color: MkColors.muted, fontSize: 13),
            ),
          if (status == 'pending')
            const Padding(
              padding: EdgeInsets.only(top: 6),
              child: TraceChip.warning(
                'Inasubiri uthibitisho wa upande wa pili',
              ),
            )
          else if (status == 'confirmed')
            const Padding(
              padding: EdgeInsets.only(top: 6),
              child: TraceChip(
                'Imethibitishwa na pande zote',
                icon: Icons.verified_outlined,
              ),
            )
          else if (status == 'disputed')
            const Padding(
              padding: EdgeInsets.only(top: 6),
              child: TraceChip.danger('Imepingwa'),
            ),
          if (full && event['has_evidence'] == true)
            const Padding(
              padding: EdgeInsets.only(top: 6),
              child: TraceChip.info('Ina ushahidi', icon: Icons.attach_file),
            ),
        ],
      ),
    );
  }
}

/// A lot made on this phone that has not reached the server yet.
class _LocalLot extends StatelessWidget {
  final List<TraceOp> ops;
  final String? code;
  const _LocalLot({required this.ops, this.code});

  @override
  Widget build(BuildContext context) {
    final first = ops.first;
    final lotCode = code ?? first.lotCode;
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        Text(
          first.label,
          style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800),
        ),
        const SizedBox(height: 8),
        TraceOpChip(first),
        if (first.error != null)
          Padding(
            padding: const EdgeInsets.only(top: 8),
            child: Text(
              first.error!,
              style: const TextStyle(color: MkColors.danger),
            ),
          ),
        const SizedBox(height: 16),
        if (lotCode != null) ...[
          Center(child: TraceQr(lotCode)),
          Center(
            child: SelectableText(
              lotCode,
              style: const TextStyle(
                fontSize: 22,
                fontWeight: FontWeight.w800,
                letterSpacing: 3,
              ),
            ),
          ),
          const SizedBox(height: 8),
          const Text(
            'Unaweza kuandika msimbo huu kwenye gunia sasa. Mzigo utaonekana kwa wengine baada ya kutumwa.',
            textAlign: TextAlign.center,
            style: TextStyle(color: MkColors.muted),
          ),
        ],
        const TraceNotice(),
      ],
    );
  }
}
