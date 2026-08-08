<?php

namespace App\Services\AI\Contracts;

use App\Services\AI\DTO\AIResponse;

interface AIProviderInterface
{
    /**
     * Generate text from prompt / chat message trajectory.
     * $messages format: [['role' => 'user'|'system'|'assistant', 'content' => '...']]
     */
    public function generateText(array $messages, array $options = []): AIResponse;

    /**
     * Generate structured JSON output matching a given schema or expectation.
     */
    public function generateStructuredData(array $messages, array $schema = [], array $options = []): AIResponse;

    /**
     * Perform vision analysis on an image file.
     */
    public function analyzeImage(string $imagePath, string $prompt, array $options = []): AIResponse;

    /**
     * Perform a lightweight health check to test API key and connectivity.
     */
    public function healthCheck(): array;

    /**
     * Retrieve list of supported models dynamically from provider or fallback catalog.
     */
    public function getModels(): array;

    /**
     * Returns provider type key (e.g. 'gemini', 'openai', 'claude').
     */
    public function getProviderType(): string;
}
