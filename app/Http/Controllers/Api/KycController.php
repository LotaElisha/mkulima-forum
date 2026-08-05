<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KycController extends Controller
{
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'kyc_status' => $user->kyc_status ?? 'not_submitted',
            'kyc_documents' => $user->kyc_documents ?? null,
            'kyc_submitted_at' => $user->kyc_submitted_at,
            'kyc_verified_at' => $user->kyc_verified_at,
        ]);
    }

    public function submit(Request $request): JsonResponse
    {
        $user = $request->user();

        if (in_array($user->kyc_status, ['pending', 'verified'], true)) {
            return response()->json([
                'message' => $user->kyc_status === 'verified'
                    ? 'KYC yako tayari imethibitishwa.'
                    : 'KYC yako inasubiri uhakiki. Subiri majibu.',
                'status' => $user->kyc_status,
            ], 422);
        }

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
    }
}
