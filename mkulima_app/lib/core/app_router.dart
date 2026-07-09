import 'package:go_router/go_router.dart';

import '../providers/auth_provider.dart';

import '../screens/cart_screen.dart';
import '../screens/drone_screen.dart';
import '../screens/escrow_screen.dart';
import '../screens/forum_screen.dart';
import '../screens/home_screen.dart';
import '../screens/iot_screen.dart';
import '../screens/ivr_screen.dart';
import '../screens/kyc_screen.dart';
import '../screens/login_screen.dart';
import '../screens/marketplace_screen.dart';
import '../screens/mkulima_bot_screen.dart';
import '../screens/notifications_screen.dart';
import '../screens/onboarding_screen.dart';
import '../screens/orders_screen.dart';
import '../screens/profile_screen.dart';
import '../screens/register_screen.dart';
import '../screens/scanner_screen.dart';
import '../screens/seller_dashboard_screen.dart';
import '../screens/settings_screen.dart';
import '../screens/sms_screen.dart';
import '../screens/splash_screen.dart';
import '../screens/wallet_screen.dart';
import '../screens/weather_screen.dart';
import '../screens/yield_screen.dart';

/// Central route table (Phase 1 redesign — see REDESIGN.md).
///
/// All no-argument screens are registered here so deep links work.
/// Screens that require constructor arguments (product detail, payment,
/// upgrade, thread detail) remain Navigator.push flows from their parents —
/// go_router is interoperable with the imperative API.
///
/// Auth guard: unauthenticated users hitting a protected path are redirected
/// to /login. The router re-evaluates whenever [AuthProvider] notifies.
const Set<String> _protectedPaths = {
  '/cart',
  '/orders',
  '/wallet',
  '/escrow',
  '/kyc',
  '/seller',
  '/notifications',
  '/settings',
  '/profile',
};

GoRouter buildAppRouter(AuthProvider auth) {
  return GoRouter(
    initialLocation: '/splash',
    refreshListenable: auth,
    redirect: (context, state) {
      final path = state.matchedLocation;
      if (!auth.isAuthenticated && _protectedPaths.contains(path)) {
        return '/login?from=$path';
      }
      return null;
    },
    routes: [
      GoRoute(path: '/splash', builder: (context, state) => const SplashScreen()),
      GoRoute(
        path: '/onboarding',
        builder: (context, state) => const OnboardingScreen(),
      ),
      GoRoute(path: '/home', builder: (context, state) => const HomeScreen()),
      GoRoute(path: '/login', builder: (context, state) => const LoginScreen()),
      GoRoute(path: '/register', builder: (context, state) => const RegisterScreen()),

      // Core pillars
      GoRoute(path: '/soko', builder: (context, state) => const MarketplaceScreen()),
      GoRoute(path: '/forum', builder: (context, state) => const ForumScreen()),
      GoRoute(path: '/scanner', builder: (context, state) => const ScannerScreen()),
      GoRoute(path: '/weather', builder: (context, state) => const WeatherScreen()),

      // Services
      GoRoute(path: '/bot', builder: (context, state) => const MkulimaBotScreen()),

      GoRoute(path: '/drone', builder: (context, state) => const DroneScreen()),
      GoRoute(path: '/iot', builder: (context, state) => const IoTScreen()),
      GoRoute(path: '/ivr', builder: (context, state) => const IvrScreen()),
      GoRoute(path: '/sms', builder: (context, state) => const SmsScreen()),
      GoRoute(path: '/yield', builder: (context, state) => const YieldScreen()),

      // Protected (require login)
      GoRoute(path: '/cart', builder: (context, state) => const CartScreen()),
      GoRoute(path: '/orders', builder: (context, state) => const OrdersScreen()),
      GoRoute(path: '/wallet', builder: (context, state) => const WalletScreen()),
      GoRoute(path: '/escrow', builder: (context, state) => const EscrowScreen()),
      GoRoute(path: '/kyc', builder: (context, state) => const KycScreen()),
      GoRoute(path: '/seller', builder: (context, state) => const SellerDashboardScreen()),
      GoRoute(
        path: '/notifications',
        builder: (context, state) => const NotificationsScreen(),
      ),
      GoRoute(path: '/settings', builder: (context, state) => const SettingsScreen()),
      GoRoute(path: '/profile', builder: (context, state) => const ProfileScreen()),
    ],
  );
}
