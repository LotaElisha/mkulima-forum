<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Prices\ListMarketPrices;
use App\Http\Api\V1Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketPriceController extends Controller
{
    public function index(Request $request, ListMarketPrices $prices): JsonResponse
    {
        $page = $prices($request);

        return V1Response::ok($page->items(), [
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
            'per_page' => $page->perPage(),
            'total' => $page->total(),
        ]);
    }

    public function filters(ListMarketPrices $prices): JsonResponse
    {
        return V1Response::ok($prices->filters());
    }
}
