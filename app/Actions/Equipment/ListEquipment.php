<?php

namespace App\Actions\Equipment;

use App\Models\EquipmentListing;
use App\Support\Nearby;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ListEquipment
{
    public function __invoke(Request $request): LengthAwarePaginator
    {
        $validated = $request->validate(Nearby::rules() + [
            'category' => ['nullable', Rule::in(array_keys(EquipmentListing::CATEGORIES))],
            'search' => ['nullable', 'string', 'max:120'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = EquipmentListing::query()
            ->with('owner:id,uuid,name')
            ->where('status', 'active');

        if (! empty($validated['category'])) {
            $query->where('category', $validated['category']);
        }
        if (! empty($validated['search'])) {
            $term = '%'.addcslashes($validated['search'], '%_\\').'%';
            $query->where(fn ($q) => $q->where('title', 'ilike', $term)->orWhere('description', 'ilike', $term));
        }

        Nearby::apply($query, $validated, 'equipment_listings');
        $query->orderByDesc('created_at');

        $page = $query->paginate($validated['per_page'] ?? 20);
        $page->getCollection()->transform(fn (EquipmentListing $l) => PresentEquipment::listing($l));

        return $page;
    }
}
