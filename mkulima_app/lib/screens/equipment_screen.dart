import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../core/format.dart';
import '../core/regions.dart';
import '../core/theme.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import '../services/location_service.dart';
import '../widgets/mk_empty_state.dart';
import '../widgets/mk_error_state.dart';
import '../widgets/mk_near_me_bar.dart';
import '../widgets/mk_skeleton.dart';

/// Kukodi Zana — rent tractors, pumps and other equipment nearby, or list
/// your own. A farmer requests dates; the owner accepts or declines. No money
/// moves in the app, and phone numbers are shared only after acceptance.
class EquipmentScreen extends StatelessWidget {
  const EquipmentScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 2,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('Kukodi zana'),
          bottom: const TabBar(
            tabs: [
              Tab(text: 'Tafuta zana'),
              Tab(text: 'Maombi yangu'),
            ],
          ),
        ),
        body: const TabBarView(children: [_BrowseTab(), _RentalsTab()]),
        floatingActionButton: Builder(
          builder: (context) => FloatingActionButton.extended(
            onPressed: () async {
              final ok = await AuthProvider.requireAuth(
                context,
                action: 'kuweka zana yako',
              );
              if (!ok || !context.mounted) return;
              await Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const _NewListingScreen()),
              );
            },
            icon: const Icon(Icons.add),
            label: const Text('Weka zana yako'),
          ),
        ),
      ),
    );
  }
}

// ── Browse ────────────────────────────────────────────────────────────────

class _BrowseTab extends StatefulWidget {
  const _BrowseTab();

  @override
  State<_BrowseTab> createState() => _BrowseTabState();
}

class _BrowseTabState extends State<_BrowseTab>
    with AutomaticKeepAliveClientMixin {
  List<dynamic> _items = [];
  List<dynamic> _categories = [];
  String? _category;
  ({double lat, double lng})? _position;
  String? _region;
  bool _locating = true;
  bool _loading = true;
  bool _failed = false;

  @override
  bool get wantKeepAlive => true;

  @override
  void initState() {
    super.initState();
    _loadCategories();
    _locate();
  }

  Future<void> _loadCategories() async {
    try {
      final r = await context.read<ApiService>().get(
        '/v1/equipment/categories',
      );
      if (mounted) {
        setState(() => _categories = (r.data['data'] as List?) ?? []);
      }
    } catch (_) {
      // Chips are optional; the list works without them.
    }
  }

  Future<void> _locate() async {
    setState(() => _locating = true);
    final position = await LocationService.approximate();
    if (!mounted) return;
    setState(() {
      _position = position;
      _locating = false;
    });
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _failed = false;
    });
    try {
      final r = await context.read<ApiService>().get(
        '/v1/equipment',
        queryParameters: {
          if (_position != null) 'lat': _position!.lat,
          if (_position != null) 'lng': _position!.lng,
          if (_region != null) 'region': _region,
          if (_category != null) 'category': _category,
        },
      );
      if (!mounted) return;
      setState(() {
        _items = (r.data['data'] as List?) ?? [];
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
    super.build(context);
    return Column(
      children: [
        MkNearMeBar(
          hasLocation: _position != null,
          locating: _locating,
          region: _region,
          onUseLocation: _locate,
          onRegion: (region) {
            setState(() {
              _region = region;
              _position = null;
            });
            _load();
          },
        ),
        if (_categories.isNotEmpty)
          SizedBox(
            height: 56,
            child: ListView(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              children: [
                _chip('Zote', null),
                for (final c in _categories)
                  _chip(c['label'] as String, c['key'] as String),
              ],
            ),
          ),
        Expanded(child: _body()),
      ],
    );
  }

  Widget _chip(String label, String? key) {
    final selected = _category == key;
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 4),
      child: ChoiceChip(
        label: Text(label),
        selected: selected,
        onSelected: (_) {
          setState(() => _category = key);
          _load();
        },
      ),
    );
  }

  Widget _body() {
    if (_loading || _locating) {
      return const MkListSkeleton(rows: 5, padding: EdgeInsets.all(16));
    }
    if (_failed) {
      return MkErrorState(
        title: 'Hatukuweza kupakia zana',
        message: 'Angalia intaneti yako, kisha jaribu tena.',
        onRetry: _load,
      );
    }
    if (_items.isEmpty) {
      return MkEmptyState(
        icon: Icons.agriculture_outlined,
        title: 'Hakuna zana karibu bado',
        subtitle: _position != null
            ? 'Hakuna zana iliyowekwa ndani ya km 50. Chagua mkoa mwingine, au weka zana yako.'
            : 'Jaribu mkoa mwingine, au weka zana yako.',
      );
    }
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView.separated(
        padding: const EdgeInsets.fromLTRB(16, 8, 16, 96),
        itemCount: _items.length,
        separatorBuilder: (_, _) => const SizedBox(height: 12),
        itemBuilder: (context, i) {
          final item = Map<String, dynamic>.from(_items[i] as Map);
          return _EquipmentCard(
            data: item,
            onTap: () => showModalBottomSheet(
              context: context,
              isScrollControlled: true,
              showDragHandle: true,
              builder: (_) => _RequestSheet(data: item),
            ),
          );
        },
      ),
    );
  }
}

String _where(Map<String, dynamic> e) {
  final distance = parseNum(e['distance_km']);
  final region = (e['region'] ?? '').toString();
  if (distance == null) return region;
  final km = distance < 1
      ? 'chini ya km 1'
      : 'km ${distance.toStringAsFixed(distance < 10 ? 1 : 0)}';
  return region.isEmpty ? km : '$km · $region';
}

class _EquipmentCard extends StatelessWidget {
  final Map<String, dynamic> data;
  final VoidCallback onTap;
  const _EquipmentCard({required this.data, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final rate = parseNum(data['daily_rate']) ?? 0;
    return Material(
      color: MkColors.surface,
      borderRadius: BorderRadius.circular(MkRadii.card),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(MkRadii.card),
        child: Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            border: Border.all(color: MkColors.border),
            borderRadius: BorderRadius.circular(MkRadii.card),
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: MkColors.leafPale,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(
                  _iconFor(data['category'] as String?),
                  color: MkColors.primary,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      data['title'] ?? '',
                      style: MkText.title,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 2),
                    Text(
                      '${data['category_label'] ?? ''} · ${_where(data)}',
                      style: MkText.bodyMuted,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Text(
                          '${mkMoney(rate)} / siku',
                          style: MkText.label.copyWith(color: MkColors.primary),
                        ),
                        if (data['with_operator'] == true) ...[
                          const SizedBox(width: 8),
                          const Flexible(
                            child: Text(
                              'Na dereva',
                              style: MkText.caption,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

IconData _iconFor(String? category) => switch (category) {
  'tractor' || 'power_tiller' || 'harvester' => Icons.agriculture,
  'water_pump' => Icons.water_drop_outlined,
  'sprayer' => Icons.shower_outlined,
  'trailer' => Icons.local_shipping_outlined,
  'planter' || 'plough' => Icons.grass_outlined,
  'thresher' => Icons.settings_outlined,
  _ => Icons.handyman_outlined,
};

class _RequestSheet extends StatefulWidget {
  final Map<String, dynamic> data;
  const _RequestSheet({required this.data});

  @override
  State<_RequestSheet> createState() => _RequestSheetState();
}

class _RequestSheetState extends State<_RequestSheet> {
  final _message = TextEditingController();
  DateTimeRange? _range;
  bool _sending = false;

  @override
  void dispose() {
    _message.dispose();
    super.dispose();
  }

  int get _days =>
      _range == null ? 0 : _range!.end.difference(_range!.start).inDays + 1;

  Future<void> _pickDates() async {
    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);
    final picked = await showDateRangePicker(
      context: context,
      firstDate: today,
      lastDate: today.add(const Duration(days: 180)),
      initialDateRange: _range,
      helpText: 'Chagua siku za kukodi',
    );
    if (picked != null) setState(() => _range = picked);
  }

  String _ymd(DateTime d) =>
      '${d.year}-${d.month.toString().padLeft(2, '0')}-${d.day.toString().padLeft(2, '0')}';

  Future<void> _send() async {
    if (_range == null) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('Chagua tarehe kwanza.')));
      return;
    }
    final ok = await AuthProvider.requireAuth(context, action: 'kukodi zana');
    if (!ok || !mounted) return;

    setState(() => _sending = true);
    try {
      await context.read<ApiService>().post(
        '/v1/equipment/${widget.data['uuid']}/rentals',
        data: {
          'start_date': _ymd(_range!.start),
          'end_date': _ymd(_range!.end),
          if (_message.text.trim().isNotEmpty) 'message': _message.text.trim(),
        },
      );
      if (!mounted) return;
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            'Ombi limetumwa. Utapata namba ya simu mwenye zana akikubali.',
          ),
        ),
      );
    } catch (e) {
      if (!mounted) return;
      setState(() => _sending = false);
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text(ApiService.formatError(e))));
    }
  }

  @override
  Widget build(BuildContext context) {
    final e = widget.data;
    final rate = parseNum(e['daily_rate']) ?? 0;
    final dates = _range == null
        ? 'Chagua tarehe'
        : '${_range!.start.day}/${_range!.start.month} – ${_range!.end.day}/${_range!.end.month} · siku $_days';

    return Padding(
      padding: EdgeInsets.fromLTRB(
        16,
        0,
        16,
        16 + MediaQuery.viewInsetsOf(context).bottom,
      ),
      child: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(e['title'] ?? '', style: MkText.page),
            const SizedBox(height: 4),
            Text(
              '${e['category_label'] ?? ''} · ${_where(e)}',
              style: MkText.bodyMuted,
            ),
            const SizedBox(height: 4),
            Text(
              'Mmiliki: ${e['owner']?['name'] ?? '—'}',
              style: MkText.bodyMuted,
            ),
            if ((e['description'] ?? '').toString().isNotEmpty) ...[
              const SizedBox(height: 12),
              Text(e['description'], style: MkText.body),
            ],
            const SizedBox(height: 12),
            Text(
              '${mkMoney(rate)} kwa siku${e['with_operator'] == true ? ' · pamoja na dereva' : ''}',
              style: MkText.title.copyWith(color: MkColors.primary),
            ),
            const SizedBox(height: 16),
            OutlinedButton.icon(
              onPressed: _pickDates,
              icon: const Icon(Icons.date_range_outlined, size: 18),
              label: Text(dates),
              style: OutlinedButton.styleFrom(
                minimumSize: const Size.fromHeight(48),
                foregroundColor: MkColors.ink,
              ),
            ),
            if (_days > 0) ...[
              const SizedBox(height: 8),
              Text(
                'Makadirio: ${mkMoney(rate * _days)}',
                style: MkText.label,
                textAlign: TextAlign.center,
              ),
            ],
            const SizedBox(height: 12),
            TextField(
              controller: _message,
              maxLines: 2,
              maxLength: 500,
              decoration: const InputDecoration(
                labelText: 'Ujumbe kwa mmiliki (si lazima)',
                hintText: 'Mf. Kulima ekari 3 Kijiji cha Mkundi',
              ),
            ),
            FilledButton(
              onPressed: _sending ? null : _send,
              style: FilledButton.styleFrom(
                minimumSize: const Size.fromHeight(52),
              ),
              child: Text(_sending ? 'Inatuma...' : 'Omba kukodi'),
            ),
            const SizedBox(height: 8),
            const Text(
              'Hakuna malipo kwenye app. Lipana na mmiliki baada ya kukubaliana.',
              style: MkText.caption,
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}

// ── My requests (as renter and as owner) ─────────────────────────────────

class _RentalsTab extends StatefulWidget {
  const _RentalsTab();

  @override
  State<_RentalsTab> createState() => _RentalsTabState();
}

class _RentalsTabState extends State<_RentalsTab> {
  String _as = 'renter';
  List<dynamic> _items = [];
  bool _loading = true;
  bool _failed = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final auth = context.read<AuthProvider>();
    if (!auth.isAuthenticated) {
      setState(() => _loading = false);
      return;
    }
    setState(() {
      _loading = true;
      _failed = false;
    });
    try {
      final r = await context.read<ApiService>().get(
        '/v1/me/rentals',
        queryParameters: {'as': _as},
      );
      if (!mounted) return;
      setState(() {
        _items = (r.data['data'] as List?) ?? [];
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

  Future<void> _act(String uuid, String action) async {
    try {
      await context.read<ApiService>().post('/v1/rentals/$uuid/$action');
      _load();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text(ApiService.formatError(e))));
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    if (!auth.isAuthenticated) {
      return MkEmptyState(
        icon: Icons.lock_outline,
        title: 'Ingia kuona maombi yako',
        subtitle: 'Maombi ya kukodi na zana ulizoweka yanaonekana hapa.',
        actionLabel: 'Ingia',
        onAction: () async {
          final ok = await AuthProvider.requireAuth(
            context,
            action: 'kuona maombi yako',
          );
          if (ok) _load();
        },
      );
    }

    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 4),
          child: SegmentedButton<String>(
            segments: const [
              ButtonSegment(value: 'renter', label: Text('Nimeomba')),
              ButtonSegment(value: 'owner', label: Text('Zana zangu')),
            ],
            selected: {_as},
            onSelectionChanged: (s) {
              setState(() => _as = s.first);
              _load();
            },
            showSelectedIcon: false,
          ),
        ),
        Expanded(child: _body()),
      ],
    );
  }

  Widget _body() {
    if (_loading) {
      return const MkListSkeleton(rows: 4, padding: EdgeInsets.all(16));
    }
    if (_failed) {
      return MkErrorState(
        title: 'Hatukuweza kupakia maombi',
        message: 'Jaribu tena.',
        onRetry: _load,
      );
    }
    if (_items.isEmpty) {
      return MkEmptyState(
        icon: Icons.event_note_outlined,
        title: _as == 'renter'
            ? 'Bado hujaomba zana'
            : 'Bado hakuna aliyeomba zana zako',
        subtitle: _as == 'renter'
            ? 'Tafuta zana karibu nawe kwenye kichupo cha kwanza.'
            : 'Ukiweka zana, maombi ya wakulima yataonekana hapa.',
      );
    }
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView.separated(
        padding: const EdgeInsets.fromLTRB(16, 8, 16, 96),
        itemCount: _items.length,
        separatorBuilder: (_, _) => const SizedBox(height: 12),
        itemBuilder: (context, i) => _RentalCard(
          data: Map<String, dynamic>.from(_items[i] as Map),
          onAction: _act,
        ),
      ),
    );
  }
}

const _statusLabels = {
  'requested': 'Inasubiri jibu',
  'accepted': 'Imekubaliwa',
  'declined': 'Imekataliwa',
  'cancelled': 'Imesitishwa',
  'completed': 'Imekamilika',
};

class _RentalCard extends StatelessWidget {
  final Map<String, dynamic> data;
  final void Function(String uuid, String action) onAction;
  const _RentalCard({required this.data, required this.onAction});

  @override
  Widget build(BuildContext context) {
    final status = data['status'] as String? ?? '';
    final isOwner = data['role'] == 'owner';
    final phone = data['counterpart']?['phone'] as String?;
    final name = data['counterpart']?['name'] as String? ?? '—';
    final uuid = data['uuid'] as String;
    final total = parseNum(data['quoted_total']) ?? 0;

    final statusColor = switch (status) {
      'accepted' || 'completed' => MkColors.primary,
      'declined' || 'cancelled' => MkColors.muted,
      _ => MkColors.accent,
    };

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
                  data['equipment']?['title'] ?? 'Zana',
                  style: MkText.title,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              const SizedBox(width: 8),
              Text(
                _statusLabels[status] ?? status,
                style: MkText.label.copyWith(color: statusColor),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Text(
            '${data['start_date']} – ${data['end_date']} · siku ${data['days']} · ${mkMoney(total)}',
            style: MkText.bodyMuted,
          ),
          const SizedBox(height: 4),
          Text(
            '${isOwner ? 'Mkodishaji' : 'Mmiliki'}: $name',
            style: MkText.body,
          ),
          if ((data['message'] ?? '').toString().isNotEmpty) ...[
            const SizedBox(height: 4),
            Text('"${data['message']}"', style: MkText.bodyMuted),
          ],
          if (phone != null) ...[
            const SizedBox(height: 8),
            OutlinedButton.icon(
              onPressed: () => launchUrl(Uri(scheme: 'tel', path: phone)),
              icon: const Icon(Icons.call_outlined, size: 18),
              label: Text('Piga simu $phone'),
              style: OutlinedButton.styleFrom(minimumSize: const Size(0, 44)),
            ),
          ],
          const SizedBox(height: 8),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              if (isOwner && status == 'requested') ...[
                FilledButton(
                  onPressed: () => onAction(uuid, 'accept'),
                  style: FilledButton.styleFrom(minimumSize: const Size(0, 44)),
                  child: const Text('Kubali'),
                ),
                OutlinedButton(
                  onPressed: () => onAction(uuid, 'decline'),
                  style: OutlinedButton.styleFrom(
                    minimumSize: const Size(0, 44),
                  ),
                  child: const Text('Kataa'),
                ),
              ],
              if (isOwner && status == 'accepted')
                OutlinedButton(
                  onPressed: () => onAction(uuid, 'complete'),
                  style: OutlinedButton.styleFrom(
                    minimumSize: const Size(0, 44),
                  ),
                  child: const Text('Imekamilika'),
                ),
              if (status == 'requested' || status == 'accepted')
                TextButton(
                  onPressed: () => onAction(uuid, 'cancel'),
                  style: TextButton.styleFrom(
                    minimumSize: const Size(0, 44),
                    foregroundColor: MkColors.danger,
                  ),
                  child: const Text('Sitisha'),
                ),
            ],
          ),
        ],
      ),
    );
  }
}

// ── List your equipment ───────────────────────────────────────────────────

class _NewListingScreen extends StatefulWidget {
  const _NewListingScreen();

  @override
  State<_NewListingScreen> createState() => _NewListingScreenState();
}

class _NewListingScreenState extends State<_NewListingScreen> {
  final _form = GlobalKey<FormState>();
  final _title = TextEditingController();
  final _description = TextEditingController();
  final _rate = TextEditingController();
  String _category = 'tractor';
  String? _region;
  bool _withOperator = false;
  bool _useLocation = true;
  bool _saving = false;

  static const _categories = {
    'tractor': 'Trekta',
    'power_tiller': 'Power tiller',
    'plough': 'Jembe la kukokotwa',
    'planter': 'Mashine ya kupanda',
    'sprayer': 'Bomba la kunyunyizia',
    'harvester': 'Mashine ya kuvuna',
    'thresher': 'Mashine ya kupura',
    'water_pump': 'Pampu ya maji',
    'trailer': 'Tela',
    'other': 'Nyingine',
  };

  @override
  void dispose() {
    _title.dispose();
    _description.dispose();
    _rate.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    if (!_form.currentState!.validate()) return;
    setState(() => _saving = true);

    final position = _useLocation ? await LocationService.approximate() : null;
    if (!mounted) return;
    try {
      await context.read<ApiService>().post(
        '/v1/equipment',
        data: {
          'category': _category,
          'title': _title.text.trim(),
          if (_description.text.trim().isNotEmpty)
            'description': _description.text.trim(),
          'daily_rate': int.parse(_rate.text.replaceAll(RegExp(r'[^0-9]'), '')),
          'with_operator': _withOperator,
          'region': _region,
          if (position != null) 'lat': position.lat,
          if (position != null) 'lng': position.lng,
        },
      );
      if (!mounted) return;
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            'Zana yako imewekwa. Wakulima wa karibu wanaiona sasa.',
          ),
        ),
      );
    } catch (e) {
      if (!mounted) return;
      setState(() => _saving = false);
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text(ApiService.formatError(e))));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Weka zana yako')),
      body: Form(
        key: _form,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            DropdownButtonFormField<String>(
              initialValue: _category,
              decoration: const InputDecoration(labelText: 'Aina ya zana'),
              items: [
                for (final e in _categories.entries)
                  DropdownMenuItem(value: e.key, child: Text(e.value)),
              ],
              onChanged: (v) => setState(() => _category = v ?? 'tractor'),
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _title,
              maxLength: 120,
              decoration: const InputDecoration(
                labelText: 'Jina',
                hintText: 'Mf. Trekta Massey Ferguson 375',
              ),
              validator: (v) =>
                  (v ?? '').trim().length < 3 ? 'Andika jina la zana' : null,
            ),
            TextFormField(
              controller: _rate,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(
                labelText: 'Bei kwa siku (TZS)',
                hintText: 'Mf. 120000',
              ),
              validator: (v) {
                final n = int.tryParse(
                  (v ?? '').replaceAll(RegExp(r'[^0-9]'), ''),
                );
                return n == null || n < 1000
                    ? 'Weka bei ya angalau TZS 1,000'
                    : null;
              },
            ),
            const SizedBox(height: 12),
            DropdownButtonFormField<String>(
              initialValue: _region,
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
              controller: _description,
              maxLines: 3,
              maxLength: 2000,
              decoration: const InputDecoration(
                labelText: 'Maelezo (si lazima)',
                hintText: 'Hali ya zana, nani analipia mafuta, n.k.',
              ),
            ),
            SwitchListTile(
              contentPadding: EdgeInsets.zero,
              title: const Text('Inakuja na dereva/mwendeshaji'),
              value: _withOperator,
              onChanged: (v) => setState(() => _withOperator = v),
            ),
            SwitchListTile(
              contentPadding: EdgeInsets.zero,
              title: const Text('Tumia mahali nilipo sasa'),
              subtitle: const Text(
                'Wakulima wa karibu wataiona kwanza. Namba yako ya simu haionyeshwi.',
              ),
              value: _useLocation,
              onChanged: (v) => setState(() => _useLocation = v),
            ),
            const SizedBox(height: 12),
            FilledButton(
              onPressed: _saving ? null : _save,
              style: FilledButton.styleFrom(
                minimumSize: const Size.fromHeight(52),
              ),
              child: Text(_saving ? 'Inaweka...' : 'Weka zana'),
            ),
          ],
        ),
      ),
    );
  }
}
