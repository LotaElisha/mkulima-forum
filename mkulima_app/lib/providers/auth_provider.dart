import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:google_sign_in/google_sign_in.dart';
import 'package:sign_in_with_apple/sign_in_with_apple.dart';
import '../models/user.dart';
import '../services/api_service.dart';
import '../services/local_database.dart' as db;
import '../screens/login_modal.dart';

class AuthProvider extends ChangeNotifier {
  final ApiService _api;
  final db.LocalDatabase _db;
  final FlutterSecureStorage _secureStorage = const FlutterSecureStorage();

  User? _user;
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
  bool get isLoading => _isLoading;
  String? get error => _error;
  String? get devOtp => _devOtp;
  String get subscriptionPlan => _subscriptionPlan;
  bool get isAuthenticated => _user != null;
  bool get isFarmer => _user?.role == 'farmer';
  bool get isBuyer => _user?.role == 'buyer';
  bool get isAdmin => _user?.role == 'admin';

  /// Reload the signed-in account from the API. Used by pull-to-refresh on
  /// profile surfaces so role, KYC and contact changes appear immediately.
  Future<void> refreshUser() async {
    if (!isAuthenticated) return;
    try {
      final freshUser = await _api.getMe();
      _user = freshUser;
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
      _user = await _api.getMe();
      final token = await _secureStorage.read(key: 'auth_token');
      if (token != null) await _db.saveUser(_user!, token);
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (error) {
      _error = error.toString();
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
      _error = e.toString();
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

      _api.setToken(token);
      await _secureStorage.write(key: 'auth_token', value: token);
      await _db.saveUser(_user!, token);

      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      String msg = e.toString();
      if (msg.contains('422')) {
        msg = 'OTP imekwisha muda au si sahihi. Tafadhali tuma OTP mpya.';
      } else if (msg.contains('429')) {
        msg = 'Maombi mengi sana. Tafadhali subiri dakika chache.';
      } else if (msg.contains('404')) {
        msg = 'Mtumiaji hajapatikana. Jiunge kwanza.';
      }
      _error = msg;
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    _api.clearToken();
    await _secureStorage.delete(key: 'auth_token');
    await _db.clearUser();
    _user = null;
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
      final msg = e.toString();
      if (msg.contains('429')) {
        _error = 'Umeomba mara nyingi mfululizo. Subiri dakika chache.';
      } else if (msg.contains('422')) {
        // A validation failure means the address was malformed, which the
        // form should already have caught; nothing to disclose either way.
        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _error = 'Hakuna mtandao. Angalia muunganisho wako.';
      }
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

      _api.setToken(token);
      await _secureStorage.write(key: 'auth_token', value: token);
      await _db.saveUser(_user!, token);

      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      String msg = e.toString();
      if (msg.contains('401')) {
        msg = 'Email au password si sahihi.';
      } else if (msg.contains('404')) {
        msg = 'Akaunti haijapatikana. Jiunge kwanza.';
      }
      _error = msg;
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
    _startLoading();
    try {
      if (!_googleInitialized) {
        const serverClientId = String.fromEnvironment(
          'GOOGLE_SERVER_CLIENT_ID',
        );
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
      return _finishError('Google sign-in imeshindikana: $e');
    }
  }

  Future<bool> signInWithApple({String role = 'farmer'}) async {
    _startLoading();
    try {
      const appleServiceId = String.fromEnvironment('APPLE_SERVICE_ID');
      final isAndroid =
          !kIsWeb && defaultTargetPlatform == TargetPlatform.android;
      if (isAndroid && appleServiceId.isEmpty) {
        throw Exception('APPLE_SERVICE_ID is not configured.');
      }
      final credential = await SignInWithApple.getAppleIDCredential(
        scopes: const [
          AppleIDAuthorizationScopes.email,
          AppleIDAuthorizationScopes.fullName,
        ],
        webAuthenticationOptions: isAndroid
            ? WebAuthenticationOptions(
                clientId: appleServiceId,
                redirectUri: Uri.parse(
                  'https://mkulimaforum.com/api/auth/apple/callback',
                ),
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
      return _finishError('Apple sign-in imeshindikana: $e');
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
      _api.setToken(token);
      await _secureStorage.write(key: 'auth_token', value: token);
      await _db.saveUser(_user!, token);
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      return _finishError(e.toString());
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
