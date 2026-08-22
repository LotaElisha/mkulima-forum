<?php

namespace Tests\Feature;

use App\Contracts\SmsProvider;
use App\Models\ServiceProvider;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Sms\Providers\AfricasTalkingProvider;
use App\Services\Sms\Providers\LogProvider;
use App\Services\Sms\Providers\TwilioProvider;
use App\Services\Sms\SmsProviderManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Regression cover for the upload, webhook and SMS-provider hardening.
 *
 * Each test pins a specific hole that was open before, so a future refactor
 * that reopens one fails here rather than in production.
 */
class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(): Tenant
    {
        return Tenant::firstOrCreate(
            ['country_code' => 'tz'],
            ['name' => 'Tanzania', 'currency' => 'TZS', 'is_active' => true]
        );
    }

    private function farmer(): User
    {
        return User::create([
            'tenant_id' => $this->tenant()->id,
            'name' => 'Juma Said',
            'email' => 'juma@example.com',
            'password' => Hash::make('correct-horse-battery'),
            'role' => 'farmer',
            'status' => 'active',
            'preferred_language' => 'sw',
        ]);
    }

    // ── Uploads ─────────────────────────────────────────────────────

    public function test_an_svg_cannot_be_uploaded_as_an_avatar(): void
    {
        Storage::fake('public');
        $user = $this->farmer();

        // Laravel's `image` rule accepts SVG, and this file used to land on
        // the public disk with a first-party URL — stored XSS against anyone
        // who viewed the profile.
        $svg = UploadedFile::fake()->createWithContent(
            'avatar.svg',
            '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'
        );

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/auth/profile', ['avatar' => $svg])
            ->assertStatus(422)
            ->assertJsonValidationErrors('avatar');

        $this->assertCount(0, Storage::disk('public')->allFiles());
    }

    public function test_a_jpeg_is_still_accepted_as_an_avatar(): void
    {
        Storage::fake('public');
        $user = $this->farmer();

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/auth/profile', [
                'avatar' => UploadedFile::fake()->image('avatar.jpg', 200, 200),
            ])
            ->assertOk();

        $this->assertNotEmpty(Storage::disk('public')->allFiles());
    }

    public function test_service_booking_media_rejects_an_arbitrary_file(): void
    {
        Storage::fake('public');
        $user = $this->farmer();

        $owner = User::create([
            'tenant_id' => $this->tenant()->id,
            'name' => 'Agronomist',
            'email' => 'agro@example.com',
            'password' => Hash::make('correct-horse-battery'),
            'role' => 'agronomist',
            'status' => 'active',
        ]);

        $provider = ServiceProvider::create([
            'tenant_id' => $this->tenant()->id,
            'user_id' => $owner->id,
            'business_name' => 'Kilimo Bora',
            'region' => 'Arusha',
            'district' => 'Arumeru',
            'service_type' => 'agronomist',
            'consultation_fee' => 10000,
            'visit_fee' => 25000,
            'is_active' => true,
            'verification_status' => 'verified',
        ]);

        // This endpoint validated `file|max:5120` — any type at all — and wrote
        // straight to the public disk.
        $this->actingAs($user, 'sanctum')
            ->postJson('/api/services/bookings', [
                'provider_uuid' => $provider->uuid,
                'booking_type' => 'consultation',
                'scheduled_at' => now()->addDays(2)->toIso8601String(),
                'media' => [
                    UploadedFile::fake()->createWithContent('payload.html', '<script>alert(1)</script>'),
                ],
            ])
            ->assertStatus(422);

        $this->assertCount(0, Storage::disk('public')->allFiles());
    }

    // ── Webhooks ────────────────────────────────────────────────────

    public function test_sms_webhook_is_refused_without_the_shared_secret(): void
    {
        config(['services.sms.webhook_secret' => 'a-real-secret']);

        // /api/sms/receive used to be fully open, and each call ran a market
        // price query plus an outbound OpenWeather call on a metered key.
        $this->postJson('/api/sms/receive', ['from' => '255700000000', 'text' => 'BEI mahindi'])
            ->assertStatus(401);
    }

    public function test_sms_webhook_is_accepted_with_the_shared_secret(): void
    {
        config(['services.sms.webhook_secret' => 'a-real-secret']);

        $this->postJson(
            '/api/sms/receive',
            ['from' => '255700000000', 'text' => 'MSAADA'],
            ['X-Webhook-Signature' => 'a-real-secret']
        )->assertOk();
    }

    public function test_sms_webhook_rejects_a_wrong_secret(): void
    {
        config(['services.sms.webhook_secret' => 'a-real-secret']);

        $this->postJson(
            '/api/sms/receive',
            ['from' => '255700000000', 'text' => 'MSAADA'],
            ['X-Webhook-Signature' => 'not-the-secret']
        )->assertStatus(401);
    }

    // ── SMS provider abstraction ────────────────────────────────────

    public function test_the_sms_gateway_can_be_swapped_by_configuration_alone(): void
    {
        $manager = new SmsProviderManager;

        $this->assertInstanceOf(AfricasTalkingProvider::class, $manager->driver('africastalking'));
        $this->assertInstanceOf(TwilioProvider::class, $manager->driver('twilio'));
        $this->assertInstanceOf(LogProvider::class, $manager->driver('log'));
    }

    public function test_an_unknown_gateway_falls_back_to_the_log_driver_instead_of_failing(): void
    {
        // A typo in SMS_PROVIDER must not take sign-in down, and must never
        // silently route messages through the wrong aggregator.
        $this->assertInstanceOf(LogProvider::class, (new SmsProviderManager)->driver('does-not-exist'));
    }

    public function test_every_provider_satisfies_the_contract(): void
    {
        $manager = new SmsProviderManager;

        foreach ($manager->available() as $name) {
            $provider = $manager->driver($name);
            $this->assertInstanceOf(SmsProvider::class, $provider);
            $this->assertSame($name, $provider->name());
        }
    }
}
