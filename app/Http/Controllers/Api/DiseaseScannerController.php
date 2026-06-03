<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DiseaseScan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
            'image' => ['required', 'image', 'max:10240'], // Max 10MB
            'crop_type' => ['nullable', 'string', 'in:maize,beans,rice,cassava,banana,coffee,tea,tomato,onion,potato'],
            'use_cloud' => ['nullable', 'boolean'],
        ]);

        // Store image
        $imagePath = $request->file('image')->store('disease-scans', 'public');
        $fullPath = Storage::disk('public')->path($imagePath);

        // Try on-device TF Lite first (simulated for now)
        $useCloud = $request->boolean('use_cloud', false);
        $tfliteResult = null;

        if (!$useCloud) {
            $tfliteResult = $this->runTfliteInference($fullPath, $validated['crop_type'] ?? null);
        }

        // If TF Lite confidence is low or cloud requested, use Gemini
        $finalResult = $tfliteResult;
        if (!$tfliteResult || ($tfliteResult['confidence'] ?? 0) < 0.7 || $useCloud) {
            $geminiResult = $this->runGeminiInference($fullPath, $validated['crop_type'] ?? null);
            if ($geminiResult) {
                $finalResult = $geminiResult;
            }
        }

        // Save scan record
        $scan = DiseaseScan::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
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
                'image_url' => Storage::disk('public')->url($imagePath),
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
                'image_url' => Storage::disk('public')->url($scan->image_path),
                'created_at' => $scan->created_at,
            ],
        ]);
    }

    /**
     * Run TF Lite inference (simulated - will be replaced with actual model)
     */
    private function runTfliteInference(string $imagePath, ?string $cropType): ?array
    {
        // TODO: Integrate actual TensorFlow Lite model
        // For now, return null to trigger Gemini fallback
        return null;
    }

    /**
     * Run Gemini Vision inference
     */
    private function runGeminiInference(string $imagePath, ?string $cropType): ?array
    {
        $apiKey = config('services.gemini.api_key');
        if (!$apiKey) {
            return null;
        }

        try {
            $imageData = base64_encode(file_get_contents($imagePath));
            $mimeType = mime_content_type($imagePath) ?: 'image/jpeg';

            $prompt = "Analyze this plant image and identify any disease. ";
            if ($cropType) {
                $prompt .= "The crop is {$cropType}. ";
            }
            $prompt .= "Provide: 1) Disease name (or 'Healthy' if no disease), 2) Confidence 0-1, 3) Brief description, 4) Treatment recommendation, 5) Affected plant areas. Return as JSON with keys: disease_name, confidence, description, treatment, affected_areas (array).";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inline_data' => [
                                    'mime_type' => $mimeType,
                                    'data' => $imageData,
                                ],
                            ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'response_mime_type' => 'application/json',
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

                if ($text) {
                    $result = json_decode($text, true);
                    if ($result) {
                        return [
                            'disease_name' => $result['disease_name'] ?? 'Unknown',
                            'confidence' => (float) ($result['confidence'] ?? 0.5),
                            'description' => $result['description'] ?? null,
                            'treatment' => $result['treatment'] ?? null,
                            'affected_areas' => $result['affected_areas'] ?? null,
                            'source' => 'gemini_cloud',
                            'raw_response' => $result,
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Gemini inference failed: ' . $e->getMessage());
        }

        return null;
    }
}
