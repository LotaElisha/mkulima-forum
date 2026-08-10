<?php

namespace App\Services\Verify;

use App\Models\CounterfeitReport;
use App\Models\ProductBatch;
use App\Models\ProductSerial;
use App\Models\RegulatedProduct;

class RiskEngine
{
    /**
     * Assess anti-counterfeit risk (0-100 score + human readable reasons).
     */
    public function assessRisk(
        ?RegulatedProduct $product,
        ?ProductBatch $batch,
        ?ProductSerial $serial,
        string $rawInput,
        ?int $geoUnitId = null
    ): array {
        $score = 0;
        $reasons = [];

        // 1. Registry Match Signal
        if (!$product) {
            $score += 45;
            $reasons[] = 'Namba au chapa haijapatikana kwenye daftari / Registration number not in registry';
        } else {
            if ($product->registration_status === 'BANNED' || $product->registration_status === 'WITHDRAWN') {
                $score += 50;
                $reasons[] = 'Usajili wa bidhaa hii ulisitishwa / Product registration suspended or withdrawn';
            }
        }

        // 2. Batch & Expiry Signal
        if ($batch) {
            if ($batch->expiry_date && $batch->expiry_date->isPast()) {
                $score += 30;
                $reasons[] = 'Muda wa matumizi wa kundi hili umeisha / Product batch expired';
            }
        }

        // 3. Serial & Duplicate Scan Signal
        if ($serial) {
            if ($serial->is_duplicate_detected) {
                $score += 40;
                $reasons[] = 'Namba hii ya usajili imechanganuliwa mara nyingi maeneo tofauti / Duplicate serial scanned in distant locations';
            }
        }

        // 4. Complaint History Signal
        if ($product) {
            $reportCount = CounterfeitReport::where('product_id', $product->id)
                ->whereIn('status', ['RECEIVED', 'UNDER_REVIEW', 'ESCALATED'])
                ->count();

            if ($reportCount > 3) {
                $score += 25;
                $reasons[] = "Bidhaa hii imetolewa taarifa mara {$reportCount} za shaka / High complaint frequency reported";
            }
        }

        $score = min(100, max(0, $score));

        return [
            'score' => $score,
            'reasons' => $reasons,
        ];
    }
}
