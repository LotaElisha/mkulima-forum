<?php

namespace App\Services\Verify;

use App\Models\CounterfeitEvidence;
use App\Models\CounterfeitReport;
use App\Services\Spine\AuditTrail;
use App\Services\Spine\EventBus;
use Illuminate\Http\UploadedFile;

class CounterfeitReportService
{
    protected EventBus $eventBus;
    protected AuditTrail $auditTrail;

    public function __construct(EventBus $eventBus, AuditTrail $auditTrail)
    {
        $this->eventBus = $eventBus;
        $this->auditTrail = $auditTrail;
    }

    public function submitReport(array $data, array $evidenceFiles = []): CounterfeitReport
    {
        $report = CounterfeitReport::create([
            'reporter_id' => $data['reporter_id'] ?? auth()->id(),
            'product_id' => $data['product_id'] ?? null,
            'product_name' => $data['product_name'],
            'product_category_id' => $data['product_category_id'] ?? null,
            'serial_number' => $data['serial_number'] ?? null,
            'batch_number' => $data['batch_number'] ?? null,
            'dealer_id' => $data['dealer_id'] ?? null,
            'dealer_name_raw' => $data['dealer_name_raw'] ?? null,
            'purchase_date' => $data['purchase_date'] ?? null,
            'geo_unit_id' => $data['geo_unit_id'] ?? null,
            'description' => $data['description'],
            'crop_affected_id' => $data['crop_affected_id'] ?? null,
            'status' => 'RECEIVED',
            'contact_preference' => $data['contact_preference'] ?? 'none',
            'reporter_anonymous' => $data['reporter_anonymous'] ?? false,
        ]);

        foreach ($evidenceFiles as $file) {
            if ($file instanceof UploadedFile) {
                $path = $file->store('counterfeit_evidence', 'public');
                $hash = hash_file('sha256', $file->getRealPath());

                CounterfeitEvidence::create([
                    'report_id' => $report->id,
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'sha256_hash' => $hash,
                    'evidence_type' => 'photo_front',
                    'uploaded_at' => now(),
                ]);
            }
        }

        $this->auditTrail->record($report, 'created', null, ['case_number' => $report->case_number]);

        $this->eventBus->fire('report.submitted', [
            'subject_type' => CounterfeitReport::class,
            'subject_id' => $report->id,
            'geo_unit_id' => $report->geo_unit_id,
            'crop_id' => $report->crop_affected_id,
            'provenance' => 'COMMUNITY',
            'metadata' => ['case_number' => $report->case_number],
        ]);

        return $report;
    }
}
