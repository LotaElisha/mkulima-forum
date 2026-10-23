<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ContentCensusTest extends TestCase
{
    use RefreshDatabase;

    public function test_census_counts_users_and_does_not_write(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['country_code' => 'tz'],
            ['name' => 'Tanzania', 'currency' => 'TZS', 'is_active' => true]
        );

        User::provision([
            'tenant_id' => $tenant->id,
            'name' => 'A Farmer',
            'email' => 'farmer@example.com',
            'password' => Hash::make('a-long-enough-password'),
            'role' => 'farmer',
            'status' => 'active',
        ]);

        $before = DB::table('users')->count();

        $exit = Artisan::call('mkulima:census', ['--json' => true]);
        $output = Artisan::output();
        $report = json_decode($output, true);

        $this->assertSame(0, $exit);
        $this->assertTrue($report['read_only'] ?? false);
        $this->assertSame($before, $report['tables']['users']['rows'] ?? null);
        $this->assertStringNotContainsString('farmer@example.com', $output);

        $this->assertSame($before, DB::table('users')->count());
        $this->assertSame('farmer@example.com', DB::table('users')->value('email'));
    }
}
