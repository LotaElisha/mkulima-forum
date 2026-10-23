<?php

namespace App\Actions\Forum;

use App\Models\ForumCategory;
use App\Models\ForumReply;
use App\Models\ForumThread;

/**
 * Forum read models. Numeric ids stay in the database.
 */
final class PresentForum
{
    public static function category(ForumCategory $category): array
    {
        return [
            'uuid' => $category->uuid,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'icon' => $category->icon,
            'region' => $category->region,
            'requires_expert' => (bool) $category->requires_expert,
        ];
    }

    public static function thread(ForumThread $thread, bool $withReplies = false): array
    {
        $payload = [
            'uuid' => $thread->uuid,
            'title' => $thread->title,
            'body' => $thread->body,
            'region' => $thread->region,
            'language' => $thread->language,
            'reply_count' => $thread->reply_count,
            'upvote_count' => $thread->upvote_count,
            'is_pinned' => (bool) $thread->is_pinned,
            'created_at' => $thread->created_at?->toIso8601String(),
            'user' => $thread->user ? [
                'uuid' => $thread->user->uuid,
                'name' => $thread->user->name,
                'is_verified_expert' => (bool) $thread->user->is_verified_expert,
            ] : null,
            'category' => $thread->category ? [
                'uuid' => $thread->category->uuid,
                'name' => $thread->category->name,
                'slug' => $thread->category->slug,
            ] : null,
        ];

        if ($withReplies) {
            $payload['replies'] = $thread->replies
                ->where('status', 'active')
                ->values()
                ->map(fn (ForumReply $reply) => [
                    'body' => $reply->body,
                    'is_expert_answer' => (bool) $reply->is_expert_answer,
                    'created_at' => $reply->created_at?->toIso8601String(),
                    'user' => $reply->user ? [
                        'uuid' => $reply->user->uuid,
                        'name' => $reply->user->name,
                        'is_verified_expert' => (bool) $reply->user->is_verified_expert,
                    ] : null,
                ])
                ->all();
        }

        return $payload;
    }
}
