<?php

namespace App\Contracts;

use App\Models\RegulatoryDataSource;

interface RegulatoryProvider
{
    /**
     * Search product in regulatory dataset.
     */
    public function searchProduct(string $query, RegulatoryDataSource $source): array;

    /**
     * Verify registration number against regulatory source.
     */
    public function verifyRegistration(string $registrationNumber, RegulatoryDataSource $source): ?array;

    /**
     * Verify batch/lot number against regulatory source.
     */
    public function verifyBatch(string $batchNumber, RegulatoryDataSource $source): ?array;

    /**
     * Verify agrodealer licence number against regulatory source.
     */
    public function verifyDealer(string $licenceNumber, RegulatoryDataSource $source): ?array;

    /**
     * Fetch active recall or suspension status for a product/batch.
     */
    public function getRecallStatus(string $registrationNumber, RegulatoryDataSource $source): ?array;

    /**
     * Perform periodic synchronization if source mode supports it.
     */
    public function syncRegistry(RegulatoryDataSource $source): array;
}
