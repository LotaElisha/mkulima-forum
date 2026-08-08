<?php

namespace App\Services\AI;

use App\Models\AiFeatureRoute;
use App\Models\AiProvider;
use App\Models\AiUsageLog;
use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\DTO\AIResponse;
use App\Services\AI\Providers\GeminiProvider;
use App\Services\AI\Providers\OpenAIProvider;
use App\Services\AI\Secrets\SecretManagerServiceInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected SecretManagerServiceInterface $secretManager;

    public function __construct(SecretManagerServiceInterface $secretManager)
    {
        $this->secretManager = $secretManager;
    }

    /**
     * Generate text using feature routing or default AI provider.
     */
    public function generateText(string $featureKey, array $messages, array $options = [], ?int $userId = null): AIResponse
    {
        $startTime = microtime(true);
        $provider = $this->getProviderForFeature($featureKey);

        try {
            $response = $provider->generateText($messages, $options);
            $this->logUsage($provider, $featureKey, $response, $userId, 'success');
            return $response;
        } catch (\Throwable $e) {
            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);
            $this->logError($provider, $featureKey, $e->getMessage(), $latencyMs, $userId);
            throw $e;
        }
    }

    /**
     * Generate structured JSON output using feature routing or default AI provider.
     */
    public function generateStructuredData(string $featureKey, array $messages, array $schema = [], array $options = [], ?int $userId = null): AIResponse
    {
        $startTime = microtime(true);
        $provider = $this->getProviderForFeature($featureKey);

        try {
            $response = $provider->generateStructuredData($messages, $schema, $options);
            $this->logUsage($provider, $featureKey, $response, $userId, 'success');
            return $response;
        } catch (\Throwable $e) {
            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);
            $this->logError($provider, $featureKey, $e->getMessage(), $latencyMs, $userId);
            throw $e;
        }
    }

    /**
     * Analyze image using feature routing or default AI provider.
     */
    public function analyzeImage(string $featureKey, string $imagePath, string $prompt, array $options = [], ?int $userId = null): AIResponse
    {
        $startTime = microtime(true);
        $provider = $this->getProviderForFeature($featureKey);

        try {
            $response = $provider->analyzeImage($imagePath, $prompt, $options);
            $this->logUsage($provider, $featureKey, $response, $userId, 'success');
            return $response;
        } catch (\Throwable $e) {
            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);
            $this->logError($provider, $featureKey, $e->getMessage(), $latencyMs, $userId);
            throw $e;
        }
    }

    /**
     * Resolve provider for a specific feature key, falling back to default provider or env config.
     */
    public function getProviderForFeature(string $featureKey, int $tenantId = 1): AIProviderInterface
    {
        $cacheKey = "ai_feature_route_{$tenantId}_{$featureKey}";

        $providerId = Cache::remember($cacheKey, 300, function () use ($featureKey, $tenantId) {
            $route = AiFeatureRoute::where('feature_key', $featureKey)
                ->where('is_active', true)
                ->first();

            return $route?->ai_provider_id;
        });

        if ($providerId) {
            $providerModel = $this->getProviderModelById($providerId);
            if ($providerModel && $providerModel->status === 'active') {
                return $this->createProviderAdapter($providerModel);
            }
        }

        return $this->getDefaultProvider($tenantId);
    }

    /**
     * Get the default AI provider, or fallback to env Gemini provider.
     */
    public function getDefaultProvider(int $tenantId = 1): AIProviderInterface
    {
        $cacheKey = "ai_default_provider_{$tenantId}";

        $providerId = Cache::remember($cacheKey, 300, function () use ($tenantId) {
            return AiProvider::withoutGlobalScopes()
                ->where('status', 'active')
                ->where('is_default', true)
                ->value('id');
        });

        if ($providerId) {
            $providerModel = $this->getProviderModelById($providerId);
            if ($providerModel && $providerModel->status === 'active') {
                return $this->createProviderAdapter($providerModel);
            }
        }

        // Fallback to active DB provider if non-default exists
        $fallbackProvider = AiProvider::withoutGlobalScopes()
            ->where('status', 'active')
            ->first();

        if ($fallbackProvider) {
            return $this->createProviderAdapter($fallbackProvider);
        }

        // Environment variable fallback for backward compatibility
        return $this->createEnvFallbackProvider();
    }

    /**
     * Instantiate AIProviderInterface adapter for a given AiProvider Eloquent model.
     */
    public function createProviderAdapter(AiProvider $providerModel): AIProviderInterface
    {
        $apiKey = $this->secretManager->getSecret($providerModel->id) ?? '';

        $config = [
            'api_key' => $apiKey,
            'model' => $providerModel->model,
            'base_url' => $providerModel->base_url,
            'temperature' => $providerModel->temperature ?? 0.7,
            'max_tokens' => $providerModel->max_tokens ?? 2048,
            'timeout' => $providerModel->timeout ?? 30,
            'organization_id' => $providerModel->organization_id,
            'project_id' => $providerModel->project_id,
        ];

        return match ($providerModel->provider_type) {
            'gemini' => new GeminiProvider($config),
            'openai' => new OpenAIProvider($config),
            default => new GeminiProvider($config),
        };
    }

    /**
     * Create fallback provider using .env configuration.
     */
    protected function createEnvFallbackProvider(): AIProviderInterface
    {
        return new GeminiProvider([
            'api_key' => config('services.gemini.api_key', ''),
            'model' => config('services.gemini.model', 'gemini-2.0-flash'),
            'temperature' => 0.7,
            'max_tokens' => 2048,
            'timeout' => 30,
        ]);
    }

    /**
     * Fetch AiProvider model by ID with caching.
     */
    protected function getProviderModelById(int $id): ?AiProvider
    {
        return Cache::remember("ai_provider_model_{$id}", 300, function () use ($id) {
            return AiProvider::withoutGlobalScopes()->find($id);
        });
    }

    /**
     * Clear all cached AI provider resolutions.
     */
    public function clearCache(int $tenantId = 1): void
    {
        Cache::forget("ai_default_provider_{$tenantId}");
        
        $features = ['farmer_chat', 'plant_diagnosis', 'input_label_check', 'agronomist_kb', 'market_analysis', 'document_analysis'];
        foreach ($features as $f) {
            Cache::forget("ai_feature_route_{$tenantId}_{$f}");
        }

        $providers = AiProvider::withoutGlobalScopes()->pluck('id');
        foreach ($providers as $id) {
            Cache::forget("ai_provider_model_{$id}");
        }
    }

    /**
     * Record successful API call in ai_usage_logs.
     */
    protected function logUsage(AIProviderInterface $providerAdapter, string $feature, AIResponse $response, ?int $userId, string $status): void
    {
        try {
            $providerModelId = null;
            if ($providerAdapter instanceof GeminiProvider || $providerAdapter instanceof OpenAIProvider) {
                // Find matching provider ID if active in DB
            }

            AiUsageLog::create([
                'tenant_id' => 1,
                'user_id' => $userId,
                'feature' => $feature,
                'provider_type' => $providerAdapter->getProviderType(),
                'model' => $response->model,
                'prompt_tokens' => $response->promptTokens,
                'completion_tokens' => $response->completionTokens,
                'total_tokens' => $response->totalTokens,
                'latency_ms' => $response->latencyMs,
                'status' => 'success',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to log AI usage: ' . $e->getMessage());
        }
    }

    /**
     * Record failed API call in ai_usage_logs.
     */
    protected function logError(AIProviderInterface $providerAdapter, string $feature, string $errorMessage, int $latencyMs, ?int $userId): void
    {
        try {
            AiUsageLog::create([
                'tenant_id' => 1,
                'user_id' => $userId,
                'feature' => $feature,
                'provider_type' => $providerAdapter->getProviderType(),
                'model' => 'unknown',
                'latency_ms' => $latencyMs,
                'status' => 'error',
                'error_type' => substr($errorMessage, 0, 250),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to log AI error usage: ' . $e->getMessage());
        }
    }
}
