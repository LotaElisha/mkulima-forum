import '../core/theme.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import 'login_screen.dart';
import 'login_modal.dart';
import 'home_screen.dart';
import 'orders_screen.dart';
import 'kyc_screen.dart';
import 'settings_screen.dart';
import 'seller_dashboard_screen.dart';
import 'notifications_screen.dart';
import 'wallet_screen.dart';
import 'weather_screen.dart';
import 'sms_screen.dart';
import 'ivr_screen.dart';
import 'mkulima_bot_screen.dart';
import 'scanner_screen.dart';
import 'drone_screen.dart';
import 'iot_screen.dart';
import 'yield_screen.dart';
import 'escrow_screen.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    final user = auth.user;

    if (!auth.isAuthenticated) {
      return _buildGuestView(context);
    }

    if (user == null) {
      return const Center(child: CircularProgressIndicator());
    }

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          _buildProfileHeader(user),
          const SizedBox(height: 24),
          _buildStatsRow(),
          const SizedBox(height: 24),
          _buildSectionTitle('Miamala na Malipo'),
          _buildMenuItem(
            icon: Icons.account_balance_wallet,
            title: 'Mkulima Pay',
            subtitle: 'Lipia na uweke pesa',
            color: Colors.green,
            onTap: () => _navigate(context, const WalletScreen()),
          ),
          _buildMenuItem(
            icon: Icons.shopping_bag,
            title: 'Maagizo Yangu',
            subtitle: 'Fuatilia ununuzi wako',
            color: Colors.indigo,
            onTap: () => _navigate(context, const OrdersScreen()),
          ),
          const SizedBox(height: 16),
          _buildSectionTitle('Zana za Kilimo'),
          _buildMenuItem(
            icon: Icons.wb_sunny,
            title: 'Hali ya Hewa',
            subtitle: 'Tahmini na arifa za hali ya hewa',
            color: Colors.orange,
            onTap: () => _navigate(context, const WeatherScreen()),
          ),
          _buildMenuItem(
            icon: Icons.psychology,
            title: 'Mkulima AI',
            subtitle: 'Msaidizi wako wa kilimo wa AI',
            color: Colors.blue,
            onTap: () => _navigate(context, const MkulimaBotScreen()),
          ),
          _buildMenuItem(
            icon: Icons.camera_alt,
            title: 'Kagua Mimea',
            subtitle: 'Piga picha ugundue ugonjwa',
            color: Colors.red,
            onTap: () => _navigate(context, const ScannerScreen()),
          ),
          const SizedBox(height: 16),
          _buildSectionTitle('Mawasiliano'),
          _buildMenuItem(
            icon: Icons.message,
            title: 'SMS/USSD',
            subtitle: 'Tuma ujumbe mfupi',
            color: Colors.blue,
            onTap: () => _navigate(context, const SmsScreen()),
          ),
          _buildMenuItem(
            icon: Icons.phone_in_talk,
            title: 'Simu ya Kupiga',
            subtitle: 'IVR msaada wa sauti',
            color: Colors.purple,
            onTap: () => _navigate(context, const IvrScreen()),
          ),
          _buildMenuItem(
            icon: Icons.notifications,
            title: 'Arifa Zangu',
            subtitle: 'Taarifa muhimu',
            color: Colors.amber,
            badge: '3',
            onTap: () => _navigate(context, const NotificationsScreen()),
          ),
          const SizedBox(height: 16),
          _buildSectionTitle('Biashara'),
          if (user.role == 'farmer' || user.role == 'agrodealer')
            _buildMenuItem(
              icon: Icons.dashboard,
              title: 'Dashibodi ya Muuzaji',
              subtitle: 'Onesha mauzo na bidhaa',
              color: Colors.cyan,
              onTap: () => _navigate(context, const SellerDashboardScreen()),
            ),
          _buildMenuItem(
            icon: Icons.verified_user,
            title: 'KYC Verification',
            subtitle: user.kycStatus.toUpperCase(),
            color: Colors.deepOrange,
            onTap: () => _navigate(context, const KycScreen()),
          ),
          const SizedBox(height: 16),
          _buildSectionTitle('Teknolojia Kuu'),
          _buildMenuItem(
            icon: Icons.flight_takeoff,
            title: 'Huduma za Drone',
            subtitle: 'Puliza, piga picha, fuatilia',
            color: Colors.indigo,
            onTap: () => _navigate(context, const DroneScreen()),
          ),
          _buildMenuItem(
            icon: Icons.sensors,
            title: 'Vifaa vya IoT',
            subtitle: 'Fuatilia udongo na hali ya hewa',
            color: Colors.blue,
            onTap: () => _navigate(context, const IoTScreen()),
          ),
          _buildMenuItem(
            icon: Icons.calculate,
            title: 'Kadiria Mavuno',
            subtitle: 'AI inakadiria mavuno yako',
            color: Colors.orange,
            onTap: () => _navigate(context, const YieldScreen()),
          ),
          _buildMenuItem(
            icon: Icons.security,
            title: 'Mkulima Escrow',
            subtitle: 'Linda malipo yako',
            color: Colors.green,
            onTap: () => _navigate(context, const EscrowScreen()),
          ),
          const SizedBox(height: 16),
          _buildSectionTitle('Mfumo'),
          _buildMenuItem(
            icon: Icons.settings,
            title: 'Mipangilio',
            subtitle: 'Weka upendeleo wa programu',
            color: Colors.grey,
            onTap: () => _navigate(context, const SettingsScreen()),
          ),
          _buildMenuItem(
            icon: Icons.help_outline,
            title: 'Msaada',
            subtitle: 'Jifunze jinsi ya kutumia',
            color: Colors.lightBlue,
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
                    MaterialPageRoute(builder: (_) => const HomeScreen()),
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

  Widget _buildGuestView(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 100,
              height: 100,
              decoration: BoxDecoration(
                color: MkColors.primary.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(24),
              ),
              child: const Icon(
                Icons.person_outline,
                size: 60,
                color: MkColors.primary,
              ),
            ),
            const SizedBox(height: 24),
            const Text(
              'Hujajiunga bado',
              style: TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Ingia ili uweze kuangalia wasifu wako, maagizo, na zaidi.',
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.grey[600]),
            ),
            const SizedBox(height: 32),
            SizedBox(
              width: double.infinity,
              height: 56,
              child: ElevatedButton(
                onPressed: () => LoginModal.show(context),
                style: ElevatedButton.styleFrom(
                  backgroundColor: MkColors.primary,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                child: const Text(
                  'Ingia Sasa',
                  style: TextStyle(fontSize: 18),
                ),
              ),
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              height: 48,
              child: OutlinedButton(
                onPressed: () {
                  Navigator.of(context).push(
                    MaterialPageRoute(
                      builder: (_) => const LoginScreen(),
                    ),
                  );
                },
                style: OutlinedButton.styleFrom(
                  foregroundColor: MkColors.primary,
                  side: const BorderSide(color: MkColors.primary),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                child: const Text('Au Jiunge'),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildProfileHeader(dynamic user) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            CircleAvatar(
              radius: 50,
              backgroundColor: MkColors.primary,
              child: Text(
                user.name.isNotEmpty ? user.name[0].toUpperCase() : 'U',
                style: const TextStyle(fontSize: 36, color: Colors.white),
              ),
            ),
            const SizedBox(height: 16),
            Text(
              user.name,
              style: const TextStyle(
                fontSize: 22,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              user.phone,
              style: TextStyle(color: Colors.grey[600], fontSize: 14),
            ),
            const SizedBox(height: 8),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Chip(
                  label: Text(
                    user.role.toUpperCase(),
                    style: const TextStyle(color: Colors.white, fontSize: 12),
                  ),
                  backgroundColor: MkColors.primary,
                ),
                const SizedBox(width: 8),
                Chip(
                  label: Text(
                    user.kycStatus.toUpperCase(),
                    style: const TextStyle(color: Colors.white, fontSize: 12),
                  ),
                  backgroundColor: user.kycStatus == 'verified'
                      ? Colors.green
                      : Colors.orange,
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatsRow() {
    return Row(
      children: [
        _buildStatCard('Mauzo', '12', Icons.trending_up, Colors.green),
        const SizedBox(width: 12),
        _buildStatCard('Manunuzi', '5', Icons.shopping_cart, Colors.blue),
        const SizedBox(width: 12),
        _buildStatCard('Mapato', '450K', Icons.attach_money, Colors.orange),
      ],
    );
  }

  Widget _buildStatCard(String label, String value, IconData icon, Color color) {
    return Expanded(
      child: Card(
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Column(
            children: [
              Icon(icon, color: color, size: 24),
              const SizedBox(height: 8),
              Text(
                value,
                style: const TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                label,
                style: TextStyle(color: Colors.grey[600], fontSize: 12),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSectionTitle(String title) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12, top: 8),
      child: Align(
        alignment: Alignment.centerLeft,
        child: Text(
          title,
          style: const TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.bold,
            color: MkColors.primary,
          ),
        ),
      ),
    );
  }

  Widget _buildMenuItem({
    required IconData icon,
    required String title,
    required String subtitle,
    required Color color,
    String? badge,
    required VoidCallback onTap,
  }) {
    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        leading: Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: color.withValues(alpha: 0.1),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(icon, color: color),
        ),
        title: Text(title),
        subtitle: Text(subtitle),
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

  void _navigate(BuildContext context, Widget screen) {
    Navigator.of(context).push(MaterialPageRoute(builder: (_) => screen));
  }
}
