import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../core/strings.dart';
import '../core/theme.dart';
import '../providers/auth_provider.dart';
import '../providers/cart_provider.dart';
import 'home_tab.dart';
import 'marketplace_screen.dart';
import 'forum_screen.dart';
import 'profile_screen.dart';
import 'market_prices_screen.dart';
import 'weather_screen.dart';
import 'notifications_screen.dart';
import 'login_modal.dart';

/// App shell: a plain five-slot bottom bar — Nyumbani, Soko, Kagua, Jukwaa,
/// Wasifu.
///
/// The AI Plant Scanner keeps the centre slot, drawn as the one filled green
/// control in the bar, but it is an ordinary destination now rather than a
/// notched floating button. The notch needed an 80px clearance hack on every
/// tab, and the scanner was reachable three ways at once (FAB, app-bar action,
/// Home hero); it is now the bar slot plus the Home card.
class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  /// 0 Nyumbani, 1 Soko (bado si kichupo kikuu), 2 Jukwaa, 3 Wasifu.
  /// Bei na hali ya hewa zinafunguliwa juu ya ukurasa, si kama kichupo.
  int _currentIndex = 0;

  int get _selectedSlot => switch (_currentIndex) {
    2 => 3,
    3 => 4,
    _ => 0,
  };

  void _onSlotSelected(int slot) {
    if (slot == 1) {
      Navigator.of(
        context,
      ).push(MaterialPageRoute(builder: (_) => const MarketPricesScreen()));
      return;
    }
    if (slot == 2) {
      Navigator.of(
        context,
      ).push(MaterialPageRoute(builder: (_) => const WeatherScreen()));
      return;
    }
    setState(() => _currentIndex = switch (slot) { 3 => 2, 4 => 3, _ => 0 });
  }

  void _openNotifications(AuthProvider auth) {
    if (!auth.isAuthenticated) {
      LoginModal.show(context);
      return;
    }
    Navigator.of(
      context,
    ).push(MaterialPageRoute(builder: (_) => const NotificationsScreen()));
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);

    final screens = [
      HomeTab(onSwitchTab: (i) => setState(() => _currentIndex = i)),
      const MarketplaceScreen(),
      const ForumScreen(),
      const ProfileScreen(),
    ];

    const titles = [
      MkStrings.titleHome,
      MkStrings.navMarket,
      MkStrings.navForum,
      MkStrings.titleProfile,
    ];

    return Scaffold(
      appBar: _currentIndex == 0
          ? null
          : AppBar(
              title: Text(titles[_currentIndex]),
              actions: [
                if (_currentIndex == 1) const _CartAction(),
                IconButton(
                  tooltip: 'Arifa',
                  icon: const Icon(Icons.notifications_outlined),
                  onPressed: () => _openNotifications(auth),
                ),
                if (!auth.isAuthenticated)
                  TextButton(
                    onPressed: () => LoginModal.show(context),
                    child: const Text(MkStrings.navLogin),
                  ),
                const SizedBox(width: 4),
              ],
            ),
      body: screens[_currentIndex],
      bottomNavigationBar: DecoratedBox(
        decoration: const BoxDecoration(
          border: Border(top: BorderSide(color: MkColors.border)),
        ),
        child: NavigationBar(
          selectedIndex: _selectedSlot,
          onDestinationSelected: _onSlotSelected,
          destinations: const [
            NavigationDestination(
              icon: Icon(Icons.home_outlined),
              selectedIcon: Icon(Icons.home),
              label: MkStrings.navHome,
            ),
            NavigationDestination(
              icon: Icon(Icons.show_chart),
              label: 'Bei',
            ),
            NavigationDestination(
              icon: Icon(Icons.wb_sunny_outlined),
              label: 'Hewa',
            ),
            NavigationDestination(
              icon: Icon(Icons.forum_outlined),
              selectedIcon: Icon(Icons.forum),
              label: MkStrings.navForum,
            ),
            NavigationDestination(
              icon: Icon(Icons.person_outline),
              selectedIcon: Icon(Icons.person),
              label: MkStrings.navProfile,
            ),
          ],
        ),
      ),
    );
  }
}

class _CartAction extends StatelessWidget {
  const _CartAction();

  @override
  Widget build(BuildContext context) {
    return Consumer<CartProvider>(
      builder: (context, cart, child) {
        final count = cart.itemCount;
        return IconButton(
          tooltip: count > 0 ? 'Kikapu, bidhaa $count' : 'Kikapu',
          onPressed: () => Navigator.of(
            context,
          ).push(MaterialPageRoute(builder: (_) => const CartScreen())),
          icon: Badge(
            isLabelVisible: count > 0,
            backgroundColor: MkColors.primary,
            textColor: Colors.white,
            textStyle: const TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w700,
            ),
            label: Text('$count'),
            child: const Icon(Icons.shopping_cart_outlined),
          ),
        );
      },
    );
  }
}
