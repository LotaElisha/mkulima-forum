import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../core/format.dart';
import '../core/regions.dart';
import '../core/theme.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import '../widgets/mk_empty_state.dart';
import '../widgets/mk_error_state.dart';
import '../widgets/mk_skeleton.dart';

/// Usafirishaji — ask for transport of produce, inputs, equipment or
/// livestock. Verified transporters quote a fare; the farmer accepts or
/// cancels. Built on the existing /api/logistics endpoints.
///
/// No fare is shown until a transporter quotes one, and nothing here claims
/// a vehicle is available right now: the app does not know that.
class LogisticsScreen extends StatelessWidget {
  const LogisticsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 3,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('Usafirishaji'),
          bottom: const TabBar(
            isScrollable: true,
            tabAlignment: TabAlignment.start,
            tabs: [Tab(text: 'Omba usafiri'), Tab(text: 'Maombi yangu'), Tab(text: 'Wasafirishaji')],
          ),
        ),
        body: const TabBarView(children: [_RequestForm(), _MyRequests(), _Transporters()]),
      ),
    );
  }
}

const _cargoTypes = {
  'produce': 'Mazao',
  'inputs': 'Pembejeo',
  'equipment': 'Zana/mashine',
  'livestock': 'Mifugo',
  'other': 'Mengineyo',
};

const _vehicleTypes = {
  'bodaboda': 'Bodaboda',
  'bajaji': 'Bajaji',
  'pickup': 'Pickup',
  'canter': 'Canter (tani 3–4)',
  'lorry': 'Lori',
  'refrigerated': 'Gari la baridi',
};

const _statusLabels = {
  'open': 'Inasubiri bei',
  'quoted': 'Bei imetumwa',
  'accepted': 'Umekubali',
  'in_transit': 'Njiani',
  'delivered': 'Imefika',
  'cancelled': 'Imesitishwa',
};

// ── Request form ──────────────────────────────────────────────────────────

class _RequestForm extends StatefulWidget {
  const _RequestForm();

  @override
  State<_RequestForm> createState() => _RequestFormState();
}

class _RequestFormState extends State<_RequestForm> with AutomaticKeepAliveClientMixin {
  final _form = GlobalKey<FormState>();
  final _pickup = TextEditingController();
  final _dropoff = TextEditingController();
  final _weight = TextEditingController();
  final _details = TextEditingController();
  String _cargo = 'produce';
  String? _vehicle;
  DateTime? _date;
  bool _sending = false;

  @override
  bool get wantKeepAlive => true;

  @override
  void dispose() {
    _pickup.dispose();
    _dropoff.dispose();
    _weight.dispose();
    _details.dispose();
    super.dispose();
  }

  Future<void> _pickDate() async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      firstDate: now.add(const Duration(days: 1)),
      lastDate: now.add(const Duration(days: 90)),
      initialDate: _date ?? now.add(const Duration(days: 1)),
      helpText: 'Siku ya kubeba mzigo',
    );
    if (picked != null) setState(() => _date = picked);
  }

  Future<void> _submit() async {
    if (!_form.currentState!.validate()) return;
    final ok = await AuthProvider.requireAuth(context, action: 'kuomba usafiri');
    if (!ok || !mounted) return;

    setState(() => _sending = true);
    try {
      await context.read<ApiService>().post('/logistics/freight', data: {
        'pickup_location': _pickup.text.trim(),
        'dropoff_location': _dropoff.text.trim(),
        'cargo_type': _cargo,
        if (_vehicle != null) 'vehicle_type': _vehicle,
        if (_weight.text.trim().isNotEmpty) 'cargo_weight_kg': num.tryParse(_weight.text.trim()),
        if (_details.text.trim().isNotEmpty) 'cargo_description': _details.text.trim(),
        if (_date != null) 'pickup_at': DateTime(_date!.year, _date!.month, _date!.day, 8).toIso8601String(),
      });
      if (!mounted) return;
      _form.currentState!.reset();
      _pickup.clear();
      _dropoff.clear();
      _weight.clear();
      _details.clear();
      setState(() {
        _sending = false;
        _date = null;
        _vehicle = null;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Ombi limetumwa. Wasafirishaji waliothibitishwa watatuma bei.')),
      );
      DefaultTabController.of(context).animateTo(1);
    } catch (e) {
      if (!mounted) return;
      setState(() => _sending = false);
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(ApiService.formatError(e))));
    }
  }

  @override
  Widget build(BuildContext context) {
    super.build(context);
    return Form(
      key: _form,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          const Text('Unasafirisha nini?', style: MkText.label),
          const SizedBox(height: 8),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              for (final e in _cargoTypes.entries)
                ChoiceChip(
                  label: Text(e.value),
                  selected: _cargo == e.key,
                  onSelected: (_) => setState(() => _cargo = e.key),
                ),
            ],
          ),
          const SizedBox(height: 16),
          TextFormField(
            controller: _pickup,
            maxLength: 255,
            decoration: const InputDecoration(labelText: 'Mahali pa kubeba', hintText: 'Mf. Shamba, Kijiji cha Mkundi, Mvomero'),
            validator: (v) => (v ?? '').trim().length < 3 ? 'Andika mahali pa kubeba' : null,
          ),
          TextFormField(
            controller: _dropoff,
            maxLength: 255,
            decoration: const InputDecoration(labelText: 'Mahali pa kufikisha', hintText: 'Mf. Soko la Mawenzi, Morogoro'),
            validator: (v) => (v ?? '').trim().length < 3 ? 'Andika mahali pa kufikisha' : null,
          ),
          TextFormField(
            controller: _weight,
            keyboardType: TextInputType.number,
            decoration: const InputDecoration(labelText: 'Uzito kwa kilo (si lazima)', hintText: 'Mf. 800 (magunia 8 ya kilo 100)'),
            validator: (v) {
              if ((v ?? '').trim().isEmpty) return null;
              final n = num.tryParse(v!.trim());
              return n == null || n <= 0 ? 'Weka namba sahihi' : null;
            },
          ),
          const SizedBox(height: 12),
          DropdownButtonFormField<String>(
            initialValue: _vehicle,
            decoration: const InputDecoration(labelText: 'Aina ya gari (si lazima)'),
            items: [
              const DropdownMenuItem(value: null, child: Text('Yoyote inayofaa')),
              for (final e in _vehicleTypes.entries) DropdownMenuItem(value: e.key, child: Text(e.value)),
            ],
            onChanged: (v) => setState(() => _vehicle = v),
          ),
          const SizedBox(height: 12),
          OutlinedButton.icon(
            onPressed: _pickDate,
            icon: const Icon(Icons.calendar_today_outlined, size: 18),
            label: Text(_date == null ? 'Siku ya kubeba (si lazima)' : 'Siku: ${_date!.day}/${_date!.month}/${_date!.year}'),
            style: OutlinedButton.styleFrom(minimumSize: const Size.fromHeight(48), foregroundColor: MkColors.ink),
          ),
          const SizedBox(height: 12),
          TextFormField(
            controller: _details,
            maxLines: 2,
            maxLength: 2000,
            decoration: const InputDecoration(labelText: 'Maelezo (si lazima)', hintText: 'Mf. Magunia 8 ya mahindi, barabara ya vumbi'),
          ),
          const SizedBox(height: 8),
          FilledButton(
            onPressed: _sending ? null : _submit,
            style: FilledButton.styleFrom(minimumSize: const Size.fromHeight(52)),
            child: Text(_sending ? 'Inatuma...' : 'Tuma ombi la usafiri'),
          ),
          const SizedBox(height: 8),
          const Text(
            'Bei inatumwa na msafirishaji. Hakuna malipo kwenye app.',
            style: MkText.caption,
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}

// ── My requests ───────────────────────────────────────────────────────────

class _MyRequests extends StatefulWidget {
  const _MyRequests();

  @override
  State<_MyRequests> createState() => _MyRequestsState();
}

class _MyRequestsState extends State<_MyRequests> {
  List<dynamic> _items = [];
  bool _loading = true;
  bool _failed = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    if (!context.read<AuthProvider>().isAuthenticated) {
      setState(() => _loading = false);
      return;
    }
    setState(() {
      _loading = true;
      _failed = false;
    });
    try {
      final r = await context.read<ApiService>().get('/logistics/freight');
      if (!mounted) return;
      setState(() {
        _items = (r.data['freight'] as List?) ?? [];
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _loading = false;
        _failed = true;
      });
    }
  }

  Future<void> _setStatus(String uuid, String status) async {
    try {
      await context.read<ApiService>().put('/logistics/freight/$uuid', data: {'status': status});
      _load();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(ApiService.formatError(e))));
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    if (!auth.isAuthenticated) {
      return MkEmptyState(
        icon: Icons.lock_outline,
        title: 'Ingia kuona maombi yako',
        actionLabel: 'Ingia',
        onAction: () async {
          if (await AuthProvider.requireAuth(context, action: 'kuona maombi ya usafiri')) _load();
        },
      );
    }
    if (_loading) return const MkListSkeleton(rows: 4, padding: EdgeInsets.all(16));
    if (_failed) return MkErrorState(title: 'Hatukuweza kupakia maombi', message: 'Jaribu tena.', onRetry: _load);
    if (_items.isEmpty) {
      return const MkEmptyState(
        icon: Icons.local_shipping_outlined,
        title: 'Bado hujaomba usafiri',
        subtitle: 'Tuma ombi kwenye kichupo cha kwanza.',
      );
    }
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView.separated(
        padding: const EdgeInsets.all(16),
        itemCount: _items.length,
        separatorBuilder: (_, _) => const SizedBox(height: 12),
        itemBuilder: (context, i) {
          final f = Map<String, dynamic>.from(_items[i] as Map);
          final status = f['status'] as String? ?? 'open';
          final fare = parseNum(f['quoted_fare']);
          final transporter = f['transporter']?['user']?['name'] as String?;
          return Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              border: Border.all(color: MkColors.border),
              borderRadius: BorderRadius.circular(MkRadii.card),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        '${f['pickup_location']} → ${f['dropoff_location']}',
                        style: MkText.title,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    const SizedBox(width: 8),
                    Text(
                      _statusLabels[status] ?? status,
                      style: MkText.label.copyWith(
                        color: status == 'quoted' ? MkColors.accent : (status == 'cancelled' ? MkColors.muted : MkColors.primary),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 4),
                Text(
                  [
                    _cargoTypes[f['cargo_type']] ?? 'Mzigo',
                    if (parseNum(f['cargo_weight_kg']) != null) 'kg ${parseNum(f['cargo_weight_kg'])!.round()}',
                    if (f['vehicle_type'] != null) _vehicleTypes[f['vehicle_type']] ?? f['vehicle_type'],
                  ].join(' · '),
                  style: MkText.bodyMuted,
                ),
                if (fare != null) ...[
                  const SizedBox(height: 6),
                  Text(
                    'Bei: ${mkMoney(fare)}${transporter != null ? ' · $transporter' : ''}',
                    style: MkText.label.copyWith(color: MkColors.primary),
                  ),
                ],
                if (status == 'quoted' || status == 'open') ...[
                  const SizedBox(height: 8),
                  Wrap(
                    spacing: 8,
                    children: [
                      if (status == 'quoted')
                        FilledButton(
                          onPressed: () => _setStatus(f['uuid'], 'accepted'),
                          style: FilledButton.styleFrom(minimumSize: const Size(0, 44)),
                          child: const Text('Kubali bei'),
                        ),
                      TextButton(
                        onPressed: () => _setStatus(f['uuid'], 'cancelled'),
                        style: TextButton.styleFrom(minimumSize: const Size(0, 44), foregroundColor: MkColors.danger),
                        child: const Text('Sitisha'),
                      ),
                    ],
                  ),
                ],
              ],
            ),
          );
        },
      ),
    );
  }
}

// ── Verified transporters ─────────────────────────────────────────────────

class _Transporters extends StatefulWidget {
  const _Transporters();

  @override
  State<_Transporters> createState() => _TransportersState();
}

class _TransportersState extends State<_Transporters> {
  List<dynamic> _items = [];
  String? _region;
  bool _loading = true;
  bool _failed = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _failed = false;
    });
    try {
      final r = await context.read<ApiService>().get(
        '/logistics/transporters',
        queryParameters: {if (_region != null) 'region': _region},
      );
      if (!mounted) return;
      setState(() {
        _items = (r.data['transporters'] as List?) ?? [];
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _loading = false;
        _failed = true;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 4),
          child: DropdownButtonFormField<String>(
            initialValue: _region,
            decoration: const InputDecoration(labelText: 'Mkoa'),
            items: [
              const DropdownMenuItem(value: null, child: Text('Tanzania nzima')),
              for (final r in tanzaniaRegions) DropdownMenuItem(value: r, child: Text(r)),
            ],
            onChanged: (v) {
              setState(() => _region = v);
              _load();
            },
          ),
        ),
        Expanded(
          child: _loading
              ? const MkListSkeleton(rows: 4, padding: EdgeInsets.all(16))
              : _failed
                  ? MkErrorState(title: 'Hatukuweza kupakia wasafirishaji', message: 'Jaribu tena.', onRetry: _load)
                  : _items.isEmpty
                      ? const MkEmptyState(
                          icon: Icons.local_shipping_outlined,
                          title: 'Hakuna msafirishaji aliyethibitishwa hapa bado',
                          subtitle: 'Bado unaweza kutuma ombi; litaonekana kwa wasafirishaji wanapothibitishwa.',
                        )
                      : ListView.separated(
                          padding: const EdgeInsets.all(16),
                          itemCount: _items.length,
                          separatorBuilder: (_, _) => const SizedBox(height: 8),
                          itemBuilder: (context, i) {
                            final t = Map<String, dynamic>.from(_items[i] as Map);
                            final capacity = parseNum(t['capacity_kg']);
                            return ListTile(
                              shape: RoundedRectangleBorder(
                                side: const BorderSide(color: MkColors.border),
                                borderRadius: BorderRadius.circular(MkRadii.card),
                              ),
                              leading: const CircleAvatar(
                                backgroundColor: MkColors.leafPale,
                                child: Icon(Icons.local_shipping_outlined, color: MkColors.primary),
                              ),
                              title: Text(t['user']?['name'] ?? 'Msafirishaji'),
                              subtitle: Text([
                                _vehicleTypes[t['vehicle_type']] ?? t['vehicle_type'] ?? '',
                                t['base_region'] ?? '',
                                if (capacity != null) 'hadi kg ${capacity.round()}',
                              ].where((s) => s.toString().isNotEmpty).join(' · ')),
                            );
                          },
                        ),
        ),
      ],
    );
  }
}
