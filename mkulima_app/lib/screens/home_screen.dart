import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import 'marketplace_screen.dart';
import 'forum_screen.dart';
import 'scanner_screen.dart';
import 'agronomist_screen.dart';
import 'profile_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _currentIndex = 0;

  final List<Widget> _screens = const [
    MarketplaceScreen(),
    ForumScreen(),
    ScannerScreen(),
    AgronomistScreen(),
    ProfileScreen(),
  ];

  final List<String> _titles = [
    'Soko',
    'Jukwaa',
    'Kagua Mimea',
    'Mtaalamu wa AI',
    'Wasifu',
  ];

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);

    return Scaffold(
      appBar: AppBar(
        title: Text(_titles[_currentIndex]),
        backgroundColor: const Color(0xFF2E7D32),
        foregroundColor: Colors.white,
        actions: [
          if (auth.user != null)
            Padding(
              padding: const EdgeInsets.only(right: 16),
              child: Center(
                child: Text(
                  'Habari, ${auth.user!.name.split(' ').first}',
                  style: const TextStyle(fontSize: 14),
                ),
              ),
            ),
        ],
      ),
      body: _screens[_currentIndex],
      bottomNavigationBar: NavigationBar(
        selectedIndex: _currentIndex,
        onDestinationSelected: (index) => setState(() => _currentIndex = index),
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.store),
            label: 'Soko',
          ),
          NavigationDestination(
            icon: Icon(Icons.forum),
            label: 'Jukwaa',
          ),
          NavigationDestination(
            icon: Icon(Icons.camera_alt),
            label: 'Kagua',
          ),
          NavigationDestination(
            icon: Icon(Icons.psychology),
            label: 'AI',
          ),
          NavigationDestination(
            icon: Icon(Icons.person),
            label: 'Wasifu',
          ),
        ],
      ),
    );
  }
}
