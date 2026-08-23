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
            // Diagnose the failure for whoever has to fix it. Every cause -
            // rejected key, exhausted quota, blocked egress, model returning
            // prose instead of JSON - reached the farmer as the same sentence
            // and the log as the same one-line string, so nobody could tell
            // them apart. `php artisan mkulima:ai-check` runs the same call
            // interactively.
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
                'message' => __('scanner.analysis_unavailable'),
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

            if (! $result) {
                // The call succeeded and the model answered with something
                // that was not the JSON we asked for. Indistinguishable from
                // a network failure before this line existed.
                \Log::warning('AI plant diagnosis returned no structured data', [
                    'provider' => $aiResponse->provider,
                    'model' => $aiResponse->model,
                    'user_id' => $userId,
                ]);
            }

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
        } catch (\Throwable $e) {
            // Throwable, not Exception: a TypeError or a JSON decode failure
            // inside the provider adapter is an Error, and was escaping this
            // catch to become a raw 500 on the client.
            \Log::error('AI plant diagnosis inference failed', [
                'reason' => $this->classifyFailure($e->getMessage()),
                'message' => $e->getMessage(),
                'crop_type' => $cropType,
                'user_id' => $userId,
            ]);
        }

        return null;
    }

    /**
     * Turn a provider error string into the one word an operator needs.
     *
     * Deliberately coarse. The point is to make "the key is wrong" and "the
     * server has no route to Google" visibly different in the log, because
     * they were previously the same line and led to hours spent on the wrong
     * one.
     */
    private function classifyFailure(string $message): string
    {
        return match (true) {
            str_contains($message, '401') => 'api_key_rejected',
            str_contains($message, '403') => 'api_key_rejected_or_api_disabled_or_network_blocked',
            str_contains($message, '429') => 'quota_exhausted',
            str_contains($message, '404') => 'model_not_found',
            str_contains($message, '400') => 'bad_request_or_wrong_model',
            str_contains($message, 'cURL'),
            str_contains($message, 'timed out'),
            str_contains($message, 'Could not resolve') => 'network_unreachable',
            default => 'unknown',
        };
    }
}
