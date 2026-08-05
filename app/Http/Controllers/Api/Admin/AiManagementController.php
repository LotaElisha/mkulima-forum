<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BotConversation;
use App\Models\DiseaseScan;
use App\Models\KbDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AiManagementController extends Controller
{
    // ─── OVERVIEW STATS ──────────────────────────────────────────────────────

    public function stats(): JsonResponse
    {
        $totalScans       = DiseaseScan::count();
        $successfulScans  = DiseaseScan::where('status', 'completed')->count();
        $failedScans      = DiseaseScan::where('status', 'failed')->count();
        $scansThisMonth   = DiseaseScan::whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)->count();

        $totalConversations = BotConversation::count();
        $totalKbDocs        = KbDocument::count();
        $verifiedKbDocs     = KbDocument::where('is_verified', true)->count();

        $topDiseases = DiseaseScan::where('status', 'completed')
            ->whereNotNull('disease_name')
            ->selectRaw('disease_name, COUNT(*) as count')
            ->groupBy('disease_name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        return response()->json([
            'scans' => [
                'total'        => $totalScans,
                'successful'   => $successfulScans,
                'failed'       => $failedScans,
                'this_month'   => $scansThisMonth,
                'success_rate' => $totalScans > 0 ? round(($successfulScans / $totalScans) * 100, 1) : 0,
            ],
            'bot' => [
                'total_conversations' => $totalConversations,
            ],
            'knowledge_base' => [
                'total'    => $totalKbDocs,
                'verified' => $verifiedKbDocs,
            ],
            'top_diseases' => $topDiseases,
            'gemini_model' => config('services.gemini.model', 'gemini-2.0-flash'),
            'gemini_configured' => !empty(config('services.gemini.api_key')),
        ]);
    }

    // ─── DISEASE SCANS ───────────────────────────────────────────────────────

    public function scans(Request $request): JsonResponse
    {
        $query = DiseaseScan::with('user:id,name,phone')
            ->latest();

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->has('disease')) {
            $query->where('disease_name', 'like', '%' . $request->input('disease') . '%');
        }
        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        $scans = $query->paginate(30);

        return response()->json(['scans' => $scans]);
    }

    public function showScan(string $uuid): JsonResponse
    {
        $scan = DiseaseScan::with('user:id,name,phone,uuid')
            ->where('uuid', $uuid)
            ->firstOrFail();

        return response()->json(['scan' => $scan]);
    }

    public function deleteScan(string $uuid): JsonResponse
    {
        $scan = DiseaseScan::where('uuid', $uuid)->firstOrFail();
        $scan->delete();

        return response()->json(['message' => 'Scan deleted successfully.']);
    }

    // ─── BOT CONVERSATIONS ────────────────────────────────────────────────────

    public function conversations(Request $request): JsonResponse
    {
        $query = BotConversation::with(['user:id,name,phone', 'messages'])
            ->latest();

        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        $conversations = $query->paginate(30);

        return response()->json(['conversations' => $conversations]);
    }

    public function showConversation(string $uuid): JsonResponse
    {
        $conversation = BotConversation::with(['user:id,name,phone', 'messages'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return response()->json(['conversation' => $conversation]);
    }

    public function deleteConversation(string $uuid): JsonResponse
    {
        $conversation = BotConversation::where('uuid', $uuid)->firstOrFail();
        $conversation->messages()->delete();
        $conversation->delete();

        return response()->json(['message' => 'Conversation deleted successfully.']);
    }

    // ─── KNOWLEDGE BASE ───────────────────────────────────────────────────────

    public function kbDocuments(Request $request): JsonResponse
    {
        $query = KbDocument::latest();

        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }
        if ($request->has('language')) {
            $query->where('language', $request->input('language'));
        }
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }
        if ($request->has('verified')) {
            $query->where('is_verified', $request->boolean('verified'));
        }

        $docs = $query->paginate(20);

        return response()->json(['documents' => $docs]);
    }

    public function createKbDocument(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'    => ['required', 'string', 'max:255'],
            'content'  => ['required', 'string'],
            'category' => ['required', 'string', 'in:crop_disease,pest_control,soil_health,irrigation,fertilization,market_prices,weather,general'],
            'language' => ['required', 'string', 'in:sw,en,lg,rw,fr'],
            'source'   => ['nullable', 'string', 'max:255'],
            'is_verified' => ['boolean'],
        ]);

        $validated['tenant_id']    = 1;
        $validated['uuid']         = Str::uuid();
        $validated['published_at'] = now();
        $validated['is_verified']  = $validated['is_verified'] ?? false;

        $doc = KbDocument::create($validated);

        return response()->json([
            'message'  => 'Knowledge base document created.',
            'document' => $doc,
        ], 201);
    }

    public function updateKbDocument(Request $request, string $uuid): JsonResponse
    {
        $doc = KbDocument::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'title'       => ['sometimes', 'string', 'max:255'],
            'content'     => ['sometimes', 'string'],
            'category'    => ['sometimes', 'string', 'in:crop_disease,pest_control,soil_health,irrigation,fertilization,market_prices,weather,general'],
            'language'    => ['sometimes', 'string', 'in:sw,en,lg,rw,fr'],
            'source'      => ['nullable', 'string', 'max:255'],
            'is_verified' => ['boolean'],
        ]);

        $doc->update($validated);

        return response()->json([
            'message'  => 'Document updated successfully.',
            'document' => $doc,
        ]);
    }

    public function deleteKbDocument(string $uuid): JsonResponse
    {
        $doc = KbDocument::where('uuid', $uuid)->firstOrFail();
        $doc->delete();

        return response()->json(['message' => 'Document deleted successfully.']);
    }

    // ─── AI CONFIG (stored as LandingSettings-style env wrapper) ────────────

    public function getAiConfig(): JsonResponse
    {
        return response()->json([
            'model'           => config('services.gemini.model', 'gemini-2.0-flash'),
            'configured'      => !empty(config('services.gemini.api_key')),
            'api_key_preview' => $this->maskApiKey(config('services.gemini.api_key')),
        ]);
    }

    private function maskApiKey(?string $key): string
    {
        if (!$key) return 'Not configured';
        return substr($key, 0, 8) . str_repeat('*', max(0, strlen($key) - 12)) . substr($key, -4);
    }
}
