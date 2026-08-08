<?php

namespace App\Services\AI\DTO;

class AIResponse
{
    public function __construct(
        public readonly string $text = '',
        public readonly ?array $structuredData = null,
        public readonly ?int $promptTokens = null,
        public readonly ?int $completionTokens = null,
        public readonly ?int $totalTokens = null,
        public readonly string $model = '',
        public readonly string $provider = '',
        public readonly int $latencyMs = 0,
        public readonly ?array $rawResponse = null
    ) {}

    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'structured_data' => $this->structuredData,
            'prompt_tokens' => $this->promptTokens,
            'completion_tokens' => $this->completionTokens,
            'total_tokens' => $this->totalTokens,
            'model' => $this->model,
            'provider' => $this->provider,
            'latency_ms' => $this->latencyMs,
        ];
    }
}
