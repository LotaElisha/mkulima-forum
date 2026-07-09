import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../providers/cart_provider.dart';
import 'marketplace_screen.dart';
import 'forum_screen.dart';
import 'features_screen.dart';
import 'profile_screen.dart';
import 'scanner_screen.dart';
import 'agronomist_screen.dart';
import 'cart_screen.dart';
import 'notifications_screen.dart';
import 'login_modal.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _currentIndex = 0;

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);

    const screens = [
      MarketplaceScreen(),
      FeaturesScreen(),
      AgronomistScreen(),
      ScannerScreen(),
      ProfileScreen(),
    ];

    const titles = [
      'Soko la Mkulima',
      'Huduma za Kilimo',
      'Mtaalamu wa AI (Chatbot)',
      'Kagua Ugonjwa (AI)',
      'Wasifu Wangu',
    ];

    const destinations = [
      NavigationDestination(
        icon: Icon(Icons.store_outlined),
        selectedIcon: Icon(Icons.store),
        label: 'Soko',
      ),
      NavigationDestination(
        icon: Icon(Icons.grid_view_outlined),
        selectedIcon: Icon(Icons.grid_view),
        label: 'Huduma',
      ),
      NavigationDestination(
        icon: Icon(Icons.chat_outlined),
        selectedIcon: Icon(Icons.chat),
        label: 'AI Chat',
      ),
      NavigationDestination(
        icon: Icon(Icons.center_focus_weak_outlined),
        selectedIcon: Icon(Icons.center_focus_strong),
        label: 'Kagua',
      ),
      NavigationDestination(
        icon: Icon(Icons.person_outline),
        selectedIcon: Icon(Icons.person),
        label: 'Wasifu',
      ),
    ];

    return Scaffold(
      appBar: AppBar(
        title: Text(titles[_currentIndex]),
        backgroundColor: const Color(0xFF2E7D32),
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          if (_currentIndex == 0)
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
          Stack(
            alignment: Alignment.center,
            children: [
              IconButton(
                icon: const Icon(Icons.notifications_outlined),
                onPressed: () {
                  if (!auth.isAuthenticated) {
                    LoginModal.show(context);
                    return;
                  }
                  Navigator.of(context).push(
                    MaterialPageRoute(
                      builder: (_) => const NotificationsScreen(),
                    ),
                  );
                },
              ),
              if (auth.isAuthenticated)
                Positioned(
                  top: 8,
                  right: 8,
                  child: Container(
                    width: 8,
                    height: 8,
                    decoration: const BoxDecoration(
                      color: Colors.red,
                      shape: BoxShape.circle,
                    ),
                  ),
                ),
            ],
          ),
          if (auth.isAuthenticated)
            Padding(
              padding: const EdgeInsets.only(right: 16),
              child: Center(
                child: Text(
                  'Habari, ${auth.user!.name.split(' ').first}',
                  style: const TextStyle(fontSize: 14),
                ),
              ),
            )
          else
            TextButton(
              onPressed: () => LoginModal.show(context),
              child: const Text('Ingia', style: TextStyle(color: Colors.white)),
            ),
        ],
      ),
      body: screens[_currentIndex],
      bottomNavigationBar: NavigationBar(
        selectedIndex: _currentIndex,
        onDestinationSelected: (index) => setState(() => _currentIndex = index),
        backgroundColor: Colors.white,
        elevation: 8,
        destinations: destinations,
      ),
    );
  }
}

class GuestFeaturesScreen extends StatelessWidget {
  const GuestFeaturesScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF2E7D32), Color(0xFF1B5E20)],
              ),
              borderRadius: BorderRadius.circular(16),
            ),
            child: const Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Icon(Icons.agriculture, size: 48, color: Colors.white),
                SizedBox(height: 12),
                Text(
                  'Karibu Mkulima Forum',
                  style: TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
                SizedBox(height: 8),
                Text(
                  'Jiunge leo kupata huduma zote za kilimo kwa kidijitali',
                  style: TextStyle(color: Colors.white70, fontSize: 14),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),
          const Text(
            'Unaweza Kufanya Bila Kuingia',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 16),
          _GuestFeatureCard(
            icon: Icons.store,
            title: 'Chunguza Soko',
            description: 'Angalia bidhaa zote zinazouzwa na wakulima wengine',
            color: Colors.green,
            onTap: () {},
          ),
          _GuestFeatureCard(
            icon: Icons.forum,
            title: 'Soma Jukwaa',
            description:
                'Soma mijadala na ushauri wa kilimo kutoka kwa wataalamu',
            color: Colors.blue,
            onTap: () {},
          ),
          const SizedBox(height: 24),
          const Text(
            'Jiunge Kufungua Zaidi',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 16),
          _GuestFeatureCard(
            icon: Icons.account_balance_wallet,
            title: 'Mkulima Pay',
            description: 'Lipa bidhaa na pokea malipo kwa usalama',
            color: Colors.orange,
            locked: true,
          ),
          _GuestFeatureCard(
            icon: Icons.psychology,
            title: 'Mtaalamu wa AI',
            description: 'Pata ushauri wa kilimo kutoka kwa akili bandia',
            color: Colors.teal,
            locked: true,
          ),
          _GuestFeatureCard(
            icon: Icons.flight_takeoff,
            title: 'Huduma za Drone',
            description: 'Weka nafasi ya drone kupuliza au kupiga picha',
            color: Colors.indigo,
            locked: true,
          ),
          _GuestFeatureCard(
            icon: Icons.sensors,
            title: 'Vifaa vya IoT',
            description: 'Fuatilia hali ya udongo na hewa kiotomatiki',
            color: Colors.blue,
            locked: true,
          ),
          _GuestFeatureCard(
            icon: Icons.calculate,
            title: 'Kadiria Mavuno',
            description: 'Tumia AI kukadiria mavuno yako kabla ya kuvuna',
            color: Colors.amber,
            locked: true,
          ),
          _GuestFeatureCard(
            icon: Icons.security,
            title: 'Mkulima Escrow',
            description: 'Linda malipo yako hadi bidhaa ikufikie',
            color: Colors.green,
            locked: true,
          ),
          const SizedBox(height: 24),
          SizedBox(
            width: double.infinity,
            height: 56,
            child: ElevatedButton(
              onPressed: () => LoginModal.show(context),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF2E7D32),
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
              child: const Text('Ingia Sasa', style: TextStyle(fontSize: 18)),
            ),
          ),
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            height: 48,
            child: OutlinedButton(
              onPressed: () {
                Navigator.of(context).push(
                  MaterialPageRoute(
                    builder: (_) => const LoginScreenPlaceholder(),
                  ),
                );
              },
              style: OutlinedButton.styleFrom(
                foregroundColor: const Color(0xFF2E7D32),
                side: const BorderSide(color: Color(0xFF2E7D32)),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
              child: const Text('Jiunge'),
            ),
          ),
          const SizedBox(height: 32),
        ],
      ),
    );
  }
}

class _GuestFeatureCard extends StatelessWidget {
  final IconData icon;
  final String title;
  final String description;
  final Color color;
  final bool locked;
  final VoidCallback? onTap;

  const _GuestFeatureCard({
    required this.icon,
    required this.title,
    required this.description,
    required this.color,
    this.locked = false,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Opacity(
        opacity: locked ? 0.6 : 1.0,
        child: ListTile(
          leading: Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: color),
          ),
          title: Row(
            children: [
              Text(title),
              if (locked) ...[
                const SizedBox(width: 8),
                Icon(Icons.lock, size: 14, color: Colors.grey[600]),
              ],
            ],
          ),
          subtitle: Text(description),
          trailing: locked
              ? const Icon(Icons.chevron_right, color: Colors.grey)
              : const Icon(Icons.chevron_right),
          onTap: locked ? () => LoginModal.show(context) : onTap,
        ),
      ),
    );
  }
}

class LoginScreenPlaceholder extends StatelessWidget {
  const LoginScreenPlaceholder({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Ingia'),
        backgroundColor: const Color(0xFF2E7D32),
        foregroundColor: Colors.white,
      ),
      body: const Center(child: Text('Login Screen')),
    );
  }
}
