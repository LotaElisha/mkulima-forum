<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Read-only counts of the rows Phase 1 must not lose.
 *
 * Prints counts only. It does not print emails, phone numbers, or row
 * contents, and it does not insert, update, or delete.
 */
class ContentCensus extends Command
{
    protected $signature = 'mkulima:census {--json : Print one JSON object}';

    protected $description = 'Count users and key content tables without changing them';

    /**
     * Tables whose row counts decide how careful the Phase 1 migration is.
     * Users are broken down further. A missing table is reported, not created.
     *
     * @var array<string, string>
     */
    private const TABLES = [
        'users' => 'Accounts',
        'social_accounts' => 'Social logins',
        'tenants' => 'Tenants',
        'products' => 'Products',
        'orders' => 'Orders',
        'order_items' => 'Order items',
        'forum_categories' => 'Forum categories',
        'forum_threads' => 'Forum threads',
        'forum_replies' => 'Forum replies',
        'market_prices' => 'Market prices',
        'community_channels' => 'Community channels',
        'disease_scans' => 'Disease scans',
        'farms' => 'Farms',
        'wallets' => 'Wallets',
        'wallet_transactions' => 'Wallet transactions',
        'escrows' => 'Escrows',
        'escrow_ledger' => 'Escrow ledger entries',
        'seller_applications' => 'Seller applications',
        'registered_inputs' => 'Registered inputs',
        'config_settings' => 'Config settings',
        'landing_settings' => 'Landing settings',
    ];

    public function handle(): int
    {
        try {
            DB::connection()->getPdo();
        } catch (Throwable $e) {
            $this->error('Database connection failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $driver = (string) DB::connection()->getDriverName();
        if ($driver !== 'pgsql') {
            $this->error("Refusing to census a '{$driver}' database. PostgreSQL is the only supported database.");

            return self::FAILURE;
        }

        $report = [
            'database' => (string) DB::connection()->getDatabaseName(),
            'driver' => $driver,
            'read_only' => true,
            'tables' => [],
        ];

        foreach (self::TABLES as $table => $label) {
            $report['tables'][$table] = $this->tableReport($table, $label);
        }

        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->newLine();
        $this->line('  <fg=green;options=bold>Mkulima Forum — content census</>');
        $this->line('  <fg=gray>'.$report['database'].' · read only</>');
        $this->newLine();

        foreach ($report['tables'] as $table => $row) {
            $count = $row['rows'] === null ? 'absent' : (string) $row['rows'];
            $this->line(sprintf('  %-24s %8s   %s', $table, $count, $row['label']));
            foreach ($row['detail'] as $name => $value) {
                $shown = is_array($value) ? json_encode($value) : (string) $value;
                $this->line(sprintf('  %-24s %8s   %s', '', $shown, $name));
            }
        }

        $this->newLine();
        $this->line('  <fg=gray>No rows were printed and nothing was written.</>');
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * @return array{label: string, rows: int|null, detail: array<string, int|array<string, int>>}
     */
    private function tableReport(string $table, string $label): array
    {
        if (! Schema::hasTable($table)) {
            return ['label' => $label, 'rows' => null, 'detail' => []];
        }

        $detail = [];
        if ($table === 'users') {
            $detail['with email'] = DB::table('users')->whereNotNull('email')->where('email', '!=', '')->count();
            $detail['with phone'] = DB::table('users')->whereNotNull('phone')->where('phone', '!=', '')->count();
            $detail['by role'] = DB::table('users')
                ->select('role', DB::raw('count(*) as aggregate'))
                ->groupBy('role')
                ->orderBy('role')
                ->pluck('aggregate', 'role')
                ->map(fn ($count) => (int) $count)
                ->all();
        }

        return [
            'label' => $label,
            'rows' => DB::table($table)->count(),
            'detail' => $detail,
        ];
    }
}
