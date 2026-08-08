<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\DTO\AIResponse;
use Illuminate\Support\Facades\Http;

class OpenAIProvider implements AIProviderInterface
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl;

    public function __construct(array $config)
    {
        $this->apiKey = $config['api_key'] ?? '';
        $this->model = $config['model'] ?? 'gpt-4o-mini';
        $this->baseUrl = rtrim($config['base_url'] ?? 'https://api.openai.com/v1', '/');
    }

    public function getProviderType(): string
    {
        return 'openai';
    }

    public function generateText(array $messages, array $options = []): AIResponse
    {
        $startTime = microtime(true);
        $model = $options['model'] ?? $this->model;

        $response = Http::withToken($this->apiKey)
            ->post("{$this->baseUrl}/chat/completions", [
                'model' => $model,
                'messages' => $messages,
            ]);

        $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

        if (!$response->successful()) {
            throw new \RuntimeException("OpenAI API call failed with status {$response->status()}");
        }

        $data = $response->json();
        $text = $data['choices'][0]['message']['content'] ?? '';
        $usage = $data['usage'] ?? [];

        return new AIResponse(
            text: $text,
            structuredData: json_decode($text, true) ?: null,
            promptTokens: $usage['prompt_tokens'] ?? null,
            completionTokens: $usage['completion_tokens'] ?? null,
            totalTokens: $usage['total_tokens'] ?? null,
            model: $model,
            provider: 'openai',
            latencyMs: $latencyMs,
            rawResponse: $data
        );
    }

    public function generateStructuredData(array $messages, array $schema = [], array $options = []): AIResponse
    {
        return $this->generateText($messages, $options);
    }

    public function analyzeImage(string $imagePath, string $prompt, array $options = []): AIResponse
    {
        $imageData = base64_encode(file_get_contents($imagePath));
        $mimeType = mime_content_type($imagePath) ?: 'image/jpeg';

        $messages = [
            [
                'role' => 'user',
                'content' => [
                    ['type' => 'text', 'text' => $prompt],
                    ['type' => 'image_url', 'image_url' => ['url' => "data:{$mimeType};base64,{$imageData}"]],
                ],
            ],
        ];

        return $this->generateText($messages, $options);
    }

    public function healthCheck(): array
    {
        $startTime = microtime(true);
        try {
            if (empty($this->apiKey)) {
                return ['success' => false, 'latency_ms' => 0, 'message' => 'API Key is missing', 'model' => $this->model];
            }
            $response = Http::withToken($this->apiKey)->get("{$this->baseUrl}/models");
            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);
            return [
                'success' => $response->successful(),
                'latency_ms' => $latencyMs,
                'message' => $response->successful() ? 'Connection Successful' : 'Connection Failed',
                'model' => $this->model,
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'latency_ms' => 0, 'message' => $e->getMessage(), 'model' => $this->model];
        }
    }

    public function getModels(): array
    {
        return [
            ['id' => 'gpt-4o-mini', 'name' => 'GPT-4o Mini', 'supports_vision' => true],
            ['id' => 'gpt-4o', 'name' => 'GPT-4o Omnimodel', 'supports_vision' => true],
        ];
    }
}
