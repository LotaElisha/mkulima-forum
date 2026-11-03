import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../core/strings.dart';
import '../core/mk_tokens.dart';
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

/// Nyumbani — greeting, search, compact weather, eight service shortcuts
/// (with "Huduma zote" for the rest), agrodealer inputs, and trending
/// discussions. The scanner is the centre tab, so it is not repeated here.
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
        setState(() => _weather = report['current'] as Map<String, dynamic>?);
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
    return SafeArea(
      bottom: false,
      child: RefreshIndicator(
        onRefresh: _load,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 24),
          children: [
            Row(
              children: [
                // The original farmer emblem, not a generic leaf tile.
                const MkulimaLogo(size: 44),
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

            const SizedBox(height: 12),
            if (_weather != null)
              _WeatherStrip(
                weather: _weather!,
                onTap: () => _push(const WeatherScreen()),
              )
            else
              _LeadCard(
                title: 'Hali ya hewa',
                body: 'Hakuna taarifa kwa sasa. Hatutungi namba.',
                action: 'Angalia',
                onTap: () => _push(const WeatherScreen()),
              ),

            const SizedBox(height: 16),
            MkSectionHeader(
              title: 'Huduma karibu nawe',
              actionLabel: 'Huduma zote',
              onAction: () => _push(const ServicesDirectoryScreen()),
            ),
            const SizedBox(height: 8),
            GridView.count(
              crossAxisCount: 4,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              mainAxisSpacing: 8,
              crossAxisSpacing: 4,
              // Fixed tile height: two lines of 13px Swahili under the icon.
              childAspectRatio: 0.78,
              children: [
                for (final key in MkServices.homeKeys)
                  _ServiceIcon(
                    service: MkServices.byKey(key),
                    onTap: () => _openService(key),
                  ),
              ],
            ),

            const SizedBox(height: 20),
            MkSectionHeader(
              title: 'Pembejeo karibu nawe',
              actionLabel: 'Zote',
              onAction: () => _openService('pembejeo'),
            ),
            const SizedBox(height: 8),
            if (_loadingInputs)
              const MkSkeleton(height: 132, radius: 16)
            else if (_inputs.isEmpty)
              const Text(
                'Bado hakuna pembejeo zilizowekwa na wauzaji.',
                style: MkText.bodyMuted,
              )
            else
              SizedBox(
                height: 148,
                child: ListView.separated(
                  scrollDirection: Axis.horizontal,
                  itemCount: _inputs.length,
                  separatorBuilder: (_, _) => const SizedBox(width: 10),
                  itemBuilder: (context, i) => _InputCard(
                    product: _inputs[i] as Product,
                    onTap: () => _push(
                      ProductDetailScreen(product: _inputs[i] as Product),
                    ),
                  ),
                ),
              ),
            Align(
              alignment: Alignment.centerLeft,
              child: TextButton(
                onPressed: () => _openService('pembejeo'),
                style: TextButton.styleFrom(minimumSize: const Size(0, 44)),
                child: const Text('Angalia pembejeo zote →'),
              ),
            ),

            const SizedBox(height: 12),
            MkSectionHeader(
              title: 'Mijadala inayovuma',
              actionLabel: 'Zote',
              onAction: () => widget.onSwitchTab != null
                  ? widget.onSwitchTab!(3)
                  : _push(const ForumScreen()),
            ),
            if (_loadingThreads)
              const MkListSkeleton(rows: 3, padding: EdgeInsets.only(top: 8))
            else if (_trending.isEmpty)
              const Padding(
                padding: EdgeInsets.symmetric(vertical: 12),
                child: Text('Bado hakuna mijadala.', style: MkText.bodyMuted),
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

class _LeadCard extends StatelessWidget {
  final String title;
  final String body;
  final String action;
  final VoidCallback onTap;

  const _LeadCard({
    required this.title,
    required this.body,
    required this.action,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(MkTokens.space4),
      decoration: BoxDecoration(
        border: Border.all(color: MkColors.border),
        borderRadius: BorderRadius.circular(MkRadii.card),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title, style: MkText.title),
          const SizedBox(height: 4),
          Text(body, style: MkText.bodyMuted),
          const SizedBox(height: 8),
          TextButton(onPressed: onTap, child: Text(action)),
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

class _ServiceIcon extends StatelessWidget {
  final MkService service;
  final VoidCallback onTap;
  const _ServiceIcon({required this.service, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Column(
        children: [
          const SizedBox(height: 4),
          Container(
            width: 52,
            height: 52,
            decoration: BoxDecoration(
              color: service.isLive ? MkColors.leafPale : MkColors.surfaceMuted,
              shape: BoxShape.circle,
            ),
            child: Icon(
              service.icon,
              color: service.isLive ? MkColors.primary : MkColors.muted,
              size: 24,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            service.label,
            textAlign: TextAlign.center,
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
            style: MkText.caption.copyWith(color: MkColors.ink, height: 1.15),
          ),
        ],
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
    return Material(
      color: MkColors.surface,
      borderRadius: BorderRadius.circular(MkRadii.card),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(MkRadii.card),
        child: Container(
          width: 140,
          padding: const EdgeInsets.all(10),
          decoration: BoxDecoration(
            border: Border.all(color: MkColors.border),
            borderRadius: BorderRadius.circular(MkRadii.card),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                height: 52,
                width: double.infinity,
                decoration: BoxDecoration(
                  color: MkColors.leafPale,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.eco_outlined, color: MkColors.primary),
              ),
              const SizedBox(height: 8),
              Text(
                product.name,
                style: MkText.label,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
              const Spacer(),
              Text(
                '${mkMoney(product.price)} / ${product.unit}',
                style: MkText.caption.copyWith(
                  color: MkColors.primary,
                  fontWeight: FontWeight.w700,
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
