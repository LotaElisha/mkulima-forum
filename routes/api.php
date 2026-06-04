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

Route::prefix('marketplace')->middleware('auth:sanctum')->group(function () {
    Route::get('/categories', [MarketplaceController::class, 'categories']);
    Route::get('/products', [MarketplaceController::class, 'products']);
    Route::get('/products/{uuid}', [MarketplaceController::class, 'product']);
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

Route::prefix('forum')->middleware('auth:sanctum')->group(function () {
    Route::get('/categories', [ForumController::class, 'categories']);
    Route::get('/threads', [ForumController::class, 'threads']);
    Route::post('/threads', [ForumController::class, 'createThread']);
    Route::get('/threads/{uuid}', [ForumController::class, 'thread']);
    Route::post('/threads/{uuid}/replies', [ForumController::class, 'createReply']);
    Route::post('/threads/{uuid}/upvote', [ForumController::class, 'upvoteThread']);
    Route::post('/replies/{replyId}/upvote', [ForumController::class, 'upvoteReply']);
});

/*
|--------------------------------------------------------------------------
| Disease Scanner Routes
|--------------------------------------------------------------------------
*/

Route::prefix('scanner')->middleware('auth:sanctum')->group(function () {
    Route::post('/scan', [DiseaseScannerController::class, 'scan']);
    Route::get('/history', [DiseaseScannerController::class, 'history']);
    Route::get('/scans/{uuid}', [DiseaseScannerController::class, 'show']);
});

/*
|--------------------------------------------------------------------------
| AI Agronomist Routes
|--------------------------------------------------------------------------
*/

Route::prefix('agronomist')->middleware('auth:sanctum')->group(function () {
    Route::post('/ask', [AgronomistController::class, 'ask']);
    Route::get('/kb/search', [AgronomistController::class, 'searchKb']);
    Route::get('/kb/{uuid}', [AgronomistController::class, 'kbDocument']);
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
    });
