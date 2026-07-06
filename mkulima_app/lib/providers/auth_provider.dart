import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/user.dart';
import '../services/api_service.dart';
import '../services/local_database.dart' as db;
import '../screens/login_modal.dart';

class AuthProvider extends ChangeNotifier {
  final ApiService _api;
  final db.LocalDatabase _db;

  User? _user;
  bool _isLoading = false;
  String? _error;
  String? _devOtp;
  String _subscriptionPlan = 'Free';

  AuthProvider({required ApiService api, required db.LocalDatabase db})
      : _api = api,
        _db = db {
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
    final token = await _db.getToken();
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
    await _db.clearUser();
    _user = null;
    _subscriptionPlan = 'Free';
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove('subscription_plan');
    } catch (_) {}
    notifyListeners();
  }

  Future<bool> loginWithEmail(String email, String password) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _api.post('/auth/login/email', data: {
        'email': email,
        'password': password,
      });

      final token = response.data['token'];
      final userData = response.data['user'];
      _user = User.fromJson(userData);

      _api.setToken(token);
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

  /// Check if user is authenticated, if not show login modal
  /// Returns true if authenticated (or login successful), false otherwise
  static Future<bool> requireAuth(BuildContext context, {String? action}) async {
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
