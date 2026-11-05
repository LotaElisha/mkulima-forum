import 'package:flutter/material.dart';
import '../screens/agronomists_screen.dart';
import '../screens/drone_screen.dart';
import '../screens/equipment_screen.dart';
import '../screens/escrow_screen.dart';
import '../screens/farm_management_screen.dart';
import '../screens/forum_screen.dart';
import '../screens/iot_screen.dart';
import '../screens/ivr_screen.dart';
import '../screens/kagua_dawa_screen.dart';
import '../screens/kyc_screen.dart';
import '../screens/logistics_screen.dart';
import '../screens/market_prices_screen.dart';
import '../screens/marketplace_screen.dart';
import '../screens/mkulima_bot_screen.dart';
import '../screens/orders_screen.dart';
import '../screens/scanner_screen.dart';
import '../screens/sms_screen.dart';
import '../screens/trace/trace_home_screen.dart';
import '../screens/wallet_screen.dart';
import '../screens/weather_screen.dart';
import '../screens/yield_screen.dart';

/// One Mkulima Forum service.
///
/// [notLiveReason] is set when the backend is not switched on (no operator
/// network, no payment or SMS credentials, or not built yet). Such services
/// stay listed so farmers know they are coming, but open an explanation
/// rather than a screen that would fail.
class MkService {
  final String key;
  final String label;
  final String description;
  final IconData icon;
  final String group;
  final Widget Function()? screen;
  final String? notLiveReason;

  const MkService({
    required this.key,
    required this.label,
    required this.description,
    required this.icon,
    required this.group,
    this.screen,
    this.notLiveReason,
  });

  bool get isLive => notLiveReason == null && screen != null;
}

/// Every service in the app. The Home grid shows [homeKeys]; "Huduma Zote"
/// shows all of them by [groups]. Add a service here and it appears in both.
class MkServices {
  MkServices._();

  static const groups = [
    'Utambuzi na ushauri',
    'Pembejeo na masoko',
    'Huduma za shamba',
    'Jamii na mawasiliano',
    'Malipo na akaunti',
  ];

  /// The eight Home shortcuts, in order (4 per row, 2 rows).
  static const homeKeys = [
    'agronomists',
    'equipment',
    'pembejeo',
    'logistics',
    'trace',
    'prices',
    'produce_market',
    'farm',
  ];

  static final List<MkService> all = [
    // ── Utambuzi na ushauri ─────────────────────────────────────────
    MkService(
      key: 'scanner',
      label: 'Kagua mimea',
      description: 'Piga picha ya jani ugundue ugonjwa na upate ushauri.',
      icon: Icons.document_scanner_outlined,
      group: groups[0],
      screen: () => const ScannerPage(),
    ),
    MkService(
      key: 'agronomists',
      label: 'Wataalamu',
      description: 'Wataalamu wa kilimo waliothibitishwa karibu nawe.',
      icon: Icons.person_search_outlined,
      group: groups[0],
      screen: () => const AgronomistsScreen(),
    ),
    MkService(
      key: 'bot',
      label: 'Mkulima AI',
      description: 'Uliza maswali ya kilimo kwa Kiswahili.',
      icon: Icons.chat_bubble_outline,
      group: groups[0],
      screen: () => const MkulimaBotScreen(),
    ),
    MkService(
      key: 'verify_inputs',
      label: 'Kagua dawa',
      description: 'Thibitisha pembejeo kabla ya kununua; gundua bidhaa feki.',
      icon: Icons.verified_user_outlined,
      group: groups[0],
      screen: () => const KaguaDawaScreen(),
    ),
    MkService(
      key: 'weather',
      label: 'Hali ya hewa',
      description: 'Utabiri wa hali ya hewa kwa eneo lako.',
      icon: Icons.wb_sunny_outlined,
      group: groups[0],
      screen: () => const WeatherScreen(),
    ),

    // ── Pembejeo na masoko ──────────────────────────────────────────
    MkService(
      key: 'pembejeo',
      label: 'Pembejeo',
      description: 'Mbegu, mbolea na viuatilifu kutoka kwa wauzaji wa pembejeo.',
      icon: Icons.storefront_outlined,
      group: groups[1],
      screen: () => const MarketplaceScreen(standalone: true),
    ),
    MkService(
      key: 'prices',
      label: 'Bei za mazao',
      description: 'Bei za mazao zilizorekodiwa kwenye masoko makuu.',
      icon: Icons.show_chart,
      group: groups[1],
      screen: () => const MarketPricesScreen(),
    ),
    MkService(
      key: 'produce_market',
      label: 'Soko la mazao',
      description: 'Kuuza na kununua mazao (mahindi, mpunga, maharage...).',
      icon: Icons.local_mall_outlined,
      group: groups[1],
      notLiveReason:
          'Soko la mazao bado halijajengwa. Kwa sasa unaweza kuona bei za mazao '
          'kwenye "Bei za mazao". Pembejeo ni soko tofauti la wauzaji wa pembejeo.',
    ),
    MkService(
      key: 'orders',
      label: 'Maagizo yangu',
      description: 'Fuatilia maagizo yako ya pembejeo.',
      icon: Icons.receipt_long_outlined,
      group: groups[1],
      screen: () => const OrdersScreen(),
    ),

    // ── Huduma za shamba ────────────────────────────────────────────
    MkService(
      key: 'farm',
      label: 'Huduma za shamba',
      description: 'Rekodi mashamba yako, mazao na shughuli za msimu.',
      icon: Icons.grass_outlined,
      group: groups[2],
      screen: () => const FarmManagementScreen(),
    ),
    MkService(
      key: 'equipment',
      label: 'Kodi zana',
      description: 'Kodi trekta, pampu na zana nyingine karibu nawe.',
      icon: Icons.agriculture_outlined,
      group: groups[2],
      screen: () => const EquipmentScreen(),
    ),
    MkService(
      key: 'logistics',
      label: 'Usafirishaji',
      description: 'Omba usafiri wa mazao, pembejeo au zana; wasafirishaji wanatuma bei.',
      icon: Icons.local_shipping_outlined,
      group: groups[2],
      screen: () => const LogisticsScreen(),
    ),
    MkService(
      key: 'trace',
      label: 'Mkulima Trace',
      description: 'Shamba hadi Soko: fuatilia mzigo wa mazao kwa QR kutoka shambani hadi kwa mnunuzi.',
      icon: Icons.qr_code_2,
      group: groups[2],
      screen: () => const TraceHomeScreen(),
    ),
    MkService(
      key: 'yield',
      label: 'Kadiria mavuno',
      description: 'Kadirio la mavuno ya msimu.',
      icon: Icons.insights_outlined,
      group: groups[2],
      screen: () => const YieldScreen(),
    ),
    MkService(
      key: 'iot',
      label: 'Teknolojia / IoT',
      description: 'Sensa za udongo na unyevu shambani.',
      icon: Icons.sensors_outlined,
      group: groups[2],
      screen: () => const IoTScreen(),
      notLiveReason: 'Hakuna mtandao wa sensa uliounganishwa bado. Huduma hii itawashwa vifaa vikipatikana.',
    ),
    MkService(
      key: 'drone',
      label: 'Drone za shamba',
      description: 'Ramani na unyunyiziaji kwa drone.',
      icon: Icons.flight_outlined,
      group: groups[2],
      screen: () => const DroneScreen(),
      notLiveReason: 'Hakuna waendeshaji wa drone waliosajiliwa bado.',
    ),

    // ── Jamii na mawasiliano ────────────────────────────────────────
    MkService(
      key: 'forum',
      label: 'Jukwaa',
      description: 'Jadiliana na wakulima wengine.',
      icon: Icons.forum_outlined,
      group: groups[3],
      screen: () => const ForumScreen(),
    ),
    MkService(
      key: 'sms',
      label: 'SMS na USSD',
      description: 'Huduma za kilimo bila intaneti kupitia simu.',
      icon: Icons.sms_outlined,
      group: groups[3],
      screen: () => const SmsScreen(),
      notLiveReason: 'SMS na USSD bado havijawashwa: mtoa huduma wa SMS hajaunganishwa.',
    ),
    MkService(
      key: 'ivr',
      label: 'Simu (IVR)',
      description: 'Msaada wa sauti kupitia simu.',
      icon: Icons.phone_in_talk_outlined,
      group: groups[3],
      screen: () => const IvrScreen(),
      notLiveReason: 'Huduma ya sauti bado haijawashwa.',
    ),

    // ── Malipo na akaunti ───────────────────────────────────────────
    MkService(
      key: 'wallet',
      label: 'Mkulima Pay',
      description: 'Pochi ya malipo ya kilimo.',
      icon: Icons.account_balance_wallet_outlined,
      group: groups[4],
      screen: () => const WalletScreen(),
      notLiveReason: 'Malipo kwenye app bado hayajawashwa. Lipana na muuzaji moja kwa moja kwa sasa.',
    ),
    MkService(
      key: 'escrow',
      label: 'Mkulima Escrow',
      description: 'Linda malipo hadi bidhaa ifike.',
      icon: Icons.shield_outlined,
      group: groups[4],
      screen: () => const EscrowScreen(),
      notLiveReason: 'Escrow inategemea malipo kwenye app, ambayo bado hayajawashwa.',
    ),
    MkService(
      key: 'kyc',
      label: 'Thibitisha utambulisho',
      description: 'Thibitisha wasifu wako ili uaminike.',
      icon: Icons.badge_outlined,
      group: groups[4],
      screen: () => const KycScreen(),
    ),
  ];

  static MkService byKey(String key) => all.firstWhere((s) => s.key == key);

  /// Opens a service, or explains why it is not available yet.
  static void open(BuildContext context, MkService service) {
    if (!service.isLive) {
      showModalBottomSheet<void>(
        context: context,
        showDragHandle: true,
        builder: (sheet) => Padding(
          padding: EdgeInsets.fromLTRB(20, 0, 20, MediaQuery.paddingOf(sheet).bottom + 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Row(
                children: [
                  Icon(service.icon, color: Theme.of(sheet).colorScheme.primary),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(service.label, style: Theme.of(sheet).textTheme.titleLarge),
                  ),
                ],
              ),
              const SizedBox(height: 4),
              const Text('Bado haijawashwa', style: TextStyle(fontWeight: FontWeight.w700)),
              const SizedBox(height: 12),
              Text(service.notLiveReason ?? 'Huduma hii bado inaandaliwa.'),
              const SizedBox(height: 20),
              FilledButton(
                onPressed: () => Navigator.pop(sheet),
                style: FilledButton.styleFrom(minimumSize: const Size.fromHeight(48)),
                child: const Text('Sawa'),
              ),
            ],
          ),
        ),
      );
      return;
    }
    Navigator.of(context).push(MaterialPageRoute(builder: (_) => service.screen!()));
  }
}
