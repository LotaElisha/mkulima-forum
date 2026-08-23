<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Services\Auth\SocialIdentityVerifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class EmailAndSocialAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Tenant::create(['name' => 'Tanzania', 'country_code' => 'tz', 'currency' => 'TZS']);
    }

    public function test_email_is_the_default_registration_method_and_phone_is_optional(): void
    {
        $response = $this->postJson('/api/auth/register/email', [
            'name' => 'Asha Mkulima',
            'email' => 'asha@example.com',
            'password' => 'A-strong-passphrase-2026',
            'password_confirmation' => 'A-strong-passphrase-2026',
            'role' => 'farmer',
            'country_code' => 'tz',
        ]);

        $response->assertOk()->assertJsonPath('user.email', 'asha@example.com');
        $this->assertNotEmpty($response->json('token'));
        $this->assertDatabaseHas('users', ['email' => 'asha@example.com', 'phone' => null, 'status' => 'active']);
    }

    public function test_social_identity_is_verified_and_linked_to_one_local_user(): void
    {
        $verifier = Mockery::mock(SocialIdentityVerifier::class);
        $verifier->shouldReceive('verify')->twice()->with('google', 'valid-google-token')->andReturn([
            'id' => 'google-user-123', 'email' => 'google@example.com',
            'name' => 'Google Farmer', 'avatar' => null,
        ]);
        $this->app->instance(SocialIdentityVerifier::class, $verifier);

        $payload = ['provider' => 'google', 'identity_token' => 'valid-google-token', 'role' => 'farmer'];
        $this->postJson('/api/auth/social', $payload)->assertOk()->assertJsonPath('user.email', 'google@example.com');
        $this->postJson('/api/auth/social', $payload)->assertOk();

        $this->assertSame(1, User::where('email', 'google@example.com')->count());
        $this->assertDatabaseHas('social_accounts', ['provider' => 'google', 'provider_user_id' => 'google-user-123']);
    }

    public function test_social_endpoint_rejects_an_unsupported_provider(): void
    {
        $this->postJson('/api/auth/social', ['provider' => 'facebook', 'identity_token' => 'token'])
            ->assertUnprocessable()->assertJsonValidationErrors('provider');
    }

    public function test_apple_android_callback_only_forwards_expected_fields(): void
    {
        $response = $this->post('/api/auth/apple/callback', [
            'code' => 'apple-code', 'id_token' => 'apple-token', 'unexpected' => 'discard-me',
        ]);

        $response->assertRedirect();
        $this->assertStringStartsWith('intent://callback?', $response->headers->get('Location'));
        $this->assertStringNotContainsString('discard-me', $response->headers->get('Location'));
    }
}
