import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../core/strings.dart';
import '../core/theme.dart';
import '../services/api_service.dart';
import 'scanner_screen.dart';
import 'search_screen.dart';
import 'kagua_dawa_screen.dart';
import 'mkulima_bot_screen.dart';
import 'forum_screen.dart';
import 'weather_screen.dart';
import 'market_prices_screen.dart';
import 'features_screen.dart';

/// Nyumbani — the homepage. The AI Plant Scanner is the flagship feature:
/// it owns the hero section above the fold and launches with one tap.
/// Everything else (AI chat, market, forum, prices) is secondary.
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
  Map<String, dynamic>? _weather;
  bool _loadingThreads = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final api = context.read<ApiService>();

    // Trending discussions (real forum data).
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

    // Compact weather strip (real data only — hidden when unavailable).
    try {
      final report = await api.getWeather();
      if (mounted && report['available'] == true) {
        setState(() => _weather = report['current'] as Map<String, dynamic>?);
      }
    } catch (_) {
      // Weather strip simply stays hidden.
    }
  }

  void _openScanner() {
    Navigator.of(
      context,
    ).push(MaterialPageRoute(builder: (_) => const ScannerPage()));
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // ============ HERO: AI PLANT SCANNER (flagship, above the fold) ====
          _ScannerHero(onScan: _openScanner),

          // Compact real-weather strip
          if (_weather != null) ...[
            const SizedBox(height: 12),
            _WeatherStrip(weather: _weather!),
          ],

          const SizedBox(height: 12),

          // Global search (products, discussions, prices, experts)
          Semantics(
            button: true,
            label: 'Tafuta bidhaa, mijadala, bei',
            child: Material(
              color: Colors.transparent,
              child: InkWell(
                borderRadius: BorderRadius.circular(14),
                onTap: () => Navigator.of(
                  context,
                ).push(MaterialPageRoute(builder: (_) => const SearchScreen())),
                child: Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 14,
                    vertical: 13,
                  ),
                  decoration: BoxDecoration(
                    color: Theme.of(context).cardColor,
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(
                      color: Colors.grey.withValues(alpha: .25),
                    ),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.search, color: Colors.grey),
                      const SizedBox(width: 10),
                      Text(
                        'Tafuta bidhaa, mijadala, bei...',
                        style: TextStyle(color: Colors.grey[600]),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),

          const SizedBox(height: 20),

          // ============ QUICK SERVICES =====================================
          const Text(
            'Huduma za Haraka',
            style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 12),
          GridView.count(
            crossAxisCount: 3,
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            mainAxisSpacing: 10,
            crossAxisSpacing: 10,
            childAspectRatio: 0.95,
            children: [
              _QuickService(
                icon: Icons.psychology_outlined,
                label: 'Mkulima AI',
                color: Colors.blue,
                onTap: () => Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const MkulimaBotScreen()),
                ),
              ),
              _QuickService(
                icon: Icons.verified_user_outlined,
                label: 'Kagua Dawa',
                color: MkColors.danger,
                onTap: () => Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const KaguaDawaScreen()),
                ),
              ),
              _QuickService(
                icon: Icons.forum_outlined,
                label: 'Jukwaa',
                color: Colors.teal,
                onTap: () => widget.onSwitchTab != null
                    ? widget.onSwitchTab!(2)
                    : Navigator.of(context).push(
                        MaterialPageRoute(builder: (_) => const ForumScreen()),
                      ),
              ),
              _QuickService(
                icon: Icons.price_change_outlined,
                label: 'Bei za Masoko',
                color: Colors.deepOrange,
                onTap: () => Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const MarketPricesScreen()),
                ),
              ),
              _QuickService(
                icon: Icons.wb_sunny_outlined,
                label: 'Hali ya Hewa',
                color: Colors.orange,
                onTap: () => Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const WeatherScreen()),
                ),
              ),
              _QuickService(
                icon: Icons.grid_view_outlined,
                label: 'Huduma Zote',
                color: Colors.indigo,
                onTap: () => Navigator.of(context).push(
                  MaterialPageRoute(
                    builder: (_) => Scaffold(
                      appBar: AppBar(
                        title: const Text(MkStrings.titleServices),
                        backgroundColor: MkColors.primary,
                        foregroundColor: Colors.white,
                      ),
                      body: const FeaturesScreen(),
                    ),
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(height: 20),

          // ============ TRENDING DISCUSSIONS ================================
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Mijadala Inayovuma',
                style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
              ),
              TextButton(
                onPressed: () => widget.onSwitchTab?.call(2),
                child: const Text('Zote'),
              ),
            ],
          ),
          if (_loadingThreads)
            const Padding(
              padding: EdgeInsets.all(24),
              child: Center(child: CircularProgressIndicator()),
            )
          else if (_trending.isEmpty)
            Card(
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Center(
                  child: Text(
                    MkStrings.emptyList,
                    style: TextStyle(color: Colors.grey[600]),
                  ),
                ),
              ),
            )
          else
            ..._trending.map(
              (t) => Card(
                margin: const EdgeInsets.only(bottom: 8),
                child: ListTile(
                  leading: const Icon(
                    Icons.forum_outlined,
                    color: MkColors.primary,
                  ),
                  title: Text(
                    t['title'] ?? '',
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontWeight: FontWeight.w600),
                  ),
                  subtitle: Text(
                    t['body'] ?? '',
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  onTap: () => Navigator.of(context).push(
                    MaterialPageRoute(
                      builder: (_) => ThreadDetailScreen(
                        threadId: t['uuid'],
                        threadTitle: t['title'] ?? '',
                      ),
                    ),
                  ),
                ),
              ),
            ),
          const SizedBox(height: 80), // clearance for the docked scan FAB
        ],
      ),
    );
  }
}

/// The flagship hero card: brand gradient, marketing message, one-tap CTA.
class _ScannerHero extends StatelessWidget {
  final VoidCallback onScan;

  const _ScannerHero({required this.onScan});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [MkColors.primary, MkColors.primaryDark],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: MkColors.accent,
                  borderRadius: BorderRadius.circular(14),
                ),
                child: const Icon(
                  Icons.center_focus_strong,
                  size: 32,
                  color: MkColors.primaryDark,
                ),
              ),
              const SizedBox(width: 12),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      MkStrings.scannerBrand,
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                    ),
                    Text(
                      MkStrings.scannerTagline,
                      style: TextStyle(
                        fontSize: 13,
                        color: MkColors.accent,
                        fontWeight: FontWeight.w600,
                        letterSpacing: 0.5,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          const Text(
            MkStrings.scannerHeroSubtitle,
            style: TextStyle(color: Colors.white70, fontSize: 14, height: 1.4),
          ),
          const SizedBox(height: 16),
          SizedBox(
            width: double.infinity,
            height: 52,
            child: ElevatedButton.icon(
              onPressed: onScan,
              icon: const Icon(Icons.photo_camera),
              label: const Text(
                MkStrings.scannerCta,
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: MkColors.accent,
                foregroundColor: MkColors.primaryDark,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(14),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _WeatherStrip extends StatelessWidget {
  final Map<String, dynamic> weather;

  const _WeatherStrip({required this.weather});

  @override
  Widget build(BuildContext context) {
    final desc = (weather['description'] ?? '').toString().toLowerCase();
    final icon = desc.contains('rain')
        ? Icons.water_drop
        : desc.contains('cloud')
        ? Icons.wb_cloudy
        : Icons.wb_sunny;

    return Card(
      child: ListTile(
        dense: true,
        leading: Icon(icon, color: Colors.blue[700]),
        title: Text(
          '${weather['location'] ?? ''} · ${weather['temperature']}°C',
          style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14),
        ),
        subtitle: Text(
          '${weather['description'] ?? ''}',
          style: const TextStyle(fontSize: 12),
        ),
        trailing: const Icon(Icons.chevron_right, size: 18),
        onTap: () => Navigator.of(
          context,
        ).push(MaterialPageRoute(builder: (_) => const WeatherScreen())),
      ),
    );
  }
}

class _QuickService extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback onTap;

  const _QuickService({
    required this.icon,
    required this.label,
    required this.color,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: EdgeInsets.zero,
      child: InkWell(
        borderRadius: BorderRadius.circular(MkRadii.card),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 8),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.12),
                  shape: BoxShape.circle,
                ),
                child: Icon(icon, color: color, size: 24),
              ),
              const SizedBox(height: 6),
              Text(
                label,
                textAlign: TextAlign.center,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(fontSize: 11.5),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
