import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme.dart';
import '../../services/api_service.dart';
import '../../trace/trace_api.dart';
import '../../trace/trace_ids.dart';
import '../../trace/trace_strings.dart';
import '../../trace/trace_sync.dart';
import '../../widgets/mk_error_state.dart';
import '../../widgets/mk_offline_banner.dart';
import '../../widgets/mk_skeleton.dart';
import 'trace_widgets.dart';

/// Hand-offs waiting for this participant to confirm or dispute.
///
/// A confirmation made offline is kept on the phone and labelled as such:
/// the lot does not move, and nobody is told it was confirmed, until the
/// server has it.
class TraceConfirmScreen extends StatefulWidget {
  const TraceConfirmScreen({super.key});

  @override
  State<TraceConfirmScreen> createState() => _TraceConfirmScreenState();
}

class _TraceConfirmScreenState extends State<TraceConfirmScreen> {
  final _store = TraceSync.instance;
  bool _loading = true;
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
    try {
      await TraceApi(api).pending();
      _error = null;
    } catch (e) {
      if (_store.pending.isEmpty) _error = traceError(e).message;
    }
    if (mounted) setState(() => _loading = false);
  }

  /// Answers already waiting on the phone for this hand-off.
  TraceOp? _queued(String eventUuid) => _store.unsent
      .where(
        (o) =>
            (o.kind == 'confirm' || o.kind == 'dispute') &&
            o.path.contains(eventUuid),
      )
      .firstOrNull;

  Future<void> _answer(Map<String, dynamic> e, {required bool confirm}) async {
    final result = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      isScrollControlled: true,
      showDragHandle: true,
      builder: (_) => _AnswerSheet(event: e, confirm: confirm),
    );
    if (result == null || !mounted) return;
    final lot = e['lot'] as Map? ?? {};
    final op = TraceOp(
      id: TraceIds.uuid(),
      kind: confirm ? 'confirm' : 'dispute',
      path: '/v1/trace/events/${e['uuid']}/${confirm ? 'confirm' : 'dispute'}',
      label:
          '${confirm ? 'Kuthibitisha' : 'Kupinga'} makabidhiano ya ${lot['code'] ?? ''}',
      lotUuid: lot['uuid'] as String?,
      lotCode: lot['code'] as String?,
      body: result,
    );
    final done = await traceSubmit(
      context,
      op,
      queuedMessage: confirm
          ? TraceStrings.confirmSavedOffline
          : TraceStrings.savedOffline,
    );
    if (done != null && done.synced) _load();
  }

  @override
  Widget build(BuildContext context) {
    final rows = _store.pending;
    return Scaffold(
      appBar: AppBar(title: const Text('Thibitisha makabidhiano')),
      body: Column(
        children: [
          const MkOfflineBanner(),
          Expanded(
            child: _loading
                ? const MkListSkeleton()
                : _error != null
                ? MkErrorState(message: _error, onRetry: _load)
                : RefreshIndicator(
                    onRefresh: _load,
                    child: rows.isEmpty
                        ? ListView(
                            children: const [
                              Padding(
                                padding: EdgeInsets.all(32),
                                child: Text(
                                  'Hakuna makabidhiano yanayosubiri uthibitisho wako.',
                                  textAlign: TextAlign.center,
                                  style: TextStyle(color: MkColors.muted),
                                ),
                              ),
                            ],
                          )
                        : ListView(
                            padding: const EdgeInsets.all(16),
                            children: [for (final e in rows) _card(e)],
                          ),
                  ),
          ),
        ],
      ),
    );
  }

  Widget _card(Map<String, dynamic> e) {
    final lot = e['lot'] as Map? ?? {};
    final queued = _queued('${e['uuid']}');
    final from = e['from'] as Map?;
    final to = e['to'] as Map?;
    String who(Map? p) => p == null
        ? '—'
        : (p['name'] as String? ?? TraceStrings.type(p['type'] as String?));
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              '${lot['product'] ?? lot['crop'] ?? ''} · ${lot['code'] ?? ''}',
              style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 6),
            Text('${who(from)} → ${who(to)}'),
            Text('Kiasi kilichoandikwa: ${traceKg(e['quantity_kg'])}'),
            if (e['price_per_kg'] != null)
              Text('Bei: ${e['price_per_kg']} ${e['currency'] ?? ''} kwa kilo'),
            if (e['payment'] is Map) ...[
              Text('Malipo: ${(e['payment'] as Map)['reference'] ?? ''}'),
              const Text(
                TraceStrings.paymentNote,
                style: TextStyle(color: MkColors.muted, fontSize: 13),
              ),
            ],
            if (e['loss_reason'] != null)
              Text('Upungufu: ${traceKg(e['loss_kg'])} (${e['loss_reason']})'),
            Text(
              traceDate(e['occurred_at']),
              style: const TextStyle(color: MkColors.muted, fontSize: 13),
            ),
            const SizedBox(height: 12),
            if (queued != null)
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  TraceOpChip(queued),
                  const SizedBox(height: 4),
                  Text(
                    queued.isFailed
                        ? (queued.error ?? '')
                        : 'Jibu lako bado liko kwenye simu; halijafika kwenye mfumo.',
                    style: TextStyle(
                      color: queued.isFailed ? MkColors.danger : MkColors.muted,
                      fontSize: 13,
                    ),
                  ),
                ],
              )
            else
              Row(
                children: [
                  Expanded(
                    child: FilledButton(
                      onPressed: () => _answer(e, confirm: true),
                      style: FilledButton.styleFrom(
                        minimumSize: const Size.fromHeight(48),
                      ),
                      child: const Text('Thibitisha'),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: OutlinedButton(
                      onPressed: () => _answer(e, confirm: false),
                      style: OutlinedButton.styleFrom(
                        minimumSize: const Size.fromHeight(48),
                      ),
                      child: const Text('Pinga'),
                    ),
                  ),
                ],
              ),
          ],
        ),
      ),
    );
  }
}

class _AnswerSheet extends StatefulWidget {
  final Map<String, dynamic> event;
  final bool confirm;
  const _AnswerSheet({required this.event, required this.confirm});

  @override
  State<_AnswerSheet> createState() => _AnswerSheetState();
}

class _AnswerSheetState extends State<_AnswerSheet> {
  final _form = GlobalKey<FormState>();
  final _qty = TextEditingController();
  final _reason = TextEditingController();
  String _unit = 'kg';

  @override
  void initState() {
    super.initState();
    _qty.text = '${widget.event['quantity_kg'] ?? ''}';
  }

  @override
  void dispose() {
    _qty.dispose();
    _reason.dispose();
    super.dispose();
  }

  bool get _iAmReceiver {
    final me = TraceSync.instance.me;
    final to = widget.event['to'] as Map?;
    return me != null && to != null && to['code'] == me['code'];
  }

  @override
  Widget build(BuildContext context) {
    final receiver = widget.confirm && _iAmReceiver;
    return Padding(
      padding: EdgeInsets.fromLTRB(
        16,
        0,
        16,
        MediaQuery.viewInsetsOf(context).bottom +
            MediaQuery.paddingOf(context).bottom +
            16,
      ),
      child: Form(
        key: _form,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text(
              widget.confirm ? 'Thibitisha makabidhiano' : 'Pinga makabidhiano',
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 12),
            if (receiver) ...[
              const Text('Andika uzito ulioupokea kwenye mizani yako.'),
              const SizedBox(height: 8),
              TraceQuantityInput(
                controller: _qty,
                unit: _unit,
                label: 'Kilichopokelewa',
                onUnit: (u) => setState(() => _unit = u),
              ),
              const SizedBox(height: 8),
              TextFormField(
                controller: _reason,
                decoration: const InputDecoration(
                  labelText: 'Sababu ya tofauti, kama ipo',
                  helperText: 'Tofauti isiyoelezwa itakaguliwa na msimamizi.',
                ),
              ),
            ],
            if (!widget.confirm)
              TextFormField(
                controller: _reason,
                maxLines: 3,
                decoration: const InputDecoration(
                  labelText: 'Kwa nini unapinga?',
                ),
                validator: (v) =>
                    (v ?? '').trim().length < 3 ? 'Eleza sababu' : null,
              ),
            const SizedBox(height: 16),
            FilledButton(
              onPressed: () {
                if (!_form.currentState!.validate()) return;
                Navigator.of(context).pop(<String, dynamic>{
                  if (receiver && traceNumber(_qty) != null)
                    'received_quantity': traceNumber(_qty),
                  if (receiver && traceNumber(_qty) != null)
                    'received_unit': _unit,
                  if (widget.confirm && traceText(_reason) != null)
                    'loss_reason': traceText(_reason),
                  if (!widget.confirm) 'reason': traceText(_reason),
                });
              },
              style: FilledButton.styleFrom(
                minimumSize: const Size.fromHeight(48),
              ),
              child: Text(widget.confirm ? 'Thibitisha' : 'Pinga'),
            ),
          ],
        ),
      ),
    );
  }
}
