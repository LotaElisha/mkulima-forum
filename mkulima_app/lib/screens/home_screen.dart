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

/// App shell. The AI Plant Scanner is the flagship feature: it owns the
/// center-docked FAB (reachable from every tab), a permanent App Bar action,
/// and the homepage hero — always one tap away.
class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _currentIndex = 0;

  void _openScanner() {
    Navigator.of(context).push(
      MaterialPageRoute(builder: (_) => const ScannerPage()),
    );
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
      MkStrings.titleMarket,
      MkStrings.titleForum,
      MkStrings.titleProfile,
    ];

    return Scaffold(
      appBar: AppBar(
        title: Text(titles[_currentIndex]),
        backgroundColor: MkColors.primary,
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          // Flagship: AI Plant Scanner — always visible, brand accent color.
          IconButton(
            tooltip: MkStrings.scannerTooltip,
            onPressed: _openScanner,
            icon: Container(
              padding: const EdgeInsets.all(6),
              decoration: BoxDecoration(
                color: MkColors.accent,
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(Icons.center_focus_strong,
                  size: 20, color: MkColors.primaryDark),
            ),
          ),
          if (_currentIndex == 1)
            Consumer<CartProvider>(
              builder: (context, cart, child) {
                return Stack(
                  alignment: Alignment.center,
                  children: [
                    IconButton(
                      icon: const Icon(Icons.shopping_cart),
                      onPressed: () {
                        Navigator.of(context).push(
                          MaterialPageRoute(builder: (_) => const CartScreen()),
                        );
                      },
                    ),
                    if (cart.itemCount > 0)
                      Positioned(
                        top: 8,
                        right: 8,
                        child: Container(
                          padding: const EdgeInsets.all(4),
                          decoration: const BoxDecoration(
                            color: Colors.red,
                            shape: BoxShape.circle,
                          ),
                          child: Text(
                            '${cart.itemCount}',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ),
                  ],
                );
              },
            ),
          IconButton(
            icon: const Icon(Icons.notifications_outlined),
            onPressed: () {
              if (!auth.isAuthenticated) {
                LoginModal.show(context);
                return;
              }
              Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const NotificationsScreen()),
              );
            },
          ),
          if (!auth.isAuthenticated)
            TextButton(
              onPressed: () => LoginModal.show(context),
              child: const Text(MkStrings.navLogin,
                  style: TextStyle(color: Colors.white)),
            ),
        ],
      ),
      body: screens[_currentIndex],

      // Flagship center action: one-tap AI Plant Scanner from any tab.
      floatingActionButton: SizedBox(
        width: 64,
        height: 64,
        child: FloatingActionButton(
          onPressed: _openScanner,
          tooltip: MkStrings.scannerTooltip,
          backgroundColor: MkColors.accent,
          foregroundColor: MkColors.primaryDark,
          shape: const CircleBorder(),
          elevation: 4,
          child: const Icon(Icons.center_focus_strong, size: 32),
        ),
      ),
      floatingActionButtonLocation: FloatingActionButtonLocation.centerDocked,

      bottomNavigationBar: BottomAppBar(
        shape: const CircularNotchedRectangle(),
        notchMargin: 8,
        color: Colors.white,
        elevation: 8,
        padding: EdgeInsets.zero,
        child: SizedBox(
          height: 62,
          child: Row(
            children: [
              _NavItem(
                icon: Icons.home_outlined,
                selectedIcon: Icons.home,
                label: MkStrings.navHome,
                selected: _currentIndex == 0,
                onTap: () => setState(() => _currentIndex = 0),
              ),
              _NavItem(
                icon: Icons.store_outlined,
                selectedIcon: Icons.store,
                label: MkStrings.navMarket,
                selected: _currentIndex == 1,
                onTap: () => setState(() => _currentIndex = 1),
              ),
              // Center gap for the docked scan FAB + its label.
              const Expanded(
                child: Padding(
                  padding: EdgeInsets.only(top: 38),
                  child: Text(
                    MkStrings.navScanner,
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                      color: MkColors.primaryDark,
                    ),
                  ),
                ),
              ),
              _NavItem(
                icon: Icons.forum_outlined,
                selectedIcon: Icons.forum,
                label: MkStrings.navForum,
                selected: _currentIndex == 2,
                onTap: () => setState(() => _currentIndex = 2),
              ),
              _NavItem(
                icon: Icons.person_outline,
                selectedIcon: Icons.person,
                label: MkStrings.navProfile,
                selected: _currentIndex == 3,
                onTap: () => setState(() => _currentIndex = 3),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _NavItem extends StatelessWidget {
  final IconData icon;
  final IconData selectedIcon;
  final String label;
  final bool selected;
  final VoidCallback onTap;

  const _NavItem({
    required this.icon,
    required this.selectedIcon,
    required this.label,
    required this.selected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final color = selected ? MkColors.primary : Colors.grey[600];

    return Expanded(
      child: InkWell(
        onTap: onTap,
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(selected ? selectedIcon : icon, color: color, size: 24),
            const SizedBox(height: 2),
            Text(
              label,
              style: TextStyle(
                fontSize: 11,
                color: color,
                fontWeight: selected ? FontWeight.w600 : FontWeight.normal,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
