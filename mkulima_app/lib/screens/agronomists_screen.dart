import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../core/format.dart';
import '../core/theme.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import '../services/location_service.dart';
import '../widgets/mk_empty_state.dart';
import '../widgets/mk_error_state.dart';
import '../widgets/mk_near_me_bar.dart';
import '../widgets/mk_skeleton.dart';

/// Wataalamu wa Kilimo — verified agronomists near the farmer.
///
/// Sorted by distance when the phone shares a coarse location, otherwise by
/// the region the farmer picks. Only admin-verified agronomists are listed,
/// and no phone numbers: the farmer sends a request and the agronomist
/// confirms it.
class AgronomistsScreen extends StatefulWidget {
  const AgronomistsScreen({super.key});

  @override
  State<AgronomistsScreen> createState() => _AgronomistsScreenState();
}

class _AgronomistsScreenState extends State<AgronomistsScreen> {
  List<dynamic> _items = [];
  ({double lat, double lng})? _position;
  String? _region;
  bool _locating = true;
  bool _loading = true;
  bool _failed = false;

  @override
  void initState() {
    super.initState();
    _locate();
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
      final response = await context.read<ApiService>().get(
        '/v1/agronomists',
        queryParameters: {
          if (_position != null) 'lat': _position!.lat,
          if (_position != null) 'lng': _position!.lng,
          if (_region != null) 'region': _region,
        },
      );
      if (!mounted) return;
      setState(() {
        _items = (response.data['data'] as List?) ?? [];
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
    return Scaffold(
      appBar: AppBar(title: const Text('Wataalamu wa kilimo')),
      body: Column(
        children: [
          MkNearMeBar(
            hasLocation: _position != null,
            locating: _locating,
            region: _region,
            onUseLocation: _locate,
            onRegion: (region) {
              setState(() {
                _region = region;
                // Picking a region means "show me there", not "near me".
                _position = null;
              });
              _load();
            },
          ),
          Expanded(child: _body()),
        ],
      ),
    );
  }

  Widget _body() {
    if (_loading || _locating) {
      return const MkListSkeleton(rows: 5, padding: EdgeInsets.all(16));
    }
    if (_failed) {
      return MkErrorState(
        title: 'Hatukuweza kupakia wataalamu',
        message: 'Angalia intaneti yako, kisha jaribu tena.',
        onRetry: _load,
      );
    }
    if (_items.isEmpty) {
      return MkEmptyState(
        icon: Icons.person_search_outlined,
        title: 'Hakuna mtaalamu karibu bado',
        subtitle: _position != null
            ? 'Hakuna aliyethibitishwa ndani ya km 50. Chagua mkoa wako kuona wengine.'
            : 'Jaribu mkoa mwingine au Tanzania nzima.',
      );
    }
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView.separated(
        padding: const EdgeInsets.all(16),
        itemCount: _items.length,
        separatorBuilder: (_, _) => const SizedBox(height: 12),
        itemBuilder: (context, i) => _AgronomistCard(
          data: Map<String, dynamic>.from(_items[i] as Map),
          onTap: () => _openDetail(Map<String, dynamic>.from(_items[i] as Map)),
        ),
      ),
    );
  }

  void _openDetail(Map<String, dynamic> a) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      showDragHandle: true,
      builder: (_) => _AgronomistSheet(data: a),
    );
  }
}

String _place(Map<String, dynamic> a) {
  final distance = parseNum(a['distance_km']);
  final where = [
    a['district'],
    a['region'],
  ].whereType<String>().where((s) => s.isNotEmpty).join(', ');
  if (distance == null) return where;
  final km = distance < 1
      ? 'chini ya km 1'
      : 'km ${distance.toStringAsFixed(distance < 10 ? 1 : 0)}';
  return where.isEmpty ? km : '$km · $where';
}

class _AgronomistCard extends StatelessWidget {
  final Map<String, dynamic> data;
  final VoidCallback onTap;

  const _AgronomistCard({required this.data, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final crops =
        (data['specializations'] as List?)?.cast<String>() ?? const [];
    final fee = parseNum(data['consultation_fee']);
    final rating = parseNum(data['rating']);

    return Material(
      color: MkColors.surface,
      borderRadius: BorderRadius.circular(MkRadii.card),
      child: InkWell(
        borderRadius: BorderRadius.circular(MkRadii.card),
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            border: Border.all(color: MkColors.border),
            borderRadius: BorderRadius.circular(MkRadii.card),
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              CircleAvatar(
                radius: 22,
                backgroundColor: MkColors.leafPale,
                child: const Icon(
                  Icons.agriculture_outlined,
                  color: MkColors.primary,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Flexible(
                          child: Text(
                            data['name'] ?? 'Mtaalamu',
                            style: MkText.title,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        if (data['verified'] == true) ...[
                          const SizedBox(width: 4),
                          const Icon(
                            Icons.verified,
                            size: 16,
                            color: MkColors.primary,
                          ),
                        ],
                      ],
                    ),
                    const SizedBox(height: 2),
                    Text(
                      _place(data),
                      style: MkText.bodyMuted,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    if (crops.isNotEmpty) ...[
                      const SizedBox(height: 8),
                      Wrap(
                        spacing: 6,
                        runSpacing: 6,
                        children: [for (final c in crops.take(3)) _Tag(c)],
                      ),
                    ],
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Text(
                          fee == null
                              ? 'Ada: kwa makubaliano'
                              : 'Ushauri ${mkMoney(fee)}',
                          style: MkText.label.copyWith(color: MkColors.primary),
                        ),
                        const Spacer(),
                        if (rating != null && rating > 0) ...[
                          const Icon(
                            Icons.star_rounded,
                            size: 16,
                            color: MkColors.accent,
                          ),
                          const SizedBox(width: 2),
                          Text(
                            '${rating.toStringAsFixed(1)} (${data['rating_count']})',
                            style: MkText.caption,
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

class _Tag extends StatelessWidget {
  final String label;
  const _Tag(this.label);

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: MkColors.leafPale,
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        label,
        style: MkText.caption.copyWith(color: MkColors.primaryDark),
      ),
    );
  }
}

/// Detail and booking. Booking uses the existing services endpoint, which
/// only accepts verified providers.
class _AgronomistSheet extends StatefulWidget {
  final Map<String, dynamic> data;
  const _AgronomistSheet({required this.data});

  @override
  State<_AgronomistSheet> createState() => _AgronomistSheetState();
}

class _AgronomistSheetState extends State<_AgronomistSheet> {
  final _problem = TextEditingController();
  String _type = 'consultation';
  DateTime? _date;
  bool _sending = false;

  @override
  void dispose() {
    _problem.dispose();
    super.dispose();
  }

  Future<void> _pickDate() async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      firstDate: now.add(const Duration(days: 1)),
      lastDate: now.add(const Duration(days: 60)),
      initialDate: _date ?? now.add(const Duration(days: 1)),
    );
    if (picked != null) setState(() => _date = picked);
  }

  Future<void> _book() async {
    if (_date == null) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('Chagua tarehe kwanza.')));
      return;
    }
    final ok = await AuthProvider.requireAuth(
      context,
      action: 'kuomba ushauri',
    );
    if (!ok || !mounted) return;

    setState(() => _sending = true);
    try {
      await context.read<ApiService>().post(
        '/services/bookings',
        data: {
          'provider_uuid': widget.data['uuid'],
          'booking_type': _type,
          'description': _problem.text.trim().isEmpty
              ? null
              : _problem.text.trim(),
          'scheduled_at': DateTime(
            _date!.year,
            _date!.month,
            _date!.day,
            9,
          ).toIso8601String(),
        },
      );
      if (!mounted) return;
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Ombi limetumwa. Mtaalamu atalithibitisha.'),
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
    final a = widget.data;
    final fee = parseNum(a['consultation_fee']);
    final visit = parseNum(a['visit_fee']);
    final dateLabel = _date == null
        ? 'Chagua tarehe'
        : '${_date!.day}/${_date!.month}/${_date!.year}';

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
            Text(a['name'] ?? 'Mtaalamu', style: MkText.page),
            const SizedBox(height: 4),
            Text(_place(a), style: MkText.bodyMuted),
            if ((a['bio'] ?? '').toString().isNotEmpty) ...[
              const SizedBox(height: 12),
              Text(a['bio'], style: MkText.body),
            ],
            const SizedBox(height: 16),
            const Text('Aina ya huduma', style: MkText.label),
            const SizedBox(height: 8),
            // Short labels: "Kutembelea · TSh 40,000" clipped at 360px.
            SegmentedButton<String>(
              segments: [
                const ButtonSegment(
                  value: 'consultation',
                  label: Text('Ushauri'),
                ),
                if (visit != null)
                  const ButtonSegment(
                    value: 'farm_visit',
                    label: Text('Kutembelea shamba'),
                  ),
              ],
              selected: {_type},
              onSelectionChanged: (s) => setState(() => _type = s.first),
              showSelectedIcon: false,
            ),
            const SizedBox(height: 6),
            Text(switch ((_type == 'farm_visit' ? visit : fee)) {
              null => 'Ada: mtakubaliana',
              final amount => 'Ada: ${mkMoney(amount)}',
            }, style: MkText.label.copyWith(color: MkColors.primary)),
            const SizedBox(height: 16),
            OutlinedButton.icon(
              onPressed: _pickDate,
              icon: const Icon(Icons.calendar_today_outlined, size: 18),
              label: Text(dateLabel),
              style: OutlinedButton.styleFrom(
                minimumSize: const Size.fromHeight(48),
                foregroundColor: MkColors.ink,
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _problem,
              maxLines: 3,
              maxLength: 500,
              decoration: const InputDecoration(
                labelText: 'Eleza tatizo (si lazima)',
                hintText: 'Mf. Mahindi yangu yana madoa ya njano kwenye majani',
              ),
            ),
            const SizedBox(height: 8),
            FilledButton(
              onPressed: _sending ? null : _book,
              style: FilledButton.styleFrom(
                minimumSize: const Size.fromHeight(52),
              ),
              child: Text(_sending ? 'Inatuma...' : 'Tuma ombi'),
            ),
            const SizedBox(height: 8),
            const Text(
              'Hakuna malipo kwenye app. Mtakubaliana ada mtaalamu akithibitisha.',
              style: MkText.caption,
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}
