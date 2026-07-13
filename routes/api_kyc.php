<?php

use App\Http\Controllers\Api\KycController;
use Illuminate\Support\Facades\Route;

Route::prefix('kyc')->middleware('auth:sanctum')->group(function () {
    Route::get('/status', [KycController::class, 'status']);
    Route::post('/submit', [KycController::class, 'submit']);
});
