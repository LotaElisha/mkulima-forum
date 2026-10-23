<?php

namespace App\Actions\Forum;

use App\Models\ForumThread;

class ShowForumThread
{
    public function __invoke(string $uuid): array
    {
        $thread = ForumThread::query()
            ->with([
                'user:id,uuid,name,is_verified_expert',
                'category:id,uuid,name,slug',
                'replies.user:id,uuid,name,is_verified_expert',
            ])
            ->where('uuid', $uuid)
            ->where('status', 'active')
            ->firstOrFail();

        return PresentForum::thread($thread, withReplies: true);
    }
}
