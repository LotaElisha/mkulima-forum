import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme.dart';
import '../../providers/connectivity_provider.dart';
import '../../services/api_service.dart';
import '../../trace/trace_api.dart';
import '../../trace/trace_ids.dart';
import '../../trace/trace_strings.dart';
import '../../trace/trace_sync.dart';
import 'trace_scan_screen.dart';
import 'trace_widgets.dart';

const _kgPerUnit = {
  'kg': 1.0,
  'tonne': 1000.0,
  'bag_50kg': 50.0,
  'bag_90kg': 90.0,
  'bag_100kg': 100.0,
};

double _toKg(double q, String unit) => q * (_kgPerUnit[unit] ?? 1);

/// Same allowance as the server: half a percent or half a kilo.
double _tolerance(double kg) => kg.abs() * 0.005 > 0.5 ? kg.abs() * 0.005 : 0.5;

String _now() => DateTime.now().toUtc().toIso8601String();

String _lotTitle(Map<String, dynamic> lot) =>
    (lot['product'] as String?)?.isNotEmpty == true
    ? lot['product'] as String
    : '${lot['crop']}';

/// A loss-reason box that appears once the numbers do not add up.
class _LossReason extends StatelessWidget {
  final TextEditingController controller;
  final double lossKg;
  const _LossReason({required this.controller, required this.lossKg});

  @override
  Widget build(BuildContext context) {
    if (lossKg <= 0) return const SizedBox.shrink();
    return Padding(
      padding: const EdgeInsets.only(top: 12),
      child: TextFormField(
        controller: controller,
        decoration: InputDecoration(
          labelText: 'Sababu ya upungufu wa ${traceKg(lossKg)}',
          helperText:
              'Lazima ueleze upungufu (mf. unyevu, kumwagika, mizani tofauti).',
        ),
        validator: (v) =>
            (v ?? '').trim().isEmpty ? 'Eleza sababu ya upungufu' : null,
      ),
    );
  }
}

// ── Hand-off ──────────────────────────────────────────────────────────

/// Hand a lot to the next participant, or (as receiver) record that you
/// took it. Custody moves only after the other side confirms.
class TraceHandoffScreen extends StatefulWidget {
  final Map<String, dynamic> lot;
  final bool asReceiver;
  const TraceHandoffScreen({
    super.key,
    required this.lot,
    this.asReceiver = false,
  });

  @override
  State<TraceHandoffScreen> createState() => _TraceHandoffScreenState();
}

class _TraceHandoffScreenState extends State<TraceHandoffScreen> {
  final _form = GlobalKey<FormState>();
  final _to = TextEditingController();
  final _qty = TextEditingController();
  final _loss = TextEditingController();
  final _price = TextEditingController();
  final _ref = TextEditingController();
  final _place = TextEditingController();
  final _notes = TextEditingController();
  late String _unit;
  String? _method;
  Map<String, dynamic>? _receiver;
  bool _busy = false;

  @override
  void initState() {
    super.initState();
    _unit = widget.lot['unit'] as String? ?? 'kg';
    _qty.text = '${widget.lot['quantity'] ?? ''}';
    _qty.addListener(() => setState(() {}));
  }

  @override
  void dispose() {
    for (final c in [_to, _qty, _loss, _price, _ref, _place, _notes]) {
      c.dispose();
    }
    super.dispose();
  }

  double get _lossKg {
    final q = traceNumber(_qty);
    final lotKg = (widget.lot['quantity_kg'] as num?)?.toDouble() ?? 0;
    if (q == null) return 0;
    final short = lotKg - _toKg(q, _unit);
    return short > _tolerance(lotKg) ? short : 0;
  }

  Future<void> _scanReceiver() async {
    final code = await Navigator.of(context).push<String>(
      MaterialPageRoute(builder: (_) => const TraceScanScreen(pickCode: true)),
    );
    if (code != null) {
      _to.text = code;
      _lookup();
    }
  }

  Future<void> _lookup() async {
    final code = TraceIds.fromScan(_to.text);
    if (code == null || !context.read<ConnectivityProvider>().isOnline) return;
    try {
      final p = await TraceApi(context.read<ApiService>()).participant(code);
      if (mounted) setState(() => _receiver = p);
    } catch (_) {
      if (mounted) setState(() => _receiver = {'missing': true});
    }
  }

  Future<void> _submit() async {
    if (!_form.currentState!.validate()) return;
    setState(() => _busy = true);
    final lot = widget.lot;
    final op = TraceOp(
      id: TraceIds.uuid(),
      kind: 'handoff',
      path: '/v1/trace/lots/${lot['uuid']}/handoff',
      label: widget.asReceiver
          ? 'Kupokea ${lot['code']}'
          : 'Kukabidhi ${lot['code']}',
      lotUuid: lot['uuid'] as String?,
      lotCode: lot['code'] as String?,
      body: {
        'uuid': TraceIds.uuid(),
        if (!widget.asReceiver) 'to_code': TraceIds.fromScan(_to.text),
        'quantity': traceNumber(_qty),
        'unit': _unit,
        if (_lossKg > 0) 'loss_reason': traceText(_loss),
        if (traceNumber(_price) != null) 'price_per_kg': traceNumber(_price),
        if (traceNumber(_price) != null) 'currency': 'TZS',
        if (_method != null) 'payment_method': _method,
        if (traceText(_ref) != null) 'payment_reference': traceText(_ref),
        if (traceText(_place) != null) 'location_label': traceText(_place),
        if (traceText(_notes) != null) 'notes': traceText(_notes),
        'occurred_at': _now(),
      },
    );
    final result = await traceSubmit(
      context,
      op,
      queuedMessage:
          'Makabidhiano yamehifadhiwa kwenye simu. Yatatumwa ukipata mtandao, kisha upande wa pili uthibitishe.',
    );
    if (!mounted) return;
    setState(() => _busy = false);
    if (result != null) Navigator.of(context).pop();
  }

  @override
  Widget build(BuildContext context) {
    final lot = widget.lot;
    final receiver = _receiver;
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.asReceiver ? 'Nimepokea mzigo' : 'Kabidhi mzigo'),
      ),
      body: TraceFormBody(
        formKey: _form,
        busy: _busy,
        submitLabel: widget.asReceiver ? 'Rekodi kupokea' : 'Kabidhi',
        onSubmit: _submit,
        children: [
          Text(
            '${_lotTitle(lot)} · ${lot['code']}',
            style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w700),
          ),
          Text(
            'Mzigo una ${traceKg(lot['quantity_kg'])}',
            style: const TextStyle(color: MkColors.muted),
          ),
          const SizedBox(height: 8),
          Text(
            widget.asReceiver
                ? 'Anayeshikilia mzigo atatakiwa kuthibitisha kabla mzigo haujahamia kwako.'
                : 'Mpokeaji atatakiwa kuthibitisha kabla mzigo haujahamia kwake.',
            style: const TextStyle(height: 1.4),
          ),
          const SizedBox(height: 16),
          if (!widget.asReceiver) ...[
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: TextFormField(
                    controller: _to,
                    textCapitalization: TextCapitalization.characters,
                    decoration: const InputDecoration(
                      labelText: 'Msimbo wa mpokeaji',
                    ),
                    onEditingComplete: _lookup,
                    validator: (v) => TraceIds.fromScan(v ?? '') == null
                        ? 'Msimbo una herufi na namba 10'
                        : null,
                  ),
                ),
                const SizedBox(width: 8),
                IconButton.filledTonal(
                  tooltip: 'Changanua msimbo wa mpokeaji',
                  onPressed: _scanReceiver,
                  icon: const Icon(Icons.qr_code_scanner),
                  constraints: const BoxConstraints(
                    minWidth: 48,
                    minHeight: 48,
                  ),
                ),
              ],
            ),
            if (receiver != null)
              Padding(
                padding: const EdgeInsets.only(top: 8),
                child: receiver['missing'] == true
                    ? const TraceChip.danger(
                        'Hakuna mshiriki mwenye msimbo huu',
                      )
                    : Wrap(
                        spacing: 6,
                        runSpacing: 6,
                        children: [
                          TraceChip(
                            '${TraceStrings.type(receiver['type'] as String?)}${receiver['name'] != null ? ' · ${receiver['name']}' : ''}',
                          ),
                          if (receiver['verified'] == true)
                            const TraceChip(
                              'Amethibitishwa',
                              icon: Icons.verified_outlined,
                            ),
                          if (receiver['active'] != true)
                            const TraceChip.danger('Bado hawezi kupokea'),
                        ],
                      ),
              ),
            const SizedBox(height: 12),
          ],
          TraceQuantityInput(
            controller: _qty,
            unit: _unit,
            onUnit: (u) => setState(() => _unit = u),
          ),
          _LossReason(controller: _loss, lossKg: _lossKg),
          const SizedBox(height: 12),
          TextFormField(
            controller: _price,
            keyboardType: const TextInputType.numberWithOptions(decimal: true),
            decoration: const InputDecoration(
              labelText: 'Bei kwa kilo, TZS (si lazima)',
            ),
          ),
          const SizedBox(height: 12),
          DropdownButtonFormField<String>(
            initialValue: _method,
            isExpanded: true,
            decoration: const InputDecoration(
              labelText: 'Njia ya malipo (si lazima)',
            ),
            items: [
              for (final e in TraceStrings.paymentMethods.entries)
                DropdownMenuItem(value: e.key, child: Text(e.value)),
            ],
            onChanged: (v) => setState(() => _method = v),
          ),
          const SizedBox(height: 12),
          TextFormField(
            controller: _ref,
            decoration: const InputDecoration(
              labelText: 'Namba ya muamala (si lazima)',
            ),
          ),
          const Padding(
            padding: EdgeInsets.only(top: 6),
            child: Text(
              TraceStrings.paymentNote,
              style: TextStyle(color: MkColors.muted, fontSize: 13),
            ),
          ),
          const SizedBox(height: 12),
          TextFormField(
            controller: _place,
            decoration: const InputDecoration(
              labelText: 'Mahali (mf. Soko la Kibaigwa)',
            ),
          ),
          const SizedBox(height: 12),
          TextFormField(
            controller: _notes,
            maxLines: 3,
            decoration: const InputDecoration(labelText: 'Maelezo (si lazima)'),
          ),
        ],
      ),
    );
  }
}

// ── Other steps by the holder ─────────────────────────────────────────

class TraceEventScreen extends StatefulWidget {
  final Map<String, dynamic> lot;
  const TraceEventScreen({super.key, required this.lot});

  @override
  State<TraceEventScreen> createState() => _TraceEventScreenState();
}

class _TraceEventScreenState extends State<TraceEventScreen> {
  final _form = GlobalKey<FormState>();
  final _qty = TextEditingController();
  final _loss = TextEditingController();
  final _grade = TextEditingController();
  final _moisture = TextEditingController();
  final _quality = TextEditingController();
  final _transporter = TextEditingController();
  final _place = TextEditingController();
  final _notes = TextEditingController();
  String _type = 'storage';
  String _unit = 'kg';
  bool _final = false;
  bool _busy = false;

  @override
  void dispose() {
    for (final c in [
      _qty,
      _loss,
      _grade,
      _moisture,
      _quality,
      _transporter,
      _place,
      _notes,
    ]) {
      c.dispose();
    }
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_form.currentState!.validate()) return;
    setState(() => _busy = true);
    final lot = widget.lot;
    final op = TraceOp(
      id: TraceIds.uuid(),
      kind: 'event',
      path: '/v1/trace/lots/${lot['uuid']}/events',
      label: '${TraceStrings.event(_type)} · ${lot['code']}',
      lotUuid: lot['uuid'] as String?,
      lotCode: lot['code'] as String?,
      body: {
        'uuid': TraceIds.uuid(),
        'type': _type,
        if (_type == 'loss') 'quantity': traceNumber(_qty),
        if (_type == 'loss') 'unit': _unit,
        if (_type == 'loss') 'loss_reason': traceText(_loss),
        if (traceText(_grade) != null) 'grade': traceText(_grade),
        if (traceNumber(_moisture) != null)
          'moisture_percent': traceNumber(_moisture),
        if (traceText(_quality) != null) 'quality_notes': traceText(_quality),
        if (_type == 'dispatch' && traceText(_transporter) != null)
          'transporter_code': TraceIds.fromScan(_transporter.text),
        if (_type == 'delivery') 'final': _final,
        if (traceText(_place) != null) 'location_label': traceText(_place),
        if (traceText(_notes) != null) 'notes': traceText(_notes),
        'occurred_at': _now(),
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
      appBar: AppBar(title: const Text('Rekodi hatua')),
      body: TraceFormBody(
        formKey: _form,
        busy: _busy,
        submitLabel: 'Hifadhi',
        onSubmit: _submit,
        children: [
          Text(
            '${_lotTitle(widget.lot)} · ${widget.lot['code']}',
            style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w700),
          ),
          const SizedBox(height: 12),
          DropdownButtonFormField<String>(
            initialValue: _type,
            isExpanded: true,
            decoration: const InputDecoration(labelText: 'Hatua'),
            items: [
              for (final t in TraceStrings.holderEvents)
                DropdownMenuItem(
                  value: t,
                  child: Text(
                    '${TraceStrings.event(t)} · ${TraceStrings.events[t]!.$2}',
                  ),
                ),
            ],
            onChanged: (v) => setState(() => _type = v ?? _type),
          ),
          const SizedBox(height: 12),
          if (_type == 'loss') ...[
            TraceQuantityInput(
              controller: _qty,
              unit: _unit,
              label: 'Kiasi kilichopotea',
              onUnit: (u) => setState(() => _unit = u),
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _loss,
              decoration: const InputDecoration(labelText: 'Sababu ya upotevu'),
              validator: (v) =>
                  (v ?? '').trim().isEmpty ? 'Eleza sababu' : null,
            ),
            const SizedBox(height: 12),
          ],
          if (_type == 'grading' || _type == 'quality_check') ...[
            TextFormField(
              controller: _grade,
              decoration: const InputDecoration(labelText: 'Daraja'),
            ),
            const SizedBox(height: 12),
          ],
          if (_type == 'quality_check' || _type == 'storage') ...[
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
              controller: _quality,
              decoration: const InputDecoration(
                labelText: 'Maelezo ya ubora (si lazima)',
              ),
            ),
            const SizedBox(height: 12),
          ],
          if (_type == 'dispatch') ...[
            TextFormField(
              controller: _transporter,
              textCapitalization: TextCapitalization.characters,
              decoration: const InputDecoration(
                labelText: 'Msimbo wa msafirishaji (si lazima)',
              ),
              validator: (v) =>
                  (v ?? '').trim().isNotEmpty && TraceIds.fromScan(v!) == null
                  ? 'Msimbo una herufi na namba 10'
                  : null,
            ),
            const SizedBox(height: 12),
          ],
          if (_type == 'delivery')
            CheckboxListTile(
              value: _final,
              contentPadding: EdgeInsets.zero,
              onChanged: (v) => setState(() => _final = v ?? false),
              title: const Text('Huu ndio mwisho wa safari ya mzigo huu'),
            ),
          TextFormField(
            controller: _place,
            decoration: const InputDecoration(labelText: 'Mahali (si lazima)'),
          ),
          const SizedBox(height: 12),
          TextFormField(
            controller: _notes,
            maxLines: 3,
            decoration: const InputDecoration(labelText: 'Maelezo (si lazima)'),
          ),
        ],
      ),
    );
  }
}

// ── Split ─────────────────────────────────────────────────────────────

class TraceSplitScreen extends StatefulWidget {
  final Map<String, dynamic> lot;
  const TraceSplitScreen({super.key, required this.lot});

  @override
  State<TraceSplitScreen> createState() => _TraceSplitScreenState();
}

class _Part {
  final qty = TextEditingController();
  final grade = TextEditingController();
  String unit = 'kg';
  void dispose() {
    qty.dispose();
    grade.dispose();
  }
}

class _TraceSplitScreenState extends State<TraceSplitScreen> {
  final _form = GlobalKey<FormState>();
  final _loss = TextEditingController();
  final _parts = [_Part(), _Part()];
  bool _busy = false;

  @override
  void initState() {
    super.initState();
    for (final p in _parts) {
      p.qty.addListener(_changed);
    }
  }

  void _changed() => setState(() {});

  @override
  void dispose() {
    _loss.dispose();
    for (final p in _parts) {
      p.dispose();
    }
    super.dispose();
  }

  double get _partsKg =>
      _parts.fold(0, (sum, p) => sum + _toKg(traceNumber(p.qty) ?? 0, p.unit));

  double get _lossKg {
    final lotKg = (widget.lot['quantity_kg'] as num?)?.toDouble() ?? 0;
    final short = lotKg - _partsKg;
    return short > _tolerance(lotKg) ? short : 0;
  }

  Future<void> _submit() async {
    if (!_form.currentState!.validate()) return;
    final lot = widget.lot;
    final lotKg = (lot['quantity_kg'] as num?)?.toDouble() ?? 0;
    if (_partsKg > lotKg + _tolerance(lotKg)) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Sehemu zinazidi kiasi cha mzigo.')),
      );
      return;
    }
    setState(() => _busy = true);
    final op = TraceOp(
      id: TraceIds.uuid(),
      kind: 'split',
      path: '/v1/trace/lots/${lot['uuid']}/split',
      label: 'Kugawanya ${lot['code']} sehemu ${_parts.length}',
      lotUuid: lot['uuid'] as String?,
      lotCode: lot['code'] as String?,
      body: {
        'uuid': TraceIds.uuid(),
        'parts': [
          for (final p in _parts)
            {
              'uuid': TraceIds.uuid(),
              'code': TraceIds.code(),
              'quantity': traceNumber(p.qty),
              'unit': p.unit,
              if (traceText(p.grade) != null) 'grade': traceText(p.grade),
            },
        ],
        if (_lossKg > 0) 'loss_reason': traceText(_loss),
        'occurred_at': _now(),
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
      appBar: AppBar(title: const Text('Gawanya mzigo')),
      body: TraceFormBody(
        formKey: _form,
        busy: _busy,
        submitLabel: 'Gawanya',
        onSubmit: _submit,
        children: [
          Text(
            '${_lotTitle(widget.lot)} · ${traceKg(widget.lot['quantity_kg'])}',
            style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w700),
          ),
          const Text(
            'Kila sehemu inapata QR yake. Mzigo wa asili na historia yake vinabaki.',
            style: TextStyle(color: MkColors.muted),
          ),
          for (final (i, p) in _parts.indexed) ...[
            const SizedBox(height: 16),
            Text(
              'Sehemu ${i + 1}',
              style: const TextStyle(fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 8),
            TraceQuantityInput(
              controller: p.qty,
              unit: p.unit,
              onUnit: (u) => setState(() => p.unit = u),
            ),
            const SizedBox(height: 8),
            TextFormField(
              controller: p.grade,
              decoration: const InputDecoration(
                labelText: 'Daraja (si lazima)',
              ),
            ),
          ],
          const SizedBox(height: 12),
          if (_parts.length < 20)
            TextButton.icon(
              onPressed: () => setState(
                () => _parts.add(_Part()..qty.addListener(_changed)),
              ),
              icon: const Icon(Icons.add),
              label: const Text('Ongeza sehemu'),
            ),
          Text(
            'Jumla ya sehemu: ${traceKg(_partsKg)}',
            style: const TextStyle(fontWeight: FontWeight.w600),
          ),
          _LossReason(controller: _loss, lossKg: _lossKg),
        ],
      ),
    );
  }
}

// ── Processing ────────────────────────────────────────────────────────

class TraceProcessScreen extends StatefulWidget {
  final Map<String, dynamic> lot;
  const TraceProcessScreen({super.key, required this.lot});

  @override
  State<TraceProcessScreen> createState() => _TraceProcessScreenState();
}

class _TraceProcessScreenState extends State<TraceProcessScreen> {
  final _form = GlobalKey<FormState>();
  final _product = TextEditingController();
  final _qty = TextEditingController();
  final _loss = TextEditingController();
  final _notes = TextEditingController();
  String _unit = 'kg';
  bool _busy = false;

  @override
  void initState() {
    super.initState();
    _qty.addListener(() => setState(() {}));
  }

  @override
  void dispose() {
    for (final c in [_product, _qty, _loss, _notes]) {
      c.dispose();
    }
    super.dispose();
  }

  double get _lossKg {
    final lotKg = (widget.lot['quantity_kg'] as num?)?.toDouble() ?? 0;
    final out = _toKg(traceNumber(_qty) ?? 0, _unit);
    final short = lotKg - out;
    return traceNumber(_qty) != null && short > _tolerance(lotKg) ? short : 0;
  }

  Future<void> _submit() async {
    if (!_form.currentState!.validate()) return;
    setState(() => _busy = true);
    final lot = widget.lot;
    final outUuid = TraceIds.uuid();
    final op = TraceOp(
      id: TraceIds.uuid(),
      kind: 'process',
      path: '/v1/trace/lots/${lot['uuid']}/process',
      label: 'Kusindika ${lot['code']} kuwa ${_product.text.trim()}',
      lotUuid: lot['uuid'] as String?,
      lotCode: lot['code'] as String?,
      body: {
        'uuid': TraceIds.uuid(),
        'output_uuid': outUuid,
        'output_code': TraceIds.code(),
        'product': _product.text.trim(),
        'output_quantity': traceNumber(_qty),
        'output_unit': _unit,
        if (_lossKg > 0) 'loss_reason': traceText(_loss),
        if (traceText(_notes) != null) 'notes': traceText(_notes),
        'occurred_at': _now(),
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
      appBar: AppBar(title: const Text('Sindika mzigo')),
      body: TraceFormBody(
        formKey: _form,
        busy: _busy,
        submitLabel: 'Hifadhi usindikaji',
        onSubmit: _submit,
        children: [
          Text(
            '${_lotTitle(widget.lot)} · ${traceKg(widget.lot['quantity_kg'])}',
            style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w700),
          ),
          const Text(
            'Bidhaa mpya inapata QR yake, ikiunganishwa na mzigo huu.',
            style: TextStyle(color: MkColors.muted),
          ),
          const SizedBox(height: 12),
          TextFormField(
            controller: _product,
            decoration: const InputDecoration(
              labelText: 'Bidhaa (mf. unga wa mahindi)',
            ),
            validator: (v) => (v ?? '').trim().isEmpty ? 'Andika bidhaa' : null,
          ),
          const SizedBox(height: 12),
          TraceQuantityInput(
            controller: _qty,
            unit: _unit,
            label: 'Kiasi cha bidhaa',
            onUnit: (u) => setState(() => _unit = u),
          ),
          _LossReason(controller: _loss, lossKg: _lossKg),
          const SizedBox(height: 12),
          TextFormField(
            controller: _notes,
            maxLines: 3,
            decoration: const InputDecoration(labelText: 'Maelezo (si lazima)'),
          ),
        ],
      ),
    );
  }
}

// ── Merge ─────────────────────────────────────────────────────────────

class TraceMergeScreen extends StatefulWidget {
  final List<Map<String, dynamic>> lots;
  const TraceMergeScreen({super.key, required this.lots});

  @override
  State<TraceMergeScreen> createState() => _TraceMergeScreenState();
}

class _TraceMergeScreenState extends State<TraceMergeScreen> {
  final _form = GlobalKey<FormState>();
  final _qty = TextEditingController();
  final _loss = TextEditingController();
  final _grade = TextEditingController();
  final _picked = <String>{};
  String _unit = 'kg';
  bool _busy = false;

  @override
  void initState() {
    super.initState();
    _qty.addListener(() => setState(() {}));
  }

  @override
  void dispose() {
    for (final c in [_qty, _loss, _grade]) {
      c.dispose();
    }
    super.dispose();
  }

  List<Map<String, dynamic>> get _chosen =>
      widget.lots.where((l) => _picked.contains(l['uuid'])).toList();

  double get _inKg => _chosen.fold(
    0,
    (s, l) => s + ((l['quantity_kg'] as num?)?.toDouble() ?? 0),
  );

  double get _lossKg {
    final q = traceNumber(_qty);
    if (q == null) return 0;
    final short = _inKg - _toKg(q, _unit);
    return short > _tolerance(_inKg) ? short : 0;
  }

  Future<void> _submit() async {
    if (_chosen.length < 2) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Chagua mizigo miwili au zaidi.')),
      );
      return;
    }
    if (!_form.currentState!.validate()) return;
    setState(() => _busy = true);
    final crop = '${_chosen.first['crop']}';
    final uuid = TraceIds.uuid();
    final code = TraceIds.code();
    final op = TraceOp(
      id: TraceIds.uuid(),
      kind: 'merge',
      path: '/v1/trace/lots/merge',
      label: 'Kuunganisha mizigo ${_chosen.length} ya $crop',
      lotUuid: uuid,
      lotCode: code,
      body: {
        'uuid': uuid,
        'event_uuid': TraceIds.uuid(),
        'code': code,
        'lot_uuids': _chosen.map((l) => l['uuid']).toList(),
        'quantity': traceNumber(_qty),
        'unit': _unit,
        if (traceText(_grade) != null) 'grade': traceText(_grade),
        if (_lossKg > 0) 'loss_reason': traceText(_loss),
        'occurred_at': _now(),
      },
    );
    final result = await traceSubmit(context, op);
    if (!mounted) return;
    setState(() => _busy = false);
    if (result != null) Navigator.of(context).pop();
  }

  @override
  Widget build(BuildContext context) {
    final crop = _chosen.isEmpty ? null : _chosen.first['crop'];
    return Scaffold(
      appBar: AppBar(title: const Text('Unganisha mizigo')),
      body: TraceFormBody(
        formKey: _form,
        busy: _busy,
        submitLabel: 'Unganisha',
        onSubmit: _submit,
        children: [
          const Text(
            'Chagua mizigo ya zao moja unayoshikilia. Mzigo mpya unapata QR yake; mizigo ya asili na historia zake vinabaki.',
            style: TextStyle(height: 1.4),
          ),
          const SizedBox(height: 8),
          for (final l in widget.lots)
            CheckboxListTile(
              value: _picked.contains(l['uuid']),
              contentPadding: EdgeInsets.zero,
              onChanged: crop != null && l['crop'] != crop
                  ? null
                  : (v) => setState(
                      () => v == true
                          ? _picked.add('${l['uuid']}')
                          : _picked.remove(l['uuid']),
                    ),
              title: Text('${_lotTitle(l)} · ${traceKg(l['quantity_kg'])}'),
              subtitle: Text('${l['code']}'),
            ),
          const SizedBox(height: 8),
          Text(
            'Jumla: ${traceKg(_inKg)}',
            style: const TextStyle(fontWeight: FontWeight.w600),
          ),
          const SizedBox(height: 12),
          TraceQuantityInput(
            controller: _qty,
            unit: _unit,
            label: 'Kiasi cha mzigo mpya',
            onUnit: (u) => setState(() => _unit = u),
          ),
          _LossReason(controller: _loss, lossKg: _lossKg),
          const SizedBox(height: 12),
          TextFormField(
            controller: _grade,
            decoration: const InputDecoration(labelText: 'Daraja (si lazima)'),
          ),
        ],
      ),
    );
  }
}
