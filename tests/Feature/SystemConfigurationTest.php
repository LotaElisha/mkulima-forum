<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\ConfigSetting;
use App\Models\Tenant;
use App\Models\User;
use App\Settings\SettingsManager;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * The production configuration centre.
 *
 * The properties that matter here are security properties: a secret must never
 * come back out, an ordinary admin must not be able to rotate one, and an
 * unknown key must not be writable at all.
 */
class SystemConfigurationTest extends TestCase
{
    use RefreshDatabase;

    private function staff(string $role): User
    {
        $tenant = Tenant::firstOrCreate(
            ['country_code' => 'tz'],
            ['name' => 'Tanzania', 'currency' => 'TZS', 'is_active' => true]
        );

        return User::provision([
            'tenant_id' => $tenant->id,
            'name' => ucfirst($role),
            'email' => $role.'@mkulimaforum.app',
            'password' => Hash::make('correct-horse-battery'),
            'role' => $role,
            'status' => 'active',
        ]);
    }

    // ── Access ──────────────────────────────────────────────────────

    public function test_the_configuration_screen_requires_authentication(): void
    {
        $this->getJson('/api/admin/system/configuration')->assertStatus(401);
    }

    public function test_a_farmer_cannot_reach_the_configuration_screen(): void
    {
        $this->actingAs($this->staff(Roles::FARMER), 'sanctum')
            ->getJson('/api/admin/system/configuration')
            ->assertStatus(403);
    }

    public function test_an_admin_can_read_the_configuration(): void
    {
        $this->actingAs($this->staff(Roles::ADMIN), 'sanctum')
            ->getJson('/api/admin/system/configuration')
            ->assertOk()
            ->assertJsonStructure(['groups' => [['key', 'label', 'settings']], 'can_manage_secrets']);
    }

    // ── Secrets ─────────────────────────────────────────────────────

    public function test_a_stored_secret_is_never_returned_to_the_client(): void
    {
        $super = $this->staff(Roles::SUPERADMIN);

        app(SettingsManager::class)->set('mail.smtp_password', 'the-real-app-password', $super);

        $response = $this->actingAs($super, 'sanctum')
            ->getJson('/api/admin/system/configuration')
            ->assertOk();

        // Not in the body anywhere — not masked, not partial, not at all.
        $this->assertStringNotContainsString('the-real-app-password', $response->getContent());

        $password = collect($response->json('groups'))
            ->firstWhere('key', 'mail')['settings'];
        $field = collect($password)->firstWhere('key', 'mail.smtp_password');

        $this->assertNull($field['value']);
        $this->assertTrue($field['is_set'], 'the client should still be told that a secret exists');
    }

    public function test_a_secret_is_encrypted_at_rest(): void
    {
        $super = $this->staff(Roles::SUPERADMIN);
        app(SettingsManager::class)->set('mail.smtp_password', 'the-real-app-password', $super);

        // Read the raw column, bypassing the model accessor.
        $raw = ConfigSetting::where('key', 'mail.smtp_password')->value('value');

        $this->assertNotSame('the-real-app-password', $raw);
        $this->assertStringNotContainsString('the-real-app-password', (string) $raw);
        $this->assertSame('the-real-app-password', app(SettingsManager::class)->get('mail.smtp_password'));
    }

    public function test_an_ordinary_admin_cannot_change_a_secret(): void
    {
        $this->actingAs($this->staff(Roles::ADMIN), 'sanctum')
            ->putJson('/api/admin/system/configuration', [
                'settings' => ['mail.smtp_password' => 'admin-should-not-manage-this'],
            ])
            ->assertStatus(403);

        $this->assertNull(app(SettingsManager::class)->get('mail.smtp_password'));
    }

    public function test_a_superadmin_can_change_a_secret(): void
    {
        $this->actingAs($this->staff(Roles::SUPERADMIN), 'sanctum')
            ->putJson('/api/admin/system/configuration', [
                'settings' => ['mail.smtp_password' => 'a-valid-app-password'],
            ])
            ->assertOk();

        $this->assertSame('a-valid-app-password', app(SettingsManager::class)->get('mail.smtp_password'));
    }

    public function test_submitting_a_blank_secret_leaves_the_existing_one_alone(): void
    {
        $super = $this->staff(Roles::SUPERADMIN);
        app(SettingsManager::class)->set('mail.smtp_password', 'keep-me', $super);

        // The UI cannot show the current value, so a blank field must mean
        // "unchanged" rather than "erase it".
        $this->actingAs($super, 'sanctum')
            ->putJson('/api/admin/system/configuration', ['settings' => ['mail.smtp_password' => '']])
            ->assertOk();

        $this->assertSame('keep-me', app(SettingsManager::class)->get('mail.smtp_password'));
    }

    // ── Whitelist ───────────────────────────────────────────────────

    public function test_an_unknown_setting_key_is_refused(): void
    {
        // The schema is a whitelist precisely so a crafted request cannot reach
        // an arbitrary config path such as the database password.
        $this->actingAs($this->staff(Roles::SUPERADMIN), 'sanctum')
            ->putJson('/api/admin/system/configuration', [
                'settings' => ['database.connections.pgsql.password' => 'pwned'],
            ])
            ->assertStatus(422);
    }

    public function test_an_invalid_value_is_refused_before_it_can_break_the_platform(): void
    {
        $this->actingAs($this->staff(Roles::SUPERADMIN), 'sanctum')
            ->putJson('/api/admin/system/configuration', [
                'settings' => ['mail.smtp_port' => 'not-a-port'],
            ])
            ->assertStatus(422);
    }

    // ── High-impact confirmation ────────────────────────────────────

    public function test_changing_the_application_url_demands_confirmation(): void
    {
        $super = $this->staff(Roles::SUPERADMIN);

        $this->actingAs($super, 'sanctum')
            ->putJson('/api/admin/system/configuration', [
                'settings' => ['app.url' => 'https://elsewhere.example'],
            ])
            ->assertStatus(409)
            ->assertJsonPath('requires_confirmation', 'app.url');

        $this->actingAs($super, 'sanctum')
            ->putJson('/api/admin/system/configuration', [
                'settings' => ['app.url' => 'https://elsewhere.example'],
                'confirm_high_impact' => true,
            ])
            ->assertOk();

        $this->assertSame('https://elsewhere.example', app(SettingsManager::class)->get('app.url'));
    }

    // ── Runtime overlay ─────────────────────────────────────────────

    public function test_a_saved_setting_overrides_laravel_runtime_config(): void
    {
        config(['mail.mailers.smtp.host' => 'from-env.example']);

        app(SettingsManager::class)->set('mail.smtp_host', 'from-database.example', $this->staff(Roles::SUPERADMIN));
        app(SettingsManager::class)->applyToRuntimeConfig();

        // This is the whole point of the architecture: the database wins over
        // the cached, env-derived config, without a cache clear or a deploy.
        $this->assertSame('from-database.example', config('mail.mailers.smtp.host'));
    }

    public function test_a_setting_with_no_database_value_falls_back_to_env(): void
    {
        // Nothing saved for this key, so the env fallback named by the schema
        // is what a caller sees.
        $this->assertNull(ConfigSetting::where('key', 'mail.smtp_host')->first());
        $this->assertSame(env('MAIL_HOST'), app(SettingsManager::class)->get('mail.smtp_host'));
    }

    public function test_clearing_a_setting_restores_the_env_fallback(): void
    {
        $super = $this->staff(Roles::SUPERADMIN);
        app(SettingsManager::class)->set('mail.smtp_host', 'from-database.example', $super);
        $this->assertSame('from-database.example', app(SettingsManager::class)->get('mail.smtp_host'));

        $this->actingAs($super, 'sanctum')
            ->putJson('/api/admin/system/configuration', ['settings' => ['mail.smtp_host' => '']])
            ->assertOk();

        $this->assertSame(env('MAIL_HOST'), app(SettingsManager::class)->get('mail.smtp_host'));
    }

    // ── Audit ───────────────────────────────────────────────────────

    public function test_a_configuration_change_is_audit_logged_without_the_value_of_a_secret(): void
    {
        $super = $this->staff(Roles::SUPERADMIN);

        app(SettingsManager::class)->set('mail.smtp_host', 'smtp.example.com', $super);
        app(SettingsManager::class)->set('mail.smtp_password', 'never-log-me', $super);

        $logs = AuditLog::where('auditable_type', ConfigSetting::class)->get();

        $this->assertCount(2, $logs);
        $this->assertSame($super->id, $logs->first()->actor_id);

        $serialised = $logs->toJson();
        $this->assertStringNotContainsString('never-log-me', $serialised);
        // The host is not a secret, so its value is recorded for accountability.
        $this->assertStringContainsString('smtp.example.com', $serialised);
    }

    // ── Test email ──────────────────────────────────────────────────

    public function test_a_test_email_is_refused_while_the_driver_is_log(): void
    {
        config(['mail.default' => 'log']);

        $this->actingAs($this->staff(Roles::SUPERADMIN), 'sanctum')
            ->postJson('/api/admin/system/test-email', ['to' => 'operator@mkulimaforum.app'])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_a_successful_test_email_is_recorded(): void
    {
        Mail::fake();
        config(['mail.default' => 'smtp']);

        $this->actingAs($this->staff(Roles::SUPERADMIN), 'sanctum')
            ->postJson('/api/admin/system/test-email', ['to' => 'operator@mkulimaforum.app'])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertNotNull(app(SettingsManager::class)->state('mail.last_test_at'));
    }

    // ── Webhook rotation ────────────────────────────────────────────

    public function test_rotating_a_webhook_secret_returns_it_exactly_once_and_stores_it_encrypted(): void
    {
        $super = $this->staff(Roles::SUPERADMIN);

        $response = $this->actingAs($super, 'sanctum')
            ->postJson('/api/admin/system/rotate-webhook-secret', ['channel' => 'sms'])
            ->assertOk();

        $secret = $response->json('secret');
        $this->assertNotEmpty($secret);
        $this->assertSame($secret, app(SettingsManager::class)->get('sms.webhook_secret'));

        // Shown once at rotation; never readable through the settings screen.
        $config = $this->actingAs($super, 'sanctum')
            ->getJson('/api/admin/system/configuration')->getContent();
        $this->assertStringNotContainsString($secret, $config);
    }

    public function test_an_ordinary_admin_cannot_rotate_a_webhook_secret(): void
    {
        $this->actingAs($this->staff(Roles::ADMIN), 'sanctum')
            ->postJson('/api/admin/system/rotate-webhook-secret', ['channel' => 'sms'])
            ->assertStatus(403);
    }

    // ── Readiness ───────────────────────────────────────────────────

    public function test_readiness_reports_checks_without_exposing_credentials(): void
    {
        $super = $this->staff(Roles::SUPERADMIN);
        app(SettingsManager::class)->set('mail.smtp_password', 'do-not-leak-me', $super);

        $response = $this->actingAs($super, 'sanctum')
            ->getJson('/api/admin/system/readiness')
            ->assertOk()
            ->assertJsonStructure(['checks' => [['group', 'name', 'status', 'blocking']], 'summary', 'ready']);

        $this->assertStringNotContainsString('do-not-leak-me', $response->getContent());
    }
}
