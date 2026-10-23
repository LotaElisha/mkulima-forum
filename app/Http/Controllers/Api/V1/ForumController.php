<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Forum\ListForumCategories;
use App\Actions\Forum\ListForumThreads;
use App\Actions\Forum\ShowForumThread;
use App\Http\Api\V1Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    public function categories(Request $request, ListForumCategories $categories): JsonResponse
    {
        $request->validate(['region' => ['nullable', 'string', 'max:64']]);

        return V1Response::ok($categories($request->input('region')));
    }

    public function threads(Request $request, ListForumThreads $threads, ?string $uuid = null): JsonResponse
    {
        $page = $threads($request, $uuid);

        return V1Response::ok($page->items(), [
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
            'per_page' => $page->perPage(),
            'total' => $page->total(),
        ]);
    }

    public function show(string $uuid, ShowForumThread $show): JsonResponse
    {
        return V1Response::ok($show($uuid));
    }
}
