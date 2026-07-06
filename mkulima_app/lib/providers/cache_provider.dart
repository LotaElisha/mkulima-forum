import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';

class CacheProvider {
  static const String _productsKey = 'cached_products';
  static const String _categoriesKey = 'cached_categories';
  static const String _forumCategoriesKey = 'cached_forum_categories';
  static const String _threadsKey = 'cached_threads';
  static const String _cacheTimeKey = 'cache_timestamp';

  static Future<void> cacheProducts(List<dynamic> products) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_productsKey, jsonEncode(products));
    await prefs.setInt(_cacheTimeKey, DateTime.now().millisecondsSinceEpoch);
  }

  static Future<List<dynamic>?> getCachedProducts() async {
    final prefs = await SharedPreferences.getInstance();
    final data = prefs.getString(_productsKey);
    if (data == null) return null;
    return jsonDecode(data);
  }

  static Future<void> cacheCategories(List<dynamic> categories) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_categoriesKey, jsonEncode(categories));
  }

  static Future<List<dynamic>?> getCachedCategories() async {
    final prefs = await SharedPreferences.getInstance();
    final data = prefs.getString(_categoriesKey);
    if (data == null) return null;
    return jsonDecode(data);
  }

  static Future<void> cacheForumCategories(List<dynamic> categories) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_forumCategoriesKey, jsonEncode(categories));
  }

  static Future<List<dynamic>?> getCachedForumCategories() async {
    final prefs = await SharedPreferences.getInstance();
    final data = prefs.getString(_forumCategoriesKey);
    if (data == null) return null;
    return jsonDecode(data);
  }

  static Future<bool> isCacheValid({int maxAgeMinutes = 30}) async {
    final prefs = await SharedPreferences.getInstance();
    final timestamp = prefs.getInt(_cacheTimeKey);
    if (timestamp == null) return false;
    final age = DateTime.now().millisecondsSinceEpoch - timestamp;
    return age < (maxAgeMinutes * 60 * 1000);
  }

  static Future<void> clearCache() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_productsKey);
    await prefs.remove(_categoriesKey);
    await prefs.remove(_forumCategoriesKey);
    await prefs.remove(_threadsKey);
    await prefs.remove(_cacheTimeKey);
  }
}
