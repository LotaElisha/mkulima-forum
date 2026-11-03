<?php

namespace App\Actions\Agronomists;

use App\Models\ServiceProvider;
use App\Support\Nearby;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ListAgronomists
{
    public function __invoke(Request $request): LengthAwarePaginator
    {
        $validated = $request->validate(Nearby::rules() + [
            'search' => ['nullable', 'string', 'max:120'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = ServiceProvider::query()
            ->with('user:id,uuid,name')
            ->where('service_type', 'agronomist')
            ->where('is_active', true)
            ->where('verification_status', 'verified');

        if (! empty($validated['search'])) {
            $term = '%'.addcslashes($validated['search'], '%_\\').'%';
            $query->where(function ($inner) use ($term) {
                $inner->where('business_name', 'ilike', $term)
                    ->orWhere('bio', 'ilike', $term)
                    ->orWhereRaw('specializations::text ilike ?', [$term]);
            });
        }

        Nearby::apply($query, $validated, 'service_providers');
        $query->orderByDesc('rating');

        $page = $query->paginate($validated['per_page'] ?? 20);
        $page->getCollection()->transform(fn (ServiceProvider $p) => PresentAgronomist::card($p));

        return $page;
    }
}
