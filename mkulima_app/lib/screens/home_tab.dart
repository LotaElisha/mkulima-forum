import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../core/strings.dart';
import '../core/theme.dart';
import '../providers/auth_provider.dart';
import 'notifications_screen.dart';
import 'login_modal.dart';
import '../services/api_service.dart';
import 'scanner_screen.dart';
import 'search_screen.dart';
import 'kagua_dawa_screen.dart';
import 'mkulima_bot_screen.dart';
import 'forum_screen.dart';
import 'weather_screen.dart';
import 'market_prices_screen.dart';
import 'features_screen.dart';
import '../core/format.dart';
import '../widgets/mk_section_header.dart';
import '../widgets/mk_skeleton.dart';

/// Nyumbani — task-first homepage: greeting, search, weather, the plant
/// scanner card, four services, today's prices and trending discussions.
///
/// Everything sits on white. The scanner card used to be a near-black slab
/// across the top of the screen; it is now a bordered white card whose one
/// green button is the loudest thing above the fold.
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
  List<dynamic> _prices = [];
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
    await Future.wait([
      _loadThreads(api),
      _loadWeather(api),
      _loadPrices(api),
    ]);
  }

  Future<void> _loadThreads(ApiService api) async {
    try {
      final response = await api.get('/forum/threads');
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
        setState(() => _weather = report['current'] as Map<String, dynamic>?);
      }
    } catch (_) {}
  }

  Future<void> _loadPrices(ApiService api) async {
    // Hidden unless the server has real prices; never filled with samples.
    try {
      final response = await api.get('/market-prices');
      final data = response.data['data'];
      if (mounted && data is List) {
        setState(() => _prices = data.take(3).toList());
      }
    } catch (_) {}
  }

  void _push(Widget screen) {
    Navigator.of(context).push(MaterialPageRoute(builder: (_) => screen));
  }

  void _openScanner() => _push(const ScannerPage());

  void _openNotifications() {
    final auth = context.read<AuthProvider>();
    if (!auth.isAuthenticated) {
      LoginModal.show(context);
      return;
    }
    _push(const NotificationsScreen());
  }

  void _openAllServices() => _push(
    Scaffold(
      appBar: AppBar(title: const Text(MkStrings.titleServices)),
      body: const FeaturesScreen(),
    ),
  );

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;
    final firstName = user?.name.trim().split(RegExp(r'\s+')).first;
    return SafeArea(
      bottom: false,
      child: RefreshIndicator(
        onRefresh: _load,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 24),
          children: [
            Row(
              children: [
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: MkColors.primary,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.eco_outlined, color: Colors.white),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        firstName == null || firstName.isEmpty
                            ? 'Karibu Mkulima'
                            : '${MkStrings.greeting}, $firstName',
                        style: MkText.section.copyWith(fontSize: 20),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const Text(
                        'Kilimo bora huanza na taarifa sahihi',
                        style: MkText.caption,
                      ),
                    ],
                  ),
                ),
                IconButton.outlined(
                  tooltip: 'Arifa',
                  onPressed: _openNotifications,
                  style: IconButton.styleFrom(
                    minimumSize: const Size(44, 44),
                    side: const BorderSide(color: MkColors.border),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  icon: const Icon(Icons.notifications_none_outlined),
                ),
              ],
            ),
            const SizedBox(height: 16),

            _SearchField(onTap: () => _push(const SearchScreen())),

            if (_weather != null) ...[
              const SizedBox(height: 12),
              _WeatherStrip(
                weather: _weather!,
                onTap: () => _push(const WeatherScreen()),
              ),
            ],

            const SizedBox(height: 16),
            _ScannerCard(onScan: _openScanner),

            const SizedBox(height: 20),
            MkSectionHeader(
              title: 'Huduma',
              actionLabel: 'Zote',
              onAction: _openAllServices,
            ),
            const SizedBox(height: 4),
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _QuickService(
                  icon: Icons.chat_bubble_outline,
                  label: MkStrings.botTitle,
                  onTap: () => _push(const MkulimaBotScreen()),
                ),
                _QuickService(
                  icon: Icons.verified_user_outlined,
                  label: 'Kagua Dawa',
                  onTap: () => _push(const KaguaDawaScreen()),
                ),
                _QuickService(
                  icon: Icons.show_chart,
                  label: 'Bei za Masoko',
                  onTap: () => _push(const MarketPricesScreen()),
                ),
                _QuickService(
                  icon: Icons.wb_cloudy_outlined,
                  label: 'Hali ya Hewa',
                  onTap: () => _push(const WeatherScreen()),
                ),
              ],
            ),

            if (_prices.isNotEmpty) ...[
              const SizedBox(height: 20),
              MkSectionHeader(
                title: 'Bei za leo (TSh)',
                actionLabel: 'Zote',
                onAction: () => _push(const MarketPricesScreen()),
              ),
              _PriceTable(prices: _prices),
            ],

            const SizedBox(height: 20),
            MkSectionHeader(
              title: 'Mijadala inayovuma',
              actionLabel: 'Zote',
              onAction: () => widget.onSwitchTab != null
                  ? widget.onSwitchTab!(2)
                  : _push(const ForumScreen()),
            ),
            if (_loadingThreads)
              const MkListSkeleton(rows: 3, padding: EdgeInsets.only(top: 8))
            else if (_trending.isEmpty)
              Padding(
                padding: const EdgeInsets.symmetric(vertical: 12),
                child: Text(
                  'Bado hakuna mijadala. Uliza swali la kwanza kwenye Jukwaa.',
                  style: MkText.bodyMuted,
                ),
              )
            else
              for (final (i, t) in _trending.indexed)
                _TrendingRow(
                  thread: t,
                  divider: i < _trending.length - 1,
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
      label: 'Tafuta bidhaa, mijadala, bei',
      excludeSemantics: true,
      child: Material(
        color: MkColors.surfaceMuted,
        borderRadius: BorderRadius.circular(14),
        child: InkWell(
          borderRadius: BorderRadius.circular(14),
          onTap: onTap,
          child: Container(
            height: 48,
            padding: const EdgeInsets.symmetric(horizontal: 14),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: MkColors.border),
            ),
            child: const Row(
              children: [
                Icon(Icons.search, color: MkColors.muted),
                SizedBox(width: 10),
                Expanded(
                  child: Text(
                    'Tafuta mbegu, mbolea, mijadala…',
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
    );
  }
}

/// The scanner, as a white card with one green action.
class _ScannerCard extends StatelessWidget {
  final VoidCallback onScan;

  const _ScannerCard({required this.onScan});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        border: Border.all(color: MkColors.border),
        borderRadius: BorderRadius.circular(MkRadii.card),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 56,
                height: 56,
                decoration: BoxDecoration(
                  color: MkColors.leafPale,
                  borderRadius: BorderRadius.circular(16),
                ),
                child: const Icon(
                  Icons.center_focus_strong,
                  size: 28,
                  color: MkColors.primary,
                ),
              ),
              const SizedBox(width: 14),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Mmea wako unaumwa?', style: MkText.title),
                    SizedBox(height: 4),
                    Text(
                      MkStrings.scannerHeroSubtitle,
                      style: MkText.bodyMuted,
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          FilledButton.icon(
            onPressed: onScan,
            icon: const Icon(Icons.photo_camera_outlined),
            label: const Text(MkStrings.scannerCta),
          ),
        ],
      ),
    );
  }
}

class _WeatherStrip extends StatelessWidget {
  final Map<String, dynamic> weather;
  final VoidCallback onTap;

  const _WeatherStrip({required this.weather, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final desc = (weather['description'] ?? '').toString();
    final lower = desc.toLowerCase();
    final icon = lower.contains('rain') || lower.contains('mvua')
        ? Icons.water_drop_outlined
        : lower.contains('cloud') || lower.contains('mawingu')
        ? Icons.wb_cloudy_outlined
        : Icons.wb_sunny_outlined;
    final location = weather['location']?.toString() ?? '';

    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(14),
        child: Container(
          constraints: const BoxConstraints(minHeight: 56),
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
          decoration: BoxDecoration(
            border: Border.all(color: MkColors.border),
            borderRadius: BorderRadius.circular(14),
          ),
          child: Row(
            children: [
              Icon(icon, color: MkColors.warning),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      '${weather['temperature']}° · $desc',
                      style: MkText.label,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    if (location.isNotEmpty)
                      Text(location, style: MkText.caption),
                  ],
                ),
              ),
              const Icon(Icons.chevron_right, color: MkColors.muted),
            ],
          ),
        ),
      ),
    );
  }
}

class _QuickService extends StatelessWidget {
  final IconData icon;
  final String label;
  final VoidCallback onTap;

  const _QuickService({
    required this.icon,
    required this.label,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    // Every service icon is the same green in the same pale circle. The old
    // grid tinted each tile a different colour, which read as decoration
    // rather than as one set of tools.
    return Expanded(
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(MkRadii.card),
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 2),
          child: Column(
            children: [
              Container(
                width: 52,
                height: 52,
                decoration: BoxDecoration(
                  color: MkColors.leafPale,
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Icon(icon, color: MkColors.primary),
              ),
              const SizedBox(height: 8),
              Text(
                label,
                textAlign: TextAlign.center,
                maxLines: 2,
                style: const TextStyle(
                  fontSize: 13,
                  height: 1.25,
                  color: MkColors.ink,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _PriceTable extends StatelessWidget {
  final List<dynamic> prices;

  const _PriceTable({required this.prices});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        border: Border.all(color: MkColors.border),
        borderRadius: BorderRadius.circular(14),
      ),
      child: Column(
        children: [
          for (final (i, p) in prices.indexed)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              decoration: BoxDecoration(
                border: i < prices.length - 1
                    ? const Border(bottom: BorderSide(color: MkColors.border))
                    : null,
              ),
              child: _PriceRow(price: p),
            ),
        ],
      ),
    );
  }
}

class _PriceRow extends StatelessWidget {
  final dynamic price;

  const _PriceRow({required this.price});

  @override
  Widget build(BuildContext context) {
    final trend = price['trend']?.toString() ?? 'stable';
    // Lightness differs as well as hue, so up and down stay distinguishable
    // for colour-blind readers; the arrow carries the meaning regardless.
    final (arrow, colour, meaning) = switch (trend) {
      'up' => (Icons.arrow_upward, MkColors.primary, 'inapanda'),
      'down' => (Icons.arrow_downward, MkColors.danger, 'inashuka'),
      _ => (Icons.remove, MkColors.muted, 'haijabadilika'),
    };
    return Row(
      children: [
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('${price['commodity'] ?? ''}', style: MkText.label),
              Text(
                [
                  price['market'],
                  if (price['unit'] != null) 'kwa ${price['unit']}',
                ].whereType<Object>().join(' · '),
                style: MkText.caption,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ],
          ),
        ),
        const SizedBox(width: 8),
        Text(
          '${mkAmount(num.tryParse('${price['min_price']}'))}–'
          '${mkAmount(num.tryParse('${price['max_price']}'))}',
          style: MkText.label.copyWith(fontWeight: FontWeight.w700),
        ),
        const SizedBox(width: 6),
        Icon(arrow, size: 18, color: colour, semanticLabel: meaning),
      ],
    );
  }
}

class _TrendingRow extends StatelessWidget {
  final dynamic thread;
  final bool divider;
  final VoidCallback onTap;

  const _TrendingRow({
    required this.thread,
    required this.divider,
    required this.onTap,
  });

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

    return InkWell(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 14),
        decoration: BoxDecoration(
          border: divider
              ? const Border(bottom: BorderSide(color: MkColors.border))
              : null,
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            CircleAvatar(
              radius: 20,
              backgroundColor: MkColors.surfaceMuted,
              foregroundColor: MkColors.primaryDark,
              child: initials.isEmpty
                  ? const Icon(Icons.person_outline, size: 20)
                  : Text(
                      initials,
                      style: const TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.w700,
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
                    style: MkText.label.copyWith(height: 1.35),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 6),
                  Wrap(
                    spacing: 8,
                    runSpacing: 4,
                    crossAxisAlignment: WrapCrossAlignment.center,
                    children: [
                      if (expert) const _ExpertPill(),
                      Text('Majibu $replies', style: MkText.caption),
                      if (region.isNotEmpty)
                        Text('· $region', style: MkText.caption),
                    ],
                  ),
                ],
              ),
            ),
          ],
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
