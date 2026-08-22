<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DiseaseScan;
use App\Services\AI\AIService;
use App\Support\UploadRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DiseaseScannerController extends Controller
{
    /**
     * Scan plant disease from image
     */
    public function scan(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'image' => ['required', ...UploadRules::raster(5120)],
            'crop_type' => ['nullable', 'string', 'in:maize,beans,rice,cassava,banana,coffee,tea,tomato,onion,potato'],
            'use_cloud' => ['nullable', 'boolean'],
        ]);

        // Store image
        $imagePath = $request->file('image')->store('disease-scans', 'local');
        $fullPath = Storage::disk('local')->path($imagePath);

        // v1 is cloud-only (Gemini Vision). On-device TF Lite is a Phase 3 feature —
        // see REDESIGN.md. We do not pretend to run local inference.
        $finalResult = $this->runGeminiInference($fullPath, $validated['crop_type'] ?? null, $user?->id);

        if (! $finalResult) {
            // Do not record a fake "completed" scan — be honest that analysis failed.
            DiseaseScan::create([
                'tenant_id' => $user?->tenant_id ?? 1,
                'user_id' => $user?->id,
                'image_path' => $imagePath,
                'disease_name' => null,
                'confidence_score' => 0,
                'scan_source' => 'gemini_cloud',
                'status' => 'failed',
            ]);
            Storage::disk('local')->delete($imagePath);

            return response()->json([
                'message' => 'Uchambuzi wa picha haukufanikiwa kwa sasa. Tafadhali jaribu tena baadaye.',
                'error' => 'analysis_unavailable',
            ], 503);
        }

        // Save scan record
        $scan = DiseaseScan::create([
            'tenant_id' => $user?->tenant_id ?? 1,
            'user_id' => $user?->id,
            'image_path' => $imagePath,
            'disease_name' => $finalResult['disease_name'] ?? 'Unknown',
            'confidence_score' => $finalResult['confidence'] ?? 0,
            'description' => $finalResult['description'] ?? null,
            'treatment_recommendation' => $finalResult['treatment'] ?? null,
            'affected_areas' => $finalResult['affected_areas'] ?? null,
            'scan_source' => $finalResult['source'] ?? 'manual',
            'status' => 'completed',
            'gemini_response' => $finalResult['raw_response'] ?? null,
        ]);

        return response()->json([
            'message' => 'Disease scan completed.',
            'scan' => [
                'uuid' => $scan->uuid,
                'disease_name' => $scan->disease_name,
                'confidence' => $scan->confidence_score,
                'description' => $scan->description,
                'treatment' => $scan->treatment_recommendation,
                'affected_areas' => $scan->affected_areas,
                'source' => $scan->scan_source,
                'image_url' => url("/api/scanner/scans/{$scan->uuid}/image"),
                'created_at' => $scan->created_at,
            ],
        ], 201);
    }

    /**
     * Get user's scan history
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();

        $scans = DiseaseScan::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'scans' => $scans->items(),
            'pagination' => [
                'current_page' => $scans->currentPage(),
                'last_page' => $scans->lastPage(),
                'per_page' => $scans->perPage(),
                'total' => $scans->total(),
            ],
        ]);
    }

    /**
     * Get single scan
     */
    public function show(string $uuid): JsonResponse
    {
        $user = request()->user();
        $scan = DiseaseScan::where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return response()->json([
            'scan' => [
                'uuid' => $scan->uuid,
                'disease_name' => $scan->disease_name,
                'confidence' => $scan->confidence_score,
                'description' => $scan->description,
                'treatment' => $scan->treatment_recommendation,
                'affected_areas' => $scan->affected_areas,
                'source' => $scan->scan_source,
                'image_url' => $scan->image_path ? url("/api/scanner/scans/{$scan->uuid}/image") : null,
                'created_at' => $scan->created_at,
            ],
        ]);
    }

    public function image(Request $request, string $uuid)
    {
        $scan = DiseaseScan::where('uuid', $uuid)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        abort_unless($scan->image_path && Storage::disk('local')->exists($scan->image_path), 404);

        return response()->file(Storage::disk('local')->path($scan->image_path), [
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Run AI Vision inference via AIService
     */
    private function runGeminiInference(string $imagePath, ?string $cropType, ?int $userId = null): ?array
    {
        try {
            $prompt = 'Analyze this plant image and identify any disease. ';
            if ($cropType) {
                $prompt .= "The crop is {$cropType}. ";
            }
            $prompt .= "Provide: 1) Disease name (or 'Healthy' if no disease), 2) Confidence 0-1, 3) Brief description, 4) Treatment recommendation, 5) Affected plant areas. Return as JSON with keys: disease_name, confidence, description, treatment, affected_areas (array).";

            $aiService = app(AIService::class);
            $aiResponse = $aiService->analyzeImage('plant_diagnosis', $imagePath, $prompt, ['require_json' => true], $userId);

            $result = $aiResponse->structuredData;
            if ($result) {
                return [
                    'disease_name' => $result['disease_name'] ?? 'Unknown',
                    'confidence' => (float) ($result['confidence'] ?? 0.5),
                    'description' => $result['description'] ?? null,
                    'treatment' => $result['treatment'] ?? null,
                    'affected_areas' => $result['affected_areas'] ?? null,
                    'source' => $aiResponse->provider.'_cloud',
                    'raw_response' => $result,
                ];
            }
        } catch (\Exception $e) {
            \Log::error('AI plant diagnosis inference failed: '.$e->getMessage());
        }

        return null;
    }
}
