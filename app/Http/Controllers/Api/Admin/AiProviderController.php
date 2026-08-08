<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiFeatureRoute;
use App\Models\AiProvider;
use App\Models\AiUsageLog;
use App\Services\AI\AIService;
use App\Services\AI\Secrets\SecretManagerServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AiProviderController extends Controller
{
    protected SecretManagerServiceInterface $secretManager;
    protected AIService $aiService;

    public function __construct(SecretManagerServiceInterface $secretManager, AIService $aiService)
    {
        $this->secretManager = $secretManager;
        $this->aiService = $aiService;
    }

    /**
     * List all configured AI providers.
     */
    public function index(): JsonResponse
    {
        $providers = AiProvider::withoutGlobalScopes()
            ->with(['updatedBy:id,name,email'])
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'providers' => $providers,
            'default_provider' => $providers->firstWhere('is_default', true),
        ]);
    }

    /**
     * Create a new AI provider.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'provider_type' => ['required', 'string', 'in:gemini,openai,kimi,claude,deepseek,groq,openrouter,custom'],
            'api_key' => ['required', 'string', 'min:3'],
            'base_url' => ['nullable', 'string', 'url'],
            'model' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'is_default' => ['nullable', 'boolean'],
            'temperature' => ['nullable', 'numeric', 'min:0', 'max:2'],
            'max_tokens' => ['nullable', 'integer', 'min:1', 'max:128000'],
            'timeout' => ['nullable', 'integer', 'min:1', 'max:300'],
            'organization_id' => ['nullable', 'string'],
            'project_id' => ['nullable', 'string'],
            'rate_limit' => ['nullable', 'integer'],
            'additional_config' => ['nullable', 'array'],
        ]);

        $user = $request->user();

        DB::beginTransaction();
        try {
            if (!empty($validated['is_default'])) {
                AiProvider::withoutGlobalScopes()->update(['is_default' => false]);
            }

            $provider = AiProvider::create([
                'tenant_id' => $user?->tenant_id ?? 1,
                'name' => $validated['name'],
                'provider_type' => $validated['provider_type'],
                'base_url' => $validated['base_url'] ?? null,
                'model' => $validated['model'],
                'status' => $validated['status'] ?? 'active',
                'is_default' => (bool) ($validated['is_default'] ?? false),
                'temperature' => $validated['temperature'] ?? 0.7,
                'max_tokens' => $validated['max_tokens'] ?? 2048,
                'timeout' => $validated['timeout'] ?? 30,
                'organization_id' => $validated['organization_id'] ?? null,
                'project_id' => $validated['project_id'] ?? null,
                'rate_limit' => $validated['rate_limit'] ?? null,
                'additional_config' => $validated['additional_config'] ?? null,
                'updated_by' => $user?->id,
            ]);

            $this->secretManager->storeSecret($provider->id, $validated['api_key']);
            DB::commit();

            $this->aiService->clearCache();

            return response()->json([
                'message' => 'AI Provider created successfully.',
                'provider' => $provider->fresh(),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to create AI provider: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to save AI provider configuration.'], 500);
        }
    }

    /**
     * Show single provider details.
     */
    public function show(string $uuid): JsonResponse
    {
        $provider = AiProvider::withoutGlobalScopes()
            ->where('uuid', $uuid)
            ->with(['updatedBy:id,name,email'])
            ->firstOrFail();

        return response()->json([
            'provider' => $provider,
        ]);
    }

    /**
     * Update an existing AI provider.
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $provider = AiProvider::withoutGlobalScopes()->where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'provider_type' => ['sometimes', 'string', 'in:gemini,openai,kimi,claude,deepseek,groq,openrouter,custom'],
            'api_key' => ['nullable', 'string', 'min:3'],
            'base_url' => ['nullable', 'string', 'url'],
            'model' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'in:active,inactive'],
            'is_default' => ['sometimes', 'boolean'],
            'temperature' => ['nullable', 'numeric', 'min:0', 'max:2'],
            'max_tokens' => ['nullable', 'integer', 'min:1', 'max:128000'],
            'timeout' => ['nullable', 'integer', 'min:1', 'max:300'],
            'organization_id' => ['nullable', 'string'],
            'project_id' => ['nullable', 'string'],
            'rate_limit' => ['nullable', 'integer'],
            'additional_config' => ['nullable', 'array'],
        ]);

        $user = $request->user();

        DB::beginTransaction();
        try {
            if (!empty($validated['is_default'])) {
                AiProvider::withoutGlobalScopes()
                    ->where('id', '!=', $provider->id)
                    ->update(['is_default' => false]);
            }

            $updateData = array_filter([
                'name' => $validated['name'] ?? null,
                'provider_type' => $validated['provider_type'] ?? null,
                'base_url' => $validated['base_url'] ?? null,
                'model' => $validated['model'] ?? null,
                'status' => $validated['status'] ?? null,
                'is_default' => isset($validated['is_default']) ? (bool) $validated['is_default'] : null,
                'temperature' => $validated['temperature'] ?? null,
                'max_tokens' => $validated['max_tokens'] ?? null,
                'timeout' => $validated['timeout'] ?? null,
                'organization_id' => $validated['organization_id'] ?? null,
                'project_id' => $validated['project_id'] ?? null,
                'rate_limit' => $validated['rate_limit'] ?? null,
                'additional_config' => $validated['additional_config'] ?? null,
                'updated_by' => $user?->id,
            ], fn ($value) => $value !== null);

            $provider->update($updateData);

            if (!empty($validated['api_key'])) {
                $this->secretManager->storeSecret($provider->id, $validated['api_key']);
            }

            DB::commit();
            $this->aiService->clearCache();

            return response()->json([
                'message' => 'AI Provider updated successfully.',
                'provider' => $provider->fresh(),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to update AI provider: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update AI provider configuration.'], 500);
        }
    }

    /**
     * Delete an AI provider.
     */
    public function destroy(string $uuid): JsonResponse
    {
        $provider = AiProvider::withoutGlobalScopes()->where('uuid', $uuid)->firstOrFail();
        $this->secretManager->deleteSecret($provider->id);
        $provider->delete();

        $this->aiService->clearCache();

        return response()->json([
            'message' => 'AI Provider deleted successfully.',
        ]);
    }

    /**
     * Test connection to provider securely.
     */
    public function test(string $uuid): JsonResponse
    {
        $providerModel = AiProvider::withoutGlobalScopes()->where('uuid', $uuid)->firstOrFail();
        $adapter = $this->aiService->createProviderAdapter($providerModel);

        $health = $adapter->healthCheck();

        $providerModel->update([
            'last_tested_at' => now(),
            'last_connection_status' => $health['success'] ? 'success' : 'failed',
            'last_connection_error' => $health['success'] ? null : $health['message'],
        ]);

        return response()->json([
            'success' => $health['success'],
            'latency_ms' => $health['latency_ms'],
            'message' => $health['message'],
            'provider' => $providerModel->name,
            'model' => $health['model'] ?? $providerModel->model,
        ]);
    }

    /**
     * Set as default AI provider.
     */
    public function setDefault(string $uuid): JsonResponse
    {
        $provider = AiProvider::withoutGlobalScopes()->where('uuid', $uuid)->firstOrFail();

        DB::transaction(function () use ($provider) {
            AiProvider::withoutGlobalScopes()->update(['is_default' => false]);
            $provider->update(['is_default' => true, 'status' => 'active']);
        });

        $this->aiService->clearCache();

        return response()->json([
            'message' => "{$provider->name} is now the default AI provider.",
            'provider' => $provider->fresh(),
        ]);
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggle(string $uuid): JsonResponse
    {
        $provider = AiProvider::withoutGlobalScopes()->where('uuid', $uuid)->firstOrFail();
        $newStatus = $provider->status === 'active' ? 'inactive' : 'active';

        $provider->update(['status' => $newStatus]);
        $this->aiService->clearCache();

        return response()->json([
            'message' => "{$provider->name} is now {$newStatus}.",
            'provider' => $provider->fresh(),
        ]);
    }

    /**
     * Fetch supported models for a provider.
     */
    public function getModels(string $uuid): JsonResponse
    {
        $providerModel = AiProvider::withoutGlobalScopes()->where('uuid', $uuid)->firstOrFail();
        $adapter = $this->aiService->createProviderAdapter($providerModel);

        $models = $adapter->getModels();

        return response()->json([
            'models' => $models,
        ]);
    }

    /**
     * Get feature routing assignments.
     */
    public function getFeatureRoutes(): JsonResponse
    {
        $routes = AiFeatureRoute::with(['provider'])->get();

        $defaultFeatures = [
            'plant_diagnosis' => 'Plant Disease Identification',
            'farmer_chat' => 'Mkulima Bot Chat Assistant',
            'input_label_check' => 'Agri-Input Label Verification',
            'agronomist_kb' => 'Agricultural Knowledge Base RAG',
            'market_analysis' => 'Market Price & Crop Trends',
            'document_analysis' => 'Document & Certificate Analysis',
        ];

        $output = [];
        foreach ($defaultFeatures as $key => $title) {
            $existing = $routes->firstWhere('feature_key', $key);
            $output[] = [
                'feature_key' => $key,
                'title' => $title,
                'ai_provider_id' => $existing?->ai_provider_id,
                'provider' => $existing?->provider,
                'model_override' => $existing?->model_override,
                'is_active' => $existing?->is_active ?? true,
            ];
        }

        return response()->json([
            'feature_routes' => $output,
        ]);
    }

    /**
     * Update feature route assignment.
     */
    public function updateFeatureRoute(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'feature_key' => ['required', 'string'],
            'ai_provider_id' => ['nullable', 'exists:ai_providers,id'],
            'model_override' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $route = AiFeatureRoute::updateOrCreate(
            ['feature_key' => $validated['feature_key']],
            [
                'ai_provider_id' => $validated['ai_provider_id'] ?? null,
                'model_override' => $validated['model_override'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]
        );

        $this->aiService->clearCache();

        return response()->json([
            'message' => 'Feature route updated successfully.',
            'route' => $route->load('provider'),
        ]);
    }

    /**
     * Get AI usage stats and logs.
     */
    public function getStats(): JsonResponse
    {
        $totalScans = AiUsageLog::count();
        $successfulCalls = AiUsageLog::where('status', 'success')->count();
        $failedCalls = AiUsageLog::where('status', 'error')->count();
        $avgLatency = (int) round(AiUsageLog::avg('latency_ms') ?? 0);
        $totalTokens = (int) AiUsageLog::sum('total_tokens');

        $recentLogs = AiUsageLog::with(['user:id,name,email', 'provider:id,name'])
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'stats' => [
                'total_requests' => $totalScans,
                'successful_requests' => $successfulCalls,
                'failed_requests' => $failedCalls,
                'average_latency_ms' => $avgLatency,
                'total_tokens_consumed' => $totalTokens,
            ],
            'logs' => $recentLogs,
        ]);
    }
}
