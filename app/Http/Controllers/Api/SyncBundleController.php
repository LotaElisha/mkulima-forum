<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Spine\OfflineBundleService;
use Illuminate\Http\JsonResponse;

class SyncBundleController extends Controller
{
    protected OfflineBundleService $bundleService;

    public function __construct(OfflineBundleService $bundleService)
    {
        $this->bundleService = $bundleService;
    }

    public function bundle(): JsonResponse
    {
        $bundle = $this->bundleService->getSignedBundle();

        return response()->json([
            'status' => 'success',
            'data' => $bundle,
        ])->header('ETag', md5($bundle['bundle_version']));
    }
}
