<?php

namespace App\Console\Commands;

use App\Services\MarketPriceSyncService;
use Illuminate\Console\Command;

class SyncMarketPrices extends Command
{
    protected $signature = 'market-prices:sync {--country=Tanzania : Country to sync prices for}';

    protected $description = 'Sync market prices from RATIN (EAGC) public API';

    public function handle(MarketPriceSyncService $service): int
    {
        $country = $this->option('country');

        if ($country !== 'Tanzania') {
            $this->error('Only Tanzania is currently supported by the RATIN adapter.');

            return self::FAILURE;
        }

        $report = $service->syncTanzania();

        if (! $report['enabled']) {
            $this->info('RATIN sync is off (RATIN_SYNC_ENABLED). No prices were written.');

            return self::SUCCESS;
        }

        $this->info('Syncing market prices from RATIN for '.$country.'...');
        $this->info('Created: '.$report['created']);
        $this->info('Updated: '.$report['updated']);
        $this->info('Skipped: '.$report['skipped']);
        $this->info('Removed old per-market rows: '.$report['removed']);

        if ($report['failed'] > 0) {
            $this->error('Failed: '.$report['failed']);

            return self::FAILURE;
        }

        $this->info('Sync completed.');

        return self::SUCCESS;
    }
}
