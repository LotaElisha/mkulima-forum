import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import '../core/theme.dart';
import 'weather_screen.dart';
import 'wallet_screen.dart';
import 'sms_screen.dart';
import 'ivr_screen.dart';
import 'agronomist_screen.dart';
import 'scanner_screen.dart';
import 'notifications_screen.dart';
import 'orders_screen.dart';
import 'seller_dashboard_screen.dart';
import 'kyc_screen.dart';
import 'settings_screen.dart';
import 'drone_screen.dart';
import 'iot_screen.dart';
import 'yield_screen.dart';
import 'escrow_screen.dart';
import 'forum_screen.dart';
import 'upgrade_screen.dart';

class ServiceItem {
  final IconData icon;
  final String name;
  final String description;
  final String requiredPlan;
  final Color color;
  final Widget targetScreen;
  final List<String> benefits;

  const ServiceItem({
    required this.icon,
    required this.name,
    required this.description,
    required this.requiredPlan,
    required this.color,
    required this.targetScreen,
    required this.benefits,
  });
}

class FeaturesScreen extends StatefulWidget {
  const FeaturesScreen({super.key});

  @override
  State<FeaturesScreen> createState() => _FeaturesScreenState();
}

class _FeaturesScreenState extends State<FeaturesScreen> {
  Map<String, dynamic>? _weather;
  bool _weatherLoading = true;

  final List<ServiceItem> _miamalaServices = [
    const ServiceItem(
      icon: Icons.account_balance_wallet_outlined,
      name: 'Mkulima Pay',
      description: 'Hifadhi na lipia miamala kwa usalama shambani.',
      requiredPlan: 'Free',
      color: Colors.green,
      targetScreen: WalletScreen(),
      benefits: ['Akaunti ya bure ya malipo', 'Tuma na upokee pesa za mazao', 'Miamala ya haraka bila makato'],
    ),
    const ServiceItem(
      icon: Icons.security_outlined,
      name: 'Mkulima Escrow',
      description: 'Linda malipo ya soko hadi bidhaa ifike.',
      requiredPlan: 'Pro',
      color: Colors.teal,
      targetScreen: EscrowScreen(),
      benefits: ['Ulinzi dhidi ya utapeli wa mazao', 'Uamuzi wa haraka wa migogoro', 'Uhifadhi salama wa malipo'],
    ),
    const ServiceItem(
      icon: Icons.shopping_bag_outlined,
      name: 'Maagizo Yangu',
      description: 'Fuatilia na dhibiti maagizo yako yote ya soko.',
      requiredPlan: 'Free',
      color: Colors.indigo,
      targetScreen: OrdersScreen(),
      benefits: ['Orodha kamili ya maagizo', 'Ufuatiliaji wa usafirishaji wa mizigo', 'Stakabadhi na kumbukumbu'],
    ),
  ];

  final List<ServiceItem> _kilimoServices = [
    const ServiceItem(
      icon: Icons.wb_sunny_outlined,
      name: 'Hali ya Hewa',
      description: 'Utabiri wa hali ya hewa ya shambani kwako.',
      requiredPlan: 'Free',
      color: Colors.orange,
      targetScreen: WeatherScreen(),
      benefits: ['Utabiri wa siku 7', 'Tahadhari za ukame/mvua za ghafla', 'Ushauri wa kupanda mazao'],
    ),
    const ServiceItem(
      icon: Icons.psychology_outlined,
      name: 'Mtaalamu wa AI',
      description: 'Uliza na upate msaada wa kilimo kutoka kwa AI.',
      requiredPlan: 'Pro',
      color: Colors.blue,
      targetScreen: AgronomistScreen(),
      benefits: ['Msaada wa saa 24/7', 'Utambuzi wa magonjwa ya mazao', 'Ushauri wa matumizi ya mbolea'],
    ),
    const ServiceItem(
      icon: Icons.center_focus_strong,
      name: 'Kagua Mimea',
      description: 'Piga picha ugundue magonjwa ya mazao.',
      requiredPlan: 'Free',
      color: Colors.red,
      targetScreen: ScannerScreen(),
      benefits: ['Ugunduzi wa haraka ndani ya sekunde', 'Ushauri wa dawa na kinga ya mmea', 'Historia ya magonjwa yaliyopita'],
    ),
    const ServiceItem(
      icon: Icons.calculate_outlined,
      name: 'Kadiria Mavuno',
      description: 'Hesabu na ukadiriaji wa mavuno ya msimu.',
      requiredPlan: 'Business',
      color: Colors.amber,
      targetScreen: YieldScreen(),
      benefits: ['Ukadiriaji wa mazao kwa ekari', 'Uchambuzi wa gharama na faida', 'Ripoti ya mavuno kwa msimu'],
    ),
  ];

  final List<ServiceItem> _teknolojiaServices = [
    const ServiceItem(
      icon: Icons.flight_takeoff_outlined,
      name: 'Drone za Shamba',
      description: 'Huduma za drone kwa ramani na upuliziaji.',
      requiredPlan: 'Enterprise',
      color: Colors.purple,
      targetScreen: DroneScreen(),
      benefits: ['Upigaji picha na ramani ya shamba', 'Upuliziaji wa kisasa wa viatilifu', 'Ufuatiliaji wa ukuaji wa mazao'],
    ),
    const ServiceItem(
      icon: Icons.sensors_outlined,
      name: 'Vifaa vya IoT',
      description: 'Sensors za kufuatilia udongo na unyevu.',
      requiredPlan: 'Business',
      color: Colors.cyan,
      targetScreen: IoTScreen(),
      benefits: ['Vipimo vya unyevu wa udongo live', 'Kiwango cha joto cha udongo shambani', 'Taarifa za virutubisho vya NPK'],
    ),
  ];

  final List<ServiceItem> _mawasilianoServices = [
    const ServiceItem(
      icon: Icons.message_outlined,
      name: 'SMS na USSD',
      description: 'Huduma za kilimo bila internet kupitia simu.',
      requiredPlan: 'Free',
      color: Colors.greenAccent,
      targetScreen: SmsScreen(),
      benefits: ['Ujumbe mfupi wa bei za soko', 'Miongozo ya kilimo kupitia USSD', 'Hali ya hewa bila bando'],
    ),
    const ServiceItem(
      icon: Icons.phone_in_talk_outlined,
      name: 'Simu (IVR)',
      description: 'Msaada wa sauti shambani kupitia IVR.',
      requiredPlan: 'Free',
      color: Colors.deepPurple,
      targetScreen: IvrScreen(),
      benefits: ['Masomo ya sauti kwa Kiswahili', 'Kuunganishwa na maafisa ugani', 'Ushauri wa kupiga bure'],
    ),
    const ServiceItem(
      icon: Icons.forum_outlined,
      name: 'Jukwaa la Jamii',
      description: 'Jadiliana na ubadilishane uzoefu na wakulima wengine.',
      requiredPlan: 'Free',
      color: Colors.tealAccent,
      targetScreen: ForumScreen(),
      benefits: ['Uliza maswali kwa jamii', 'Uzoefu wa kilimo kutoka kwa wengine', 'Soko la kubadilishana taarifa'],
    ),
    const ServiceItem(
      icon: Icons.verified_user_outlined,
      name: 'Thibitisha KYC',
      description: 'Thibitisha wasifu wako ili kuaminika.',
      requiredPlan: 'Free',
      color: Colors.deepOrange,
      targetScreen: KycScreen(),
      benefits: ['Beji maalum ya uthibitisho', 'Ruhusa ya kuuza soko la mkulima', 'Uaminifu mkubwa kutoka kwa wanunuzi'],
    ),
  ];

  @override
  void initState() {
    super.initState();
    _fetchWeather();
  }

  Future<void> _fetchWeather() async {
    try {
      final api = Provider.of<ApiService>(context, listen: false);
      final weatherData = await api.getWeather();
      if (mounted) {
        setState(() {
          _weather = weatherData;
          _weatherLoading = false;
        });
      }
    } catch (_) {
      if (mounted) {
        setState(() => _weatherLoading = false);
      }
    }
  }

  int _getPlanRank(String plan) {
    switch (plan.toLowerCase()) {
      case 'free':
        return 1;
      case 'pro':
        return 2;
      case 'business':
        return 3;
      case 'enterprise':
        return 4;
      default:
        return 0;
    }
  }

  bool _isPlanAuthorized(String userPlan, String requiredPlan) {
    return _getPlanRank(userPlan) >= _getPlanRank(requiredPlan);
  }

  Color _getBadgeColor(String plan) {
    switch (plan.toLowerCase()) {
      case 'free':
        return Colors.green;
      case 'pro':
        return Colors.blue;
      case 'business':
        return Colors.orange[700]!;
      case 'enterprise':
        return Colors.purple;
      default:
        return Colors.grey;
    }
  }

  Future<void> _handleServiceTap(ServiceItem service) async {
    final auth = Provider.of<AuthProvider>(context, listen: false);
    final authenticated = await AuthProvider.requireAuth(
      context,
      action: 'kutumia huduma ya ${service.name}',
    );
    if (!authenticated) return;

    final userPlan = auth.subscriptionPlan;
    final isAuthorized = _isPlanAuthorized(userPlan, service.requiredPlan);

    if (isAuthorized) {
      if (mounted) {
        Navigator.of(context).push(
          MaterialPageRoute(builder: (_) => service.targetScreen),
        );
      }
    } else {
      if (mounted) {
        Navigator.of(context).push(
          MaterialPageRoute(
            builder: (_) => UpgradeScreen(
              currentPlan: userPlan,
              requiredPlan: service.requiredPlan,
              serviceName: service.name,
              benefits: service.benefits,
              targetScreen: service.targetScreen,
            ),
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);

    return Scaffold(
      body: RefreshIndicator(
        onRefresh: () async {
          setState(() => _weatherLoading = true);
          await _fetchWeather();
        },
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // 1. Hero Header Section with embedded Weather Gadget
              _buildHeroSection(auth),
              const SizedBox(height: 24),

              // 2. Services Grid Category Blocks
              _buildCategoryBlock('Miamala na Malipo', _miamalaServices),
              _buildCategoryBlock('Zana za Kilimo (AI)', _kilimoServices),
              _buildCategoryBlock('Teknolojia ya Juu', _teknolojiaServices),
              _buildCategoryBlock('Mawasiliano na Wasifu', _mawasilianoServices),

              // System Settings Screen Card
              const SizedBox(height: 16),
              Card(
                margin: EdgeInsets.zero,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(MkRadii.card),
                  side: BorderSide(color: Colors.grey[200]!),
                ),
                child: ListTile(
                  leading: const Icon(Icons.settings, color: Colors.grey),
                  title: const Text('Mipangilio ya Mfumo'),
                  subtitle: const Text('Weka mapendeleo ya lugha na arifa'),
                  trailing: const Icon(Icons.chevron_right),
                  onTap: () => _handleServiceTap(const ServiceItem(
                    icon: Icons.settings,
                    name: 'Mipangilio',
                    description: 'Weka mapendeleo ya lugha na arifa',
                    requiredPlan: 'Free',
                    color: Colors.grey,
                    targetScreen: SettingsScreen(),
                    benefits: [],
                  )),
                ),
              ),
              const SizedBox(height: 40),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHeroSection(AuthProvider auth) {
    final userName = auth.isAuthenticated ? auth.user!.name.split(' ').first : 'Mgeni';

    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF2E7D32), Color(0xFF1B5E20)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF2E7D32).withValues(alpha: 0.3),
            blurRadius: 10,
            offset: const Offset(0, 4),
          )
        ],
      ),
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Habari, $userName!',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 22,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    auth.isAuthenticated
                        ? 'Kifurushi chako: ${auth.subscriptionPlan}'
                        : 'Jiunge leo kupata huduma zote',
                    style: const TextStyle(color: Colors.white70, fontSize: 13),
                  ),
                ],
              ),
              if (!auth.isAuthenticated)
                ElevatedButton(
                  onPressed: () => AuthProvider.requireAuth(context),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.white,
                    foregroundColor: const Color(0xFF2E7D32),
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  ),
                  child: const Text('Ingia', style: TextStyle(fontWeight: FontWeight.bold)),
                ),
            ],
          ),
          const SizedBox(height: 20),
          const Divider(color: Colors.white24, height: 1),
          const SizedBox(height: 16),

          // Embedded Weather Gadget
          GestureDetector(
            onTap: () => Navigator.of(context).push(
              MaterialPageRoute(builder: (_) => const WeatherScreen()),
            ),
            child: Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.15),
                borderRadius: BorderRadius.circular(16),
              ),
              child: _weatherLoading
                  ? const Center(
                      child: SizedBox(
                        width: 24,
                        height: 24,
                        child: CircularProgressIndicator(
                          color: Colors.white,
                          strokeWidth: 2,
                        ),
                      ),
                    )
                  : Row(
                      children: [
                        const Icon(Icons.cloud, color: Colors.white, size: 36),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                _weather?['location']?['city'] ?? 'Dar es Salaam',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 14,
                                ),
                              ),
                              Text(
                                _weather?['current']?['description'] ?? 'Mawingu kiasi',
                                style: const TextStyle(color: Colors.white70, fontSize: 12),
                              ),
                            ],
                          ),
                        ),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.end,
                          children: [
                            Text(
                              '${_weather?['current']?['temp'] ?? 28}°C',
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 24,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const Text(
                              'Unyevu: 70%',
                              style: TextStyle(color: Colors.white60, fontSize: 10),
                            ),
                          ],
                        ),
                      ],
                    ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCategoryBlock(String categoryTitle, List<ServiceItem> services) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(vertical: 12),
          child: Text(
            categoryTitle,
            style: const TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: Color(0xFF2E7D32),
            ),
          ),
        ),
        GridView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 2,
            crossAxisSpacing: 12,
            mainAxisSpacing: 12,
            childAspectRatio: 1.15,
          ),
          itemCount: services.length,
          itemBuilder: (context, index) {
            final service = services[index];
            return _buildServiceGridCard(service);
          },
        ),
        const SizedBox(height: 12),
      ],
    );
  }

  Widget _buildServiceGridCard(ServiceItem service) {
    final planBadge = service.requiredPlan;

    return Card(
      elevation: 1,
      margin: EdgeInsets.zero,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(MkRadii.card),
        side: BorderSide(color: Colors.grey[100]!),
      ),
      child: InkWell(
        onTap: () => _handleServiceTap(service),
        borderRadius: BorderRadius.circular(MkRadii.card),
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: service.color.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Icon(service.icon, color: service.color, size: 22),
                  ),
                  if (planBadge != 'Free')
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: _getBadgeColor(planBadge).withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        planBadge,
                        style: TextStyle(
                          color: _getBadgeColor(planBadge),
                          fontSize: 9,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                ],
              ),
              const Spacer(),
              Text(
                service.name,
                style: const TextStyle(
                  fontWeight: FontWeight.bold,
                  fontSize: 14,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                service.description,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  color: Colors.grey[600],
                  fontSize: 10,
                  height: 1.2,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
