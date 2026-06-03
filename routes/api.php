<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MarketplaceController;
use App\Http\Controllers\Api\ForumController;
use App\Http\Controllers\Api\DiseaseScannerController;
use App\Http\Controllers\Api\AgronomistController;

/*
|--------------------------------------------------------------------------
| MkulimaForum API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'mkulima-forum',
        'version' => '1.0.0',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Authentication
Route::post('/auth/otp/request', [AuthController::class, 'requestOtp']);
Route::post('/auth/otp/verify', [AuthController::class, 'verifyOtp']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/logout-all', [AuthController::class, 'logoutAll']);

    // Marketplace
    Route::get('/marketplace/categories', [MarketplaceController::class, 'categories']);
    Route::get('/marketplace/products', [MarketplaceController::class, 'products']);
    Route::get('/marketplace/products/{uuid}', [MarketplaceController::class, 'product']);
    Route::post('/marketplace/products', [MarketplaceController::class, 'createProduct']);
    Route::put('/marketplace/products/{uuid}', [MarketplaceController::class, 'updateProduct']);
    Route::delete('/marketplace/products/{uuid}', [MarketplaceController::class, 'deleteProduct']);
    Route::post('/marketplace/orders', [MarketplaceController::class, 'createOrder']);
    Route::get('/marketplace/orders', [MarketplaceController::class, 'orders']);
    Route::get('/marketplace/orders/{uuid}', [MarketplaceController::class, 'order']);

    // Forum
    Route::get('/forum/categories', [ForumController::class, 'categories']);
    Route::get('/forum/threads', [ForumController::class, 'threads']);
    Route::post('/forum/threads', [ForumController::class, 'createThread']);
    Route::get('/forum/threads/{uuid}', [ForumController::class, 'thread']);
    Route::post('/forum/threads/{uuid}/replies', [ForumController::class, 'createReply']);
    Route::post('/forum/threads/{uuid}/upvote', [ForumController::class, 'upvoteThread']);
    Route::post('/forum/replies/{replyId}/upvote', [ForumController::class, 'upvoteReply']);

    // Disease Scanner
    Route::post('/scanner/scan', [DiseaseScannerController::class, 'scan']);
    Route::get('/scanner/history', [DiseaseScannerController::class, 'history']);
    Route::get('/scanner/scans/{uuid}', [DiseaseScannerController::class, 'show']);

    // AI Agronomist
    Route::post('/agronomist/ask', [AgronomistController::class, 'ask']);
    Route::get('/agronomist/kb/search', [AgronomistController::class, 'searchKb']);
    Route::get('/agronomist/kb/{uuid}', [AgronomistController::class, 'kbDocument']);
});
