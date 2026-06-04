import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import 'login_screen.dart';
import 'orders_screen.dart';
import 'kyc_screen.dart';
import 'settings_screen.dart';
import 'seller_dashboard_screen.dart';
import 'notifications_screen.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    final user = auth.user;

    if (user == null) {
      return const Center(child: CircularProgressIndicator());
    }

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          CircleAvatar(
            radius: 50,
            backgroundColor: const Color(0xFF2E7D32),
            child: Text(
              user.name.isNotEmpty ? user.name[0].toUpperCase() : 'U',
              style: const TextStyle(fontSize: 36, color: Colors.white),
            ),
          ),
          const SizedBox(height: 16),
          Text(
            user.name,
            style: Theme.of(context).textTheme.headlineSmall,
          ),
          const SizedBox(height: 4),
          Text(
            user.phone,
            style: TextStyle(color: Colors.grey[600]),
          ),
          const SizedBox(height: 4),
          Chip(
            label: Text(
              user.role.toUpperCase(),
              style: const TextStyle(color: Colors.white, fontSize: 12),
            ),
            backgroundColor: const Color(0xFF2E7D32),
          ),
          const SizedBox(height: 32),
          _buildMenuItem(
            icon: Icons.notifications,
            title: 'Arifa',
            badge: '2',
            onTap: () {
              Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const NotificationsScreen()),
              );
            },
          ),
          _buildMenuItem(
            icon: Icons.shopping_bag,
            title: 'Maagizo Yangu',
            onTap: () {
              Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const OrdersScreen()),
              );
            },
          ),
          if (user.role == 'farmer' || user.role == 'agrodealer')
            _buildMenuItem(
              icon: Icons.store,
              title: 'Dashibodi ya Muuzaji',
              onTap: () {
                Navigator.of(context).push(
                  MaterialPageRoute(
                    builder: (_) => const SellerDashboardScreen(),
                  ),
                );
              },
            ),
          _buildMenuItem(
            icon: Icons.verified_user,
            title: 'KYC Verification',
            subtitle: user.kycStatus.toUpperCase(),
            onTap: () {
              Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const KycScreen()),
              );
            },
          ),
          _buildMenuItem(
            icon: Icons.settings,
            title: 'Mipangilio',
            onTap: () {
              Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const SettingsScreen()),
              );
            },
          ),
          _buildMenuItem(
            icon: Icons.help,
            title: 'Msaada',
            onTap: () {},
          ),
          const SizedBox(height: 32),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              onPressed: () async {
                await auth.logout();
                if (context.mounted) {
                  Navigator.of(context).pushReplacement(
                    MaterialPageRoute(builder: (_) => const LoginScreen()),
                  );
                }
              },
              icon: const Icon(Icons.logout),
              label: const Text('Toka'),
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.red[700],
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 16),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMenuItem({
    required IconData icon,
    required String title,
    String? subtitle,
    String? badge,
    required VoidCallback onTap,
  }) {
    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        leading: Icon(icon, color: const Color(0xFF2E7D32)),
        title: Text(title),
        subtitle: subtitle != null ? Text(subtitle) : null,
        trailing: badge != null
            ? Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: Colors.red,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  badge,
                  style: const TextStyle(color: Colors.white, fontSize: 12),
                ),
              )
            : const Icon(Icons.chevron_right),
        onTap: onTap,
      ),
    );
  }
}
