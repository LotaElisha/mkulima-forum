<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Agronomists\ListAgronomists;
use App\Actions\Agronomists\PresentAgronomist;
use App\Http\Api\V1Response;
use App\Http\Controllers\Controller;
use App\Models\ServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Find a verified agronomist near the farmer. Booking one still goes through
 * POST /api/services/bookings with the provider uuid.
 */
class AgronomistController extends Controller
{
    public function index(Request $request, ListAgronomists $list): JsonResponse
    {
        $page = $list($request);

        return V1Response::ok($page->items(), [
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
            'per_page' => $page->perPage(),
            'total' => $page->total(),
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $provider = ServiceProvider::with('user:id,uuid,name')
            ->where('uuid', $uuid)
            ->where('service_type', 'agronomist')
            ->where('is_active', true)
            ->where('verification_status', 'verified')
            ->firstOrFail();

        return V1Response::ok(PresentAgronomist::detail($provider));
    }
}
