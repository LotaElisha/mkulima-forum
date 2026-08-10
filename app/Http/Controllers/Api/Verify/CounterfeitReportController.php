<?php

namespace App\Http\Controllers\Api\Verify;

use App\Http\Controllers\Controller;
use App\Models\CounterfeitReport;
use App\Services\Verify\CounterfeitReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            'evidence.*' => 'nullable|file|image|max:10240',
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

    public function show(string $caseNumber): JsonResponse
    {
        $report = CounterfeitReport::with(['evidence', 'geoUnit'])
            ->where('case_number', $caseNumber)
            ->orWhere('uuid', $caseNumber)
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
