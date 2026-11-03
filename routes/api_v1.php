<?php

use App\Actions\Equipment\DecideRental;
use App\Http\Controllers\Api\V1\AgronomistController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\EquipmentController;
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

    // Find an agronomist nearby. ?lat&lng&radius_km, or ?region.
    Route::get('/agronomists', [AgronomistController::class, 'index'])->middleware('throttle:60,1');
    Route::get('/agronomists/{uuid}', [AgronomistController::class, 'show']);

    // Equipment rentals. Browsing is public; listing and renting need an account.
    Route::get('/equipment/categories', [EquipmentController::class, 'categories']);
    Route::get('/equipment', [EquipmentController::class, 'index'])->middleware('throttle:60,1');
    Route::get('/equipment/{uuid}', [EquipmentController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/equipment', [EquipmentController::class, 'store'])->middleware('throttle:10,1');
        Route::patch('/equipment/{uuid}/status', [EquipmentController::class, 'updateStatus']);
        Route::get('/me/equipment', [EquipmentController::class, 'mine']);
        Route::post('/equipment/{uuid}/rentals', [EquipmentController::class, 'requestRental'])->middleware('throttle:10,1');
        Route::get('/me/rentals', [EquipmentController::class, 'rentals']);
        Route::post('/rentals/{uuid}/{action}', [EquipmentController::class, 'decide'])
            ->whereIn('action', DecideRental::actions());
    });
});
