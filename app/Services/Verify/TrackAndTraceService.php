<?php

namespace App\Services\Verify;

use App\Models\ProductSerial;

class TrackAndTraceService
{
    /**
     * Register product serial scan and check for anomalies (A8).
     */
    public function traceSerial(string $serialInput, ?int $geoUnitId = null): ?array
    {
        $serial = ProductSerial::with(['product', 'batch'])
            ->where('internal_serial', $serialInput)
            ->orWhere('manufacturer_serial', $serialInput)
            ->first();

        if (!$serial) return null;

        // Flag duplicate scan if needed
        if ($serial->current_holder_type === 'farmer') {
            $serial->is_duplicate_detected = true;
            $serial->save();
        }

        return [
            'serial' => $serial->internal_serial,
            'gtin' => $serial->gtin,
            'duplicate_detected' => $serial->is_duplicate_detected,
            'current_holder' => $serial->current_holder_type,
            'product_name' => $serial->product?->trade_name,
            'batch_number' => $serial->batch?->batch_number,
        ];
    }
}
