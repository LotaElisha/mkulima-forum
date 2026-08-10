<?php

namespace App\Services\Verify;

use App\Models\ProductBatch;
use App\Models\ProductSerial;
use App\Models\RecallNotice;
use App\Models\RegulatedProduct;
use App\Models\VerificationResult;
use App\Models\VerificationScan;

class VerificationEngine
{
    protected RiskEngine $riskEngine;

    public function __construct(RiskEngine $riskEngine)
    {
        $this->riskEngine = $riskEngine;
    }

    /**
     * Resolve scan input to a single comprehensive VerificationResult.
     */
    public function verify(string $rawInput, string $scanMethod = 'barcode', ?int $userId = null, ?int $geoUnitId = null, bool $isOffline = false): array
    {
        $scan = VerificationScan::create([
            'scanner_id' => $userId,
            'scan_method' => $scanMethod,
            'raw_input' => trim($rawInput),
            'geo_unit_id' => $geoUnitId,
            'is_offline' => $isOffline,
            'occurred_at' => now(),
        ]);

        $input = trim($rawInput);

        // 1. Check Product Serials first (Track & Trace)
        $serial = ProductSerial::with(['product', 'batch'])->where('internal_serial', $input)
            ->orWhere('manufacturer_serial', $input)->first();

        $product = $serial ? $serial->product : null;
        $batch = $serial ? $serial->batch : null;

        // 2. Check Registration Number if not serial match
        if (!$product) {
            $product = RegulatedProduct::where('registration_number', $input)
                ->orWhere('trade_name', 'like', "%{$input}%")->first();
        }

        // 3. Check Product Batches
        if (!$batch && $product) {
            $batch = ProductBatch::where('product_id', $product->id)->where('batch_number', $input)->first();
        }

        if ($product) {
            $scan->product_id = $product->id;
            $scan->save();
        }

        // 4. Calculate Risk Score & Signals via RiskEngine
        $riskData = $this->riskEngine->assessRisk($product, $batch, $serial, $input, $geoUnitId);
        $score = $riskData['score'];
        $reasons = $riskData['reasons'];

        // 5. Determine Result Status based on rules
        $status = 'UNVERIFIED';
        $provenance = $product ? $product->provenance : 'PLATFORM';
        $confidence = $product ? $product->confidence : 50;

        if (!$product) {
            $status = 'UNVERIFIED';
            $reasons[] = 'Sio katika daftari ya sasa ya pembejeo / Product not found in current registry';
        } else {
            // Check recall/suspension status
            $activeRecall = RecallNotice::where('product_id', $product->id)->where('status', 'ACTIVE')->first();
            if ($activeRecall) {
                $status = 'RECALLED';
                $reasons[] = "BIDHAA IMERUDISHWA SOKONI: {$activeRecall->reason} / PRODUCT RECALLED";
            } elseif ($product->registration_status === 'BANNED' || $product->registration_status === 'WITHDRAWN') {
                $status = 'SUSPENDED';
                $reasons[] = "USAJILI UMESIMAMISHWA AU KUZUIWA / Registration suspended or banned";
            } elseif ($score >= 70) {
                $status = 'SUSPICIOUS';
                $reasons[] = "INATILIWA SHAKA: Alama za hatari ni {$score}/100 / High risk warning";
            } elseif ($product->provenance === 'REGULATORY') {
                $status = 'REGISTERED_SOURCE_CONFIRMED';
                $reasons[] = "Imesajiliwa — taarifa kutoka chanzo cha kisheria ({$product->authority?->acronym}) / Registered regulatory record matched";
            } elseif ($product->provenance === 'PLATFORM') {
                $status = 'VERIFIED';
                $reasons[] = "Imethibitishwa na Mkulima Forum / Mkulima Verified";
            } else {
                $status = 'COMMUNITY_SUPPLIER_RECORD';
                $reasons[] = "Taarifa ya msambazaji kutoka kwenye jamii / Community supplier record matched";
            }
        }

        $recommendedAction = [
            'sw' => $this->getRecommendedActionSw($status, $score),
            'en' => $this->getRecommendedActionEn($status, $score),
        ];

        $result = VerificationResult::create([
            'scan_id' => $scan->id,
            'status' => $status,
            'provenance' => $provenance,
            'confidence' => $confidence,
            'reasons' => $reasons,
            'recommended_action' => $recommendedAction,
            'risk_score' => $score,
            'as_of' => $product ? ($product->as_of ?? now()) : now(),
        ]);

        return [
            'scan_uuid' => $scan->uuid,
            'status' => $result->status,
            'provenance' => $result->provenance,
            'confidence' => $result->confidence,
            'risk_score' => $result->risk_score,
            'as_of' => $result->as_of->toIso8601String(),
            'reasons' => $result->reasons,
            'recommended_action' => $result->recommended_action,
            'product' => $product ? [
                'uuid' => $product->uuid,
                'trade_name' => $product->trade_name,
                'registration_number' => $product->registration_number,
                'active_ingredient' => $product->active_ingredient,
                'authority' => $product->authority?->acronym,
                'status' => $product->registration_status,
            ] : null,
            'batch' => $batch ? [
                'batch_number' => $batch->batch_number,
                'expiry_date' => $batch->expiry_date ? $batch->expiry_date->toDateString() : null,
            ] : null,
        ];
    }

    protected function getRecommendedActionSw(string $status, int $score): string
    {
        return match ($status) {
            'VERIFIED', 'REGISTERED_SOURCE_CONFIRMED' => 'Unaweza kutumia pembejeo hii. Hakikisha tarehe ya mwisho haijapita.',
            'RECALLED', 'SUSPENDED' => 'USITUMIE PEMBEJEO HII. Imerudishwa sokoni au kusimamishwa. Tuma ripoti kupitia Mkulima Forum.',
            'SUSPICIOUS' => 'TILIWA SHAKA! Usinunue wala kutumia kabla ya kuhakikisha duka na namba ya usajili. Ripoti iwapo umeinunua tayari.',
            'COMMUNITY_SUPPLIER_RECORD' => 'Taarifa ipo lakini haijathibitishwa rasmi. Nunua kutoka kwa mawakala waliothibitishwa.',
            default => 'Pembejeo hii haipo kwenye daftari ya sasa. Hakikisha chapa na namba ya usajili kwenye kifungashio.',
        };
    }

    protected function getRecommendedActionEn(string $status, int $score): string
    {
        return match ($status) {
            'VERIFIED', 'REGISTERED_SOURCE_CONFIRMED' => 'Safe to use. Ensure expiry date has not elapsed.',
            'RECALLED', 'SUSPENDED' => 'DO NOT USE THIS PRODUCT. Recalled or suspended by authority. Report via Mkulima Forum.',
            'SUSPICIOUS' => 'WARNING: SUSPICIOUS PRODUCT. Verify dealer licence and serial before use. Submit report if purchased.',
            'COMMUNITY_SUPPLIER_RECORD' => 'Supplier record exists but not independently confirmed against regulatory registry.',
            default => 'Product not found in current registry database. Check label registration details.',
        };
    }
}
