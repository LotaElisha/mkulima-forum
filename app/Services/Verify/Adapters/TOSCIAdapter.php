<?php

namespace App\Services\Verify\Adapters;

use App\Contracts\RegulatoryProvider;
use App\Models\RegulatedProduct;
use App\Models\RegulatoryDataSource;

class TOSCIAdapter implements RegulatoryProvider
{
    public function searchProduct(string $query, RegulatoryDataSource $source): array
    {
        return RegulatedProduct::whereHas('authority', function ($q) {
            $q->where('acronym', 'TOSCI');
        })->where(function ($q) use ($query) {
            $q->where('trade_name', 'like', "%{$query}%")
              ->orWhere('registration_number', 'like', "%{$query}%");
        })->get()->toArray();
    }

    public function verifyRegistration(string $registrationNumber, RegulatoryDataSource $source): ?array
    {
        $product = RegulatedProduct::whereHas('authority', function ($q) {
            $q->where('acronym', 'TOSCI');
        })->where('registration_number', $registrationNumber)->first();

        if (!$product) return null;

        return [
            'registration_number' => $product->registration_number,
            'trade_name' => $product->trade_name,
            'status' => $product->registration_status,
            'authority' => 'TOSCI',
            'provenance' => 'REGULATORY',
            'confidence' => $source->confidence_level ?? 95,
            'as_of' => $product->as_of ? $product->as_of->toIso8601String() : now()->toIso8601String(),
        ];
    }

    public function verifyBatch(string $batchNumber, RegulatoryDataSource $source): ?array
    {
        return null; // Local batch verification handled by VerificationEngine
    }

    public function verifyDealer(string $licenceNumber, RegulatoryDataSource $source): ?array
    {
        return null;
    }

    public function getRecallStatus(string $registrationNumber, RegulatoryDataSource $source): ?array
    {
        return null;
    }

    public function syncRegistry(RegulatoryDataSource $source): array
    {
        return ['status' => 'success', 'imported' => 0, 'mode' => $source->backing_mode];
    }
}
