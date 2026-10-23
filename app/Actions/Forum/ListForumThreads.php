<?php

namespace App\Actions\Forum;

use App\Models\ForumThread;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ListForumThreads
{
    public function __invoke(Request $request, ?string $categoryUuid = null): LengthAwarePaginator
    {
        $validated = $request->validate([
            'category' => ['nullable', 'uuid'],
            'region' => ['nullable', 'string', 'max:64'],
            'search' => ['nullable', 'string', 'max:120'],
            'sort' => ['nullable', 'in:created_at,upvote_count,reply_count'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $categoryUuid ??= $validated['category'] ?? null;

        $query = ForumThread::query()
            ->with([
                'user:id,uuid,name,is_verified_expert',
                'category:id,uuid,name,slug',
            ])
            ->where('status', 'active');

        if ($categoryUuid) {
            $query->whereHas('category', fn ($q) => $q->where('uuid', $categoryUuid));
        }

        if (! empty($validated['region'])) {
            $query->where('region', $validated['region']);
        }

        if (! empty($validated['search'])) {
            $term = '%'.addcslashes($validated['search'], '%_\\').'%';
            $query->where(function ($inner) use ($term) {
                $inner->where('title', 'ilike', $term)->orWhere('body', 'ilike', $term);
            });
        }

        $sort = $validated['sort'] ?? 'created_at';
        $query->orderByDesc('is_pinned')->orderByDesc($sort);

        $threads = $query->paginate($validated['per_page'] ?? 20);
        $threads->getCollection()->transform(fn (ForumThread $thread) => PresentForum::thread($thread));

        return $threads;
    }
}
