<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

Route::prefix('kyc')->middleware('auth:sanctum')->group(function () {
    Route::get('/status', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'kyc_status' => $user->kyc_status ?? 'not_submitted',
            'kyc_documents' => $user->kyc_documents ?? null,
            'kyc_submitted_at' => $user->kyc_submitted_at,
            'kyc_verified_at' => $user->kyc_verified_at,
        ]);
    });
    
    Route::post('/submit', function (Request $request) {
        $user = $request->user();
        
        $validated = $request->validate([
            'id_type' => 'required|string|in:national_id,drivers_license,passport,voter_id',
            'id_number' => 'required|string|max:50',
            'full_name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'region' => 'required|string|max:100',
            'district' => 'required|string|max:100',
        ]);
        
        $user->update([
            'kyc_status' => 'pending',
            'kyc_documents' => json_encode($validated),
            'kyc_submitted_at' => now(),
        ]);
        
        return response()->json([
            'message' => 'KYC submitted successfully. Awaiting verification.',
            'status' => 'pending',
        ]);
    });
});
