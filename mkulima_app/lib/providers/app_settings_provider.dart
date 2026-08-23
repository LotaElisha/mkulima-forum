import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

class AppSettingsProvider extends ChangeNotifier {
  bool _darkMode = false;
  bool _notificationsEnabled = true;
  bool _loaded = false;

  bool get darkMode => _darkMode;
  bool get notificationsEnabled => _notificationsEnabled;
  bool get loaded => _loaded;

  AppSettingsProvider() {
    _load();
  }

  Future<void> _load() async {
    final prefs = await SharedPreferences.getInstance();
    _darkMode = prefs.getBool('dark_mode') ?? false;
    _notificationsEnabled = prefs.getBool('notifications_enabled') ?? true;
    _loaded = true;
    notifyListeners();
  }

  Future<void> setDarkMode(bool value) async {
    _darkMode = value;
    notifyListeners();
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('dark_mode', value);
  }

  Future<void> setNotificationsEnabled(bool value) async {
    _notificationsEnabled = value;
    notifyListeners();
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('notifications_enabled', value);
  }
}
