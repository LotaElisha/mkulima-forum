<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * admin:create resets passwords on the production admin account. It must never
 * change that account's role as a side effect: production has exactly one
 * admin, a superadmin, and a demotion locks them out of the secrets page.
 */
class CreateAdminUserCommandTest extends TestCase
{
    use RefreshDatabase;

    private const PROMPT = 'Enter a strong administrator password';

    private const NEW_PASSWORD = 'a-brand-new-long-password';

    protected function setUp(): void
    {
        parent::setUp();

        Tenant::firstOrCreate(
            ['country_code' => 'tz'],
            ['name' => 'Tanzania', 'currency' => 'TZS', 'is_active' => true]
        );
    }

    private function existing(string $role, string $status = 'active'): User
    {
        return User::provision([
            'tenant_id' => Tenant::first()->id,
            'name' => 'Existing',
            'email' => 'owner@mkulimaforum.com',
            'password' => Hash::make('the-old-long-password'),
            'role' => $role,
            'status' => $status,
        ]);
    }

    public function test_resetting_a_superadmin_keeps_them_superadmin(): void
    {
        $user = $this->existing(Roles::SUPERADMIN, 'suspended');

        $this->artisan('admin:create', ['email' => $user->email])
            ->expectsQuestion(self::PROMPT, self::NEW_PASSWORD)
            ->assertSuccessful();

        $user->refresh();
        $this->assertSame(Roles::SUPERADMIN, $user->role);
        $this->assertSame('active', $user->status);
        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $user->password));
        $this->assertTrue($user->can('viewAny', \App\Models\ConfigSetting::class));
    }

    public function test_resetting_an_admin_keeps_them_admin(): void
    {
        $user = $this->existing(Roles::ADMIN);

        $this->artisan('admin:create', ['email' => $user->email])
            ->expectsQuestion(self::PROMPT, self::NEW_PASSWORD)
            ->assertSuccessful();

        $this->assertSame(Roles::ADMIN, $user->refresh()->role);
    }

    public function test_reset_changes_role_only_when_asked_explicitly(): void
    {
        $user = $this->existing(Roles::ADMIN);

        $this->artisan('admin:create', ['email' => $user->email, '--role' => Roles::SUPERADMIN])
            ->expectsQuestion(self::PROMPT, self::NEW_PASSWORD)
            ->assertSuccessful();

        $this->assertSame(Roles::SUPERADMIN, $user->refresh()->role);
    }

    public function test_resetting_a_non_admin_does_not_promote_them(): void
    {
        $user = $this->existing(Roles::FARMER);

        $this->artisan('admin:create', ['email' => $user->email])
            ->expectsQuestion(self::PROMPT, self::NEW_PASSWORD)
            ->expectsOutputToContain('Pass --role=admin')
            ->assertSuccessful();

        $this->assertSame(Roles::FARMER, $user->refresh()->role);
    }

    public function test_a_short_password_is_refused_and_nothing_changes(): void
    {
        $user = $this->existing(Roles::SUPERADMIN);
        $before = $user->password;

        $this->artisan('admin:create', ['email' => $user->email])
            ->expectsQuestion(self::PROMPT, 'elevenchars')
            ->expectsOutput('Password must contain at least 12 characters.')
            ->assertFailed();

        $user->refresh();
        $this->assertSame($before, $user->password);
        $this->assertSame(Roles::SUPERADMIN, $user->role);
    }

    public function test_an_unknown_role_is_refused_before_prompting(): void
    {
        $user = $this->existing(Roles::SUPERADMIN);

        $this->artisan('admin:create', ['email' => $user->email, '--role' => Roles::FARMER])
            ->assertFailed();

        $this->assertSame(Roles::SUPERADMIN, $user->refresh()->role);
    }

    public function test_a_new_account_is_created_as_admin_by_default(): void
    {
        $this->artisan('admin:create', ['email' => 'new@mkulimaforum.com'])
            ->expectsQuestion(self::PROMPT, self::NEW_PASSWORD)
            ->assertSuccessful();

        $user = User::where('email', 'new@mkulimaforum.com')->firstOrFail();
        $this->assertSame(Roles::ADMIN, $user->role);
        $this->assertSame('active', $user->status);
        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $user->password));
    }

    public function test_a_new_account_can_be_created_as_superadmin(): void
    {
        $this->artisan('admin:create', ['email' => 'new@mkulimaforum.com', '--role' => Roles::SUPERADMIN])
            ->expectsQuestion(self::PROMPT, self::NEW_PASSWORD)
            ->assertSuccessful();

        $this->assertSame(Roles::SUPERADMIN, User::where('email', 'new@mkulimaforum.com')->value('role'));
    }
}
