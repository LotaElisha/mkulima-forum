import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import 'login_screen.dart';

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
              user.name[0].toUpperCase(),
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
            icon: Icons.shopping_bag,
            title: 'Maagizo Yangu',
            onTap: () {},
          ),
          _buildMenuItem(
            icon: Icons.location_on,
            title: 'Anwani ya Utoaji',
            onTap: () {},
          ),
          _buildMenuItem(
            icon: Icons.language,
            title: 'Lugha',
            subtitle: user.preferredLanguage == 'sw' ? 'Kiswahili' : 'English',
            onTap: () {},
          ),
          _buildMenuItem(
            icon: Icons.verified_user,
            title: 'KYC Status',
            subtitle: user.kycStatus.toUpperCase(),
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
    required VoidCallback onTap,
  }) {
    return ListTile(
      leading: Icon(icon, color: const Color(0xFF2E7D32)),
      title: Text(title),
      subtitle: subtitle != null ? Text(subtitle) : null,
      trailing: const Icon(Icons.chevron_right),
      onTap: onTap,
    );
  }
}