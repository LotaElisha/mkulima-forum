<?php

namespace App\Services\Verify;

use App\Models\CounterfeitReport;
use App\Models\RegulatoryCase;
use App\Services\Spine\AuditTrail;
use Illuminate\Support\Facades\Storage;

class EscalationEngine
{
    protected AuditTrail $auditTrail;

    public function __construct(AuditTrail $auditTrail)
    {
        $this->auditTrail = $auditTrail;
    }

    /**
     * Escalate a counterfeit incident report and generate regulator-ready case files.
     */
    public function escalateReport(CounterfeitReport $report, string $escalationMode = 'INTERNAL_ONLY', ?int $authorityId = null): RegulatoryCase
    {
        // Generate JSON case file
        $caseData = [
            'case_number' => $report->case_number,
            'incident' => [
                'product_name' => $report->product_name,
                'serial_number' => $report->serial_number,
                'batch_number' => $report->batch_number,
                'dealer_name' => $report->dealer_name_raw,
                'description' => $report->description,
                'reported_at' => $report->created_at->toIso8601String(),
            ],
            'evidence' => $report->evidence->map(fn ($e) => [
                'file_path' => $e->file_path,
                'sha256_hash' => $e->sha256_hash,
                'type' => $e->evidence_type,
            ])->toArray(),
            'provenance' => 'COMMUNITY',
            'generated_at' => now()->toIso8601String(),
        ];

        $jsonPath = "regulatory_cases/{$report->case_number}.json";
        Storage::disk('public')->put($jsonPath, json_encode($caseData, JSON_PRETTY_PRINT));

        // Generate HTML/PDF case report
        $htmlContent = $this->generateCaseHtml($caseData);
        $htmlPath = "regulatory_cases/{$report->case_number}.html";
        Storage::disk('public')->put($htmlPath, $htmlContent);

        $case = RegulatoryCase::create([
            'report_id' => $report->id,
            'case_number' => $report->case_number,
            'escalation_mode' => $escalationMode,
            'authority_id' => $authorityId,
            'status' => 'ESCALATED',
            'case_file_pdf_path' => $htmlPath,
            'case_file_json_path' => $jsonPath,
            'submitted_at' => $escalationMode !== 'INTERNAL_ONLY' ? now() : null,
        ]);

        $report->status = 'ESCALATED';
        $report->save();

        $this->auditTrail->record($case, 'escalated', null, ['mode' => $escalationMode, 'case_number' => $report->case_number]);

        return $case;
    }

    protected function generateCaseHtml(array $caseData): string
    {
        $num = htmlspecialchars($caseData['case_number']);
        $prod = htmlspecialchars($caseData['incident']['product_name']);
        $desc = htmlspecialchars($caseData['incident']['description']);
        $date = htmlspecialchars($caseData['incident']['reported_at']);

        return "
        <!DOCTYPE html>
        <html>
        <head><title>Case File {$num}</title><style>body{font-family:sans-serif;padding:20px;color:#1A1A2E;} .header{border-bottom:2px solid #0E4220;padding-bottom:10px;} .label{font-weight:bold;}</style></head>
        <body>
          <div class='header'>
            <h2>Mkulima Forum — Incident Case File {$num}</h2>
            <p>Provenance: COMMUNITY | Generated: {$caseData['generated_at']}</p>
          </div>
          <h3>Incident Details</h3>
          <p><span class='label'>Product:</span> {$prod}</p>
          <p><span class='label'>Description:</span> {$desc}</p>
          <p><span class='label'>Reported At:</span> {$date}</p>
          <h3>Evidence Items (SHA-256 Verified)</h3>
          <ul>".implode('', array_map(fn ($e) => "<li>{$e['type']}: {$e['sha256_hash']}</li>", $caseData['evidence'])).'</ul>
        </body>
        </html>';
    }
}
