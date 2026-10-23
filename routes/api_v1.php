<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ForumController;
use App\Http\Controllers\Api\V1\MarketPriceController;
use App\Http\Controllers\Api\V1\WeatherController;
use Illuminate\Support\Facades\Route;

/*
| Phase 1 contract. Lists and records are {data, meta}. Errors are
| {message, errors}. Identifiers in URLs are UUIDs. Posting, payments,
| scanner and SMS stay on routes/legacy.php until a later phase.
*/
Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
        Route::post('/password/forgot', [AuthController::class, 'forgot'])->middleware('throttle:5,10');
        Route::post('/password/reset', [AuthController::class, 'reset'])->middleware('throttle:5,10');
        Route::get('/email/verify/{uuid}/{hash}', [AuthController::class, 'verifyEmail'])
            ->middleware(['signed', 'throttle:6,1'])
            ->name('v1.verification.verify');

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::get('/email/status', [AuthController::class, 'emailStatus']);
            Route::post('/email/resend', [AuthController::class, 'resend'])->middleware('throttle:3,10');
        });
    });

    Route::get('/weather/current', [WeatherController::class, 'current'])->middleware('throttle:60,1');

    Route::get('/market-prices/filters', [MarketPriceController::class, 'filters']);
    Route::get('/market-prices', [MarketPriceController::class, 'index']);

    Route::get('/forum/categories', [ForumController::class, 'categories']);
    Route::get('/forum/categories/{uuid}/threads', [ForumController::class, 'threads']);
    Route::get('/forum/threads', [ForumController::class, 'threads']);
    Route::get('/forum/threads/{uuid}', [ForumController::class, 'show']);
});
