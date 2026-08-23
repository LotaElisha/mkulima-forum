import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:google_sign_in/google_sign_in.dart';
import 'package:sign_in_with_apple/sign_in_with_apple.dart';
import '../core/social_auth_config.dart';
import '../models/seller_state.dart';
import '../models/user.dart';
import '../services/api_service.dart';
import '../services/local_database.dart' as db;
import '../screens/login_modal.dart';

/// Base URL of the MkulimaForum API.
///
/// Single definition so the API host and the Apple redirect URI cannot drift
/// apart. Override at build time:
///   flutter build apk --dart-define=API_URL=https://mkulimaforum.app/api
const String kApiBaseUrl = String.fromEnvironment(
  'API_URL',
  defaultValue: 'https://mkulimaforum.app/api',
);

class AuthProvider extends ChangeNotifier {
  final ApiService _api;
  final db.LocalDatabase _db;
  final FlutterSecureStorage _secureStorage = const FlutterSecureStorage();

  User? _user;

  SellerState _seller = SellerState.unknown;
  bool _isLoading = false;
  String? _error;
  String? _devOtp;
  String _subscriptionPlan = 'Free';
  bool _googleInitialized = false;

  AuthProvider({required ApiService api, required db.LocalDatabase db})
    : _api = api,
      _db = db {
    _api.onUnauthorized = logout;
    _loadUser();
    _loadSubscriptionPlan();
  }

  User? get user => _user;

  /// Selling state, as the server reports it.
  ///
  /// Never derived from [User.role] in the UI. The profile screen used to do
  /// exactly that - `role == 'farmer' || role == 'agrodealer'` - and offered
  /// every farmer a dashboard the API refuses.
  SellerState get seller => _seller;
  bool get isLoading => _isLoading;
  String? get error => _error;
  String? get devOtp => _devOtp;
  String get subscriptionPlan => _subscriptionPlan;
  bool get isAuthenticated => _user != null;
  bool get isFarmer => _user?.role == 'farmer';
  bool get canSell => _seller.canSell;
  bool get isBuyer => _user?.role == 'buyer';
  bool get isAdmin => _user?.role == 'admin';

  /// Reload the signed-in account from the API. Used by pull-to-refresh on
  /// profile surfaces so role, KYC and contact changes appear immediately.
  Future<void> refreshUser() async {
    if (!isAuthenticated) return;
    try {
      final fresh = await _api.getMeDetailed();
      final freshUser = fresh.user;
      _user = freshUser;
      _seller = fresh.seller;
      final token = await _secureStorage.read(key: 'auth_token');
      if (token != null) await _db.saveUser(freshUser, token);
      notifyListeners();
    } catch (_) {
      // Keep the cached profile available when refresh fails offline.
    }
  }

  Future<bool> updateProfile(Map<String, dynamic> values) async {
    _isLoading = true;
    _error = null;
    notifyListeners();
    try {
      await _api.put('/auth/profile', data: values);
      final refreshed = await _api.getMeDetailed();
      _user = refreshed.user;
      _seller = refreshed.seller;
      final token = await _secureStorage.read(key: 'auth_token');
      if (token != null) await _db.saveUser(_user!, token);
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (error) {
      _error = ApiService.formatError(error);
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> _loadSubscriptionPlan() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      _subscriptionPlan = prefs.getString('subscription_plan') ?? 'Free';
      notifyListeners();
    } catch (_) {}
  }

  Future<void> setSubscriptionPlan(String plan) async {
    try {
      _subscriptionPlan = plan;
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('subscription_plan', plan);
      notifyListeners();
    } catch (_) {}
  }

  Future<void> _loadUser() async {
    final token =
        await _secureStorage.read(key: 'auth_token') ?? await _db.getToken();
    if (token != null) {
      _api.setToken(token);
      try {
        final dbUser = await _db.getCurrentUser();
        if (dbUser != null) {
          _user = User(
            uuid: dbUser.uuid,
            name: dbUser.name,
            phone: dbUser.phone,
            email: dbUser.email,
            role: dbUser.role,
            preferredLanguage: dbUser.preferredLanguage,
          );
        }
        notifyListeners();
      } catch (e) {
        await logout();
      }
    }
  }

  Future<bool> requestOtp(String phone, String purpose) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _api.requestOtp(phone, purpose);
      _devOtp = response['dev_code'];
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      // A 503 here is the expected answer while auth.otp_enabled is off, and
      // the backend sends a translated sentence saying so. Showing the raw
      // DioException instead - which is what used to happen - made a
      // deliberate feature flag look like the app was broken.
      _error = ApiService.formatError(e);
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> verifyOtp({
    required String phone,
    required String code,
    required String purpose,
    String? name,
    String? countryCode,
    String? role,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _api.verifyOtp(
        phone: phone,
        code: code,
        purpose: purpose,
        name: name,
        countryCode: countryCode,
        role: role,
      );

      final token = response['token'];
      final userData = response['user'];
      _user = User.fromJson(userData);
      _seller = SellerState.fromAnywhere(response.data);

      _api.setToken(token);
      await _secureStorage.write(key: 'auth_token', value: token);
      await _db.saveUser(_user!, token);

      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      // Was matching on substrings of the exception's toString(), which meant
      // an OTP code that happened to contain "422" could pick the wrong
      // branch, and anything unmatched printed the whole DioException.
      final error = ApiService.asApiError(e);
      _error = switch (error.statusCode) {
        422 => 'OTP imekwisha muda au si sahihi. Tafadhali tuma OTP mpya.',
        429 => 'Maombi mengi sana. Tafadhali subiri dakika chache.',
        404 => 'Mtumiaji hajapatikana. Jiunge kwanza.',
        _ => error.message,
      };
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }


  /// A social sign-in failure the user can read.
  ///
  /// The provider SDKs put configuration diagnostics in their exception
  /// messages, so interpolating the exception - which both of these methods
  /// used to do - shows the user our client ids and setup problems.
  String _socialFailureMessage(String provider, Object error) {
    final text = error.toString().toLowerCase();
    if (text.contains('cancel')) {
      return 'Umeghairi kuingia kwa $provider.';
    }
    if (text.contains('network') || text.contains('socket')) {
      return 'Hakikisha una intaneti kisha ujaribu tena.';
    }
    if (kDebugMode) debugPrint('$provider sign-in failed: $error');
    return 'Kuingia kwa $provider hakukufanikiwa. Tafadhali jaribu njia '
        'nyingine au tumia barua pepe.';
  }

  Future<void> logout() async {
    _api.clearToken();
    await _secureStorage.delete(key: 'auth_token');
    await _db.clearUser();
    _user = null;
    _seller = SellerState.unknown;
    _subscriptionPlan = 'Free';
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove('subscription_plan');
    } catch (_) {}
    notifyListeners();
  }

  /// Ask the backend to email a password reset link.
  ///
  /// Deliberately returns true for any non-network outcome: the endpoint
  /// answers identically for registered and unregistered addresses so it
  /// cannot be used to discover which emails hold accounts, and the app must
  /// not undo that by showing a different message for each case.
  Future<bool> requestPasswordReset(String email) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      await _api.post('/auth/password/forgot', data: {'email': email});
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      final error = ApiService.asApiError(e);
      if (error.statusCode == 422) {
        // A validation failure means the address was malformed, which the
        // form should already have caught; nothing to disclose either way.
        // Reporting success here is what keeps registered and unregistered
        // addresses indistinguishable from the outside.
        _isLoading = false;
        notifyListeners();
        return true;
      }
      _error = error.statusCode == 429
          ? 'Umeomba mara nyingi mfululizo. Subiri dakika chache.'
          : error.message;
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  /// Re-send the email verification link for the signed-in account.
  Future<bool> resendEmailVerification() async {
    try {
      await _api.post('/auth/email/resend', data: const {});
      return true;
    } catch (_) {
      return false;
    }
  }

  Future<bool> loginWithEmail(String email, String password) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _api.post(
        '/auth/login/email',
        data: {'email': email, 'password': password},
      );

      final token = response.data['token'];
      final userData = response.data['user'];
      _user = User.fromJson(userData);
      _seller = SellerState.fromAnywhere(response.data);

      _api.setToken(token);
      await _secureStorage.write(key: 'auth_token', value: token);
      await _db.saveUser(_user!, token);

      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      final error = ApiService.asApiError(e);
      _error = switch (error.statusCode) {
        // Deliberately identical for "no such account" and "wrong password":
        // a different message for each turns the login form into a way to
        // discover which addresses are registered.
        401 || 404 => 'Barua pepe au nenosiri si sahihi.',
        403 => error.message,
        _ => error.message,
      };
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> registerWithEmail({
    required String name,
    required String email,
    required String password,
    required String role,
    String countryCode = 'tz',
  }) async => _authenticateRequest('/auth/register/email', {
    'name': name,
    'email': email,
    'password': password,
    'password_confirmation': password,
    'role': role,
    'country_code': countryCode,
  });

  Future<bool> signInWithGoogle({String role = 'farmer'}) async {
    // Checked before _startLoading so an unconfigured build fails instantly
    // and legibly instead of spinning and then throwing
    // `clientConfigurationError` at the user.
    if (!SocialAuthConfig.isGoogleConfigured) {
      _error = 'Kuingia kwa Google hakujawashwa kwenye toleo hili.';
      notifyListeners();
      return false;
    }

    _startLoading();
    try {
      if (!_googleInitialized) {
        const serverClientId = SocialAuthConfig.googleServerClientId;
        await GoogleSignIn.instance.initialize(
          clientId: kIsWeb && serverClientId.isNotEmpty ? serverClientId : null,
          serverClientId: !kIsWeb && serverClientId.isNotEmpty
              ? serverClientId
              : null,
        );
        _googleInitialized = true;
      }
      final account = await GoogleSignIn.instance.authenticate();
      final token = account.authentication.idToken;
      if (token == null) {
        throw Exception('Google did not return an identity token.');
      }
      return await _authenticateRequest('/auth/social', {
        'provider': 'google',
        'identity_token': token,
        'name': account.displayName,
        'role': role,
        'country_code': 'tz',
      }, loadingAlreadyStarted: true);
    } catch (e) {
      // Never interpolate the exception: GoogleSignInException prints its own
      // configuration diagnostics, which is how "serverClientId must be
      // provided on Android" reached a farmer's screen.
      return _finishError(_socialFailureMessage('Google', e));
    }
  }

  Future<bool> signInWithApple({String role = 'farmer'}) async {
    if (!SocialAuthConfig.isAppleConfigured) {
      _error = 'Kuingia kwa Apple hakujawashwa kwenye toleo hili.';
      notifyListeners();
      return false;
    }

    _startLoading();
    try {
      const appleServiceId = SocialAuthConfig.appleServiceId;
      final isAndroid =
          !kIsWeb && defaultTargetPlatform == TargetPlatform.android;
      final credential = await SignInWithApple.getAppleIDCredential(
        scopes: const [
          AppleIDAuthorizationScopes.email,
          AppleIDAuthorizationScopes.fullName,
        ],
        webAuthenticationOptions: isAndroid
            ? WebAuthenticationOptions(
                clientId: appleServiceId,
                // Must match APP_URL, or Apple refuses the redirect. Built
                // from the same --dart-define as the API base so the two
                // cannot drift apart the way they had.
                redirectUri: Uri.parse('$kApiBaseUrl/auth/apple/callback'),
              )
            : null,
      );
      if (credential.identityToken == null) {
        throw Exception('Apple did not return an identity token.');
      }
      final name = [
        credential.givenName,
        credential.familyName,
      ].whereType<String>().join(' ').trim();
      return await _authenticateRequest('/auth/social', {
        'provider': 'apple',
        'identity_token': credential.identityToken,
        if (name.isNotEmpty) 'name': name,
        'role': role,
        'country_code': 'tz',
      }, loadingAlreadyStarted: true);
    } catch (e) {
      return _finishError(_socialFailureMessage('Apple', e));
    }
  }

  Future<bool> _authenticateRequest(
    String path,
    Map<String, dynamic> data, {
    bool loadingAlreadyStarted = false,
  }) async {
    if (!loadingAlreadyStarted) _startLoading();
    try {
      final response = await _api.post(path, data: data);
      final token = response.data['token'] as String;
      _user = User.fromJson(response.data['user']);
      _seller = SellerState.fromAnywhere(response.data);
      _api.setToken(token);
      await _secureStorage.write(key: 'auth_token', value: token);
      await _db.saveUser(_user!, token);
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      return _finishError(ApiService.formatError(e));
    }
  }

  void _startLoading() {
    _isLoading = true;
    _error = null;
    notifyListeners();
  }

  bool _finishError(String message) {
    _error = message;
    _isLoading = false;
    notifyListeners();
    return false;
  }

  /// Check if user is authenticated, if not show login modal
  /// Returns true if authenticated (or login successful), false otherwise
  static Future<bool> requireAuth(
    BuildContext context, {
    String? action,
  }) async {
    final auth = Provider.of<AuthProvider>(context, listen: false);
    if (auth.isAuthenticated) return true;

    // Show login modal
    final result = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => LoginModal(action: action),
    );

    return result ?? false;
  }
}
