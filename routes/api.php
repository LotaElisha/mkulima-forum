<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MarketplaceController;
use App\Http\Controllers\Api\ForumController;
use App\Http\Controllers\Api\DiseaseScannerController;
use App\Http\Controllers\Api\AgronomistController;
use App\Http\Controllers\Api\Payments\PaymentController;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Admin\AdminProfileController;
use App\Http\Controllers\Api\Admin\CatalogController;
use App\Http\Controllers\Api\Admin\FinancialReportController;
use App\Http\Controllers\Api\Admin\HrController;
use App\Http\Controllers\Api\Admin\PosController;
use App\Http\Controllers\Api\Admin\VendorController;
use App\Http\Middleware\AdminMiddleware;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'mkulima-forum',
        'version' => '1.0.0',
        'timestamp' => now()->toIso8601String(),
    ]);
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/login/email', [AuthController::class, 'loginWithEmail']);
    Route::post('/otp/request', [AuthController::class, 'requestOtp']);
    Route::post('/otp/verify', [AuthController::class, 'verifyOtp']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    });
});

/*
|--------------------------------------------------------------------------
| Marketplace Routes
|--------------------------------------------------------------------------
*/

// Public - browse without login
Route::prefix('marketplace')->group(function () {
    Route::get('/categories', [MarketplaceController::class, 'categories']);
    Route::get('/products', [MarketplaceController::class, 'products']);
    Route::get('/products/{uuid}', [MarketplaceController::class, 'product']);
});

// Protected - requires login
Route::prefix('marketplace')->middleware('auth:sanctum')->group(function () {
    Route::post('/products', [MarketplaceController::class, 'createProduct']);
    Route::put('/products/{uuid}', [MarketplaceController::class, 'updateProduct']);
    Route::delete('/products/{uuid}', [MarketplaceController::class, 'deleteProduct']);

    Route::get('/orders', [MarketplaceController::class, 'orders']);
    Route::get('/orders/{uuid}', [MarketplaceController::class, 'order']);
    Route::post('/orders', [MarketplaceController::class, 'createOrder']);
});

/*
|--------------------------------------------------------------------------
| Forum Routes
|--------------------------------------------------------------------------
*/

// Public - read without login
Route::prefix('forum')->group(function () {
    Route::get('/categories', [ForumController::class, 'categories']);
    Route::get('/threads', [ForumController::class, 'threads']);
    Route::get('/threads/{uuid}', [ForumController::class, 'thread']);
});

// Protected - requires login to post
Route::prefix('forum')->middleware('auth:sanctum')->group(function () {
    Route::post('/threads', [ForumController::class, 'createThread']);
    Route::post('/threads/{uuid}/replies', [ForumController::class, 'createReply']);
    Route::post('/threads/{uuid}/upvote', [ForumController::class, 'upvoteThread']);
    Route::post('/replies/{replyId}/upvote', [ForumController::class, 'upvoteReply']);
    Route::post('/replies/{replyId}/mark-expert-answer', [ForumController::class, 'markExpertAnswer']);
});

/*
|--------------------------------------------------------------------------
| Disease Scanner Routes
|--------------------------------------------------------------------------
*/

// Public - AI scan without login
Route::prefix('scanner')->group(function () {
    Route::post('/scan', [DiseaseScannerController::class, 'scan']);
});

// Protected - history requires login
Route::prefix('scanner')->middleware('auth:sanctum')->group(function () {
    Route::get('/history', [DiseaseScannerController::class, 'history']);
    Route::get('/scans/{uuid}', [DiseaseScannerController::class, 'show']);
});

/*
|--------------------------------------------------------------------------
| AI Agronomist Routes
|--------------------------------------------------------------------------
*/

// Public - KB search without login
Route::prefix('agronomist')->group(function () {
    Route::get('/kb/search', [AgronomistController::class, 'searchKb']);
    Route::get('/kb/{uuid}', [AgronomistController::class, 'kbDocument']);
});

// Protected - ask requires login
Route::prefix('agronomist')->middleware('auth:sanctum')->group(function () {
    Route::post('/ask', [AgronomistController::class, 'ask']);
});

/*
|--------------------------------------------------------------------------
| Mkulima Bot — AI chatbot & farm advisor
|--------------------------------------------------------------------------
*/

Route::prefix('bot')->middleware('auth:sanctum')->group(function () {
    Route::post('/chat', [\App\Http\Controllers\Api\MkulimaBotController::class, 'chat']);
    Route::get('/conversations', [\App\Http\Controllers\Api\MkulimaBotController::class, 'conversations']);
    Route::get('/conversations/{uuid}', [\App\Http\Controllers\Api\MkulimaBotController::class, 'show']);
    Route::delete('/conversations/{uuid}', [\App\Http\Controllers\Api\MkulimaBotController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Services Marketplace Routes (agronomist / veterinary / soil testing)
|--------------------------------------------------------------------------
*/

// Public - browse provider directory
Route::prefix('services')->group(function () {
    Route::get('/providers', [\App\Http\Controllers\Api\ServiceBookingController::class, 'providers']);
    Route::get('/providers/{uuid}', [\App\Http\Controllers\Api\ServiceBookingController::class, 'provider']);
});

// Protected - register & book
Route::prefix('services')->middleware('auth:sanctum')->group(function () {
    Route::post('/providers', [\App\Http\Controllers\Api\ServiceBookingController::class, 'registerProvider']);
    Route::get('/bookings', [\App\Http\Controllers\Api\ServiceBookingController::class, 'bookings']);
    Route::post('/bookings', [\App\Http\Controllers\Api\ServiceBookingController::class, 'createBooking']);
    Route::put('/bookings/{uuid}', [\App\Http\Controllers\Api\ServiceBookingController::class, 'updateBooking']);
    Route::post('/bookings/{uuid}/rate', [\App\Http\Controllers\Api\ServiceBookingController::class, 'rateBooking']);
});

/*
|--------------------------------------------------------------------------
| Logistics Routes (EF-005) & Warehouse Routes (EF-006)
|--------------------------------------------------------------------------
*/

// Public directories
Route::get('/logistics/transporters', [\App\Http\Controllers\Api\LogisticsController::class, 'transporters']);
Route::get('/warehouses', [\App\Http\Controllers\Api\WarehouseController::class, 'index']);
Route::get('/warehouses/{uuid}', [\App\Http\Controllers\Api\WarehouseController::class, 'show'])
    ->where('uuid', '[0-9a-fA-F-]{36}');

// Protected
Route::prefix('logistics')->middleware('auth:sanctum')->group(function () {
    Route::post('/transporters', [\App\Http\Controllers\Api\LogisticsController::class, 'registerTransporter']);
    Route::get('/freight', [\App\Http\Controllers\Api\LogisticsController::class, 'freight']);
    Route::post('/freight', [\App\Http\Controllers\Api\LogisticsController::class, 'createFreight']);
    Route::post('/freight/{uuid}/quote', [\App\Http\Controllers\Api\LogisticsController::class, 'quoteFreight']);
    Route::put('/freight/{uuid}', [\App\Http\Controllers\Api\LogisticsController::class, 'updateFreight']);
    Route::post('/freight/{uuid}/rate', [\App\Http\Controllers\Api\LogisticsController::class, 'rateFreight']);
});

Route::prefix('warehouses')->middleware('auth:sanctum')->group(function () {
    Route::post('/', [\App\Http\Controllers\Api\WarehouseController::class, 'store']);
    Route::get('/bookings', [\App\Http\Controllers\Api\WarehouseController::class, 'bookings']);
    Route::post('/bookings', [\App\Http\Controllers\Api\WarehouseController::class, 'createBooking']);
    Route::put('/bookings/{uuid}', [\App\Http\Controllers\Api\WarehouseController::class, 'updateBooking']);
});

/*
|--------------------------------------------------------------------------
| Payment Routes
|--------------------------------------------------------------------------
*/

Route::prefix('payments')->middleware('auth:sanctum')->group(function () {
    Route::post('/initiate', [PaymentController::class, 'initiate']);
    Route::get('/escrows', [PaymentController::class, 'myEscrows']);
    Route::get('/escrows/{uuid}', [PaymentController::class, 'status']);
    Route::post('/escrows/{uuid}/confirm', [PaymentController::class, 'confirmDelivery']);
    Route::post('/escrows/{uuid}/refund', [PaymentController::class, 'requestRefund']);
    Route::get('/stats', [PaymentController::class, 'stats']);
});

Route::post('/payments/mpesa/callback', [PaymentController::class, 'mpesaCallback']);

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->middleware(['auth:sanctum', AdminMiddleware::class])
    ->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/users', [AdminController::class, 'users']);
        Route::post('/users', [AdminController::class, 'createUser']);
        Route::get('/users/{uuid}', [AdminController::class, 'showUser']);
        Route::put('/users/{uuid}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{uuid}', [AdminController::class, 'deleteUser']);
        Route::get('/orders', [AdminController::class, 'orders']);
        Route::get('/orders/{uuid}', [AdminController::class, 'showOrder']);
        Route::put('/orders/{uuid}', [AdminController::class, 'updateOrder']);
        Route::delete('/orders/{uuid}', [AdminController::class, 'deleteOrder']);
        Route::get('/escrows', [AdminController::class, 'escrows']);
        Route::post('/escrows/{uuid}/release', [AdminController::class, 'releaseEscrow']);
        Route::post('/escrows/{uuid}/refund', [AdminController::class, 'refundEscrow']);
        Route::get('/kyc/pending', [AdminController::class, 'kycPending']);
        Route::post('/kyc/{uuid}/verify', [AdminController::class, 'verifyKyc']);
        Route::post('/kyc/{uuid}/reject', [AdminController::class, 'rejectKyc']);
        Route::get('/analytics', [AdminController::class, 'analytics']);

        // Admin Profile
        Route::get('/profile', [AdminProfileController::class, 'show']);
        Route::put('/profile', [AdminProfileController::class, 'update']);
        Route::post('/profile/change-password', [AdminProfileController::class, 'changePassword']);
        Route::post('/profile/avatar', [AdminProfileController::class, 'updateAvatar']);
        Route::get('/profile/activity', [AdminProfileController::class, 'activityLog']);

        // Human Resources
        Route::get('/hr/staff', [HrController::class, 'index']);
        Route::post('/hr/staff', [HrController::class, 'store']);
        Route::get('/hr/staff/{uuid}', [HrController::class, 'show']);
        Route::put('/hr/staff/{uuid}', [HrController::class, 'update']);
        Route::delete('/hr/staff/{uuid}', [HrController::class, 'destroy']);
        Route::get('/hr/statistics', [HrController::class, 'statistics']);
        Route::post('/hr/staff/{uuid}/permissions', [HrController::class, 'assignPermissions']);

        // Field POS Terminal
        Route::get('/pos/products', [PosController::class, 'searchProducts']);
        Route::post('/pos/orders', [PosController::class, 'createOrder']);
        Route::get('/pos/orders/{uuid}/receipt', [PosController::class, 'receipt']);
        Route::get('/pos/history', [PosController::class, 'history']);
        Route::get('/pos/daily-summary', [PosController::class, 'dailySummary']);

        // Market Catalog Management
        Route::get('/catalog/products', [CatalogController::class, 'index']);
        Route::post('/catalog/products', [CatalogController::class, 'store']);
        Route::get('/catalog/products/{uuid}', [CatalogController::class, 'show']);
        Route::put('/catalog/products/{uuid}', [CatalogController::class, 'update']);
        Route::delete('/catalog/products/{uuid}', [CatalogController::class, 'destroy']);
        Route::post('/catalog/products/bulk-delete', [CatalogController::class, 'bulkDelete']);
        Route::post('/catalog/products/bulk-status', [CatalogController::class, 'bulkUpdateStatus']);
        Route::post('/catalog/products/export', [CatalogController::class, 'export']);
        Route::get('/catalog/low-stock', [CatalogController::class, 'lowStock']);
        Route::get('/catalog/categories', [CatalogController::class, 'categories']);

        // Vendor & Partner Audit
        Route::get('/vendors', [VendorController::class, 'index']);
        Route::get('/vendors/{uuid}', [VendorController::class, 'show']);
        Route::put('/vendors/{uuid}', [VendorController::class, 'update']);
        Route::post('/vendors/{uuid}/suspend', [VendorController::class, 'suspend']);
        Route::post('/vendors/{uuid}/reactivate', [VendorController::class, 'reactivate']);
        Route::get('/vendors/{uuid}/reviews', [VendorController::class, 'reviews']);

        // Financial Reports
        Route::get('/financial-reports', [FinancialReportController::class, 'index']);
        Route::get('/financial-reports/daily', [FinancialReportController::class, 'dailyReport']);

        // Feature Flags Management
        Route::get('/features', [\App\Http\Controllers\Admin\FeatureController::class, 'index']);
        Route::post('/features/{key}/toggle', [\App\Http\Controllers\Admin\FeatureController::class, 'toggle']);
        Route::put('/features/{key}', [\App\Http\Controllers\Admin\FeatureController::class, 'update']);
        Route::get('/features/category/{category}', [\App\Http\Controllers\Admin\FeatureController::class, 'byCategory']);
    });


// Notifications API
require __DIR__ . '/api_notifications.php';


// Seller API
require __DIR__ . '/api_seller.php';


// KYC API
require __DIR__ . '/api_kyc.php';

/*
|--------------------------------------------------------------------------
| Weather Routes
|--------------------------------------------------------------------------
*/
Route::prefix('weather')->group(function () {
    Route::get('/current', [\App\Http\Controllers\Api\WeatherController::class, 'current']);
    Route::get('/forecast', [\App\Http\Controllers\Api\WeatherController::class, 'forecast']);
    Route::get('/advisory', [\App\Http\Controllers\Api\WeatherController::class, 'advisory']);
    Route::get('/report', [\App\Http\Controllers\Api\WeatherController::class, 'fullReport']);
});

/*
|--------------------------------------------------------------------------
| SMS Routes
|--------------------------------------------------------------------------
*/
Route::prefix('sms')->middleware('auth:sanctum')->group(function () {
    Route::post('/send', [\App\Http\Controllers\Api\SmsController::class, 'send']);
    Route::get('/history', [\App\Http\Controllers\Api\SmsController::class, 'getHistory']);
});

Route::post('/sms/callback', [\App\Http\Controllers\Api\SmsController::class, 'callback']);
Route::post('/sms/receive', [\App\Http\Controllers\Api\SmsController::class, 'receive']);

/*
|--------------------------------------------------------------------------
| Wallet Routes (Mkulima Pay)
|--------------------------------------------------------------------------
*/
Route::prefix('wallet')->middleware('auth:sanctum')->group(function () {
    Route::get('/balance', [\App\Http\Controllers\Api\WalletController::class, 'getBalance']);
    Route::get('/transactions', [\App\Http\Controllers\Api\WalletController::class, 'getTransactions']);
    Route::post('/deposit', [\App\Http\Controllers\Api\WalletController::class, 'deposit']);
    Route::post('/withdraw', [\App\Http\Controllers\Api\WalletController::class, 'withdraw']);
    Route::post('/transfer', [\App\Http\Controllers\Api\WalletController::class, 'transfer']);
    Route::get('/history', [\App\Http\Controllers\Api\WalletController::class, 'getTransactions']);
    Route::get('/stats', [\App\Http\Controllers\Api\WalletController::class, 'getBalance']);
});

/*
|--------------------------------------------------------------------------
| IVR Routes
|--------------------------------------------------------------------------
*/
Route::prefix('ivr')->group(function () {
    Route::post('/incoming', [\App\Http\Controllers\Api\IvrController::class, 'handleIncoming']);
    Route::post('/callback', [\App\Http\Controllers\Api\IvrController::class, 'handleCallback']);
});

// Weather APIs
Route::get('/weather/current', [App\Http\Controllers\Api\WeatherController::class, 'getCurrent']);
Route::get('/weather/advisory', [App\Http\Controllers\Api\WeatherController::class, 'getAdvisory']);

// Admin Feature Management
Route::prefix('admin')->middleware(['auth:sanctum', AdminMiddleware::class])->group(function () {
    Route::get('/features', [App\Http\Controllers\Admin\FeatureController::class, 'index']);
    Route::post('/features/{key}/toggle', [App\Http\Controllers\Admin\FeatureController::class, 'toggle']);
    Route::put('/features/{key}', [App\Http\Controllers\Admin\FeatureController::class, 'update']);
    Route::get('/features/category/{category}', [App\Http\Controllers\Admin\FeatureController::class, 'byCategory']);
});

// Public feature status
Route::get('/features/status', [App\Http\Controllers\Admin\FeatureController::class, 'publicStatus']);
Route::get('/features/check/{key}', [App\Http\Controllers\Admin\FeatureController::class, 'check']);

// Drone APIs
Route::get('/drone/services', [App\Http\Controllers\Api\DroneController::class, 'services']);
Route::middleware('auth:sanctum')->prefix('drone')->group(function () {
    Route::post('/book', [App\Http\Controllers\Api\DroneController::class, 'book']);
    Route::get('/bookings', [App\Http\Controllers\Api\DroneController::class, 'myBookings']);
});

// IoT APIs
Route::get('/iot/sensors', [App\Http\Controllers\Api\IoTController::class, 'sensors']);
Route::middleware('auth:sanctum')->prefix('iot')->group(function () {
    Route::get('/my-sensors', [App\Http\Controllers\Api\IoTController::class, 'mySensors']);
    Route::get('/readings/{sensorId}', [App\Http\Controllers\Api\IoTController::class, 'readings']);
    Route::post('/readings', [App\Http\Controllers\Api\IoTController::class, 'storeReading']);
});

// Yield Estimation APIs
Route::middleware('auth:sanctum')->prefix('yield')->group(function () {
    Route::post('/estimate', [App\Http\Controllers\Api\YieldController::class, 'estimate']);
    Route::post('/analyze-photo', [App\Http\Controllers\Api\YieldController::class, 'analyzePhoto']);
    Route::get('/history', [App\Http\Controllers\Api\YieldController::class, 'history']);
});

// Escrow APIs
Route::middleware('auth:sanctum')->prefix('escrow')->group(function () {
    Route::post('/create', [App\Http\Controllers\Api\EscrowController::class, 'create']);
    Route::get('/my-escrows', [App\Http\Controllers\Api\EscrowController::class, 'myEscrows']);
    Route::post('/{escrowId}/release', [App\Http\Controllers\Api\EscrowController::class, 'release']);
    Route::post('/{escrowId}/dispute', [App\Http\Controllers\Api\EscrowController::class, 'dispute']);
    Route::get('/stats', [App\Http\Controllers\Api\EscrowController::class, 'stats']);
});
