<?php

namespace App\Http\Controllers\Api\Verify;

use App\Http\Controllers\Controller;
use App\Models\Agrodealer;
use Illuminate\Http\JsonResponse;

class VerifyDealerController extends Controller
{
    public function show(string $id): JsonResponse
    {
        $dealer = Agrodealer::with(['geoUnit', 'authority'])
            ->where('uuid', $id)
            ->orWhere('id', $id)
            ->orWhere('regulator_licence_number', $id)
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => [
                'uuid' => $dealer->uuid,
                'business_name' => $dealer->business_name,
                'owner_name' => $dealer->owner_name,
                'regulator_licence_number' => $dealer->regulator_licence_number,
                'physical_address' => $dealer->physical_address,
                'geo_unit' => $dealer->geoUnit?->name,
                'status' => $dealer->status, // MKULIMA_VERIFIED / REGULATOR_RECORD_MATCHED / PENDING / etc.
                'licence_expiry' => $dealer->licence_expiry ? $dealer->licence_expiry->toDateString() : null,
                'authority' => $dealer->authority?->acronym,
                'verified_at' => $dealer->verified_at ? $dealer->verified_at->toIso8601String() : null,
            ],
        ]);
    }
}
