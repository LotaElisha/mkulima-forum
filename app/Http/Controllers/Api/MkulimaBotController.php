<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BotConversation;
use App\Services\MkulimaBotService;
use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Mkulima Bot — AI chatbot & farm advisor.
 *
 * POST /bot/chat                 send a message (creates conversation if none given)
 * GET  /bot/conversations        list my conversations
 * GET  /bot/conversations/{uuid} full message history
 * DELETE /bot/conversations/{uuid}
 */
class MkulimaBotController extends Controller
{
    public function __construct(private readonly MkulimaBotService $bot)
    {
    }

    public function chat(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
            'conversation_uuid' => ['nullable', 'string', 'exists:bot_conversations,uuid'],
            'language' => ['nullable', 'string', 'in:sw,en,lg,rw,fr'],
            'region' => ['nullable', 'string', 'max:64'],
        ]);

        if (!$this->bot->isConfigured()) {
            return response()->json([
                'message' => 'Mkulima Bot haipatikani kwa sasa. Tafadhali jaribu tena baadaye.',
            ], 503);
        }

        if (!empty($validated['conversation_uuid'])) {
            $conversation = BotConversation::where('uuid', $validated['conversation_uuid'])
                ->where('user_id', $user->id)
                ->firstOrFail();
        } else {
            $conversation = BotConversation::create([
                'tenant_id' => $user->tenant_id,
                'user_id' => $user->id,
                'title' => Str::limit($validated['message'], 80),
                'language' => $validated['language'] ?? $user->preferred_language ?? 'sw',
            ]);
        }

        // Optional weather grounding — best effort, never blocks the chat.
        $weather = null;
        if (!empty($validated['region'])) {
            try {
                $weather = app(WeatherService::class)->getCurrentWeather($validated['region']);
            } catch (\Exception) {
                $weather = null;
            }
        }

        $reply = $this->bot->reply(
            $conversation,
            $validated['message'],
            $validated['region'] ?? null,
            $weather,
        );

        if ($reply === null) {
            return response()->json([
                'message' => 'Mkulima Bot imeshindwa kujibu kwa sasa. Tafadhali jaribu tena.',
            ], 503);
        }

        // Persist the exchange only after a successful reply, so failed
        // turns don't pollute the history.
        $conversation->messages()->create([
            'role' => 'user',
            'content' => $validated['message'],
        ]);
        $botMessage = $conversation->messages()->create([
            'role' => 'model',
            'content' => $reply['text'],
            'metadata' => [
                'sources' => $reply['sources'],
                'model' => config('services.gemini.model'),
            ],
        ]);
        $conversation->touch();

        return response()->json([
            'conversation_uuid' => $conversation->uuid,
            'reply' => $reply['text'],
            'sources' => $reply['sources'],
            'message_id' => $botMessage->id,
        ]);
    }

    public function conversations(Request $request): JsonResponse
    {
        $conversations = BotConversation::where('user_id', $request->user()->id)
            ->orderByDesc('updated_at')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'conversations' => $conversations->items(),
            'pagination' => [
                'current_page' => $conversations->currentPage(),
                'last_page' => $conversations->lastPage(),
                'total' => $conversations->total(),
            ],
        ]);
    }

    public function show(Request $request, string $uuid): JsonResponse
    {
        $conversation = BotConversation::with('messages')
            ->where('uuid', $uuid)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return response()->json(['conversation' => $conversation]);
    }

    public function destroy(Request $request, string $uuid): JsonResponse
    {
        BotConversation::where('uuid', $uuid)
            ->where('user_id', $request->user()->id)
            ->firstOrFail()
            ->delete();

        return response()->json(['message' => 'Mazungumzo yamefutwa.']);
    }
}
