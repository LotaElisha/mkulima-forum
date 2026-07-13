import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import '../models/user.dart';
import '../models/product.dart';
import '../models/order.dart';
import '../providers/cache_provider.dart';

class ApiService {
  final Dio _dio;

  ApiService({required String baseUrl})
    : _dio = Dio(
        BaseOptions(
          baseUrl: baseUrl,
          connectTimeout: const Duration(seconds: 30),
          receiveTimeout: const Duration(seconds: 30),
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
        ),
      ) {
    _dio.interceptors.add(
      LogInterceptor(requestBody: kDebugMode, responseBody: kDebugMode),
    );
  }

  void setToken(String token) {
    _dio.options.headers['Authorization'] = 'Bearer $token';
  }

  void clearToken() {
    _dio.options.headers.remove('Authorization');
  }

  // Auth APIs
  Future<Map<String, dynamic>> requestOtp(String phone, String purpose) async {
    final response = await _dio.post(
      '/auth/otp/request',
      data: {'phone': phone, 'purpose': purpose},
    );
    return response.data;
  }

  Future<Map<String, dynamic>> verifyOtp({
    required String phone,
    required String code,
    required String purpose,
    String? name,
    String? countryCode,
    String? role,
  }) async {
    final response = await _dio.post(
      '/auth/otp/verify',
      data: {
        'phone': phone,
        'code': code,
        'purpose': purpose,
        'name': ?name,
        'country_code': ?countryCode,
        'role': ?role,
      },
    );
    return response.data;
  }

  Future<User> getMe() async {
    final response = await _dio.get('/auth/me');
    return User.fromJson(response.data['user']);
  }

  // Marketplace APIs
  Future<List<Product>> getProducts({
    String? categoryId,
    String? search,
    int page = 1,
  }) async {
    try {
      final response = await _dio.get(
        '/marketplace/products',
        queryParameters: {
          'category_id': ?categoryId,
          'search': ?search,
          'page': page,
        },
      );
      final products = (response.data['products'] as List? ?? [])
          .map((e) => Product.fromJson(e))
          .toList();
      // Cache for offline use
      await CacheProvider.cacheProducts(response.data['products'] ?? []);
      return products;
    } catch (e) {
      // Try to return cached data if offline
      final cached = await CacheProvider.getCachedProducts();
      if (cached != null) {
        return cached.map((e) => Product.fromJson(e)).toList();
      }
      rethrow;
    }
  }

  Future<Product> getProduct(String id) async {
    final response = await _dio.get('/marketplace/products/$id');
    return Product.fromJson(response.data['product'] ?? response.data['data']);
  }

  Future<Product> createProduct(Map<String, dynamic> data) async {
    final response = await _dio.post('/marketplace/products', data: data);
    return Product.fromJson(response.data['product'] ?? response.data['data']);
  }

  // Order APIs
  Future<Order> createOrder(Map<String, dynamic> data) async {
    final response = await _dio.post('/marketplace/orders', data: data);
    return Order.fromJson(response.data['order'] ?? response.data['data']);
  }

  Future<List<Order>> getOrders() async {
    final response = await _dio.get('/marketplace/orders');
    return (response.data['orders'] ?? response.data['data'] ?? [])
        .map((e) => Order.fromJson(e))
        .toList();
  }

  // Forum APIs
  Future<List<dynamic>> getForumCategories() async {
    try {
      final response = await _dio.get('/forum/categories');
      await CacheProvider.cacheForumCategories(
        response.data['categories'] ?? response.data['data'] ?? [],
      );
      return response.data['categories'] ?? response.data['data'] ?? [];
    } catch (e) {
      final cached = await CacheProvider.getCachedForumCategories();
      if (cached != null) return cached;
      rethrow;
    }
  }

  Future<List<dynamic>> getThreads(String categoryId) async {
    final response = await _dio.get(
      '/forum/threads',
      queryParameters: {'category_id': categoryId},
    );
    return response.data['threads'] ?? response.data['data'] ?? [];
  }

  Future<Map<String, dynamic>> createThread(Map<String, dynamic> data) async {
    final response = await _dio.post('/forum/threads', data: data);
    return response.data['thread'] ?? response.data;
  }

  Future<Map<String, dynamic>> getThread(String threadId) async {
    final response = await _dio.get('/forum/threads/$threadId');
    return response.data['thread'] ?? response.data['data'] ?? response.data;
  }

  Future<void> createReply(String threadId, String body) async {
    await _dio.post('/forum/threads/$threadId/replies', data: {'body': body});
  }

  // Disease Scanner APIs
  Future<Map<String, dynamic>> scanDisease(
    Uint8List imageBytes,
    String filename,
  ) async {
    final formData = FormData.fromMap({
      'image': MultipartFile.fromBytes(imageBytes, filename: filename),
    });
    final response = await _dio.post('/scanner/scan', data: formData);
    return response.data['scan'] ?? response.data['data'] ?? response.data;
  }

  Future<List<dynamic>> getDiseaseHistory() async {
    final response = await _dio.get('/scanner/history');
    return response.data['scans'] ?? response.data['data'] ?? [];
  }

  // AI Agronomist APIs
  Future<Map<String, dynamic>> askAgronomist(String query) async {
    final response = await _dio.post(
      '/agronomist/ask',
      data: {'question': query},
    );
    return response.data['answer'] ?? response.data;
  }

  Future<List<dynamic>> getKbDocuments() async {
    final response = await _dio.get('/agronomist/kb/search');
    return response.data['documents'] ?? response.data['data'] ?? [];
  }

  // Mkulima Bot Chat APIs
  Future<Map<String, dynamic>> botChat({
    required String message,
    String? conversationUuid,
  }) async {
    final response = await _dio.post(
      '/bot/chat',
      data: {
        'message': message,
        'conversation_uuid': conversationUuid,
      },
    );
    return response.data;
  }

  // Notifications APIs
  Future<Map<String, dynamic>> getNotifications() async {
    final response = await _dio.get('/notifications');
    return response.data['notifications'] ?? response.data;
  }

  Future<void> markNotificationRead(String id) async {
    await _dio.post('/notifications/$id/read');
  }

  Future<void> markAllNotificationsRead() async {
    await _dio.post('/notifications/read-all');
  }

  // Seller APIs
  Future<Map<String, dynamic>> getSellerDashboard() async {
    final response = await _dio.get('/seller/dashboard');
    return response.data['stats'] ?? response.data;
  }

  Future<List<dynamic>> getSellerProducts() async {
    final response = await _dio.get('/seller/products');
    return response.data['products'] ?? [];
  }

  Future<List<dynamic>> getSellerOrders() async {
    final response = await _dio.get('/seller/orders');
    return response.data['orders'] ?? [];
  }

  // KYC APIs
  Future<Map<String, dynamic>> getKycStatus() async {
    final response = await _dio.get('/kyc/status');
    return response.data['kyc'] ?? response.data;
  }

  Future<void> submitKyc(Map<String, dynamic> data) async {
    await _dio.post('/kyc/submit', data: data);
  }

  // Wallet APIs
  Future<Map<String, dynamic>> getWalletBalance() async {
    final response = await _dio.get('/wallet/balance');
    return response.data['wallet'] ?? response.data;
  }

  Future<List<dynamic>> getWalletTransactions() async {
    final response = await _dio.get('/wallet/transactions');
    return response.data['transactions'] ?? response.data['data'] ?? [];
  }

  Future<void> deposit(double amount, String phone, String provider) async {
    await _dio.post(
      '/wallet/deposit',
      data: {'amount': amount, 'phone': phone, 'provider': provider},
    );
  }

  Future<void> transfer(
    String phone,
    double amount, {
    String? description,
  }) async {
    await _dio.post(
      '/wallet/transfer',
      data: {
        'recipient_phone': phone,
        'amount': amount,
        'description': description,
      },
    );
  }

  // Weather APIs — full report: current + 5-day forecast + farming advisory.
  // Data is real (OpenWeather); `available: false` means no data, and
  // `is_stale: true` flags a cached last-known reading.
  Future<Map<String, dynamic>> getWeather({String? location}) async {
    final response = await _dio.get(
      '/weather/report',
      queryParameters: location != null ? {'location': location} : null,
    );
    return response.data;
  }

  // Generic HTTP methods
  Future<Response> get(
    String path, {
    Map<String, dynamic>? queryParameters,
  }) async {
    return await _dio.get(path, queryParameters: queryParameters);
  }

  Future<Response> post(String path, {dynamic data}) async {
    return await _dio.post(path, data: data);
  }

  /// Multipart upload from bytes (works on mobile and web).
  Future<Response> postMultipart(
    String path, {
    required String fileField,
    required Uint8List fileBytes,
    required String filename,
    Map<String, dynamic>? fields,
  }) async {
    final formData = FormData.fromMap({
      ...?fields,
      fileField: MultipartFile.fromBytes(fileBytes, filename: filename),
    });
    return await _dio.post(path, data: formData);
  }

  Future<Response> put(String path, {dynamic data}) async {
    return await _dio.put(path, data: data);
  }

  Future<Response> delete(String path) async {
    return await _dio.delete(path);
  }

  // SMS APIs
  Future<void> sendSms(String phone, String message) async {
    await _dio.post('/sms/send', data: {'phone': phone, 'message': message});
  }
}
