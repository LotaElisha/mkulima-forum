import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/regions.dart';
import '../../core/theme.dart';
import '../../providers/auth_provider.dart';
import '../../providers/connectivity_provider.dart';
import '../../services/api_service.dart';
import '../../trace/trace_api.dart';
import '../../trace/trace_strings.dart';
import '../../trace/trace_sync.dart';
import '../../widgets/mk_error_state.dart';
import '../../widgets/mk_offline_banner.dart';
import '../../widgets/mk_skeleton.dart';
import 'trace_confirm_screen.dart';
import 'trace_farm_screen.dart';
import 'trace_forms.dart';
import 'trace_lot_screen.dart';
import 'trace_scan_screen.dart';
import 'trace_sync_screen.dart';
import 'trace_widgets.dart';

/// Mkulima Trace — Shamba hadi Soko. Follow a crop lot from the plot to the
/// buyer: register, record harvests, hand lots over, and scan a QR code to
/// see a lot's history. Works offline; writes wait on the phone until there
/// is a connection.
class TraceHomeScreen extends StatefulWidget {
  const TraceHomeScreen({super.key});

  @override
  State<TraceHomeScreen> createState() => _TraceHomeScreenState();
}

class _TraceHomeScreenState extends State<TraceHomeScreen> {
  final _store = TraceSync.instance;
  bool _loading = true;
  bool _fromCache = false;
  String? _error;
  bool _history = false;
  int _pendingConfirmations = 0;
  bool? _wasOnline;

  Map<String, dynamic>? get _me => _store.me;

  @override
  void initState() {
    super.initState();
    _store.addListener(_onStore);
    _refresh();
  }

  @override
  void dispose() {
    _store.removeListener(_onStore);
    super.dispose();
  }

  void _onStore() {
    if (mounted) setState(() {});
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final online = context.watch<ConnectivityProvider>().isOnline;
    // Back online: send what waited on the phone, then refresh.
    if (_wasOnline == false && online) _refresh();
    _wasOnline = online;
  }

  Future<void> _refresh() async {
    await _store.load();
    if (!mounted) return;
    final auth = context.read<AuthProvider>();
    if (!auth.isAuthenticated) {
      setState(() => _loading = false);
      return;
    }
    final api = context.read<ApiService>();
    final trace = TraceApi(api);
    setState(() {
      _loading = _me == null;
      _error = null;
    });
    try {
      if (_store.pendingCount > 0) await _store.syncNow(posterFor(api));
      final me = await trace.me();
      _pendingConfirmations = me.pending;
      if (me.participant != null && me.participant!['status'] == 'active') {
        await Future.wait([
          trace.lots(),
          trace.lots(history: true),
          if (me.participant!['type'] == 'farmer') trace.plots(),
        ]);
      }
      _fromCache = false;
    } catch (e) {
      _fromCache = true;
      if (_me == null) _error = traceError(e).message;
    }
    if (mounted) setState(() => _loading = false);
  }

  Future<void> _push(Widget screen) async {
    await Navigator.of(context).push(MaterialPageRoute(builder: (_) => screen));
    if (mounted) _refresh();
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    return Scaffold(
      appBar: AppBar(
        title: const Text(TraceStrings.brand),
        actions: [
          if (auth.isAuthenticated)
            IconButton(
              tooltip: 'Changanua QR',
              icon: const Icon(Icons.qr_code_scanner),
              onPressed: () => _push(const TraceScanScreen()),
            ),
        ],
      ),
      body: Column(
        children: [
          const MkOfflineBanner(),
          Expanded(child: _body(auth)),
        ],
      ),
    );
  }

  Widget _body(AuthProvider auth) {
    if (!auth.isAuthenticated) {
      return _SignedOut(
        onScan: () => _push(const TraceScanScreen()),
        onSignedIn: _refresh,
      );
    }
    if (_loading) return const MkListSkeleton();
    if (_error != null && _me == null) {
      return MkErrorState(message: _error, onRetry: _refresh);
    }
    final me = _me;
    if (me == null) return _RegisterView(onDone: _refresh);

    return RefreshIndicator(
      onRefresh: _refresh,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
        children: [
          _Header(me: me),
          if (_fromCache)
            const Padding(
              padding: EdgeInsets.only(top: 12),
              child: TraceChip.info(
                'Unaona taarifa zilizohifadhiwa kwenye simu',
                icon: Icons.history,
              ),
            ),
          if (me['status'] != 'active')
            _StatusCard(me: me, onEdit: () => _push(const _RegisterScreen())),
          if (_store.unsent.isNotEmpty) ...[
            const SizedBox(height: 12),
            _SyncCard(onTap: () => _push(const TraceSyncScreen())),
          ],
          if (me['status'] == 'active') ...[
            const SizedBox(height: 16),
            _Actions(
              me: me,
              pendingConfirmations: _pendingConfirmations,
              heldLots: _store.lots
                  .where((l) => l['i_hold'] == true && l['status'] == 'active')
                  .toList(),
              onOpen: _push,
            ),
            const SizedBox(height: 20),
            SegmentedButton<bool>(
              segments: const [
                ButtonSegment(value: false, label: Text('Mizigo yangu')),
                ButtonSegment(value: true, label: Text('Historia')),
              ],
              selected: {_history},
              onSelectionChanged: (s) => setState(() => _history = s.first),
            ),
            const SizedBox(height: 12),
            ..._lotList(),
          ],
          const TraceNotice(),
        ],
      ),
    );
  }

  List<Widget> _lotList() {
    final rows = _history ? _store.history : _store.lots;
    // Lots made on this phone that the server has not seen yet.
    final local = _history
        ? const <TraceOp>[]
        : _store.unsent
              .where((o) => o.kind == 'harvest' || o.kind == 'merge')
              .toList();
    if (rows.isEmpty && local.isEmpty) {
      return [
        Padding(
          padding: const EdgeInsets.symmetric(vertical: 24),
          child: Text(
            _history
                ? 'Bado hujashiriki kwenye mzigo wowote.'
                : 'Huna mzigo kwa sasa. ${_me?['type'] == 'farmer' ? 'Rekodi mavuno ili kuanza.' : 'Changanua QR ya mzigo unaopokea.'}',
            textAlign: TextAlign.center,
            style: const TextStyle(color: MkColors.muted),
          ),
        ),
      ];
    }
    return [
      for (final op in local)
        _LocalLotTile(
          op: op,
          onTap: () =>
              _push(TraceLotScreen(uuid: op.lotUuid!, code: op.lotCode)),
        ),
      for (final lot in rows)
        _LotTile(
          lot: lot,
          onTap: () => _push(
            TraceLotScreen(
              uuid: lot['uuid'] as String,
              code: lot['code'] as String?,
            ),
          ),
        ),
    ];
  }
}

class _Header extends StatelessWidget {
  final Map<String, dynamic> me;
  const _Header({required this.me});

  @override
  Widget build(BuildContext context) {
    final type = TraceStrings.types[me['type']];
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: MkColors.leafPale,
        borderRadius: BorderRadius.circular(16),
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  TraceStrings.tagline,
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.w800,
                    color: MkColors.primaryDark,
                  ),
                ),
                const Text(
                  TraceStrings.taglineEn,
                  style: TextStyle(fontSize: 13, color: MkColors.muted),
                ),
                const SizedBox(height: 10),
                Text(
                  (me['organisation_name'] as String?)?.isNotEmpty == true
                      ? me['organisation_name'] as String
                      : (me['name'] as String? ?? ''),
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 6),
                Wrap(
                  spacing: 8,
                  runSpacing: 6,
                  children: [
                    TraceChip(type?.$1 ?? '${me['type']}'),
                    if (me['verified'] == true)
                      const TraceChip(
                        'Amethibitishwa na msimamizi',
                        icon: Icons.verified_outlined,
                      )
                    else if (me['type'] != 'farmer')
                      const TraceChip.warning('Bado hajathibitishwa'),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(width: 12),
          InkWell(
            borderRadius: BorderRadius.circular(12),
            onTap: () => _showMyCode(context, me),
            child: Padding(
              padding: const EdgeInsets.all(4),
              child: Column(
                children: [
                  TraceQr(me['code'] as String, size: 72),
                  const SizedBox(height: 4),
                  const Text(
                    'Msimbo wangu',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  static void _showMyCode(BuildContext context, Map<String, dynamic> me) {
    showModalBottomSheet<void>(
      context: context,
      showDragHandle: true,
      builder: (sheet) => Padding(
        padding: EdgeInsets.fromLTRB(
          24,
          0,
          24,
          MediaQuery.paddingOf(sheet).bottom + 24,
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text(
              'Msimbo wako wa Mkulima Trace',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 8),
            const Text(
              'Mwonyeshe anayekukabidhi mzigo. Msimbo hauna jina, simu wala mahali ulipo.',
              textAlign: TextAlign.center,
              style: TextStyle(color: MkColors.muted),
            ),
            const SizedBox(height: 16),
            TraceQr(me['code'] as String, size: 220),
            const SizedBox(height: 8),
            SelectableText(
              me['code'] as String,
              style: const TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.w800,
                letterSpacing: 3,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _StatusCard extends StatelessWidget {
  final Map<String, dynamic> me;
  final VoidCallback onEdit;
  const _StatusCard({required this.me, required this.onEdit});

  @override
  Widget build(BuildContext context) {
    final pending = me['status'] == 'pending';
    return Container(
      margin: const EdgeInsets.only(top: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: pending ? MkColors.accentSoft : MkColors.dangerSoft,
        borderRadius: BorderRadius.circular(14),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            pending
                ? 'Usajili wako unasubiri msimamizi'
                : 'Usajili wako haujakubaliwa',
            style: TextStyle(
              fontWeight: FontWeight.w700,
              color: pending ? MkColors.onAccentSoft : MkColors.danger,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            pending
                ? 'Msimamizi atahakiki taarifa zako. Ukishakubaliwa utaweza kupokea na kurekodi mizigo. Unaweza kuchanganua QR ya mzigo kuona muhtasari wake.'
                : ((me['review_note'] as String?)?.isNotEmpty == true
                      ? me['review_note'] as String
                      : 'Wasiliana na msimamizi kwa maelezo zaidi.'),
            style: const TextStyle(height: 1.4),
          ),
          const SizedBox(height: 8),
          TextButton(onPressed: onEdit, child: const Text('Badilisha taarifa')),
        ],
      ),
    );
  }
}

class _SyncCard extends StatelessWidget {
  final VoidCallback onTap;
  const _SyncCard({required this.onTap});

  @override
  Widget build(BuildContext context) {
    final store = TraceSync.instance;
    final failed = store.failedCount;
    return Material(
      color: failed > 0 ? MkColors.dangerSoft : MkColors.accentSoft,
      borderRadius: BorderRadius.circular(14),
      child: InkWell(
        borderRadius: BorderRadius.circular(14),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Row(
            children: [
              Icon(
                failed > 0 ? Icons.error_outline : Icons.cloud_upload_outlined,
                color: failed > 0 ? MkColors.danger : MkColors.onAccentSoft,
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  [
                    if (store.pendingCount > 0)
                      'Rekodi ${store.pendingCount} zinasubiri kutumwa',
                    if (failed > 0) '$failed zimeshindwa kutumwa',
                  ].join(' · '),
                  style: const TextStyle(fontWeight: FontWeight.w600),
                ),
              ),
              if (store.syncing)
                const SizedBox(
                  width: 20,
                  height: 20,
                  child: CircularProgressIndicator(strokeWidth: 2),
                )
              else
                const Icon(Icons.chevron_right),
            ],
          ),
        ),
      ),
    );
  }
}

class _Actions extends StatelessWidget {
  final Map<String, dynamic> me;
  final int pendingConfirmations;
  final List<Map<String, dynamic>> heldLots;
  final Future<void> Function(Widget) onOpen;

  const _Actions({
    required this.me,
    required this.pendingConfirmations,
    required this.heldLots,
    required this.onOpen,
  });

  @override
  Widget build(BuildContext context) {
    final farmer = me['type'] == 'farmer';
    final items = <(IconData, String, Widget)>[
      (Icons.qr_code_scanner, 'Changanua QR', const TraceScanScreen()),
      (
        Icons.fact_check_outlined,
        pendingConfirmations > 0
            ? 'Thibitisha ($pendingConfirmations)'
            : 'Thibitisha makabidhiano',
        const TraceConfirmScreen(),
      ),
      if (farmer)
        (
          Icons.agriculture_outlined,
          'Rekodi mavuno',
          const TraceHarvestScreen(),
        ),
      if (farmer)
        (Icons.grass_outlined, 'Mashamba yangu', const TraceFarmScreen()),
      if (heldLots.length >= 2)
        (
          Icons.merge_type,
          'Unganisha mizigo',
          TraceMergeScreen(lots: heldLots),
        ),
    ];
    return LayoutBuilder(
      builder: (context, c) {
        final width = (c.maxWidth - 12) / 2;
        return Wrap(
          spacing: 12,
          runSpacing: 12,
          children: [
            for (final (icon, label, screen) in items)
              SizedBox(
                width: width,
                child: OutlinedButton.icon(
                  onPressed: () => onOpen(screen),
                  icon: Icon(icon),
                  label: Text(label, textAlign: TextAlign.center),
                  style: OutlinedButton.styleFrom(
                    minimumSize: const Size.fromHeight(56),
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 10,
                    ),
                    alignment: Alignment.centerLeft,
                  ),
                ),
              ),
          ],
        );
      },
    );
  }
}

class _LotTile extends StatelessWidget {
  final Map<String, dynamic> lot;
  final VoidCallback onTap;
  const _LotTile({required this.lot, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final title = (lot['product'] as String?)?.isNotEmpty == true
        ? lot['product'] as String
        : '${lot['crop']}';
    return Card(
      margin: const EdgeInsets.only(bottom: 10),
      child: ListTile(
        onTap: onTap,
        minVerticalPadding: 12,
        leading: const Icon(
          Icons.inventory_2_outlined,
          color: MkColors.primary,
        ),
        title: Text(
          '$title · ${traceKg(lot['quantity_kg'])}',
          style: const TextStyle(fontWeight: FontWeight.w700),
        ),
        subtitle: Padding(
          padding: const EdgeInsets.only(top: 6),
          child: Wrap(
            spacing: 6,
            runSpacing: 6,
            children: [
              Text(
                '${lot['code']}',
                style: const TextStyle(fontFamily: 'monospace', fontSize: 13),
              ),
              if (lot['awaiting_me'] == true)
                const TraceChip.warning('Inasubiri uthibitisho wako')
              else if (lot['status'] == 'in_transfer')
                const TraceChip.warning('Inakabidhiwa')
              else
                TraceChip(
                  TraceStrings.lotStatus[lot['status']] ?? '${lot['status']}',
                ),
              if (lot['needs_review'] == true)
                const TraceChip.danger('Inakaguliwa'),
            ],
          ),
        ),
        trailing: const Icon(Icons.chevron_right),
      ),
    );
  }
}

class _LocalLotTile extends StatelessWidget {
  final TraceOp op;
  final VoidCallback onTap;
  const _LocalLotTile({required this.op, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 10),
      child: ListTile(
        onTap: onTap,
        minVerticalPadding: 12,
        leading: const Icon(Icons.phone_android, color: MkColors.muted),
        title: Text(
          op.label,
          style: const TextStyle(fontWeight: FontWeight.w700),
        ),
        subtitle: Padding(
          padding: const EdgeInsets.only(top: 6),
          child: Wrap(
            spacing: 6,
            runSpacing: 6,
            children: [
              if (op.lotCode != null)
                Text(
                  op.lotCode!,
                  style: const TextStyle(fontFamily: 'monospace', fontSize: 13),
                ),
              TraceOpChip(op),
            ],
          ),
        ),
        trailing: const Icon(Icons.chevron_right),
      ),
    );
  }
}

class _SignedOut extends StatelessWidget {
  final VoidCallback onScan;
  final Future<void> Function() onSignedIn;
  const _SignedOut({required this.onScan, required this.onSignedIn});

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(24),
      children: [
        const Icon(Icons.qr_code_2, size: 64, color: MkColors.primary),
        const SizedBox(height: 12),
        const Text(
          TraceStrings.tagline,
          textAlign: TextAlign.center,
          style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800),
        ),
        const Text(
          TraceStrings.taglineEn,
          textAlign: TextAlign.center,
          style: TextStyle(color: MkColors.muted),
        ),
        const SizedBox(height: 12),
        const Text(
          TraceStrings.serviceDescription,
          textAlign: TextAlign.center,
          style: TextStyle(height: 1.45),
        ),
        const SizedBox(height: 24),
        FilledButton(
          onPressed: () async {
            final ok = await AuthProvider.requireAuth(
              context,
              action: 'kutumia Mkulima Trace',
            );
            if (ok) await onSignedIn();
          },
          style: FilledButton.styleFrom(minimumSize: const Size.fromHeight(48)),
          child: const Text('Ingia ili uanze'),
        ),
        const SizedBox(height: 12),
        OutlinedButton.icon(
          onPressed: onScan,
          icon: const Icon(Icons.qr_code_scanner),
          label: const Text('Changanua QR ya mzigo'),
          style: OutlinedButton.styleFrom(
            minimumSize: const Size.fromHeight(48),
          ),
        ),
        const TraceNotice(),
      ],
    );
  }
}

/// Registration, as its own screen (used to edit a pending registration).
class _RegisterScreen extends StatelessWidget {
  const _RegisterScreen();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Usajili wa Mkulima Trace')),
      body: _RegisterView(onDone: () async => Navigator.of(context).pop()),
    );
  }
}

class _RegisterView extends StatefulWidget {
  final Future<void> Function() onDone;
  const _RegisterView({required this.onDone});

  @override
  State<_RegisterView> createState() => _RegisterViewState();
}

class _RegisterViewState extends State<_RegisterView> {
  final _form = GlobalKey<FormState>();
  final _org = TextEditingController();
  late String _type;
  String? _region;
  bool _shareContact = false;
  bool _busy = false;

  @override
  void initState() {
    super.initState();
    final me = TraceSync.instance.me;
    _type = me?['type'] as String? ?? 'farmer';
    _org.text = me?['organisation_name'] as String? ?? '';
    _region = me?['region'] as String?;
    _shareContact = me?['share_contact_with_chain'] == true;
  }

  @override
  void dispose() {
    _org.dispose();
    super.dispose();
  }

  bool get _needsOrg => !['farmer', 'transporter'].contains(_type);

  Future<void> _submit() async {
    if (!_form.currentState!.validate()) return;
    if (!context.read<ConnectivityProvider>().isOnline) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            'Usajili unahitaji intaneti. Jaribu tena ukipata mtandao.',
          ),
        ),
      );
      return;
    }
    setState(() => _busy = true);
    try {
      final r = await context.read<ApiService>().post(
        '/v1/trace/participants',
        data: {
          'type': _type,
          if (_org.text.trim().isNotEmpty)
            'organisation_name': _org.text.trim(),
          if (_region != null) 'region': _region,
          'share_contact_with_chain': _shareContact,
        },
      );
      await TraceSync.instance.remember('me', ApiService.v1Data(r.data));
      await widget.onDone();
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text(traceError(e).message)));
      }
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return TraceFormBody(
      formKey: _form,
      busy: _busy,
      submitLabel: 'Jisajili',
      onSubmit: _submit,
      children: [
        const Text(
          'Jiunge na Mkulima Trace',
          style: TextStyle(fontSize: 22, fontWeight: FontWeight.w800),
        ),
        const Text(
          '${TraceStrings.tagline} · ${TraceStrings.taglineEn}',
          style: TextStyle(color: MkColors.muted),
        ),
        const SizedBox(height: 16),
        const Text(
          'Unashiriki kama nani?',
          style: TextStyle(fontWeight: FontWeight.w700),
        ),
        const SizedBox(height: 8),
        RadioGroup<String>(
          groupValue: _type,
          onChanged: (v) => setState(() => _type = v ?? _type),
          child: Column(
            children: [
              for (final e in TraceStrings.types.entries)
                RadioListTile<String>(
                  value: e.key,
                  contentPadding: EdgeInsets.zero,
                  title: Text('${e.value.$1} · ${e.value.$2}'),
                  subtitle: Text(TraceStrings.typeHelp[e.key] ?? ''),
                ),
            ],
          ),
        ),
        const SizedBox(height: 8),
        TextFormField(
          controller: _org,
          decoration: InputDecoration(
            labelText: _needsOrg
                ? 'Jina la ushirika / kampuni'
                : 'Jina la kikundi au biashara (si lazima)',
          ),
          validator: (v) => _needsOrg && (v ?? '').trim().isEmpty
              ? 'Andika jina la ushirika au kampuni'
              : null,
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
        ),
        const SizedBox(height: 12),
        SwitchListTile(
          value: _shareContact,
          contentPadding: EdgeInsets.zero,
          onChanged: (v) => setState(() => _shareContact = v),
          title: const Text(
            'Ruhusu walio kwenye mnyororo wa mzigo wangu kuona namba yangu ya simu',
          ),
          subtitle: const Text(
            'Bila ruhusa hii, namba yako inabaki siri. Unaweza kubadilisha baadaye.',
          ),
        ),
      ],
    );
  }
}
