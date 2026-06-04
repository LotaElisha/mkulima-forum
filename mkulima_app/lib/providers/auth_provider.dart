import 'package:flutter/foundation.dart';
import '../models/user.dart';
import '../services/api_service.dart';
import '../services/local_database.dart';

class AuthProvider extends ChangeNotifier {
  final ApiService _api;
  final LocalDatabase _db;

  User? _user;
  bool _isLoading = false;
  String? _error;
  String? _devOtp;

  AuthProvider({required ApiService api, required LocalDatabase db})
      : _api = api,
        _db = db {
    _loadUser();
  }

  User? get user => _user;
  bool get isLoading => _isLoading;
  String? get error => _error;
  String? get devOtp => _devOtp;
  bool get isAuthenticated => _user != null;
  bool get isFarmer => _user?.role == 'farmer';
  bool get isBuyer => _user?.role == 'buyer';
  bool get isAdmin => _user?.role == 'admin';

  Future<void> _loadUser() async {
    final token = await _db.getToken();
    if (token != null) {
      _api.setToken(token);
      try {
        _user = await _db.getCurrentUser();
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
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    _api.clearToken();
    await _db.clearUser();
    _user = null;
    notifyListeners();
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }
}
