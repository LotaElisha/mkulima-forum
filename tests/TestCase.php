<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->rewindPostgresSequences();
    }

    /**
     * Postgres does not roll sequence values back with the test transaction.
     * SQLite reused ids from 1, and many tests create a tenant and then
     * insert rows with tenant_id 1. Rewind empty-table sequences after the
     * transaction opens so that assumption still holds.
     */
    private function rewindPostgresSequences(): void
    {
        $connection = DB::connection();

        if ($connection->getDriverName() !== 'pgsql' || $connection->transactionLevel() < 1) {
            return;
        }

        // One statement. Doing this per sequence from PHP opens no extra
        // connection and keeps the locks inside the test transaction.
        $connection->statement(<<<'SQL'
            select setval(format('%I.%I', sequence_schema, sequence_name)::regclass, 1, false)
            from information_schema.sequences
            where sequence_schema = 'public'
              and sequence_name <> 'migrations_id_seq'
        SQL);
    }
}
