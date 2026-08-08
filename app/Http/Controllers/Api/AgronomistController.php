<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KbDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AgronomistController extends Controller
{
    /**
     * Ask AI Agronomist
     */
    public function ask(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'question' => ['required', 'string', 'max:2000'],
            'language' => ['nullable', 'string', 'in:sw,en,lg,rw,fr'],
            'context' => ['nullable', 'array'],
        ]);

        $language = $validated['language'] ?? $user->preferred_language ?? 'sw';
        $question = $validated['question'];

        // Search knowledge base for relevant documents
        $relevantDocs = $this->searchKnowledgeBase($question, $user->tenant_id, $language);

        // Build context from KB
        $context = $this->buildContext($relevantDocs);

        // Query Gemini with RAG
        $answer = $this->queryGemini($question, $context, $language);

        return response()->json([
            'answer' => $answer['text'],
            'sources' => $relevantDocs->map(function ($doc) {
                return [
                    'title' => $doc->title,
                    'source' => $doc->source,
                    'category' => $doc->category,
                ];
            }),
            'language' => $language,
            'confidence' => $answer['confidence'] ?? 'medium',
        ]);
    }

    /**
     * Search knowledge base
     */
    public function searchKb(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenantId = $user ? $user->tenant_id : 1; // Default tenant for public access

        $validated = $request->validate([
            'query' => ['required', 'string', 'min:2'],
            'category' => ['nullable', 'string'],
            'source' => ['nullable', 'string', 'in:tari,fao,kephis,custom'],
        ]);

        $query = KbDocument::where('tenant_id', $tenantId)
            ->where('is_verified', true)
            ->where(function ($q) use ($validated) {
                $q->where('title', 'ilike', "%{$validated['query']}%")
                  ->orWhere('content', 'ilike', "%{$validated['query']}%");
            });

        if (!empty($validated['category'])) {
            $query->where('category', $validated['category']);
        }

        if (!empty($validated['source'])) {
            $query->where('source', $validated['source']);
        }

        $docs = $query->orderBy('published_at', 'desc')
            ->paginate($request->input('per_page', 10));

        return response()->json([
            'documents' => $docs->items(),
            'pagination' => [
                'current_page' => $docs->currentPage(),
                'last_page' => $docs->lastPage(),
                'per_page' => $docs->perPage(),
                'total' => $docs->total(),
            ],
        ]);
    }

    /**
     * Get KB document
     */
    public function kbDocument(string $uuid): JsonResponse
    {
        $doc = KbDocument::where('uuid', $uuid)
            ->where('is_verified', true)
            ->first();

        if (!$doc) {
            return response()->json([
                'message' => 'Document not found',
            ], 404);
        }

        return response()->json([
            'document' => [
                'uuid' => $doc->uuid,
                'title' => $doc->title,
                'content' => $doc->content,
                'source' => $doc->source,
                'category' => $doc->category,
                'language' => $doc->language,
                'published_at' => $doc->published_at,
            ],
        ]);
    }

    /**
     * Search knowledge base for relevant documents
     */
    private function searchKnowledgeBase(string $question, int $tenantId, string $language)
    {
        return KbDocument::where('tenant_id', $tenantId)
            ->where('is_verified', true)
            ->where(function ($q) use ($question) {
                $q->where('title', 'ilike', "%{$question}%")
                  ->orWhere('content', 'ilike', "%{$question}%");
            })
            ->where('language', $language)
            ->orderBy('published_at', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Build context string from documents
     */
    private function buildContext($docs): string
    {
        $context = "";
        foreach ($docs as $doc) {
            $context .= "Source: {$doc->title}\n";
            $context .= substr($doc->content, 0, 500) . "\n\n";
        }
        return $context;
    }

    /**
     * Query Gemini with RAG context
     */
    private function queryGemini(string $question, string $context, string $language): array
    {
        $langName = match ($language) {
            'sw' => 'Swahili',
            'lg' => 'Luganda',
            'rw' => 'Kinyarwanda',
            'fr' => 'French',
            default => 'English',
        };

        $systemInstruction = "You are MkulimaForum AI Agronomist, an expert agricultural assistant for East African farmers. Answer questions in {$langName}. Use the provided context from agricultural knowledge base. If context doesn't contain enough info, say so and provide general advice. Keep answers practical and actionable for smallholder farmers.";

        $prompt = "Context:\n{$context}\n\nQuestion: {$question}";

        try {
            $aiService = app(\App\Services\AI\AIService::class);
            $aiResponse = $aiService->generateText(
                'agronomist_kb',
                [['role' => 'user', 'content' => $prompt]],
                ['system_instruction' => $systemInstruction, 'temperature' => 0.3],
                auth()->id()
            );

            if (!empty($aiResponse->text)) {
                return [
                    'text' => $aiResponse->text,
                    'confidence' => 'high',
                ];
            }
        } catch (\Exception $e) {
            \Log::error('AI agronomist query failed: ' . $e->getMessage());
        }

        return [
            'text' => 'Unable to generate answer at this time. Please try again.',
            'confidence' => 'low',
        ];
    }
}
