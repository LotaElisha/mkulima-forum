import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import '../core/api_error.dart';
import '../models/app_notification.dart';
import '../models/user.dart';
import '../models/product.dart';
import '../models/seller_state.dart';
import '../providers/cache_provider.dart';

class ApiService {
  final Dio _dio;
  VoidCallback? onUnauthorized;

  ApiService({required String baseUrl, this.onUnauthorized})
    : _dio = Dio(
        BaseOptions(
          baseUrl: baseUrl,
          connectTimeout: const Duration(seconds: 30),
          receiveTimeout: const Duration(seconds: 30),
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'User-Agent': 'MkulimaApp/1.0 (Mobile; Flutter)',
          },
        ),
      ) {
    _dio.interceptors.add(
      LogInterceptor(requestBody: kDebugMode, responseBody: kDebugMode),
    );
    _dio.interceptors.add(
      InterceptorsWrapper(
        onError: (DioException err, ErrorInterceptorHandler handler) {
          if (err.response?.statusCode == 401) {
            clearToken();
            onUnauthorized?.call();
          }
          return handler.next(err);
        },
      ),
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
        if (name != null) 'name': name,
        if (countryCode != null) 'country_code': countryCode,
        if (role != null) 'role': role,
      },
    );
    return response.data;
  }

  // ── Identity linking ──────────────────────────────────────────────
  //
  // Attaching a phone number to an account that already exists, rather than
  // creating a second account. Backed by /api/auth/phone/link/* — see
  // docs/CONFIGURATION.md and MKULIMA_FORUM_AUDIT.md section 3.

  /// Every sign-in identity currently on this account.
  Future<Map<String, dynamic>> getIdentities() async {
    final response = await _dio.get('/auth/identities');
    return Map<String, dynamic>.from(response.data['identities'] as Map);
  }

  /// Ask the backend to text a code to a number the user wants to attach.
  ///
  /// Throws a DioException with status 422 when the number already belongs to
  /// another account — the backend refuses before spending an SMS.
  Future<Map<String, dynamic>> requestPhoneLink(String phone) async {
    final response = await _dio.post(
      '/auth/phone/link/request',
      data: {'phone': phone},
    );
    return Map<String, dynamic>.from(response.data as Map);
  }

  /// Verify the code and attach the number to the signed-in account.
  Future<Map<String, dynamic>> confirmPhoneLink({
    required String phone,
    required String code,
  }) async {
    final response = await _dio.post(
      '/auth/phone/link/confirm',
      data: {'phone': phone, 'code': code},
    );
    return Map<String, dynamic>.from(response.data as Map);
  }

  /// Detach the phone number. Requires the account password, and the backend
  /// refuses when the phone is the only way in.
  Future<Map<String, dynamic>> unlinkPhone(String currentPassword) async {
    final response = await _dio.delete(
      '/auth/phone/link',
      data: {'current_password': currentPassword},
    );
    return Map<String, dynamic>.from(response.data as Map);
  }

  /// Current email verification state, for the "confirm your email" nudge.
  Future<Map<String, dynamic>> getEmailStatus() async {
    final response = await _dio.get('/auth/email/status');
    return Map<String, dynamic>.from(response.data as Map);
  }

  /// Re-send the email verification link.
  Future<void> resendEmailVerification() async {
    await _dio.post('/auth/email/resend');
  }

  Future<User> getMe() async {
    final response = await _dio.get('/auth/me');
    return User.fromJson(response.data['user']);
  }

  /// The account plus its selling state, from one request.
  ///
  /// `/auth/me` returns both. Fetching them separately would let the two drift
  /// apart between calls, which is the class of bug that put a Seller
  /// Dashboard button in front of farmers in the first place.
  Future<({User user, SellerState seller})> getMeDetailed() async {
    final response = await _dio.get('/auth/me');
    return (
      user: User.fromJson(response.data['user']),
      seller: SellerState.fromAnywhere(response.data),
    );
  }

  // ── Selling ───────────────────────────────────────────────────────
  //
  // /seller/status is open to every authenticated account by design: it is
  // what the app asks before deciding whether to draw the business section.

  Future<SellerState> getSellerStatus() async {
    final response = await _dio.get('/seller/status');
    return SellerState.fromAnywhere(response.data);
  }

  Future<SellerState> submitSellerApplication({
    required String businessName,
    required String businessType,
    required String region,
    String? district,
    required String contactPhone,
    String? description,
  }) async {
    final response = await _dio.post(
      '/seller/application',
      data: {
        'business_name': businessName,
        'business_type': businessType,
        'region': region,
        if (district != null && district.isNotEmpty) 'district': district,
        'contact_phone': contactPhone,
        if (description != null && description.isNotEmpty)
          'description': description,
      },
    );
    return SellerState.fromAnywhere(response.data);
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
          if (categoryId != null) 'category_id': categoryId,
          if (search != null) 'search': search,
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
  Future<Map<String, dynamic>> createOrder(Map<String, dynamic> data) async {
    final response = await _dio.post('/marketplace/orders', data: data);
    return Map<String, dynamic>.from(
      response.data['order'] ?? response.data['data'],
    );
  }

  Future<Map<String, dynamic>> initiatePayment({
    required int orderId,
    required String paymentMethod,
    required String phone,
  }) async {
    final response = await _dio.post(
      '/payments/initiate',
      data: {
        'order_id': orderId,
        'payment_method': paymentMethod,
        'phone': phone,
      },
    );
    return Map<String, dynamic>.from(response.data);
  }

  Future<List<dynamic>> getOrders() async {
    final response = await _dio.get('/marketplace/orders');
    return List<dynamic>.from(
      response.data['orders'] ?? response.data['data'] ?? const [],
    );
  }

  // Forum APIs
  Future<List<dynamic>> getForumCategories() async {
    try {
      final response = await _dio.get('/forum/categories');
      await CacheProvider.cacheForumCategories(
        response.data['categories'] ?? response.data['data'] ?? [],
      );
      return _asList(response.data, const ['categories']);
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
    return _asList(response.data, const ['threads']);
  }

  /// Start a forum thread.
  ///
  /// Named parameters rather than a loose map on purpose. The caller was
  /// passing `category_id` while the API requires `forum_category_id`, so
  /// every attempt to start a thread failed with
  ///
  ///   422 {"errors":{"forum_category_id":["The forum category id field is
  ///   required."]}}
  ///
  /// A `Map<String, dynamic>` argument cannot catch a misspelled key; a
  /// parameter list can, and now the field name is written down exactly once.
  Future<Map<String, dynamic>> createThread({
    required String categoryId,
    required String title,
    required String body,
  }) async {
    final response = await _dio.post('/forum/threads', data: {
      // Sent as the string the screen carries. Laravel's `exists` rule
      // resolves a numeric string against the id column without complaint.
      'forum_category_id': categoryId,
      'title': title,
      'body': body,
    });
    return _asMap(response.data['thread'] ?? response.data);
  }

  Future<Map<String, dynamic>> getThread(String threadId) async {
    final response = await _dio.get('/forum/threads/$threadId');
    return _asMap(response.data['thread'] ?? response.data['data'] ?? response.data);
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
    return _asMap(response.data['scan'] ?? response.data['data'] ?? response.data);
  }

  Future<List<dynamic>> getDiseaseHistory() async {
    final response = await _dio.get('/scanner/history');
    return _asList(response.data, const ['scans']);
  }

  // AI Agronomist APIs
  Future<Map<String, dynamic>> askAgronomist(String query) async {
    final response = await _dio.post(
      '/agronomist/ask',
      data: {'question': query},
    );
    return _asMap(response.data['answer'] ?? response.data);
  }

  Future<List<dynamic>> getKbDocuments() async {
    final response = await _dio.get('/agronomist/kb/search');
    return _asList(response.data, const ['documents']);
  }

  // Mkulima AI chat APIs
  Future<Map<String, dynamic>> botChat({
    required String message,
    String? conversationUuid,
  }) async {
    final response = await _dio.post(
      '/bot/chat',
      data: {'message': message, 'conversation_uuid': conversationUuid},
    );
    return response.data;
  }

  // Notifications APIs
  /// The notification feed, parsed.
  ///
  /// This method used to be declared `Future<Map<String, dynamic>>` while
  /// returning `response.data['notifications']` - a List. Every call threw
  /// before the screen saw a single row. Returning a typed object means the
  /// declared type and the returned value cannot drift apart again.
  Future<NotificationFeed> getNotifications() async {
    final response = await _dio.get('/notifications');
    return NotificationFeed.fromResponse(response.data);
  }

  Future<void> markNotificationRead(String id) async {
    await _dio.post('/notifications/$id/read');
  }

  Future<void> markAllNotificationsRead() async {
    await _dio.post('/notifications/read-all');
  }

  // Seller APIs
  //
  // This returned `response.data['stats']` - the inner object - while
  // SellerDashboardScreen read `data['stats']` and `data['recent_orders']`
  // off the result. Both came back null, so a genuine seller saw an empty
  // dashboard with no error to explain it. The envelope is what the screen
  // wants, so the envelope is what it gets.
  Future<Map<String, dynamic>> getSellerDashboard() async {
    final response = await _dio.get('/seller/dashboard');
    return _asMap(response.data);
  }

  Future<List<dynamic>> getSellerProducts() async {
    final response = await _dio.get('/seller/products');
    return _asList(response.data, const ['products']);
  }

  Future<List<dynamic>> getSellerOrders() async {
    final response = await _dio.get('/seller/orders');
    return _asList(response.data, const ['orders']);
  }

  // KYC APIs
  Future<Map<String, dynamic>> getKycStatus() async {
    final response = await _dio.get('/kyc/status');
    return _asMap(response.data['kyc'] ?? response.data);
  }

  Future<void> submitKyc(Map<String, dynamic> data) async {
    await _dio.post('/kyc/submit', data: data);
  }

  // Wallet APIs
  Future<Map<String, dynamic>> getWalletBalance() async {
    final response = await _dio.get('/wallet/balance');
    return _asMap(response.data['wallet'] ?? response.data);
  }

  Future<List<dynamic>> getWalletTransactions() async {
    final response = await _dio.get('/wallet/transactions');
    return _asList(response.data, const ['transactions']);
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

  // Farm Management APIs
  Future<List<dynamic>> getFarms() async {
    final response = await _dio.get('/farms');
    return _asList(response.data, const ['farms']);
  }

  Future<Map<String, dynamic>> createFarm(Map<String, dynamic> farmData) async {
    final response = await _dio.post('/farms', data: farmData);
    return response.data;
  }

  Future<Map<String, dynamic>> addFarmActivity(
    String farmUuid,
    Map<String, dynamic> activityData,
  ) async {
    final response = await _dio.post(
      '/farms/$farmUuid/activities',
      data: activityData,
    );
    return response.data;
  }


  /// Coerce a response body to a map without throwing.
  ///
  /// Every accessor in this file used to index straight into `response.data`,
  /// which is `dynamic`. When the shape was not what the declared return type
  /// promised, the cast failed at the call site with a message naming Dart
  /// types rather than the endpoint - which is how
  /// `type 'List<dynamic>' is not a subtype of 'FutureOr<Map<String,
  /// dynamic>>'` reached the notifications screen.
  static Map<String, dynamic> _asMap(dynamic body) {
    if (body is Map) return Map<String, dynamic>.from(body);
    return const {};
  }

  /// Pull a list out of whichever envelope the endpoint uses.
  static List<dynamic> _asList(dynamic body, List<String> keys) {
    if (body is List) return body;
    if (body is Map) {
      for (final key in keys) {
        final value = body[key];
        if (value is List) return value;
        // Laravel's paginator nests the rows one level deeper.
        if (value is Map && value['data'] is List) return value['data'] as List;
      }
      if (body['data'] is List) return body['data'] as List;
    }
    return const [];
  }

  /// A message safe to show a farmer.
  ///
  /// Every call site in this file already routed its failures through here,
  /// so fixing the mapping in one place fixes the whole app. The previous
  /// implementation fell through to `error.message` and then
  /// `error.toString()`, which is how "DioException [bad response] ... read
  /// more at developer.mozilla.org" ended up on a production screen.
  static String formatError(dynamic error) => ApiError.from(error).message;

  /// The structured form, for callers that need the status code or the
  /// field-level validation errors rather than one sentence.
  static ApiError asApiError(dynamic error) => ApiError.from(error);
}
