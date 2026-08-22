<?php

use App\Http\Controllers\Admin\FeatureController;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Admin\AdminFarmController;
use App\Http\Controllers\Api\Admin\AdminProfileController;
use App\Http\Controllers\Api\Admin\AiManagementController;
use App\Http\Controllers\Api\Admin\AiProviderController;
use App\Http\Controllers\Api\Admin\CatalogController;
use App\Http\Controllers\Api\Admin\Community\AdminCommunityController;
use App\Http\Controllers\Api\Admin\DocumentController;
use App\Http\Controllers\Api\Admin\FinancialReportController;
use App\Http\Controllers\Api\Admin\HrController;
use App\Http\Controllers\Api\Admin\PosController;
use App\Http\Controllers\Api\Admin\VendorController;
use App\Http\Controllers\Api\Admin\Verify\AdminVerifyController;
use App\Http\Controllers\Api\AgronomistController;
use App\Http\Controllers\Api\Auth\EmailVerificationController;
use App\Http\Controllers\Api\Auth\IdentityController;
use App\Http\Controllers\Api\Auth\PasswordController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Community\CommunityClickController;
use App\Http\Controllers\Api\Community\SocialLinksController;
use App\Http\Controllers\Api\DiseaseScannerController;
use App\Http\Controllers\Api\DroneController;
use App\Http\Controllers\Api\FarmController;
use App\Http\Controllers\Api\ForumController;
use App\Http\Controllers\Api\InputVerificationController;
use App\Http\Controllers\Api\IoTController;
use App\Http\Controllers\Api\IvrController;
use App\Http\Controllers\Api\LogisticsController;
use App\Http\Controllers\Api\MarketplaceController;
use App\Http\Controllers\Api\MarketPriceController;
use App\Http\Controllers\Api\MkulimaBotController;
use App\Http\Controllers\Api\Payments\PaymentController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\ServiceBookingController;
use App\Http\Controllers\Api\SmsController;
use App\Http\Controllers\Api\SyncBundleController;
use App\Http\Controllers\Api\Verify\AdvisoryController;
use App\Http\Controllers\Api\Verify\CounterfeitReportController;
use App\Http\Controllers\Api\Verify\VerifyDealerController;
use App\Http\Controllers\Api\Verify\VerifyProductController;
use App\Http\Controllers\Api\Verify\VerifyScanController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\Api\WeatherController;
use App\Http\Controllers\Api\YieldController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

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
| Mkulima Verify & Community Hub API (v1) — Part E
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {
    // Verify Endpoints
    Route::post('/verify/scan', [VerifyScanController::class, 'scan'])->middleware('throttle:30,1');
    Route::get('/verify/product/{id}', [VerifyProductController::class, 'show']);
    Route::get('/verify/seed-varieties', [VerifyProductController::class, 'seedVarieties']);
    Route::get('/verify/pesticides', [VerifyProductController::class, 'pesticides']);
    Route::get('/verify/dealers/{id}', [VerifyDealerController::class, 'show']);
    // Unauthenticated by design (a farmer reporting fake inputs should not
    // need an account), but it accepts file uploads, so it is throttled per IP.
    Route::post('/reports/counterfeit', [CounterfeitReportController::class, 'store'])
        ->middleware('throttle:5,60');
    Route::get('/reports/{caseNumber}', [CounterfeitReportController::class, 'show']);
    Route::get('/advisories', [AdvisoryController::class, 'index']);

    // Community Hub Endpoints (Rule 6 compliant — DB backed)
    Route::get('/public/social-links', [SocialLinksController::class, 'socialLinks']);
    Route::get('/public/community-links', [SocialLinksController::class, 'communityLinks']);
    Route::post('/community/click', [CommunityClickController::class, 'recordClick']);

    // Offline Sync Bundle
    Route::get('/sync/bundle', [SyncBundleController::class, 'bundle']);
});

// Admin Verify & Community Management Routes
Route::prefix('admin')->middleware(['auth:sanctum', AdminMiddleware::class])->group(function () {
    Route::get('/verify/stats', [AdminVerifyController::class, 'stats']);
    Route::get('/verify/reports', [AdminVerifyController::class, 'reports']);
    Route::post('/verify/reports/{id}/escalate', [AdminVerifyController::class, 'escalateReport']);
    Route::post('/verify/advisories', [AdminVerifyController::class, 'storeAdvisory']);
    Route::put('/verify/dealers/{id}/status', [AdminVerifyController::class, 'updateDealerStatus']);

    Route::get('/community/channels', [AdminCommunityController::class, 'index']);
    Route::post('/community/channels', [AdminCommunityController::class, 'store']);
    Route::put('/community/channels/{id}', [AdminCommunityController::class, 'update']);
    Route::delete('/community/channels/{id}', [AdminCommunityController::class, 'destroy']);
    Route::post('/community/channels/{id}/qr', [AdminCommunityController::class, 'generateQr']);
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {
    // IP-level throttling against credential brute force; OTP additionally
    // has per-phone rate limiting inside OtpService.
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/login/email', [AuthController::class, 'loginWithEmail'])->middleware('throttle:10,1');
    Route::post('/register/email', [AuthController::class, 'registerWithEmail'])->middleware('throttle:5,1');
    Route::post('/social', [AuthController::class, 'social'])->middleware('throttle:10,1');
    Route::post('/apple/callback', [AuthController::class, 'appleAndroidCallback'])->middleware('throttle:20,1');
    Route::post('/otp/request', [AuthController::class, 'requestOtp'])->middleware('throttle:5,1');
    Route::post('/otp/verify', [AuthController::class, 'verifyOtp'])->middleware('throttle:10,1');

    // Password recovery. Both endpoints answer identically for known and
    // unknown addresses, so neither can be used to enumerate accounts; the
    // throttles are tighter than login because each call can send mail.
    Route::post('/password/forgot', [PasswordController::class, 'forgot'])->middleware('throttle:5,10');
    Route::post('/password/reset', [PasswordController::class, 'reset'])->middleware('throttle:5,10');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);

        Route::post('/password/change', [PasswordController::class, 'change'])->middleware('throttle:5,10');

        Route::get('/email/status', [EmailVerificationController::class, 'status']);
        Route::post('/email/resend', [EmailVerificationController::class, 'resend'])->middleware('throttle:3,10');
        Route::post('/email/change', [EmailVerificationController::class, 'requestChange'])->middleware('throttle:3,10');
        Route::delete('/email/change', [EmailVerificationController::class, 'cancelChange']);

        // Identity linking. This is what stops one farmer becoming two
        // accounts: rather than discovering a duplicate later, a signed-in
        // user attaches their phone number to the account they already have.
        Route::get('/identities', [IdentityController::class, 'index']);
        Route::post('/phone/link/request', [IdentityController::class, 'requestPhoneLink'])->middleware('throttle:5,10');
        Route::post('/phone/link/confirm', [IdentityController::class, 'confirmPhoneLink'])->middleware('throttle:10,10');
        Route::delete('/phone/link', [IdentityController::class, 'unlinkPhone'])->middleware('throttle:5,10');
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

// AI scans are authenticated and tightly throttled because each request stores
// an image and can invoke a paid cloud model.
Route::prefix('scanner')->middleware(['auth:sanctum', 'throttle:5,1'])->group(function () {
    Route::post('/scan', [DiseaseScannerController::class, 'scan']);
    Route::get('/history', [DiseaseScannerController::class, 'history']);
    Route::get('/scans/{uuid}', [DiseaseScannerController::class, 'show']);
    Route::get('/scans/{uuid}/image', [DiseaseScannerController::class, 'image']);
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
    Route::post('/chat', [MkulimaBotController::class, 'chat']);
    Route::get('/conversations', [MkulimaBotController::class, 'conversations']);
    Route::get('/conversations/{uuid}', [MkulimaBotController::class, 'show']);
    Route::delete('/conversations/{uuid}', [MkulimaBotController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Services Marketplace Routes (agronomist / veterinary / soil testing)
|--------------------------------------------------------------------------
*/

// Public - browse provider directory
Route::prefix('services')->group(function () {
    Route::get('/providers', [ServiceBookingController::class, 'providers']);
    Route::get('/providers/{uuid}', [ServiceBookingController::class, 'provider']);
});

// Protected - register & book
Route::prefix('services')->middleware('auth:sanctum')->group(function () {
    Route::post('/providers', [ServiceBookingController::class, 'registerProvider']);
    Route::get('/bookings', [ServiceBookingController::class, 'bookings']);
    Route::post('/bookings', [ServiceBookingController::class, 'createBooking']);
    Route::put('/bookings/{uuid}', [ServiceBookingController::class, 'updateBooking']);
    Route::post('/bookings/{uuid}/rate', [ServiceBookingController::class, 'rateBooking']);
});

/*
|--------------------------------------------------------------------------
| Logistics Routes (EF-005) & Warehouse Routes (EF-006)
|--------------------------------------------------------------------------
*/

// Public directories
Route::get('/logistics/transporters', [LogisticsController::class, 'transporters']);
Route::get('/warehouses', [WarehouseController::class, 'index']);
Route::get('/warehouses/{uuid}', [WarehouseController::class, 'show'])
    ->where('uuid', '[0-9a-fA-F-]{36}');

// Protected
Route::prefix('logistics')->middleware('auth:sanctum')->group(function () {
    Route::post('/transporters', [LogisticsController::class, 'registerTransporter']);
    Route::get('/freight', [LogisticsController::class, 'freight']);
    Route::post('/freight', [LogisticsController::class, 'createFreight']);
    Route::post('/freight/{uuid}/quote', [LogisticsController::class, 'quoteFreight']);
    Route::put('/freight/{uuid}', [LogisticsController::class, 'updateFreight']);
    Route::post('/freight/{uuid}/rate', [LogisticsController::class, 'rateFreight']);
});

Route::prefix('warehouses')->middleware('auth:sanctum')->group(function () {
    Route::post('/', [WarehouseController::class, 'store']);
    Route::get('/bookings', [WarehouseController::class, 'bookings']);
    Route::post('/bookings', [WarehouseController::class, 'createBooking']);
    Route::put('/bookings/{uuid}', [WarehouseController::class, 'updateBooking']);
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
        Route::get('/permissions', [AdminController::class, 'getPermissions']);
        Route::post('/users/{uuid}/permissions', [AdminController::class, 'assignPermissions']);
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
        Route::get('/settings/landing', [AdminController::class, 'getLandingSettings']);
        Route::post('/settings/landing', [AdminController::class, 'updateLandingSettings']);
        Route::post('/settings/landing/logo', [AdminController::class, 'uploadLandingLogo']);
        Route::delete('/settings/landing/logo', [AdminController::class, 'deleteLandingLogo']);
        Route::post('/settings/landing/media', [AdminController::class, 'uploadLandingMedia']);
        Route::get('/settings/otp', [AdminController::class, 'getOtpSettings']);
        Route::put('/settings/otp', [AdminController::class, 'updateOtpSettings']);
        Route::get('/documents', [DocumentController::class, 'index']);
        Route::post('/documents/sync', [DocumentController::class, 'sync'])->middleware('throttle:5,1');
        Route::get('/documents/{document}/open', [DocumentController::class, 'open'])->whereNumber('document');

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

        // Vendor & Partner Audit & Onboarding
        Route::get('/vendors', [VendorController::class, 'index']);
        Route::post('/vendors', [VendorController::class, 'store']);
        Route::get('/vendors/{uuid}', [VendorController::class, 'show']);
        Route::put('/vendors/{uuid}', [VendorController::class, 'update']);
        Route::post('/vendors/{uuid}/suspend', [VendorController::class, 'suspend']);
        Route::post('/vendors/{uuid}/reactivate', [VendorController::class, 'reactivate']);
        Route::get('/vendors/{uuid}/reviews', [VendorController::class, 'reviews']);
        Route::delete('/vendors/{uuid}', [VendorController::class, 'destroy']);

        // Admin Farm Management
        Route::get('/farms', [AdminFarmController::class, 'index']);
        Route::post('/farms', [AdminFarmController::class, 'store']);

        // AI Management & Providers
        Route::get('/ai/providers', [AiProviderController::class, 'index']);
        Route::post('/ai/providers', [AiProviderController::class, 'store']);
        Route::get('/ai/providers/routes', [AiProviderController::class, 'getFeatureRoutes']);
        Route::post('/ai/providers/routes', [AiProviderController::class, 'updateFeatureRoute']);
        Route::get('/ai/providers/usage-stats', [AiProviderController::class, 'getStats']);
        Route::get('/ai/providers/{uuid}', [AiProviderController::class, 'show']);
        Route::put('/ai/providers/{uuid}', [AiProviderController::class, 'update']);
        Route::delete('/ai/providers/{uuid}', [AiProviderController::class, 'destroy']);
        Route::post('/ai/providers/{uuid}/test', [AiProviderController::class, 'test']);
        Route::post('/ai/providers/{uuid}/set-default', [AiProviderController::class, 'setDefault']);
        Route::post('/ai/providers/{uuid}/toggle', [AiProviderController::class, 'toggle']);
        Route::get('/ai/providers/{uuid}/models', [AiProviderController::class, 'getModels']);

        Route::get('/ai/stats', [AiManagementController::class, 'stats']);
        Route::get('/ai/scans', [AiManagementController::class, 'scans']);
        Route::get('/ai/scans/{uuid}', [AiManagementController::class, 'showScan']);
        Route::delete('/ai/scans/{uuid}', [AiManagementController::class, 'deleteScan']);
        Route::get('/ai/conversations', [AiManagementController::class, 'conversations']);
        Route::get('/ai/conversations/{uuid}', [AiManagementController::class, 'showConversation']);
        Route::delete('/ai/conversations/{uuid}', [AiManagementController::class, 'deleteConversation']);
        Route::get('/ai/kb', [AiManagementController::class, 'kbDocuments']);
        Route::post('/ai/kb', [AiManagementController::class, 'createKbDocument']);
        Route::put('/ai/kb/{uuid}', [AiManagementController::class, 'updateKbDocument']);
        Route::delete('/ai/kb/{uuid}', [AiManagementController::class, 'deleteKbDocument']);
        Route::get('/ai/config', [AiManagementController::class, 'getAiConfig']);

        // Financial Reports
        Route::get('/financial-reports', [FinancialReportController::class, 'index']);
        Route::get('/financial-reports/daily', [FinancialReportController::class, 'dailyReport']);

        // Feature Flags Management
        Route::get('/features', [FeatureController::class, 'index']);
        Route::post('/features/{key}/toggle', [FeatureController::class, 'toggle']);
        Route::put('/features/{key}', [FeatureController::class, 'update']);
        Route::get('/features/category/{category}', [FeatureController::class, 'byCategory']);
    });

// Notifications API
require __DIR__.'/api_notifications.php';

// Seller API
require __DIR__.'/api_seller.php';

// KYC API
require __DIR__.'/api_kyc.php';

/*
|--------------------------------------------------------------------------
| Weather Routes
|--------------------------------------------------------------------------
*/
// Public, but every cache miss spends an OpenWeather call against a metered
// key, so anonymous callers are throttled.
Route::prefix('weather')->middleware('throttle:60,1')->group(function () {
    Route::get('/current', [WeatherController::class, 'current']);
    Route::get('/forecast', [WeatherController::class, 'forecast']);
    Route::get('/advisory', [WeatherController::class, 'advisory']);
    Route::get('/report', [WeatherController::class, 'fullReport']);
});

/*
|--------------------------------------------------------------------------
| Global Search
|--------------------------------------------------------------------------
*/

Route::get('/search', [SearchController::class, 'search'])
    ->middleware('throttle:30,1');

/*
|--------------------------------------------------------------------------
| Kagua Dawa — Counterfeit Agri-Input Verification
|--------------------------------------------------------------------------
*/

Route::prefix('inputs')->group(function () {
    // Public: registry lookup, confirmed alerts, hand-check checklist
    Route::get('/verify', [InputVerificationController::class, 'verify'])
        ->middleware('throttle:30,1');
    Route::get('/alerts', [InputVerificationController::class, 'alerts']);
    Route::get('/checklist', [InputVerificationController::class, 'checklist']);

    // Auth: label photo check (Gemini) + community counterfeit report
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/check-label', [InputVerificationController::class, 'checkLabel'])
            ->middleware('throttle:10,1');
        Route::post('/report', [InputVerificationController::class, 'report'])
            ->middleware('throttle:10,60');
    });
});

// Admin: registry management + alert review queue
Route::prefix('admin')
    ->middleware(['auth:sanctum', AdminMiddleware::class])
    ->group(function () {
        Route::get('/inputs', [InputVerificationController::class, 'registryIndex']);
        Route::post('/inputs', [InputVerificationController::class, 'registryStore']);
        Route::put('/inputs/{uuid}', [InputVerificationController::class, 'registryUpdate']);
        Route::delete('/inputs/{uuid}', [InputVerificationController::class, 'registryDestroy']);
        Route::get('/input-alerts', [InputVerificationController::class, 'alertQueue']);
        Route::post('/input-alerts/{uuid}/review', [InputVerificationController::class, 'reviewAlert']);
    });

/*
|--------------------------------------------------------------------------
| Content Reporting & Moderation Routes
|--------------------------------------------------------------------------
*/

// Any authenticated user can report content (throttled against abuse)
Route::post('/reports', [ReportController::class, 'store'])
    ->middleware(['auth:sanctum', 'throttle:10,60']);

// Admin moderation queue
Route::prefix('admin/reports')
    ->middleware(['auth:sanctum', AdminMiddleware::class])
    ->group(function () {
        Route::get('/', [ReportController::class, 'index']);
        Route::post('/{uuid}/resolve', [ReportController::class, 'resolve']);
        Route::post('/{uuid}/dismiss', [ReportController::class, 'dismiss']);
    });

/*
|--------------------------------------------------------------------------
| Market Prices Routes
|--------------------------------------------------------------------------
*/

// Public price board
Route::prefix('market-prices')->group(function () {
    Route::get('/', [MarketPriceController::class, 'index']);
    Route::get('/filters', [MarketPriceController::class, 'filters']);
});

// Admin price management
Route::prefix('admin/market-prices')
    ->middleware(['auth:sanctum', AdminMiddleware::class])
    ->group(function () {
        Route::post('/', [MarketPriceController::class, 'store']);
        Route::put('/{uuid}', [MarketPriceController::class, 'update']);
        Route::delete('/{uuid}', [MarketPriceController::class, 'destroy']);
    });

/*
|--------------------------------------------------------------------------
| SMS Routes
|--------------------------------------------------------------------------
*/
Route::prefix('sms')->middleware('auth:sanctum')->group(function () {
    // Sending arbitrary SMS is an admin-only capability (spam prevention).
    Route::post('/send', [SmsController::class, 'send'])
        ->middleware(AdminMiddleware::class);
    Route::get('/history', [SmsController::class, 'getHistory']);
});

// Gateway webhooks. These carry no user credential, so they are protected by
// a shared-secret signature (config: services.sms.webhook_secret) and a hard
// per-IP throttle. Before this, /sms/receive was an open endpoint that ran a
// DB query and an outbound OpenWeather call on every anonymous request.
Route::middleware(['verify.webhook:sms', 'throttle:60,1'])->group(function () {
    Route::post('/sms/callback', [SmsController::class, 'callback']);
    Route::post('/sms/receive', [SmsController::class, 'receive']);
});

/*
|--------------------------------------------------------------------------
| Wallet Routes (Mkulima Pay)
|--------------------------------------------------------------------------
*/
Route::prefix('wallet')->middleware('auth:sanctum')->group(function () {
    Route::get('/balance', [WalletController::class, 'getBalance']);
    Route::get('/transactions', [WalletController::class, 'getTransactions']);
    Route::post('/deposit', [WalletController::class, 'deposit']);
    Route::post('/withdraw', [WalletController::class, 'withdraw']);
    Route::post('/transfer', [WalletController::class, 'transfer']);
    Route::get('/history', [WalletController::class, 'getTransactions']);
    Route::get('/stats', [WalletController::class, 'getBalance']);
});

/*
|--------------------------------------------------------------------------
| IVR Routes
|--------------------------------------------------------------------------
*/
Route::prefix('ivr')->middleware(['verify.webhook:ivr', 'throttle:60,1'])->group(function () {
    Route::post('/incoming', [IvrController::class, 'handleIncoming']);
    Route::post('/callback', [IvrController::class, 'handleCallback']);
});

// Public feature status
Route::get('/features/status', [FeatureController::class, 'publicStatus']);
Route::get('/features/check/{key}', [FeatureController::class, 'check']);

// Drone APIs
Route::get('/drone/services', [DroneController::class, 'services']);
Route::middleware('auth:sanctum')->prefix('drone')->group(function () {
    Route::post('/book', [DroneController::class, 'book']);
    Route::get('/bookings', [DroneController::class, 'myBookings']);
});

// IoT APIs
Route::get('/iot/sensors', [IoTController::class, 'sensors']);
Route::middleware('auth:sanctum')->prefix('iot')->group(function () {
    Route::get('/my-sensors', [IoTController::class, 'mySensors']);
    Route::get('/readings/{sensorId}', [IoTController::class, 'readings']);
    Route::post('/readings', [IoTController::class, 'storeReading']);
});

// Yield Estimation APIs
Route::middleware('auth:sanctum')->prefix('yield')->group(function () {
    Route::post('/estimate', [YieldController::class, 'estimate']);
    Route::post('/analyze-photo', [YieldController::class, 'analyzePhoto']);
    Route::get('/history', [YieldController::class, 'history']);
});

// Farm Management APIs
Route::middleware('auth:sanctum')->prefix('farms')->group(function () {
    Route::get('/', [FarmController::class, 'index']);
    Route::post('/', [FarmController::class, 'store']);
    Route::get('/{uuid}', [FarmController::class, 'show']);
    Route::put('/{uuid}', [FarmController::class, 'update']);
    Route::delete('/{uuid}', [FarmController::class, 'destroy']);
    Route::post('/{uuid}/activities', [FarmController::class, 'storeActivity']);
    Route::delete('/activities/{activityUuid}', [FarmController::class, 'destroyActivity']);
});
