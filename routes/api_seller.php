<?php

use App\Http\Controllers\Api\Seller\SellerApplicationController;
use App\Http\Controllers\Api\SellerController;
use Illuminate\Support\Facades\Route;

Route::prefix('seller')->middleware('auth:sanctum')->group(function () {
    // Open to every authenticated user - this is the endpoint that tells the
    // app whether to draw the business section at all, so gating it behind
    // seller-only access would defeat its purpose.
    Route::get('/status', [SellerApplicationController::class, 'status']);
    Route::post('/application', [SellerApplicationController::class, 'store'])
        ->middleware('throttle:5,60');
    Route::post('/applications/{uuid}/review', [SellerApplicationController::class, 'review']);

    // Seller-only. The controller still checks - a client that skips the
    // status call must not get further than a 403.
    Route::get('/dashboard', [SellerController::class, 'dashboard']);
    Route::get('/products', [SellerController::class, 'products']);
    Route::get('/orders', [SellerController::class, 'orders']);
});
