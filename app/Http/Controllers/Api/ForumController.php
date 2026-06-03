<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ForumCategory;
use App\Models\ForumThread;
use App\Models\ForumReply;
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
        $categories = ForumCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug', 'description', 'icon', 'requires_expert']);

        return response()->json([
            'categories' => $categories,
        ]);
    }

    /**
     * List threads
     */
    public function threads(Request $request): JsonResponse
    {
        $query = ForumThread::with(['user:id,uuid,name,avatar', 'category:id,name,slug'])
            ->where('status', 'active');

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
     * Upvote thread
     */
    public function upvoteThread(string $uuid): JsonResponse
    {
        $thread = ForumThread::where('uuid', $uuid)->firstOrFail();
        $thread->increment('upvote_count');

        return response()->json([
            'message' => 'Thread upvoted.',
            'upvote_count' => $thread->upvote_count,
        ]);
    }

    /**
     * Upvote reply
     */
    public function upvoteReply(int $replyId): JsonResponse
    {
        $reply = ForumReply::findOrFail($replyId);
        $reply->increment('upvote_count');

        return response()->json([
            'message' => 'Reply upvoted.',
            'upvote_count' => $reply->upvote_count,
        ]);
    }
}
