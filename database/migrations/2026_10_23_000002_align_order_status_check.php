<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Postgres enforces the check constraint. Escrow release writes completed,
     * escrow refunds write refunded, and the admin order update accepts processing.
     */
    private const STATUSES = [
        'pending',
        'confirmed',
        'paid',
        'processing',
        'shipped',
        'delivered',
        'completed',
        'cancelled',
        'disputed',
        'refunded',
    ];

    public function up(): void
    {
        $this->replaceStatusCheck(self::STATUSES);
    }

    public function down(): void
    {
        $this->replaceStatusCheck([
            'pending',
            'confirmed',
            'paid',
            'shipped',
            'delivered',
            'cancelled',
            'disputed',
        ]);
    }

    /**
     * @param  list<string>  $statuses
     */
    private function replaceStatusCheck(array $statuses): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql' || ! Schema::hasTable('orders')) {
            return;
        }

        $quoted = implode(', ', array_map(
            fn (string $status) => "'".str_replace("'", "''", $status)."'",
            $statuses
        ));

        DB::statement('alter table orders drop constraint if exists orders_status_check');
        DB::statement("alter table orders add constraint orders_status_check check (status in ({$quoted}))");
    }
};
