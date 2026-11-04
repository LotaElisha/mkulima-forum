import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../core/strings.dart';
import '../core/theme.dart';
import '../providers/auth_provider.dart';
import 'notifications_screen.dart';
import 'login_modal.dart';
import '../services/api_service.dart';
import 'search_screen.dart';
import 'forum_screen.dart';
import 'weather_screen.dart';
import 'product_detail_screen.dart';
import 'services_directory_screen.dart';
import '../core/services_catalog.dart';
import '../models/product.dart';
import '../core/format.dart';
import '../widgets/mkulima_logo.dart';
import '../widgets/mk_section_header.dart';
import '../widgets/mk_skeleton.dart';
import '../widgets/mk_illustrations.dart';
import 'scanner_screen.dart';

/// Nyumbani, laid out to the owner's mockup (2026-11-04): brand header, a
/// painted landscape hero with greeting and search, a deep-green weather card,
/// the Mkulima AI scanner card, eight service tiles, agrodealer inputs and
/// trending discussions.
///
/// The owner chose this greener look over the earlier "90% white" rule. What
/// did not carry over from the mockup, on purpose: the search mic (there is no
/// voice search), "Agrodealer Verified" badges (products carry no such flag),
/// and any claim that the AI diagnoses offline (it runs on the server; offline
/// the photo is kept and checked later).
class HomeTab extends StatefulWidget {
  /// Lets the shell switch bottom-nav tabs (Soko/Jukwaa) instead of pushing
  /// duplicate screens.
  final void Function(int index)? onSwitchTab;

  const HomeTab({super.key, this.onSwitchTab});

  @override
  State<HomeTab> createState() => _HomeTabState();
}

class _HomeTabState extends State<HomeTab> {
  List<dynamic> _trending = [];
  List<dynamic> _inputs = [];
  bool _loadingInputs = true;
  Map<String, dynamic>? _weather;
  bool _loadingThreads = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final api = context.read<ApiService>();

    // Each block loads independently, so a slow price feed never holds up
    // the discussions, and a failure hides only its own section.
    await Future.wait([_loadThreads(api), _loadWeather(api), _loadInputs(api)]);
  }

  Future<void> _loadThreads(ApiService api) async {
    try {
      final response = await api.get('/v1/forum/threads');
      if (mounted) {
        setState(() {
          _trending =
              ((response.data['data'] ?? response.data['threads'] ?? [])
                      as List)
                  .take(3)
                  .toList();
          _loadingThreads = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _loadingThreads = false);
    }
  }

  Future<void> _loadWeather(ApiService api) async {
    // Real data only: the strip stays hidden when weather is unavailable.
    try {
      final report = await api.getWeather();
      if (mounted && report['available'] == true) {
        setState(() => _weather = report);
      }
    } catch (_) {}
  }

  Future<void> _loadInputs(ApiService api) async {
    // Real agrodealer products only; the section says so when there are none.
    try {
      final products = await api.getProducts();
      if (mounted) {
        setState(() {
          _inputs = products.take(6).toList();
          _loadingInputs = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _loadingInputs = false);
    }
  }

  void _openService(String key) {
    // Pembejeo is a bottom-nav tab: switch to it rather than stacking a copy.
    if (key == 'pembejeo' && widget.onSwitchTab != null) {
      widget.onSwitchTab!(1);
      return;
    }
    MkServices.open(context, MkServices.byKey(key));
  }

  void _push(Widget screen) {
    Navigator.of(context).push(MaterialPageRoute(builder: (_) => screen));
  }

  void _openNotifications() {
    final auth = context.read<AuthProvider>();
    if (!auth.isAuthenticated) {
      LoginModal.show(context);
      return;
    }
    _push(const NotificationsScreen());
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;
    final firstName = user?.name.trim().split(RegExp(r'\s+')).first;
    final greeting = firstName == null || firstName.isEmpty
        ? 'Karibu Mkulima'
        : 'Karibu, $firstName';

    return ColoredBox(
      color: MkColors.surface,
      child: SafeArea(
        bottom: false,
        child: RefreshIndicator(
          onRefresh: _load,
          child: ListView(
            padding: const EdgeInsets.only(bottom: 24),
            children: [
              _BrandHeader(onNotifications: _openNotifications),
              _Hero(
                greeting: greeting,
                onSearch: () => _push(const SearchScreen()),
              ),
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 12, 16, 0),
                child: _WeatherCard(
                  report: _weather,
                  onTap: () => _push(const WeatherScreen()),
                ),
              ),
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 12, 16, 0),
                child: _ScannerCard(
                  onScan: () => widget.onSwitchTab != null
                      ? widget.onSwitchTab!(2)
                      : _push(const ScannerPage()),
                ),
              ),
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 20, 16, 0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    MkSectionHeader(
                      title: 'Huduma Karibu Nawe',
                      actionLabel: 'Huduma Zote',
                      onAction: () => _push(const ServicesDirectoryScreen()),
                    ),
                    const SizedBox(height: 8),
                    GridView.builder(
                      shrinkWrap: true,
                      padding: EdgeInsets.zero,
                      physics: const NeverScrollableScrollPhysics(),
                      itemCount: MkServices.homeKeys.length,
                      // Fixed height, not an aspect ratio: Swahili labels wrap
                      // to two lines at 360px and must not clip.
                      gridDelegate:
                          const SliverGridDelegateWithFixedCrossAxisCount(
                            crossAxisCount: 4,
                            mainAxisSpacing: 8,
                            crossAxisSpacing: 6,
                            mainAxisExtent: 128,
                          ),
                      itemBuilder: (context, i) {
                        final key = MkServices.homeKeys[i];
                        return _ServiceTile(
                          service: MkServices.byKey(key),
                          subtitle: _homeSubtitles[key],
                          onTap: () => _openService(key),
                        );
                      },
                    ),
                    const SizedBox(height: 20),
                    MkSectionHeader(
                      title: 'Pembejeo (Agrodealers)',
                      actionLabel: 'Angalia Zote',
                      onAction: () => _openService('pembejeo'),
                    ),
                    const Text(
                      'Nunua mbegu, mbolea, viuatilifu na pembejeo nyingine '
                      'kutoka kwa wauzaji wa pembejeo.',
                      style: MkText.caption,
                    ),
                    const SizedBox(height: 10),
                    if (_loadingInputs)
                      const MkSkeleton(height: 196, radius: 16)
                    else if (_inputs.isEmpty)
                      const Text(
                        'Bado hakuna pembejeo zilizowekwa na wauzaji.',
                        style: MkText.bodyMuted,
                      )
                    else
                      SizedBox(
                        height: 204,
                        child: ListView.separated(
                          scrollDirection: Axis.horizontal,
                          clipBehavior: Clip.none,
                          itemCount: _inputs.length,
                          separatorBuilder: (_, _) => const SizedBox(width: 10),
                          itemBuilder: (context, i) => _InputCard(
                            product: _inputs[i] as Product,
                            onTap: () => _push(
                              ProductDetailScreen(
                                product: _inputs[i] as Product,
                              ),
                            ),
                          ),
                        ),
                      ),
                    const SizedBox(height: 20),
                    MkSectionHeader(
                      title: 'Mijadala Inayovuma',
                      actionLabel: 'Zote',
                      onAction: () => widget.onSwitchTab != null
                          ? widget.onSwitchTab!(3)
                          : _push(const ForumScreen()),
                    ),
                    const SizedBox(height: 4),
                    if (_loadingThreads)
                      const MkListSkeleton(
                        rows: 3,
                        padding: EdgeInsets.only(top: 8),
                      )
                    else if (_trending.isEmpty)
                      const Padding(
                        padding: EdgeInsets.symmetric(vertical: 12),
                        child: Text(
                          'Bado hakuna mijadala.',
                          style: MkText.bodyMuted,
                        ),
                      )
                    else
                      for (final t in _trending)
                        _TrendingRow(
                          thread: t,
                          onTap: () => _push(
                            ThreadDetailScreen(
                              threadId: t['uuid'],
                              threadTitle: t['title'] ?? '',
                            ),
                          ),
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

/// English glosses under the Swahili tile labels, as in the design.
const _homeSubtitles = {
  'agronomists': 'Agronomist',
  'equipment': 'Equipment',
  'pembejeo': 'Agrodealers',
  'logistics': 'Logistics',
  'iot': 'IoT & Smart',
  'prices': 'Market Prices',
  'produce_market': 'Produce Market',
  'farm': 'Farm Services',
};

/// Tile labels on Home. Shorter than the directory's where the design is.
const _homeLabels = {'iot': 'Teknolojia'};

class _BrandHeader extends StatelessWidget {
  final VoidCallback onNotifications;
  const _BrandHeader({required this.onNotifications});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 10, 12, 10),
      child: Row(
        children: [
          const MkulimaLogo(size: 48),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text.rich(
                  const TextSpan(
                    children: [
                      TextSpan(
                        text: 'Mkulima ',
                        style: TextStyle(color: MkColors.primary),
                      ),
                      TextSpan(
                        text: 'Forum',
                        style: TextStyle(color: MkColors.brandOrange),
                      ),
                    ],
                  ),
                  style: const TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.w800,
                    height: 1.1,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 2),
                const Text(
                  'Afya ya mimea, Maarifa, Huduma na Soko la Kilimo',
                  style: MkText.caption,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
          const SizedBox(width: 8),
          IconButton.outlined(
            tooltip: 'Arifa',
            onPressed: onNotifications,
            style: IconButton.styleFrom(
              minimumSize: const Size(48, 48),
              backgroundColor: MkColors.surface,
              side: const BorderSide(color: MkColors.border),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(14),
              ),
            ),
            icon: const Icon(
              Icons.notifications_none_outlined,
              color: MkColors.primary,
            ),
          ),
        ],
      ),
    );
  }
}

class _Hero extends StatelessWidget {
  final String greeting;
  final VoidCallback onSearch;
  const _Hero({required this.greeting, required this.onSearch});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 196,
      child: Stack(
        fit: StackFit.expand,
        children: [
          const MkLandscape(),
          // Scrim so white text holds contrast over the painted sky.
          const DecoratedBox(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.centerLeft,
                end: Alignment.centerRight,
                colors: [
                  Color(0xE614532D),
                  Color(0x9914532D),
                  Color(0x0014532D),
                ],
                stops: [0, 0.55, 1],
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 18, 16, 16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  greeting,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 28,
                    fontWeight: FontWeight.w800,
                    height: 1.15,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 4),
                const Text(
                  'Kilimo bora huanza na taarifa sahihi',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 16,
                    fontWeight: FontWeight.w500,
                  ),
                ),
                const Spacer(),
                _SearchField(onTap: onSearch),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _SearchField extends StatelessWidget {
  final VoidCallback onTap;

  const _SearchField({required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Semantics(
      button: true,
      label: 'Tafuta huduma, pembejeo, wataalamu',
      excludeSemantics: true,
      child: Material(
        color: MkColors.surface,
        elevation: 2,
        shadowColor: const Color(0x33000000),
        borderRadius: BorderRadius.circular(16),
        child: InkWell(
          borderRadius: BorderRadius.circular(16),
          onTap: onTap,
          child: const SizedBox(
            height: 52,
            child: Padding(
              padding: EdgeInsets.symmetric(horizontal: 14),
              child: Row(
                children: [
                  Icon(Icons.search, color: MkColors.ink),
                  SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      'Tafuta huduma, pembejeo, wataalamu…',
                      style: MkText.bodyMuted,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

/// Deep green weather card. Real data only: when the service has nothing the
/// card says so instead of showing a number.
class _WeatherCard extends StatelessWidget {
  final Map<String, dynamic>? report;
  final VoidCallback onTap;

  const _WeatherCard({required this.report, required this.onTap});

  static IconData _iconFor(String desc) {
    final lower = desc.toLowerCase();
    if (lower.contains('rain') || lower.contains('mvua')) {
      return Icons.thunderstorm_outlined;
    }
    if (lower.contains('cloud') || lower.contains('mawingu')) {
      return Icons.wb_cloudy_outlined;
    }
    return Icons.wb_sunny_outlined;
  }

  static String _deg(dynamic v) {
    final n = parseNum(v);
    return n == null ? '–' : '${n.round()}°';
  }

  @override
  Widget build(BuildContext context) {
    final current = report?['current'] as Map<String, dynamic>?;
    final forecast = report?['forecast'] as List?;
    final tomorrow = forecast != null && forecast.length > 1
        ? forecast[1] as Map<String, dynamic>?
        : null;
    final desc = (current?['description'] ?? '').toString();
    final location = (report?['location'] ?? current?['location'] ?? '')
        .toString();
    final temp = parseNum(current?['temperature']);

    const white = TextStyle(color: Colors.white);
    final white70 = TextStyle(color: Colors.white.withValues(alpha: 0.9));

    return Material(
      color: MkColors.heroDeep,
      borderRadius: BorderRadius.circular(MkRadii.card),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(MkRadii.card),
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
          child: current == null
              ? Row(
                  children: [
                    const Icon(Icons.cloud_off_outlined, color: Colors.white),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        'Hali ya hewa haipatikani kwa sasa. Gusa kujaribu tena.',
                        style: white.copyWith(fontSize: 15),
                      ),
                    ),
                    const Icon(Icons.chevron_right, color: Colors.white),
                  ],
                )
              : Row(
                  children: [
                    Icon(
                      _iconFor(desc),
                      color: const Color(0xFFFDE68A),
                      size: 34,
                    ),
                    const SizedBox(width: 10),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          temp == null ? '–' : '${temp.toStringAsFixed(1)}°C',
                          style: white.copyWith(
                            fontSize: 24,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                        Text(
                          'Hali ya Hewa',
                          style: white70.copyWith(fontSize: 13),
                        ),
                      ],
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          if (location.isNotEmpty)
                            Row(
                              children: [
                                const Icon(
                                  Icons.place_outlined,
                                  color: Colors.white,
                                  size: 16,
                                ),
                                const SizedBox(width: 2),
                                Flexible(
                                  child: Text(
                                    location,
                                    style: white.copyWith(
                                      fontSize: 14,
                                      fontWeight: FontWeight.w600,
                                    ),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                              ],
                            ),
                          Text(
                            desc,
                            style: white70.copyWith(fontSize: 13),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ],
                      ),
                    ),
                    if (tomorrow != null) ...[
                      Container(
                        width: 1,
                        height: 36,
                        margin: const EdgeInsets.symmetric(horizontal: 8),
                        color: Colors.white24,
                      ),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text('Kesho', style: white70.copyWith(fontSize: 13)),
                          Text(
                            '${_deg(tomorrow['temp_max'])} / ${_deg(tomorrow['temp_min'])}',
                            style: white.copyWith(
                              fontSize: 14,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ],
                      ),
                    ],
                    const Icon(Icons.chevron_right, color: Colors.white),
                  ],
                ),
        ),
      ),
    );
  }
}

/// Mkulima AI scanner card. Diagnosis needs the server, so the copy promises
/// only what is true offline: the photo is kept and checked later.
class _ScannerCard extends StatelessWidget {
  final VoidCallback onScan;
  const _ScannerCard({required this.onScan});

  @override
  Widget build(BuildContext context) {
    return ClipRRect(
      borderRadius: BorderRadius.circular(MkRadii.card),
      child: DecoratedBox(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [MkColors.heroMid, MkColors.heroDeep],
          ),
        ),
        child: IntrinsicHeight(
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const SizedBox(
                width: 112,
                child: Stack(
                  fit: StackFit.expand,
                  children: [
                    MkSpottedLeaf(),
                    MkScanCorners(inset: 14, length: 20, stroke: 3.5),
                  ],
                ),
              ),
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(12, 12, 12, 12),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 10,
                          vertical: 3,
                        ),
                        decoration: BoxDecoration(
                          color: MkColors.surface,
                          borderRadius: BorderRadius.circular(999),
                        ),
                        child: const Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.eco, size: 15, color: MkColors.primary),
                            SizedBox(width: 4),
                            Text(
                              'Mkulima AI',
                              style: TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.w700,
                                color: MkColors.primaryDark,
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 8),
                      const Text(
                        'Mmea wako una tatizo?',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 20,
                          fontWeight: FontWeight.w800,
                          height: 1.2,
                        ),
                      ),
                      const SizedBox(height: 4),
                      const Text(
                        'Piga picha ya jani au mmea. Tambua tatizo na upate '
                        'ushauri. Bila mtandao, picha huhifadhiwa.',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 14,
                          height: 1.35,
                        ),
                      ),
                      const SizedBox(height: 10),
                      SizedBox(
                        width: double.infinity,
                        child: FilledButton.icon(
                          onPressed: onScan,
                          icon: const Icon(Icons.photo_camera_outlined),
                          label: const Text('Scan Mmea Sasa'),
                          iconAlignment: IconAlignment.start,
                          style: FilledButton.styleFrom(
                            backgroundColor: MkColors.cta,
                            foregroundColor: Colors.white,
                            minimumSize: const Size(0, 48),
                            padding: const EdgeInsets.symmetric(horizontal: 12),
                            textStyle: const TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w700,
                            ),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(999),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _TrendingRow extends StatelessWidget {
  final dynamic thread;
  final VoidCallback onTap;

  const _TrendingRow({required this.thread, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final author = thread['user']?['name']?.toString() ?? '';
    final initials = author
        .trim()
        .split(RegExp(r'\s+'))
        .where((w) => w.isNotEmpty)
        .take(2)
        .map((w) => w[0].toUpperCase())
        .join();
    final replies = thread['reply_count'] ?? thread['replies_count'] ?? 0;
    final region = thread['region']?.toString() ?? '';
    final expert = thread['user']?['is_verified_expert'] == true;
    final meta = [
      if (author.isNotEmpty) author,
      'Majibu $replies',
      if (region.isNotEmpty) region,
    ].join('  •  ');

    return Padding(
      padding: const EdgeInsets.only(top: 8),
      child: Material(
        color: MkColors.surface,
        borderRadius: BorderRadius.circular(14),
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(14),
          child: Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              border: Border.all(color: MkColors.border),
              borderRadius: BorderRadius.circular(14),
            ),
            child: Row(
              children: [
                Container(
                  width: 48,
                  height: 48,
                  alignment: Alignment.center,
                  decoration: BoxDecoration(
                    color: MkColors.leafPale,
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: initials.isEmpty
                      ? const Icon(
                          Icons.forum_outlined,
                          color: MkColors.primary,
                        )
                      : Text(
                          initials,
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w700,
                            color: MkColors.primaryDark,
                          ),
                        ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        thread['title'] ?? '',
                        style: MkText.label.copyWith(height: 1.3),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 4),
                      Wrap(
                        spacing: 8,
                        runSpacing: 4,
                        crossAxisAlignment: WrapCrossAlignment.center,
                        children: [
                          if (expert) const _ExpertPill(),
                          Text(
                            meta,
                            style: MkText.caption,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _ExpertPill extends StatelessWidget {
  const _ExpertPill();

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
      decoration: BoxDecoration(
        color: MkColors.leafPale,
        borderRadius: BorderRadius.circular(999),
      ),
      child: const Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.verified, size: 14, color: MkColors.primaryDark),
          SizedBox(width: 4),
          Text(
            MkStrings.expertBadge,
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: MkColors.primaryDark,
            ),
          ),
        ],
      ),
    );
  }
}

class _ServiceTile extends StatelessWidget {
  final MkService service;
  final String? subtitle;
  final VoidCallback onTap;
  const _ServiceTile({
    required this.service,
    required this.subtitle,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final live = service.isLive;
    final label = _homeLabels[service.key] ?? service.label;
    return Semantics(
      button: true,
      label: live ? label : '$label, bado haijawashwa',
      excludeSemantics: true,
      child: Material(
        color: live ? MkColors.leafPale : MkColors.surfaceMuted,
        borderRadius: BorderRadius.circular(14),
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(14),
          child: Padding(
            padding: const EdgeInsets.fromLTRB(2, 12, 2, 8),
            child: Column(
              children: [
                Icon(
                  service.icon,
                  size: 32,
                  color: live ? MkColors.primary : MkColors.muted,
                ),
                const SizedBox(height: 8),
                Text(
                  label,
                  textAlign: TextAlign.center,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w700,
                    color: MkColors.ink,
                    height: 1.15,
                  ),
                ),
                if (subtitle != null)
                  Text(
                    // No parentheses: at 360px "(Agronomist)" is wider than
                    // the tile and broke mid-word.
                    subtitle!,
                    textAlign: TextAlign.center,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: MkText.caption.copyWith(height: 1.15),
                  ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _InputCard extends StatelessWidget {
  final Product product;
  final VoidCallback onTap;
  const _InputCard({required this.product, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final image = product.images?.firstWhere(
      (u) => u.trim().isNotEmpty,
      orElse: () => '',
    );
    final placeholder = Container(
      color: MkColors.leafPale,
      alignment: Alignment.center,
      child: const Icon(Icons.eco_outlined, color: MkColors.primary, size: 32),
    );
    return Material(
      color: MkColors.surface,
      borderRadius: BorderRadius.circular(MkRadii.card),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(MkRadii.card),
        child: Container(
          width: 148,
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            border: Border.all(color: MkColors.border),
            borderRadius: BorderRadius.circular(MkRadii.card),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              ClipRRect(
                borderRadius: BorderRadius.circular(10),
                child: SizedBox(
                  height: 88,
                  width: double.infinity,
                  child: image == null || image.isEmpty
                      ? placeholder
                      : Image.network(
                          image,
                          fit: BoxFit.cover,
                          // Decode small: this is a 148px card on metered data.
                          cacheWidth: 300,
                          errorBuilder: (_, _, _) => placeholder,
                        ),
                ),
              ),
              const SizedBox(height: 8),
              Text(
                product.name,
                style: MkText.label.copyWith(fontSize: 15, height: 1.2),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
              if (product.description.trim().isNotEmpty)
                Text(
                  product.description.trim(),
                  style: MkText.caption,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              const Spacer(),
              Text(
                mkMoney(product.price),
                style: const TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w800,
                  color: MkColors.cta,
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
