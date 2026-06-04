<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MarketplaceController;
use App\Http\Controllers\Api\ForumController;
use App\Http\Controllers\Api\DiseaseScannerController;
use App\Http\Controllers\Api\AgronomistController;
use App\Http\Controllers\Api\Payments\PaymentController;
use App\Http\Controllers\Api\Admin\AdminController;
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
        Route::put('/users/{uuid}', [AdminController::class, 'updateUser']);
        Route::get('/orders', [AdminController::class, 'orders']);
        Route::get('/escrows', [AdminController::class, 'escrows']);
        Route::post('/escrows/{uuid}/release', [AdminController::class, 'releaseEscrow']);
        Route::post('/escrows/{uuid}/refund', [AdminController::class, 'refundEscrow']);
        Route::get('/kyc/pending', [AdminController::class, 'kycPending']);
        Route::post('/kyc/{uuid}/verify', [AdminController::class, 'verifyKyc']);
        Route::post('/kyc/{uuid}/reject', [AdminController::class, 'rejectKyc']);
        Route::get('/analytics', [AdminController::class, 'analytics']);
    });
