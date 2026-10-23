<?php

namespace App\Actions\Forum;

use App\Models\ForumCategory;
use Illuminate\Support\Collection;

class ListForumCategories
{
    public function __invoke(?string $region = null): Collection
    {
        $query = ForumCategory::query()->where('is_active', true);

        if ($region) {
            $query->where(function ($inner) use ($region) {
                $inner->whereNull('region')->orWhere('region', $region);
            });
        }

        return $query->orderBy('sort_order')->orderBy('name')->get()
            ->map(fn (ForumCategory $category) => PresentForum::category($category))
            ->values();
    }
}
