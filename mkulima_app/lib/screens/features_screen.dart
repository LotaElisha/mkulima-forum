import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../core/theme.dart';
import 'weather_screen.dart';
import 'market_prices_screen.dart';
import 'kagua_dawa_screen.dart';
import 'wallet_screen.dart';
import 'sms_screen.dart';
import 'ivr_screen.dart';
import 'mkulima_bot_screen.dart';
import 'scanner_screen.dart';
import 'orders_screen.dart';
import 'kyc_screen.dart';
import 'settings_screen.dart';
import 'drone_screen.dart';
import 'iot_screen.dart';
import 'yield_screen.dart';
import 'escrow_screen.dart';
import 'forum_screen.dart';

class ServiceItem {
  final IconData icon;
  final String name;
  final String description;
  final String requiredPlan;
  final Color color;
  final Widget targetScreen;
  final List<String> benefits;

  /// True when the backend for this service is not live yet.
  ///
  /// Drone and IoT front endpoints that answer 503 by design — there is no
  /// operator network and no device fleet. The entries stay because they
  /// describe the roadmap, but they open an explanation rather than a screen
  /// that will fail.
  final bool comingSoon;

  const ServiceItem({
    required this.icon,
    required this.name,
    required this.description,
    required this.requiredPlan,
    required this.color,
    required this.targetScreen,
    required this.benefits,
    this.comingSoon = false,
  });
}

class FeaturesScreen extends StatefulWidget {
  const FeaturesScreen({super.key});

  @override
  State<FeaturesScreen> createState() => _FeaturesScreenState();
}

class _FeaturesScreenState extends State<FeaturesScreen> {
  final List<ServiceItem> _miamalaServices = [
    const ServiceItem(
      icon: Icons.account_balance_wallet_outlined,
      name: 'Mkulima Pay',
      description: 'Hifadhi na lipia miamala kwa usalama shambani.',
      requiredPlan: 'Free',
      color: MkColors.primary,
      targetScreen: WalletScreen(),
      benefits: [
        'Akaunti ya bure ya malipo',
        'Tuma na upokee pesa za mazao',
        'Miamala ya haraka bila makato',
      ],
    ),
    const ServiceItem(
      icon: Icons.security_outlined,
      name: 'Mkulima Escrow',
      description: 'Linda malipo ya soko hadi bidhaa ifike.',
      requiredPlan: 'Pro',
      color: MkColors.primary,
      targetScreen: EscrowScreen(),
      benefits: [
        'Ulinzi dhidi ya utapeli wa mazao',
        'Uamuzi wa haraka wa migogoro',
        'Uhifadhi salama wa malipo',
      ],
    ),
    const ServiceItem(
      icon: Icons.shopping_bag_outlined,
      name: 'Maagizo Yangu',
      description: 'Fuatilia na dhibiti maagizo yako yote ya soko.',
      requiredPlan: 'Free',
      color: MkColors.primary,
      targetScreen: OrdersScreen(),
      benefits: [
        'Orodha kamili ya maagizo',
        'Ufuatiliaji wa usafirishaji wa mizigo',
        'Stakabadhi na kumbukumbu',
      ],
    ),
  ];

  final List<ServiceItem> _kilimoServices = [
    const ServiceItem(
      icon: Icons.wb_sunny_outlined,
      name: 'Hali ya Hewa',
      description: 'Utabiri wa hali ya hewa ya shambani kwako.',
      requiredPlan: 'Free',
      color: MkColors.warning,
      targetScreen: WeatherScreen(),
      benefits: [
        'Utabiri wa siku 7',
        'Tahadhari za ukame/mvua za ghafla',
        'Ushauri wa kupanda mazao',
      ],
    ),
    const ServiceItem(
      icon: Icons.verified_user_outlined,
      name: 'Kagua Dawa',
      description: 'Gundua dawa na mbolea feki kabla ya kununua.',
      requiredPlan: 'Free',
      color: MkColors.danger,
      targetScreen: KaguaDawaScreen(),
      benefits: [
        'Orodha ya usajili ya TPHPA/TFRA',
        'Kagua lebo kwa picha (AI)',
        'Tahadhari za dawa feki mkoani kwako',
      ],
    ),
    const ServiceItem(
      icon: Icons.price_change_outlined,
      name: 'Bei za Masoko',
      description: 'Bei halisi za mazao kwenye masoko makuu.',
      requiredPlan: 'Free',
      color: MkColors.primary,
      targetScreen: MarketPricesScreen(),
      benefits: [
        'Bei za chini na juu kwa kila soko',
        'Mwenendo wa bei (inapanda/inashuka)',
        'Tarehe ya bei kuonyeshwa wazi',
      ],
    ),
    const ServiceItem(
      icon: Icons.psychology_outlined,
      name: 'Mkulima AI',
      description:
          'Msaidizi wako wa kilimo wa AI — mazungumzo na ushauri wa haraka.',
      requiredPlan: 'Free',
      color: MkColors.primary,
      targetScreen: MkulimaBotScreen(),
      benefits: [
        'Mazungumzo yanayoendelea (kumbukumbu)',
        'Msaada wa saa 24/7',
        'Utambuzi wa magonjwa ya mazao',
        'Ushauri wa mazao, mbolea na wadudu',
      ],
    ),
    const ServiceItem(
      icon: Icons.center_focus_strong,
      name: 'Kagua Mimea',
      description: 'Piga picha ugundue magonjwa ya mazao.',
      requiredPlan: 'Free',
      color: MkColors.danger,
      targetScreen: ScannerPage(),
      benefits: [
        'Ugunduzi wa haraka ndani ya sekunde',
        'Ushauri wa dawa na kinga ya mmea',
        'Historia ya magonjwa yaliyopita',
      ],
    ),
    const ServiceItem(
      icon: Icons.calculate_outlined,
      name: 'Kadiria Mavuno',
      description: 'Hesabu na ukadiriaji wa mavuno ya msimu.',
      requiredPlan: 'Business',
      color: MkColors.warning,
      targetScreen: YieldScreen(),
      benefits: [
        'Ukadiriaji wa mazao kwa ekari',
        'Uchambuzi wa gharama na faida',
        'Ripoti ya mavuno kwa msimu',
      ],
    ),
  ];

  final List<ServiceItem> _teknolojiaServices = [
    const ServiceItem(
      icon: Icons.flight_takeoff_outlined,
      name: 'Drone za Shamba',
      description: 'Huduma za drone kwa ramani na upuliziaji.',
      requiredPlan: 'Enterprise',
      color: MkColors.primary,
      targetScreen: DroneScreen(),
      comingSoon: true,
      benefits: [
        'Upigaji picha na ramani ya shamba',
        'Upuliziaji wa kisasa wa viatilifu',
        'Ufuatiliaji wa ukuaji wa mazao',
      ],
    ),
    const ServiceItem(
      icon: Icons.sensors_outlined,
      name: 'Vifaa vya IoT',
      description: 'Sensors za kufuatilia udongo na unyevu.',
      requiredPlan: 'Business',
      color: MkColors.primary,
      targetScreen: IoTScreen(),
      comingSoon: true,
      benefits: [
        'Vipimo vya unyevu wa udongo live',
        'Kiwango cha joto cha udongo shambani',
        'Taarifa za virutubisho vya NPK',
      ],
    ),
  ];

  final List<ServiceItem> _mawasilianoServices = [
    const ServiceItem(
      icon: Icons.message_outlined,
      name: 'SMS na USSD',
      description: 'Huduma za kilimo bila internet kupitia simu.',
      requiredPlan: 'Free',
      color: MkColors.primary,
      targetScreen: SmsScreen(),
      benefits: [
        'Ujumbe mfupi wa bei za soko',
        'Miongozo ya kilimo kupitia USSD',
        'Hali ya hewa bila bando',
      ],
    ),
    const ServiceItem(
      icon: Icons.phone_in_talk_outlined,
      name: 'Simu (IVR)',
      description: 'Msaada wa sauti shambani kupitia IVR.',
      requiredPlan: 'Free',
      color: MkColors.primary,
      targetScreen: IvrScreen(),
      benefits: [
        'Masomo ya sauti kwa Kiswahili',
        'Kuunganishwa na maafisa ugani',
        'Ushauri wa kupiga bure',
      ],
    ),
    const ServiceItem(
      icon: Icons.forum_outlined,
      name: 'Jukwaa la Jamii',
      description: 'Jadiliana na ubadilishane uzoefu na wakulima wengine.',
      requiredPlan: 'Free',
      color: MkColors.primary,
      targetScreen: ForumScreen(),
      benefits: [
        'Uliza maswali kwa jamii',
        'Uzoefu wa kilimo kutoka kwa wengine',
        'Soko la kubadilishana taarifa',
      ],
    ),
    const ServiceItem(
      icon: Icons.verified_user_outlined,
      name: 'Thibitisha KYC',
      description: 'Thibitisha wasifu wako ili kuaminika.',
      requiredPlan: 'Free',
      color: MkColors.warning,
      targetScreen: KycScreen(),
      benefits: [
        'Beji maalum ya uthibitisho',
        'Ruhusa ya kuuza soko la mkulima',
        'Uaminifu mkubwa kutoka kwa wanunuzi',
      ],
    ),
  ];

  Color _getBadgeColor(String plan) {
    switch (plan.toLowerCase()) {
      case 'free':
        return MkColors.primary;
      case 'pro':
        return MkColors.info;
      case 'business':
        return MkColors.warning;
      case 'enterprise':
        return MkColors.info;
      default:
        return MkColors.muted;
    }
  }

  void _handleServiceTap(ServiceItem service) {
    if (!mounted) return;

    // A service whose backend answers 503 by design opens an explanation, not
    // a screen that will fail. Meeting an error here teaches a farmer that the
    // app is unreliable, when in fact the feature simply is not built yet.
    if (service.comingSoon) {
      showModalBottomSheet<void>(
        context: context,
        builder: (sheetContext) => Padding(
          padding: EdgeInsets.only(
            left: 20,
            right: 20,
            top: 24,
            bottom: MediaQuery.of(sheetContext).padding.bottom + 24,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: MkColors.leafPale,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(
                      Icons.schedule_outlined,
                      color: MkColors.primary,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      service.name,
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w800,
                        color: MkColors.ink,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 14),
              Text(
                'Huduma hii bado haijaanza. ${service.description} '
                'Tutakujulisha itakapopatikana katika eneo lako.',
                style: const TextStyle(color: MkColors.muted, height: 1.5),
              ),
              const SizedBox(height: 22),
              FilledButton(
                onPressed: () => Navigator.of(sheetContext).pop(),
                child: const Text('Nimeelewa'),
              ),
            ],
          ),
        ),
      );

      return;
    }

    Navigator.of(
      context,
    ).push(MaterialPageRoute(builder: (_) => service.targetScreen));
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);

    return Scaffold(
      body: SingleChildScrollView(
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
                side: BorderSide(color: MkColors.border),
              ),
              child: ListTile(
                leading: const Icon(Icons.settings, color: MkColors.muted),
                title: const Text('Mipangilio ya Mfumo'),
                subtitle: const Text('Weka mapendeleo ya lugha na arifa'),
                trailing: const Icon(Icons.chevron_right),
                onTap: () => _handleServiceTap(
                  const ServiceItem(
                    icon: Icons.settings,
                    name: 'Mipangilio',
                    description: 'Weka mapendeleo ya lugha na arifa',
                    requiredPlan: 'Free',
                    color: MkColors.muted,
                    targetScreen: SettingsScreen(),
                    benefits: [],
                  ),
                ),
              ),
            ),
            const SizedBox(height: 40),
          ],
        ),
      ),
    );
  }

  Widget _buildHeroSection(AuthProvider auth) {
    final userName = auth.isAuthenticated
        ? auth.user!.name.split(' ').first
        : 'Mgeni';

    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: MkColors.surface,
        border: Border.all(color: MkColors.border),
        borderRadius: BorderRadius.circular(20),
      ),
      padding: const EdgeInsets.all(20),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Habari, $userName!',
                  style: const TextStyle(
                    color: MkColors.ink,
                    fontSize: 22,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  auth.isAuthenticated
                      ? 'Huduma zako za kilimo sehemu moja'
                      : 'Jiunge leo kupata huduma zote',
                  style: const TextStyle(color: MkColors.muted, fontSize: 13),
                ),
              ],
            ),
          ),
          if (!auth.isAuthenticated)
            ElevatedButton(
              onPressed: () => AuthProvider.requireAuth(context),
              style: ElevatedButton.styleFrom(
                backgroundColor: MkColors.primary,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 8,
                ),
              ),
              child: const Text(
                'Ingia',
                style: TextStyle(fontWeight: FontWeight.bold),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildCategoryBlock(String categoryTitle, List<ServiceItem> services) {
    final double screenWidth = MediaQuery.of(context).size.width;
    final bool isTablet = screenWidth >= 600;

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
              color: MkColors.primary,
            ),
          ),
        ),
        GridView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
            // Two columns on phones: three left ~100px per tile at 360px,
            // which clipped Swahili names and descriptions at 13px.
            crossAxisCount: isTablet ? 3 : 2,
            crossAxisSpacing: 12,
            mainAxisSpacing: 12,
            mainAxisExtent: 156,
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
      margin: EdgeInsets.zero,
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
                      padding: const EdgeInsets.symmetric(
                        horizontal: 8,
                        vertical: 3,
                      ),
                      decoration: BoxDecoration(
                        color: _getBadgeColor(
                          planBadge,
                        ).withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        planBadge,
                        style: TextStyle(
                          color: _getBadgeColor(planBadge),
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                ],
              ),
              const Spacer(),
              Text(
                service.name,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: MkText.label.copyWith(height: 1.25),
              ),
              const SizedBox(height: 4),
              Text(
                service.description,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  color: MkColors.muted,
                  fontSize: 13,
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
