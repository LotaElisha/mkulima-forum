import 'dart:convert';
import 'dart:io';
import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import '../models/user.dart';
import '../models/product.dart';
import '../models/order.dart';

class ApiService {
  final Dio _dio;
  String? _token;

  ApiService({required String baseUrl})
      : _dio = Dio(BaseOptions(
          baseUrl: baseUrl,
          connectTimeout: const Duration(seconds: 30),
          receiveTimeout: const Duration(seconds: 30),
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
        )) {
    _dio.interceptors.add(LogInterceptor(
      requestBody: kDebugMode,
      responseBody: kDebugMode,
    ));
  }

  void setToken(String token) {
    _token = token;
    _dio.options.headers['Authorization'] = 'Bearer $token';
  }

  void clearToken() {
    _token = null;
    _dio.options.headers.remove('Authorization');
  }

  // Auth APIs
  Future<Map<String, dynamic>> requestOtp(String phone, String purpose) async {
    final response = await _dio.post('/auth/otp/request', data: {
      'phone': phone,
      'purpose': purpose,
    });
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
    final response = await _dio.post('/auth/otp/verify', data: {
      'phone': phone,
      'code': code,
      'purpose': purpose,
      if (name != null) 'name': name,
      if (countryCode != null) 'country_code': countryCode,
      if (role != null) 'role': role,
    });
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
    final response = await _dio.get('/marketplace/products', queryParameters: {
      if (categoryId != null) 'category_id': categoryId,
      if (search != null) 'search': search,
      'page': page,
    });
    return (response.data['data'] as List)
        .map((e) => Product.fromJson(e))
        .toList();
  }

  Future<Product> getProduct(String id) async {
    final response = await _dio.get('/marketplace/products/$id');
    return Product.fromJson(response.data['data']);
  }

  Future<Product> createProduct(Map<String, dynamic> data) async {
    final response = await _dio.post('/marketplace/products', data: data);
    return Product.fromJson(response.data['data']);
  }

  // Order APIs
  Future<Order> createOrder(Map<String, dynamic> data) async {
    final response = await _dio.post('/marketplace/orders', data: data);
    return Order.fromJson(response.data['data']);
  }

  Future<List<Order>> getOrders() async {
    final response = await _dio.get('/marketplace/orders');
    return (response.data['data'] as List)
        .map((e) => Order.fromJson(e))
        .toList();
  }

  // Forum APIs
  Future<List<dynamic>> getForumCategories() async {
    final response = await _dio.get('/forum/categories');
    return response.data['data'];
  }

  Future<List<dynamic>> getThreads(String categoryId) async {
    final response = await _dio.get('/forum/categories/$categoryId/threads');
    return response.data['data'];
  }

  Future<Map<String, dynamic>> createThread(Map<String, dynamic> data) async {
    final response = await _dio.post('/forum/threads', data: data);
    return response.data;
  }

  // Disease Scanner APIs
  Future<Map<String, dynamic>> scanDisease(File image) async {
    final formData = FormData.fromMap({
      'image': await MultipartFile.fromFile(image.path),
    });
    final response = await _dio.post('/scanner/scan', data: formData);
    return response.data;
  }

  Future<List<dynamic>> getDiseaseHistory() async {
    final response = await _dio.get('/scanner/history');
    return response.data['data'];
  }

  // AI Agronomist APIs
  Future<Map<String, dynamic>> askAgronomist(String query) async {
    final response = await _dio.post('/agronomist/ask', data: {
      'query': query,
    });
    return response.data;
  }

  Future<List<dynamic>> getKbDocuments() async {
    final response = await _dio.get('/agronomist/kb');
    return response.data['data'];
  }
}
