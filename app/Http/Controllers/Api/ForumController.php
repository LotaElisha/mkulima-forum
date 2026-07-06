<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ForumCategory;
use App\Models\ForumThread;
use App\Models\ForumReply;
use App\Models\ForumVote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ForumController extends Controller
{
    /**
     * List forum categories
     */
    public function categories(Request $request): JsonResponse
    {
        $query = ForumCategory::where('is_active', true);

        // Regional sub-forums: ?region=Mbeya returns national + regional categories.
        if ($request->filled('region')) {
            $query->where(function ($q) use ($request) {
                $q->whereNull('region')->orWhere('region', $request->input('region'));
            });
        }

        $categories = $query->orderBy('sort_order')
            ->get(['id', 'name', 'slug', 'description', 'icon', 'requires_expert', 'region']);

        return response()->json([
            'categories' => $categories,
        ]);
    }

    /**
     * List threads
     */
    public function threads(Request $request): JsonResponse
    {
        $query = ForumThread::with([
                'user:id,uuid,name,avatar,is_verified_expert,expert_title',
                'category:id,name,slug',
            ])
            ->where('status', 'active');

        if ($request->filled('region')) {
            $query->where('region', $request->input('region'));
        }

        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->input('category'));
            });
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                  ->orWhere('body', 'ilike', "%{$search}%");
            });
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $threads = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'threads' => $threads->items(),
            'pagination' => [
                'current_page' => $threads->currentPage(),
                'last_page' => $threads->lastPage(),
                'per_page' => $threads->perPage(),
                'total' => $threads->total(),
            ],
        ]);
    }

    /**
     * Create thread
     */
    public function createThread(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'forum_category_id' => ['required', 'exists:forum_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'media' => ['nullable', 'array'],
            'media.*' => ['file', 'max:5120'],
            'language' => ['nullable', 'string', 'in:sw,en,lg,rw,fr'],
            'region' => ['nullable', 'string', 'max:64'],
        ]);

        $validated['user_id'] = $user->id;
        $validated['tenant_id'] = $user->tenant_id;
        $validated['language'] = $validated['language'] ?? $user->preferred_language ?? 'sw';

        if ($request->hasFile('media')) {
            $media = [];
            foreach ($request->file('media') as $file) {
                $media[] = $file->store('forum/media', 'public');
            }
            $validated['media'] = $media;
        }

        $thread = ForumThread::create($validated);

        return response()->json([
            'message' => 'Thread created successfully.',
            'thread' => $thread->load(['user:id,uuid,name,avatar', 'category:id,name,slug']),
        ], 201);
    }

    /**
     * Get single thread with replies
     */
    public function thread(string $uuid): JsonResponse
    {
        $thread = ForumThread::with([
            'user:id,uuid,name,avatar,role',
            'category:id,name,slug',
            'replies' => function ($q) {
                $q->where('status', 'active')
                  ->with(['user:id,uuid,name,avatar,role'])
                  ->orderBy('created_at', 'asc');
            }
        ])
            ->where('uuid', $uuid)
            ->where('status', 'active')
            ->firstOrFail();

        // Increment view count
        $thread->increment('view_count');

        return response()->json([
            'thread' => $thread,
        ]);
    }

    /**
     * Create reply
     */
    public function createReply(Request $request, string $threadUuid): JsonResponse
    {
        $user = $request->user();
        $thread = ForumThread::where('uuid', $threadUuid)->firstOrFail();

        if ($thread->is_locked) {
            return response()->json([
                'message' => 'This thread is locked.',
            ], 403);
        }

        $validated = $request->validate([
            'body' => ['required', 'string'],
            'parent_id' => ['nullable', 'exists:forum_replies,id'],
            'media' => ['nullable', 'array'],
            'media.*' => ['file', 'max:5120'],
        ]);

        $validated['forum_thread_id'] = $thread->id;
        $validated['user_id'] = $user->id;
        $validated['tenant_id'] = $user->tenant_id;
        $validated['is_expert_answer'] = $user->isExpert();

        if ($request->hasFile('media')) {
            $media = [];
            foreach ($request->file('media') as $file) {
                $media[] = $file->store('forum/replies', 'public');
            }
            $validated['media'] = $media;
        }

        $reply = ForumReply::create($validated);

        // Update thread reply count
        $thread->increment('reply_count');

        return response()->json([
            'message' => 'Reply posted successfully.',
            'reply' => $reply->load(['user:id,uuid,name,avatar,role']),
        ], 201);
    }

    /**
     * Toggle upvote on a thread (one vote per user).
     */
    public function upvoteThread(Request $request, string $uuid): JsonResponse
    {
        $thread = ForumThread::where('uuid', $uuid)->firstOrFail();

        return $this->toggleVote($request->user()->id, $thread);
    }

    /**
     * Toggle upvote on a reply (one vote per user).
     */
    public function upvoteReply(Request $request, int $replyId): JsonResponse
    {
        $reply = ForumReply::findOrFail($replyId);

        return $this->toggleVote($request->user()->id, $reply);
    }

    /**
     * Mark a reply as the accepted/expert answer. Allowed for the thread
     * author or a verified expert/admin.
     */
    public function markExpertAnswer(Request $request, int $replyId): JsonResponse
    {
        $user = $request->user();
        $reply = ForumReply::with('thread')->findOrFail($replyId);
        $thread = $reply->thread;

        $canMark = $thread->user_id === $user->id
            || $user->is_verified_expert
            || $user->hasRole('admin');

        if (!$canMark) {
            return response()->json(['message' => 'Not authorized.'], 403);
        }

        $reply->update(['is_expert_answer' => true]);
        $thread->update(['is_verified_answer' => true]);

        return response()->json(['message' => 'Reply marked as verified answer.']);
    }

    private function toggleVote(int $userId, ForumThread|ForumReply $votable): JsonResponse
    {
        $existing = ForumVote::where('user_id', $userId)
            ->where('votable_type', $votable->getMorphClass())
            ->where('votable_id', $votable->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $votable->decrement('upvote_count');
            $voted = false;
        } else {
            ForumVote::create([
                'user_id' => $userId,
                'votable_type' => $votable->getMorphClass(),
                'votable_id' => $votable->id,
            ]);
            $votable->increment('upvote_count');
            $voted = true;
        }

        return response()->json([
            'message' => $voted ? 'Upvoted.' : 'Upvote removed.',
            'voted' => $voted,
            'upvote_count' => $votable->fresh()->upvote_count,
        ]);
    }
}
