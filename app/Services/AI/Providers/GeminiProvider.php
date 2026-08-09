<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\DTO\AIResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider implements AIProviderInterface
{
    protected string $apiKey;

    protected string $model;

    protected string $baseUrl;

    protected float $temperature;

    protected int $maxTokens;

    protected int $timeout;

    public function __construct(array $config)
    {
        $this->apiKey = $config['api_key'] ?? '';
        $this->model = $config['model'] ?? 'gemini-3-flash-preview';
        $this->baseUrl = rtrim($config['base_url'] ?? 'https://generativelanguage.googleapis.com', '/');
        $this->temperature = (float) ($config['temperature'] ?? 0.7);
        $this->maxTokens = (int) ($config['max_tokens'] ?? 2048);
        $this->timeout = (int) ($config['timeout'] ?? 30);
    }

    public function getProviderType(): string
    {
        return 'gemini';
    }

    public function generateText(array $messages, array $options = []): AIResponse
    {
        $startTime = microtime(true);
        $model = $options['model'] ?? $this->model;
        $temperature = (float) ($options['temperature'] ?? $this->temperature);
        $maxTokens = (int) ($options['max_tokens'] ?? $this->maxTokens);

        $contents = $this->formatMessages($messages);

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => $temperature,
                'maxOutputTokens' => $maxTokens,
            ],
        ];

        if (! empty($options['enable_grounding']) || ! empty($options['google_search'])) {
            $payload['tools'] = [
                ['googleSearch' => new \stdClass],
            ];
        }

        if (! empty($options['system_instruction'])) {
            $payload['system_instruction'] = [
                'parts' => [['text' => $options['system_instruction']]],
            ];
        }

        $url = "{$this->baseUrl}/v1beta/models/{$model}:generateContent?key={$this->apiKey}";

        $response = Http::timeout($this->timeout)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $payload);

        $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

        if (! $response->successful()) {
            $errorMsg = "Gemini API error ({$response->status()}): ".$response->body();
            Log::error($errorMsg);
            throw new \RuntimeException("Gemini API call failed with status {$response->status()}");
        }

        $data = $response->json();
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $usage = $data['usageMetadata'] ?? [];

        return new AIResponse(
            text: $text,
            structuredData: null,
            promptTokens: $usage['promptTokenCount'] ?? null,
            completionTokens: $usage['candidatesTokenCount'] ?? null,
            totalTokens: $usage['totalTokenCount'] ?? null,
            model: $model,
            provider: 'gemini',
            latencyMs: $latencyMs,
            rawResponse: $data
        );
    }

    public function generateStructuredData(array $messages, array $schema = [], array $options = []): AIResponse
    {
        $startTime = microtime(true);
        $model = $options['model'] ?? $this->model;
        $temperature = (float) ($options['temperature'] ?? $this->temperature);

        $contents = $this->formatMessages($messages);

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'response_mime_type' => 'application/json',
                'temperature' => $temperature,
            ],
        ];

        if (! empty($options['system_instruction'])) {
            $payload['system_instruction'] = [
                'parts' => [['text' => $options['system_instruction']]],
            ];
        }

        $url = "{$this->baseUrl}/v1beta/models/{$model}:generateContent?key={$this->apiKey}";

        $response = Http::timeout($this->timeout)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $payload);

        $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

        if (! $response->successful()) {
            throw new \RuntimeException("Gemini API call failed with status {$response->status()}");
        }

        $data = $response->json();
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $structuredData = json_decode($text, true) ?: null;
        $usage = $data['usageMetadata'] ?? [];

        return new AIResponse(
            text: $text,
            structuredData: $structuredData,
            promptTokens: $usage['promptTokenCount'] ?? null,
            completionTokens: $usage['candidatesTokenCount'] ?? null,
            totalTokens: $usage['totalTokenCount'] ?? null,
            model: $model,
            provider: 'gemini',
            latencyMs: $latencyMs,
            rawResponse: $data
        );
    }

    public function analyzeImage(string $imagePath, string $prompt, array $options = []): AIResponse
    {
        $startTime = microtime(true);
        $model = $options['model'] ?? $this->model;

        if (! file_exists($imagePath)) {
            throw new \InvalidArgumentException("Image file not found at path: {$imagePath}");
        }

        $imageData = base64_encode(file_get_contents($imagePath));
        $mimeType = mime_content_type($imagePath) ?: 'image/jpeg';

        $payload = [
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
        ];

        if (! empty($options['require_json'])) {
            $payload['generationConfig'] = ['response_mime_type' => 'application/json'];
        }

        $url = "{$this->baseUrl}/v1beta/models/{$model}:generateContent?key={$this->apiKey}";

        $response = Http::timeout($this->timeout)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $payload);

        $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

        if (! $response->successful()) {
            throw new \RuntimeException("Gemini Vision API call failed with status {$response->status()}");
        }

        $data = $response->json();
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $structuredData = json_decode($text, true) ?: null;
        $usage = $data['usageMetadata'] ?? [];

        return new AIResponse(
            text: $text,
            structuredData: $structuredData,
            promptTokens: $usage['promptTokenCount'] ?? null,
            completionTokens: $usage['candidatesTokenCount'] ?? null,
            totalTokens: $usage['totalTokenCount'] ?? null,
            model: $model,
            provider: 'gemini',
            latencyMs: $latencyMs,
            rawResponse: $data
        );
    }

    public function healthCheck(): array
    {
        $startTime = microtime(true);
        try {
            if (empty($this->apiKey)) {
                return [
                    'success' => false,
                    'latency_ms' => 0,
                    'message' => 'API Key is missing or empty.',
                    'model' => $this->model,
                ];
            }

            // Small test prompt
            $url = "{$this->baseUrl}/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";
            $response = Http::timeout(10)->post($url, [
                'contents' => [
                    ['parts' => [['text' => 'Ping']]],
                ],
                'generationConfig' => ['maxOutputTokens' => 5],
            ]);

            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'latency_ms' => $latencyMs,
                    'message' => 'Connection Successful',
                    'model' => $this->model,
                ];
            }

            $status = $response->status();
            $reason = match ($status) {
                400 => 'Invalid request payload or model',
                401, 403 => 'Invalid or expired API Key',
                404 => 'Model endpoint not found',
                429 => 'Rate limit or quota exceeded',
                default => "HTTP Server Error ({$status})",
            };

            return [
                'success' => false,
                'latency_ms' => $latencyMs,
                'message' => "Connection Failed: {$reason}",
                'model' => $this->model,
            ];
        } catch (\Throwable $e) {
            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

            return [
                'success' => false,
                'latency_ms' => $latencyMs,
                'message' => 'Connection Failed: '.$e->getMessage(),
                'model' => $this->model,
            ];
        }
    }

    public function getModels(): array
    {
        $defaultModels = [
            ['id' => 'gemini-3-flash-preview', 'name' => 'Gemini 3 Flash (Next-Gen Ultra Fast & Multimodal)', 'supports_vision' => true],
            ['id' => 'gemini-3-pro-preview', 'name' => 'Gemini 3 Pro (Complex Reasoning & Search Grounding)', 'supports_vision' => true],
            ['id' => 'gemini-2.0-flash', 'name' => 'Gemini 2.0 Flash (Fast & Multimodal)', 'supports_vision' => true],
            ['id' => 'gemini-1.5-pro', 'name' => 'Gemini 1.5 Pro (Deep Context)', 'supports_vision' => true],
        ];

        if (empty($this->apiKey)) {
            return $defaultModels;
        }

        try {
            $url = "{$this->baseUrl}/v1beta/models?key={$this->apiKey}";
            $response = Http::timeout(10)->get($url);

            if ($response->successful()) {
                $modelsData = $response->json('models') ?? [];
                $fetchedModels = [];

                foreach ($modelsData as $item) {
                    $name = $item['name'] ?? '';
                    $cleanId = str_replace('models/', '', $name);
                    $methods = $item['supportedGenerationMethods'] ?? [];

                    if (in_array('generateContent', $methods, true)) {
                        $fetchedModels[] = [
                            'id' => $cleanId,
                            'name' => $item['displayName'] ?? $cleanId,
                            'supports_vision' => true,
                        ];
                    }
                }

                return ! empty($fetchedModels) ? $fetchedModels : $defaultModels;
            }
        } catch (\Throwable $e) {
            Log::warning('Gemini getModels failed: '.$e->getMessage());
        }

        return $defaultModels;
    }

    protected function formatMessages(array $messages): array
    {
        $formatted = [];
        foreach ($messages as $msg) {
            $role = match ($msg['role'] ?? 'user') {
                'assistant' => 'model',
                'model' => 'model',
                default => 'user',
            };

            $formatted[] = [
                'role' => $role,
                'parts' => [['text' => $msg['content'] ?? '']],
            ];
        }

        return $formatted;
    }
}
