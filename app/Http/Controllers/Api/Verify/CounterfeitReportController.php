<?php

namespace App\Http\Controllers\Api\Verify;

use App\Http\Controllers\Controller;
use App\Models\CounterfeitReport;
use App\Services\Verify\CounterfeitReportService;
use App\Support\UploadRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CounterfeitReportController extends Controller
{
    protected CounterfeitReportService $reportService;

    public function __construct(CounterfeitReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'product_id' => 'nullable|integer|exists:regulated_products,id',
            'product_category_id' => 'nullable|integer|exists:product_categories,id',
            'serial_number' => 'nullable|string|max:64',
            'batch_number' => 'nullable|string|max:64',
            'dealer_id' => 'nullable|integer|exists:agrodealers,id',
            'dealer_name_raw' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'geo_unit_id' => 'nullable|integer|exists:geo_units,id',
            'description' => 'required|string|max:2000',
            'crop_affected_id' => 'nullable|integer|exists:crops,id',
            'contact_preference' => 'nullable|string|in:phone,email,none',
            'reporter_anonymous' => 'nullable|boolean',
            'evidence' => ['nullable', 'array', 'max:5'],
            'evidence.*' => UploadRules::rasterOrDocument(10240),
        ]);

        $evidenceFiles = $request->file('evidence') ?? [];
        $report = $this->reportService->submitReport($validated, is_array($evidenceFiles) ? $evidenceFiles : [$evidenceFiles]);

        return response()->json([
            'status' => 'success',
            'message' => 'Ripoti yako imepokelewa. Asante kwa kusaidia wakulima wengine.',
            'data' => [
                'uuid' => $report->uuid,
                'case_number' => $report->case_number,
                'status' => $report->status,
                'created_at' => $report->created_at->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Look up one report.
     *
     * Accepts the UUID only. It used to accept the human-readable case number
     * too (MF-2026-000123 and similar), which is sequential enough to walk:
     * anyone could enumerate every counterfeit report on the platform along
     * with its description and the district it came from. A farmer reporting a
     * fake pesticide in a small village should not have that readable by the
     * dealer they reported.
     *
     * The UUID is returned to the reporter at submission time, so the person
     * who filed it keeps their access. Staff read reports through the
     * authenticated admin endpoints.
     */
    public function show(string $caseNumber): JsonResponse
    {
        if (! Str::isUuid($caseNumber)) {
            return response()->json([
                'status' => 'error',
                'message' => __('auth_flows.report_lookup_uuid'),
            ], 404);
        }

        $report = CounterfeitReport::with(['evidence', 'geoUnit'])
            ->where('uuid', $caseNumber)
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => [
                'case_number' => $report->case_number,
                'product_name' => $report->product_name,
                'status' => $report->status,
                'description' => $report->description,
                'location' => $report->geoUnit?->name,
                'created_at' => $report->created_at->toIso8601String(),
                'evidence_count' => $report->evidence->count(),
            ],
        ]);
    }
}
