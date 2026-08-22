<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * One person, one account.
 *
 * Before this, OTP registration keyed on phone alone: a farmer who signed up
 * with an email in January and signed in with their phone in March ended up
 * with two accounts holding different farm records, orders and wallets.
 */
class AccountIdentityLinkingTest extends TestCase
{
    use RefreshDatabase;

    private const PHONE = '255712345678';

    protected function setUp(): void
    {
        parent::setUp();
        Tenant::firstOrCreate(
            ['country_code' => 'tz'],
            ['name' => 'Tanzania', 'currency' => 'TZS', 'is_active' => true]
        );
        // OTP is off by default in production; these tests exercise it on.
        config(['app.debug' => true]);
    }

    private function user(array $overrides = []): User
    {
        return User::provision(array_merge([
            'tenant_id' => Tenant::where('country_code', 'tz')->value('id'),
            'name' => 'Neema Mushi',
            'email' => 'neema'.uniqid().'@example.com',
            'password' => Hash::make('correct-horse-battery'),
            'role' => 'farmer',
            'status' => 'active',
            'preferred_language' => 'sw',
        ], $overrides));
    }

    private function issueOtp(string $phone, string $purpose): string
    {
        return app(OtpService::class)->generate($phone, $purpose)['code'];
    }

    // ── The duplicate this prevents ─────────────────────────────────

    public function test_a_signed_in_user_verifying_a_free_number_does_not_create_a_second_account(): void
    {
        $user = $this->user();
        $this->assertSame(1, User::count());

        $code = $this->issueOtp(self::PHONE, 'register');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/auth/otp/verify', [
                'phone' => self::PHONE,
                'code' => $code,
                'purpose' => 'register',
                'name' => 'Neema Mushi',
                'country_code' => 'tz',
            ])
            ->assertOk();

        // The whole point: still one account, now holding both identities.
        $this->assertSame(1, User::count());
        $user->refresh();
        $this->assertSame(self::PHONE, $user->phone);
        $this->assertNotNull($user->phone_verified_at);
        $this->assertNotNull($user->email);
    }

    public function test_an_anonymous_registration_still_creates_an_account(): void
    {
        $code = $this->issueOtp(self::PHONE, 'register');

        $this->postJson('/api/auth/otp/verify', [
            'phone' => self::PHONE,
            'code' => $code,
            'purpose' => 'register',
            'name' => 'Juma Said',
            'country_code' => 'tz',
        ])->assertOk();

        $this->assertSame(self::PHONE, User::where('phone', self::PHONE)->value('phone'));
    }

    public function test_a_number_belonging_to_someone_else_is_refused_not_merged(): void
    {
        $owner = $this->user(['phone' => self::PHONE, 'phone_verified_at' => now()]);
        $other = $this->user();

        $code = $this->issueOtp(self::PHONE, 'login');

        $this->actingAs($other, 'sanctum')
            ->postJson('/api/auth/otp/verify', [
                'phone' => self::PHONE,
                'code' => $code,
                'purpose' => 'login',
            ])
            ->assertStatus(422);

        // Neither account moved.
        $this->assertSame(self::PHONE, $owner->fresh()->phone);
        $this->assertNull($other->fresh()->phone);
    }

    // ── Explicit linking ────────────────────────────────────────────

    public function test_a_user_can_link_a_phone_number_to_their_account(): void
    {
        $user = $this->user();

        $request = $this->actingAs($user, 'sanctum')
            ->postJson('/api/auth/phone/link/request', ['phone' => self::PHONE])
            ->assertOk();

        $code = $request->json('dev_code');
        $this->assertNotNull($code, 'the local/testing debug code should be returned');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/auth/phone/link/confirm', ['phone' => self::PHONE, 'code' => $code])
            ->assertOk()
            ->assertJsonPath('identities.phone.value', self::PHONE)
            ->assertJsonPath('identities.phone.verified', true);

        $this->assertSame(1, User::count());
    }

    public function test_linking_a_number_that_belongs_to_another_account_is_refused_before_any_sms(): void
    {
        $this->user(['phone' => self::PHONE, 'phone_verified_at' => now()]);
        $other = $this->user();

        $this->actingAs($other, 'sanctum')
            ->postJson('/api/auth/phone/link/request', ['phone' => self::PHONE])
            ->assertStatus(422)
            ->assertJsonValidationErrors('phone');
    }

    public function test_a_wrong_code_does_not_link_the_number(): void
    {
        $user = $this->user();
        $this->issueOtp(self::PHONE, 'link');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/auth/phone/link/confirm', ['phone' => self::PHONE, 'code' => '000000'])
            ->assertStatus(422);

        $this->assertNull($user->fresh()->phone);
    }

    // ── Unlinking ───────────────────────────────────────────────────

    public function test_unlinking_requires_the_current_password(): void
    {
        $user = $this->user(['phone' => self::PHONE, 'phone_verified_at' => now()]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/auth/phone/link', ['current_password' => 'wrong'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('current_password');

        $this->assertSame(self::PHONE, $user->fresh()->phone);
    }

    public function test_a_user_can_unlink_a_phone_when_an_email_login_remains(): void
    {
        $user = $this->user(['phone' => self::PHONE, 'phone_verified_at' => now()]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/auth/phone/link', ['current_password' => 'correct-horse-battery'])
            ->assertOk();

        $user->refresh();
        $this->assertNull($user->phone);
        $this->assertNull($user->phone_verified_at);
    }

    public function test_the_last_way_into_an_account_cannot_be_removed(): void
    {
        // Phone-only account: no email, no password. Removing the number would
        // lock the owner out permanently.
        $user = User::provision([
            'tenant_id' => Tenant::where('country_code', 'tz')->value('id'),
            'name' => 'Phone Only',
            'phone' => self::PHONE,
            'phone_verified_at' => now(),
            'role' => 'farmer',
            'status' => 'active',
        ]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/auth/phone/link', ['current_password' => 'anything'])
            ->assertStatus(422);

        $this->assertSame(self::PHONE, $user->fresh()->phone);
    }

    // ── Identity summary ────────────────────────────────────────────

    public function test_the_identities_endpoint_describes_every_way_in(): void
    {
        $user = $this->user(['phone' => self::PHONE, 'phone_verified_at' => now()]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/auth/identities')
            ->assertOk()
            ->assertJsonPath('identities.phone.verified', true)
            ->assertJsonPath('identities.email.verified', false)
            ->assertJsonPath('identities.phone.can_unlink', true)
            ->assertJsonStructure(['identities' => ['email', 'phone', 'social']]);
    }
}
