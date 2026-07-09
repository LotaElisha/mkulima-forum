<?php

namespace App\Services;

use App\Models\BotConversation;
use App\Models\KbDocument;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Mkulima Bot — multi-turn AI farm advisor (Gemini).
 *
 * Grounding, per turn:
 *  - full conversation history (Gemini multi-turn `contents`)
 *  - top KB documents matching the new message (lightweight RAG)
 *  - optional region weather snapshot (best effort, never blocks)
 *
 * Honest failure: without an API key or on upstream error, returns null so
 * the controller can respond 503 — the bot never fabricates advice.
 */
class MkulimaBotService
{
    private const MAX_HISTORY_MESSAGES = 20;

    public function isConfigured(): bool
    {
        return (bool) config('services.gemini.api_key');
    }

    /**
     * @return array{text: string, sources: array}|null
     */
    public function reply(
        BotConversation $conversation,
        string $userMessage,
        ?string $region = null,
        ?array $weather = null,
    ): ?array {
        if (!$this->isConfigured()) {
            return null;
        }

        $kbDocs = $this->searchKb($userMessage, $conversation->tenant_id, $conversation->language);

        $contents = [];
        foreach ($conversation->messages()->latest('id')->limit(self::MAX_HISTORY_MESSAGES)->get()->reverse() as $msg) {
            $contents[] = [
                'role' => $msg->role,
                'parts' => [['text' => $msg->content]],
            ];
        }
        $contents[] = ['role' => 'user', 'parts' => [['text' => $userMessage]]];

        $model = config('services.gemini.model', 'gemini-2.0-flash');
        $apiKey = config('services.gemini.api_key');

        try {
            $response = Http::timeout(30)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                [
                    'system_instruction' => [
                        'parts' => [['text' => $this->systemPrompt($conversation->language, $region, $weather, $kbDocs)]],
                    ],
                    'contents' => $contents,
                    'generationConfig' => [
                        'temperature' => 0.4,
                        'maxOutputTokens' => 1024,
                    ],
                ],
            );

            if (!$response->successful()) {
                Log::warning('Mkulima Bot: Gemini returned '.$response->status());
                return null;
            }

            $text = $response->json('candidates.0.content.parts.0.text');
            if (!$text) {
                return null;
            }

            return [
                'text' => $text,
                'sources' => $kbDocs->map(fn ($doc) => [
                    'title' => $doc->title,
                    'source' => $doc->source,
                    'category' => $doc->category,
                ])->values()->all(),
            ];
        } catch (\Exception $e) {
            Log::error('Mkulima Bot: Gemini query failed: '.$e->getMessage());
            return null;
        }
    }

    private function searchKb(string $message, int $tenantId, string $language)
    {
        // Keyword RAG v1; pgvector semantic search is the Phase-3 upgrade.
        $terms = collect(preg_split('/\s+/', mb_strtolower($message)))
            ->filter(fn ($t) => mb_strlen($t) >= 4)
            ->take(6);

        if ($terms->isEmpty()) {
            return collect();
        }

        return KbDocument::where('tenant_id', $tenantId)
            ->where('is_verified', true)
            ->where(function ($q) use ($terms) {
                foreach ($terms as $term) {
                    $q->orWhereRaw('LOWER(title) LIKE ?', ["%{$term}%"])
                      ->orWhereRaw('LOWER(content) LIKE ?', ["%{$term}%"]);
                }
            })
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();
    }

    private function systemPrompt(string $language, ?string $region, ?array $weather, $kbDocs): string
    {
        $langName = match ($language) {
            'sw' => 'Swahili',
            'lg' => 'Luganda',
            'rw' => 'Kinyarwanda',
            'fr' => 'French',
            default => 'English',
        };

        $prompt = "You are Mkulima Bot, the friendly AI farm advisor inside the MkulimaForum app, "
            ."serving East African smallholder farmers. Reply in {$langName}. "
            ."Be practical, concise and actionable: quantities per acre, local crop names, "
            ."costs in TZS where relevant. Recommend consulting a verified agronomist "
            ."(bookable in the app under Huduma) for diagnoses you are unsure about. "
            ."Never invent prices, weather or regulations you do not know.";

        if ($region) {
            $prompt .= "\n\nThe farmer is in the {$region} region.";
        }

        if ($weather && !empty($weather['temperature'])) {
            $stale = !empty($weather['is_stale']) ? ' (cached, may be outdated)' : '';
            $prompt .= "\nCurrent weather{$stale}: {$weather['temperature']}°C, "
                .($weather['condition'] ?? 'unknown').', humidity '
                .($weather['humidity'] ?? '?').'%.';
        }

        if ($kbDocs->isNotEmpty()) {
            $prompt .= "\n\nVerified knowledge base excerpts (prefer these over general knowledge):\n";
            foreach ($kbDocs as $doc) {
                $prompt .= "- [{$doc->source}] {$doc->title}: ".mb_substr($doc->content, 0, 400)."\n";
            }
        }

        return $prompt;
    }
}
