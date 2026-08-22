<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Covers the email authentication lifecycle that did not exist before:
 * verification, password reset, password change and proof-of-ownership email
 * change. Each test names the failure it prevents rather than the method it
 * calls.
 */
class PasswordAndEmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant();
    }

    private function tenant(): Tenant
    {
        return Tenant::firstOrCreate(
            ['country_code' => 'tz'],
            ['name' => 'Tanzania', 'currency' => 'TZS', 'is_active' => true]
        );
    }

    private function farmer(array $overrides = []): User
    {
        return User::provision(array_merge([
            'tenant_id' => $this->tenant()->id,
            'name' => 'Neema Mushi',
            'email' => 'neema@example.com',
            'password' => Hash::make('correct-horse-battery'),
            'role' => 'farmer',
            'status' => 'active',
            'preferred_language' => 'sw',
        ], $overrides));
    }

    // ── Registration ────────────────────────────────────────────────

    public function test_registration_leaves_the_email_unverified_and_sends_a_link(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/auth/register/email', [
            'name' => 'Neema Mushi',
            'email' => 'Neema@Example.com',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
        ]);

        $response->assertOk();

        $user = User::where('email', 'neema@example.com')->firstOrFail();

        // Registration used to hand out a token and never touch this column,
        // so "verified email" was permanently false for every account.
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_registration_rejects_a_password_under_twelve_characters(): void
    {
        $this->postJson('/api/auth/register/email', [
            'name' => 'Neema',
            'email' => 'short@example.com',
            'password' => 'short123',
            'password_confirmation' => 'short123',
        ])->assertStatus(422)->assertJsonValidationErrors('password');
    }

    // ── Verification link ───────────────────────────────────────────

    public function test_a_valid_signed_link_verifies_the_address(): void
    {
        $user = $this->farmer();

        $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);

        $this->get($url)->assertRedirect('/login?verified=success');
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_an_unsigned_link_cannot_verify_an_address(): void
    {
        $user = $this->farmer();

        // Hand-built URL with no signature: the middleware must refuse it,
        // otherwise anyone could verify any account by guessing an id.
        $this->get("/email/verify/{$user->id}/".sha1($user->email))
            ->assertStatus(403);

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_a_link_for_a_different_address_is_refused(): void
    {
        $user = $this->farmer();

        $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), [
            'id' => $user->id,
            'hash' => sha1('attacker@example.com'),
        ]);

        $this->get($url)->assertRedirect('/login?verified=invalid');
        $this->assertNull($user->fresh()->email_verified_at);
    }

    // ── Password reset ──────────────────────────────────────────────

    public function test_forgot_password_sends_a_reset_link(): void
    {
        Notification::fake();
        $user = $this->farmer();

        $this->postJson('/api/auth/password/forgot', ['email' => 'neema@example.com'])
            ->assertOk();

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_forgot_password_does_not_reveal_whether_an_account_exists(): void
    {
        Notification::fake();
        $this->farmer();

        $known = $this->postJson('/api/auth/password/forgot', ['email' => 'neema@example.com']);
        $unknown = $this->postJson('/api/auth/password/forgot', ['email' => 'nobody@example.com']);

        // Identical status and body, or this endpoint becomes a way to test
        // which addresses hold MkulimaForum accounts.
        $this->assertSame($known->status(), $unknown->status());
        $this->assertSame($known->json('message'), $unknown->json('message'));
    }

    public function test_reset_changes_the_password_verifies_the_email_and_revokes_tokens(): void
    {
        $user = $this->farmer();
        $user->createToken('mobile-app')->plainTextToken;
        $this->assertSame(1, $user->tokens()->count());

        $token = app('auth.password.broker')->createToken($user);

        $this->postJson('/api/auth/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'a-brand-new-passphrase',
            'password_confirmation' => 'a-brand-new-passphrase',
        ])->assertOk();

        $user->refresh();
        $this->assertTrue(Hash::check('a-brand-new-passphrase', $user->password));
        // A reset proves inbox control, so it also settles verification.
        $this->assertNotNull($user->email_verified_at);
        // And it must not leave an attacker's session alive.
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_a_reset_token_cannot_be_replayed(): void
    {
        $user = $this->farmer();
        $token = app('auth.password.broker')->createToken($user);

        $payload = [
            'token' => $token,
            'email' => $user->email,
            'password' => 'a-brand-new-passphrase',
            'password_confirmation' => 'a-brand-new-passphrase',
        ];

        $this->postJson('/api/auth/password/reset', $payload)->assertOk();
        $this->postJson('/api/auth/password/reset', $payload)->assertStatus(422);
    }

    // ── Password change ─────────────────────────────────────────────

    public function test_changing_a_password_requires_the_current_one(): void
    {
        $user = $this->farmer();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/auth/password/change', [
                'current_password' => 'not-the-password',
                'password' => 'another-good-passphrase',
                'password_confirmation' => 'another-good-passphrase',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('current_password');

        $this->assertTrue(Hash::check('correct-horse-battery', $user->fresh()->password));
    }

    public function test_changing_a_password_signs_out_other_devices(): void
    {
        $user = $this->farmer();
        $user->createToken('other-phone');
        $user->createToken('old-tablet');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/auth/password/change', [
                'current_password' => 'correct-horse-battery',
                'password' => 'another-good-passphrase',
                'password_confirmation' => 'another-good-passphrase',
            ])
            ->assertOk();

        // actingAs does not create a stored token, so every stored token here
        // belongs to another device and all of them must be gone.
        $this->assertSame(0, $user->fresh()->tokens()->count());
    }

    // ── Email change ────────────────────────────────────────────────

    public function test_profile_update_can_no_longer_change_the_email(): void
    {
        $user = $this->farmer();

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/auth/profile', [
                'name' => 'Neema M.',
                'email' => 'attacker@example.com',
            ])
            ->assertOk();

        // The whole point: a stolen bearer token must not be able to move the
        // account to an inbox the attacker controls.
        $this->assertSame('neema@example.com', $user->fresh()->email);
    }

    public function test_email_change_is_staged_and_requires_the_current_password(): void
    {
        Notification::fake();
        $user = $this->farmer(['email_verified_at' => now()]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/auth/email/change', [
                'email' => 'new@example.com',
                'current_password' => 'wrong-password',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('current_password');

        $this->assertNull($user->fresh()->pending_email);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/auth/email/change', [
                'email' => 'new@example.com',
                'current_password' => 'correct-horse-battery',
            ])
            ->assertOk();

        $user->refresh();
        // Staged, not applied — the old address keeps working until the new
        // one is proved.
        $this->assertSame('new@example.com', $user->pending_email);
        $this->assertSame('neema@example.com', $user->email);
        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_verifying_the_staged_address_promotes_it(): void
    {
        $user = $this->farmer([
            'email_verified_at' => now(),
            'pending_email' => 'new@example.com',
            'pending_email_requested_at' => now(),
        ]);

        $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), [
            'id' => $user->id,
            'hash' => sha1('new@example.com'),
        ]);

        $this->get($url)->assertRedirect('/login?verified=email-changed');

        $user->refresh();
        $this->assertSame('new@example.com', $user->email);
        $this->assertNull($user->pending_email);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_a_staged_address_claimed_by_someone_else_is_not_promoted(): void
    {
        $this->farmer(['email' => 'taken@example.com']);
        $user = $this->farmer([
            'email' => 'neema2@example.com',
            'pending_email' => 'taken@example.com',
            'pending_email_requested_at' => now(),
        ]);

        $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), [
            'id' => $user->id,
            'hash' => sha1('taken@example.com'),
        ]);

        $this->get($url)->assertRedirect('/login?verified=taken');

        $user->refresh();
        $this->assertSame('neema2@example.com', $user->email);
        $this->assertNull($user->pending_email);
    }
}
