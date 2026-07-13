<?php

use App\Http\Controllers\Api\SellerController;
use Illuminate\Support\Facades\Route;

Route::prefix('seller')->middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [SellerController::class, 'dashboard']);
    Route::get('/products', [SellerController::class, 'products']);
    Route::get('/orders', [SellerController::class, 'orders']);
});
