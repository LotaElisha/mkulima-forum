<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Equipment\DecideRental;
use App\Actions\Equipment\ListEquipment;
use App\Actions\Equipment\PresentEquipment;
use App\Actions\Equipment\RequestRental;
use App\Http\Api\V1Response;
use App\Http\Controllers\Controller;
use App\Models\EquipmentListing;
use App\Models\EquipmentRental;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Equipment rentals: owners list tractors, pumps and the like; farmers
 * request dates; the owner accepts or declines. No money moves in the app.
 */
class EquipmentController extends Controller
{
    public function categories(): JsonResponse
    {
        $data = collect(EquipmentListing::CATEGORIES)
            ->map(fn (string $label, string $key) => ['key' => $key, 'label' => $label])
            ->values();

        return V1Response::ok($data);
    }

    public function index(Request $request, ListEquipment $list): JsonResponse
    {
        $page = $list($request);

        return V1Response::ok($page->items(), self::meta($page));
    }

    public function show(string $uuid): JsonResponse
    {
        $listing = EquipmentListing::with('owner:id,uuid,name')
            ->where('uuid', $uuid)
            ->where('status', 'active')
            ->firstOrFail();

        return V1Response::ok(PresentEquipment::listing($listing));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => ['required', Rule::in(array_keys(EquipmentListing::CATEGORIES))],
            'title' => ['required', 'string', 'min:3', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'daily_rate' => ['required', 'numeric', 'min:1000', 'max:10000000'],
            'with_operator' => ['nullable', 'boolean'],
            'region' => ['required', 'string', 'max:64'],
            'district' => ['nullable', 'string', 'max:64'],
            'lat' => ['nullable', 'numeric', 'between:-90,90', 'required_with:lng'],
            'lng' => ['nullable', 'numeric', 'between:-180,180', 'required_with:lat'],
        ]);

        $user = $request->user();
        $listing = EquipmentListing::create([
            'tenant_id' => $user->tenant_id,
            'owner_id' => $user->id,
            'category' => $validated['category'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'daily_rate' => $validated['daily_rate'],
            'with_operator' => (bool) ($validated['with_operator'] ?? false),
            'region' => $validated['region'],
            'district' => $validated['district'] ?? null,
            'latitude' => $validated['lat'] ?? null,
            'longitude' => $validated['lng'] ?? null,
            'status' => 'active',
        ]);

        return V1Response::ok(PresentEquipment::listing($listing->load('owner:id,uuid,name')), [], 201);
    }

    /** The owner pauses or relists. An admin-hidden listing stays hidden. */
    public function updateStatus(Request $request, string $uuid): JsonResponse
    {
        $validated = $request->validate(['status' => ['required', Rule::in(['active', 'paused'])]]);

        $listing = EquipmentListing::where('uuid', $uuid)
            ->where('owner_id', $request->user()->id)
            ->where('status', '!=', 'hidden')
            ->firstOrFail();
        $listing->update(['status' => $validated['status']]);

        return V1Response::ok(PresentEquipment::listing($listing->load('owner:id,uuid,name')));
    }

    public function mine(Request $request): JsonResponse
    {
        $listings = EquipmentListing::with('owner:id,uuid,name')
            ->where('owner_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn (EquipmentListing $l) => PresentEquipment::listing($l));

        return V1Response::ok($listings);
    }

    public function requestRental(Request $request, string $uuid, RequestRental $requestRental): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        $listing = EquipmentListing::where('uuid', $uuid)->firstOrFail();
        $rental = $requestRental($request->user(), $listing, $validated);

        return V1Response::ok(PresentEquipment::rental($rental->load('listing.owner', 'renter'), $request->user()), [], 201);
    }

    /** ?as=renter (default): my requests. ?as=owner: requests for my equipment. */
    public function rentals(Request $request): JsonResponse
    {
        $validated = $request->validate(['as' => ['nullable', Rule::in(['renter', 'owner'])]]);
        $user = $request->user();

        $query = EquipmentRental::with('listing.owner', 'renter')->latest();
        if (($validated['as'] ?? 'renter') === 'owner') {
            $query->whereHas('listing', fn ($q) => $q->where('owner_id', $user->id));
        } else {
            $query->where('renter_id', $user->id);
        }

        $page = $query->paginate(20);
        $page->getCollection()->transform(fn (EquipmentRental $r) => PresentEquipment::rental($r, $user));

        return V1Response::ok($page->items(), self::meta($page));
    }

    public function decide(Request $request, string $uuid, string $action, DecideRental $decide): JsonResponse
    {
        $rental = EquipmentRental::with('listing.owner', 'renter')->where('uuid', $uuid)->firstOrFail();
        $rental = $decide($request->user(), $rental, $action);

        return V1Response::ok(PresentEquipment::rental($rental->load('listing.owner', 'renter'), $request->user()));
    }

    private static function meta($page): array
    {
        return [
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
            'per_page' => $page->perPage(),
            'total' => $page->total(),
        ];
    }
}
