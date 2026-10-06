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
import 'scanner_screen.dart';
import 'cart_screen.dart';
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
  /// Index into [_screens]: 0 Home, 1 Soko, 2 Jukwaa, 3 Wasifu. Kept stable
  /// because [HomeTab.onSwitchTab] callers pass these numbers.
  int _currentIndex = 0;

  /// Bar slot 2 is the scanner, which opens a page instead of a tab.
  static const _scannerSlot = 2;

  int get _selectedSlot =>
      _currentIndex >= _scannerSlot ? _currentIndex + 1 : _currentIndex;

  void _openScanner() {
    Navigator.of(
      context,
    ).push(MaterialPageRoute(builder: (_) => const ScannerPage()));
  }

  void _onSlotSelected(int slot) {
    if (slot == _scannerSlot) {
      _openScanner();
      return;
    }
    setState(() => _currentIndex = slot > _scannerSlot ? slot - 1 : slot);
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
              icon: Icon(Icons.storefront_outlined),
              selectedIcon: Icon(Icons.storefront),
              label: MkStrings.navMarket,
            ),
            NavigationDestination(
              icon: _ScanIcon(),
              label: MkStrings.navScanner,
              tooltip: MkStrings.scannerTooltip,
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

/// The scanner slot's icon: the only filled green control in the bar, so the
/// flagship action stands out without a floating button.
class _ScanIcon extends StatelessWidget {
  const _ScanIcon();

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 48,
      height: 34,
      decoration: BoxDecoration(
        color: MkColors.primary,
        borderRadius: BorderRadius.circular(12),
      ),
      child: const Icon(
        Icons.photo_camera_outlined,
        color: Colors.white,
        size: 22,
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
