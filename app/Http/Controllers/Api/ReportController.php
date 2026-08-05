<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Models\Product;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * File a report against a thread, reply, product or user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|in:' . implode(',', array_keys(Report::TYPES)),
            'id' => 'required',
            'reason' => 'required|string|in:' . implode(',', Report::REASONS),
            'details' => 'nullable|string|max:2000',
        ]);

        $modelClass = Report::TYPES[$validated['type']];

        // Targets are addressed by uuid where the model has one, else by id.
        $target = $modelClass::where('uuid', $validated['id'])->first()
            ?? $modelClass::find($validated['id']);

        if (!$target) {
            return response()->json(['message' => 'Maudhui hayajapatikana.'], 404);
        }

        if ($validated['type'] === 'user' && $target->id === $request->user()->id) {
            return response()->json(['message' => 'Huwezi kujiripoti mwenyewe.'], 422);
        }

        $existing = Report::where('reporter_id', $request->user()->id)
            ->where('reportable_type', $validated['type'])
            ->where('reportable_id', $target->id)
            ->where('status', 'pending')
            ->exists();

        if ($existing) {
            return response()->json([
                'message' => 'Tayari umeripoti maudhui haya. Yanasubiri uhakiki.',
            ], 422);
        }

        $report = Report::create([
            'reporter_id' => $request->user()->id,
            'reportable_type' => $validated['type'],
            'reportable_id' => $target->id,
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Ripoti imepokelewa. Asante kwa kutusaidia kulinda jukwaa.',
            'report' => ['uuid' => $report->uuid, 'status' => $report->status],
        ], 201);
    }

    // ---------------- Admin moderation queue ----------------

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'nullable|string|in:pending,resolved,dismissed',
            'type' => 'nullable|string|in:' . implode(',', array_keys(Report::TYPES)),
        ]);

        $reports = Report::with('reporter:id,uuid,name,role')
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->input('type'), fn ($q, $t) => $q->where('reportable_type', $t))
            ->latest()
            ->paginate(20);

        $reports->getCollection()->transform(function ($report) {
            $target = $report->reportable();

            return [
                'uuid' => $report->uuid,
                'type' => $report->reportable_type,
                'reason' => $report->reason,
                'details' => $report->details,
                'status' => $report->status,
                'reporter' => $report->reporter?->only(['uuid', 'name', 'role']),
                'target_preview' => $this->preview($report->reportable_type, $target),
                'resolution_action' => $report->resolution_action,
                'resolution_notes' => $report->resolution_notes,
                'created_at' => $report->created_at->toIso8601String(),
                'resolved_at' => $report->resolved_at?->toIso8601String(),
            ];
        });

        return response()->json($reports);
    }

    /**
     * Resolve a report, optionally hiding the offending content.
     */
    public function resolve(Request $request, string $uuid): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|string|in:none,content_hidden,listing_disabled',
            'notes' => 'nullable|string|max:2000',
        ]);

        $report = Report::where('uuid', $uuid)->firstOrFail();

        if ($report->status !== 'pending') {
            return response()->json(['message' => 'Ripoti hii tayari imeshughulikiwa.'], 422);
        }

        if ($validated['action'] !== 'none') {
            $this->applyAction($report, $validated['action']);
        }

        $report->update([
            'status' => 'resolved',
            'resolved_by' => $request->user()->id,
            'resolution_action' => $validated['action'],
            'resolution_notes' => $validated['notes'] ?? null,
            'resolved_at' => now(),
        ]);

        return response()->json(['message' => 'Ripoti imeshughulikiwa.', 'report' => $report->fresh()]);
    }

    public function dismiss(Request $request, string $uuid): JsonResponse
    {
        $report = Report::where('uuid', $uuid)->firstOrFail();

        if ($report->status !== 'pending') {
            return response()->json(['message' => 'Ripoti hii tayari imeshughulikiwa.'], 422);
        }

        $report->update([
            'status' => 'dismissed',
            'resolved_by' => $request->user()->id,
            'resolution_action' => 'none',
            'resolution_notes' => $request->input('notes'),
            'resolved_at' => now(),
        ]);

        return response()->json(['message' => 'Ripoti imekataliwa.']);
    }

    protected function applyAction(Report $report, string $action): void
    {
        $target = $report->reportable();
        if (!$target) {
            return;
        }

        match ($report->reportable_type) {
            'forum_thread', 'forum_reply' => $target->update(['status' => 'hidden']),
            'product' => $target->update(['status' => 'inactive']),
            'user' => $target->update(['status' => 'suspended']),
            default => null,
        };
    }

    protected function preview(string $type, $target): ?array
    {
        if (!$target) {
            return null;
        }

        return match ($type) {
            'forum_thread' => ['uuid' => $target->uuid, 'title' => $target->title, 'status' => $target->status],
            'forum_reply' => ['id' => $target->id, 'body' => str($target->body)->limit(120)->toString(), 'status' => $target->status],
            'product' => ['uuid' => $target->uuid, 'name' => $target->name, 'status' => $target->status],
            'user' => ['uuid' => $target->uuid, 'name' => $target->name, 'role' => $target->role, 'status' => $target->status],
            default => null,
        };
    }
}
