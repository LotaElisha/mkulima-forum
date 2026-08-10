<?php

namespace App\Http\Controllers\Api\Admin\Verify;

use App\Http\Controllers\Controller;
use App\Models\Advisory;
use App\Models\Agrodealer;
use App\Models\CounterfeitReport;
use App\Models\RegulatedProduct;
use App\Models\RegulatoryAuthority;
use App\Models\RegulatoryDataSource;
use App\Models\VerificationScan;

use App\Services\Verify\AdvisoryService;
use App\Services\Verify\AgrodealerKycService;
use App\Services\Verify\EscalationEngine;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminVerifyController extends Controller
{
    protected AdvisoryService $advisoryService;
    protected AgrodealerKycService $kycService;
    protected EscalationEngine $escalationEngine;

    public function __construct(AdvisoryService $advisoryService, AgrodealerKycService $kycService, EscalationEngine $escalationEngine)
    {
        $this->advisoryService = $advisoryService;
        $this->kycService = $kycService;
        $this->escalationEngine = $escalationEngine;
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'total_scans' => VerificationScan::count(),
                'total_products' => RegulatedProduct::count(),
                'total_reports' => CounterfeitReport::count(),
                'total_dealers' => Agrodealer::count(),
                'suspicious_scans' => VerificationScan::whereHas('result', fn($q) => $q->where('status', 'SUSPICIOUS'))->count(),
                'verified_dealers' => Agrodealer::whereIn('status', ['MKULIMA_VERIFIED', 'REGULATOR_RECORD_MATCHED'])->count(),
                'authorities' => RegulatoryAuthority::select(['id', 'acronym', 'name', 'is_active'])->get(),
            ],
        ]);
    }

    public function reports(): JsonResponse
    {
        $reports = CounterfeitReport::with(['reporter', 'geoUnit', 'evidence', 'dealer'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json(['status' => 'success', 'data' => $reports]);
    }

    public function escalateReport(Request $request, string $id): JsonResponse
    {
        $report = CounterfeitReport::findOrFail($id);
        $mode = $request->input('mode', 'INTERNAL_ONLY');
        $authorityId = $request->input('authority_id');

        $case = $this->escalationEngine->escalateReport($report, $mode, $authorityId);

        return response()->json([
            'status' => 'success',
            'message' => 'Report escalated and regulatory case file generated',
            'data' => $case,
        ]);
    }

    public function storeAdvisory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'title_sw' => 'required|string',
            'title_en' => 'required|string',
            'body_sw' => 'required|string',
            'body_en' => 'required|string',
            'channel_targets' => 'nullable|array',
            'geo_unit_ids' => 'nullable|array',
            'crop_ids' => 'nullable|array',
        ]);

        $advisory = Advisory::create([
            'type' => $validated['type'],
            'title' => ['sw' => $validated['title_sw'], 'en' => $validated['title_en']],
            'body' => ['sw' => $validated['body_sw'], 'en' => $validated['body_en']],
            'channel_targets' => $validated['channel_targets'] ?? ['push', 'sms', 'in_app'],
            'geo_unit_ids' => $validated['geo_unit_ids'] ?? null,
            'crop_ids' => $validated['crop_ids'] ?? null,
            'status' => 'DRAFT',
            'composed_by' => auth()->id(),
        ]);

        if ($request->input('dispatch_now')) {
            $this->advisoryService->dispatchAdvisory($advisory);
        }

        return response()->json(['status' => 'success', 'data' => $advisory], 201);
    }

    public function updateDealerStatus(Request $request, string $id): JsonResponse
    {
        $dealer = Agrodealer::findOrFail($id);
        $newStatus = $request->input('status');
        $notes = $request->input('notes');

        $updated = $this->kycService->updateStatus($dealer, $newStatus, $notes, auth()->id());

        return response()->json(['status' => 'success', 'data' => $updated]);
    }
}
