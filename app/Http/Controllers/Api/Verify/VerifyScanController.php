<?php

namespace App\Http\Controllers\Api\Verify;

use App\Http\Controllers\Controller;
use App\Services\Verify\VerificationEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerifyScanController extends Controller
{
    protected VerificationEngine $verificationEngine;

    public function __construct(VerificationEngine $verificationEngine)
    {
        $this->verificationEngine = $verificationEngine;
    }

    public function scan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'input' => 'required|string|max:128',
            'scan_method' => 'nullable|string|in:barcode,qr,serial,registration,scratch,manual',
            'geo_unit_id' => 'nullable|integer|exists:geo_units,id',
            'is_offline' => 'nullable|boolean',
        ]);

        $result = $this->verificationEngine->verify(
            $validated['input'],
            $validated['scan_method'] ?? 'barcode',
            auth()->id(),
            $validated['geo_unit_id'] ?? null,
            $validated['is_offline'] ?? false
        );

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ]);
    }
}
