<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The pre-flight command has to be trustworthy in both directions: it must fail
 * on a misconfigured environment, and it must pass on a correct one. A check
 * that always fails gets ignored, which is worse than no check at all.
 */
class PreflightCheckTest extends TestCase
{
    use RefreshDatabase;

    private function productionConfig(): void
    {
        config([
            'app.env' => 'production',
            'app.debug' => false,
            'app.key' => 'base64:'.base64_encode(random_bytes(32)),
            'app.url' => 'https://mkulimaforum.com',
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'smtp.gmail.com',
            'mail.mailers.smtp.password' => 'an-app-password',
            'mail.from.address' => 'hello@mkulimaforum.com',
            'sanctum.expiration' => 43200,
            'session.secure' => true,
            'services.sms.webhook_secret' => 'a-shared-secret',
            'services.ivr.webhook_secret' => 'another-shared-secret',
            'services.short_links.allowed_hosts' => ['mkulimaforum.com', 'wa.me'],
            'services.africastalking.username' => 'mkulima',
            'services.africastalking.api_key' => 'a-key',
            'queue.default' => 'database',
        ]);
    }

    private function seedMinimum(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['country_code' => 'tz'],
            ['name' => 'Tanzania', 'currency' => 'TZS', 'is_active' => true]
        );

        User::provision([
            'tenant_id' => $tenant->id,
            'name' => 'Admin',
            'email' => 'admin@mkulimaforum.com',
            'password' => Hash::make('a-long-enough-password'),
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    public function test_a_correctly_configured_environment_passes(): void
    {
        $this->productionConfig();
        $this->seedMinimum();

        $this->artisan('mkulima:preflight')->assertExitCode(0);
    }

    public function test_an_empty_smtp_password_blocks_the_deploy(): void
    {
        $this->productionConfig();
        $this->seedMinimum();

        // The single most expensive silent failure: mail with no password does
        // not error, it just never sends.
        config(['mail.mailers.smtp.password' => '']);

        $this->artisan('mkulima:preflight')->assertExitCode(1);
    }

    public function test_debug_mode_blocks_the_deploy(): void
    {
        $this->productionConfig();
        $this->seedMinimum();
        config(['app.debug' => true]);

        $this->artisan('mkulima:preflight')->assertExitCode(1);
    }

    public function test_a_missing_webhook_secret_blocks_the_deploy(): void
    {
        $this->productionConfig();
        $this->seedMinimum();
        config(['services.sms.webhook_secret' => '']);

        $this->artisan('mkulima:preflight')->assertExitCode(1);
    }

    public function test_an_allowlist_missing_our_own_host_blocks_the_deploy(): void
    {
        $this->productionConfig();
        $this->seedMinimum();
        // The exact bug I shipped and had to fix: the allowlist did not include
        // the production domain, so short links to our own site would have hit
        // the "you are leaving" interstitial.
        config(['services.short_links.allowed_hosts' => ['wa.me']]);

        $this->artisan('mkulima:preflight')->assertExitCode(1);
    }

    public function test_an_unmigrated_database_blocks_the_deploy(): void
    {
        $this->productionConfig();
        $this->seedMinimum();

        Schema::drop('password_reset_tokens');

        $this->artisan('mkulima:preflight')->assertExitCode(1);
    }
}
