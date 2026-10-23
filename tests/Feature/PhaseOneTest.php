<?php

namespace Tests\Feature;

use App\Filament\Pages\SystemConfiguration;
use App\Livewire\Auth\Login;
use App\Livewire\ContactForm;
use App\Models\ConfigSetting;
use App\Models\ForumCategory;
use App\Models\ForumThread;
use App\Models\MarketPrice;
use App\Models\Tenant;
use App\Models\User;
use App\Settings\SettingsManager;
use App\Support\KycBackfill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Tests\TestCase;

class PhaseOneTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Tenant::create([
            'name' => 'Tanzania',
            'country_code' => 'tz',
            'currency' => 'TZS',
        ]);
    }

    public function test_v1_register_is_farmer_only_and_uses_the_envelope(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Amina Juma',
            'email' => 'amina@example.com',
            'password' => 'nywila-ndefu-sana',
            'password_confirmation' => 'nywila-ndefu-sana',
            'role' => 'admin',
        ]);

        $response->assertStatus(422)->assertJsonStructure(['message', 'errors']);

        $created = $this->postJson('/api/v1/auth/register', [
            'name' => 'Amina Juma',
            'email' => 'amina@example.com',
            'password' => 'nywila-ndefu-sana',
            'password_confirmation' => 'nywila-ndefu-sana',
        ]);

        $created->assertCreated()
            ->assertJsonPath('data.user.role', 'farmer')
            ->assertJsonPath('data.user.email', 'amina@example.com')
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonStructure(['data' => ['token', 'user' => ['uuid']], 'meta']);

        $this->assertArrayNotHasKey('id', $created->json('data.user'));

        $token = $created->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.user.uuid', $created->json('data.user.uuid'));
    }

    public function test_v1_login_and_password_reset_do_not_reveal_whether_the_address_exists(): void
    {
        $user = User::factory()->create([
            'email' => 'farmer@example.com',
            'password' => Hash::make('nywila-ndefu-sana'),
            'status' => 'active',
        ]);

        $wrong = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'sio-nenosiri-hili',
        ]);
        $missing = $this->postJson('/api/v1/auth/login', [
            'email' => 'hakuna@example.com',
            'password' => 'sio-nenosiri-hili',
        ]);

        $wrong->assertStatus(401);
        $this->assertSame($wrong->json(), $missing->json());

        $known = $this->postJson('/api/v1/auth/password/forgot', ['email' => $user->email]);
        $unknown = $this->postJson('/api/v1/auth/password/forgot', ['email' => 'hakuna@example.com']);

        $known->assertOk();
        $this->assertSame($known->json(), $unknown->json());
        $this->assertSame(['data', 'meta'], array_keys($known->json()));
    }

    public function test_legacy_login_shape_is_unchanged(): void
    {
        $user = User::factory()->create([
            'email' => 'legacy@example.com',
            'password' => Hash::make('nywila-ndefu-sana'),
            'status' => 'active',
        ]);

        $this->postJson('/api/auth/login/email', [
            'email' => $user->email,
            'password' => 'nywila-ndefu-sana',
        ])->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.email', 'legacy@example.com');
    }

    public function test_v1_reads_weather_prices_and_forum_without_numeric_ids(): void
    {
        Http::fake();

        $this->getJson('/api/v1/weather/current?location=Dodoma')
            ->assertOk()
            ->assertJsonStructure(['data' => ['location', 'available'], 'meta']);

        MarketPrice::create([
            'commodity' => 'Mahindi',
            'market' => 'Kariakoo',
            'region' => 'Dar es Salaam',
            'min_price' => 500,
            'max_price' => 800,
            'avg_price' => 650,
            'unit' => 'kg',
            'currency' => 'TZS',
            'price_date' => now()->toDateString(),
            'source' => 'soko',
        ]);

        $prices = $this->getJson('/api/v1/market-prices?commodity=mahindi')
            ->assertOk()
            ->assertJsonPath('data.0.commodity', 'Mahindi')
            ->assertJsonPath('meta.total', 1);

        $this->assertArrayNotHasKey('id', $prices->json('data.0'));

        $this->getJson('/api/v1/market-prices/filters')
            ->assertOk()
            ->assertJsonPath('data.commodities.0', 'Mahindi');

        $author = User::factory()->create(['status' => 'active']);
        $category = ForumCategory::create([
            'tenant_id' => 1,
            'name' => 'Mahindi',
            'slug' => 'mahindi',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $thread = ForumThread::create([
            'tenant_id' => 1,
            'forum_category_id' => $category->id,
            'user_id' => $author->id,
            'title' => 'Mbolea ya mahindi',
            'body' => 'Niambie mbolea inayofaa.',
            'status' => 'active',
        ]);

        $categories = $this->getJson('/api/v1/forum/categories')->assertOk();
        $this->assertArrayNotHasKey('id', $categories->json('data.0'));
        $this->assertNotEmpty($categories->json('data.0.uuid'));

        $threads = $this->getJson('/api/v1/forum/threads?category='.$category->uuid)->assertOk();
        $this->assertSame($thread->uuid, $threads->json('data.0.uuid'));
        $this->assertArrayNotHasKey('id', $threads->json('data.0'));
        $this->assertArrayNotHasKey('id', $threads->json('data.0.user'));

        $this->getJson('/api/v1/forum/threads/'.$thread->uuid)
            ->assertOk()
            ->assertJsonPath('data.title', 'Mbolea ya mahindi');
    }

    public function test_kyc_columns_default_and_backfill_are_additive(): void
    {
        $untouched = User::factory()->create([
            'kyc_status' => 'pending',
            'kyc_documents' => null,
        ]);
        $submitted = User::factory()->create([
            'kyc_status' => 'pending',
            'kyc_documents' => ['id_type' => 'national_id'],
        ]);

        KycBackfill::notSubmittedWithoutDocuments();

        $this->assertSame('not_submitted', $untouched->fresh()->kyc_status);
        $this->assertSame('pending', $submitted->fresh()->kyc_status);

        $submitted->setPrivileged([
            'kyc_status' => 'unverified',
            'kyc_submitted_at' => now(),
        ]);

        $this->assertSame('unverified', $submitted->fresh()->kyc_status);
        $this->assertNotNull($submitted->fresh()->kyc_submitted_at);

        $default = DB::selectOne(
            "select column_default from information_schema.columns where table_name = 'users' and column_name = 'kyc_status'"
        );
        $this->assertStringContainsString('not_submitted', (string) $default->column_default);
    }

    public function test_admin_is_not_a_superadmin_and_cannot_open_secrets(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $super = User::factory()->create(['role' => 'superadmin', 'status' => 'active']);
        $farmer = User::factory()->create(['role' => 'farmer', 'status' => 'active']);

        $this->assertFalse($admin->can('viewAny', ConfigSetting::class));
        $this->assertTrue($super->can('viewAny', ConfigSetting::class));
        $this->assertFalse($admin->can('system.secrets.manage'));

        $this->actingAs($farmer)->get('/admin')->assertForbidden();
        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/configuration')->assertForbidden();
        $this->actingAs($super)->get('/admin')->assertRedirect();

        Livewire::actingAs($admin)->test(SystemConfiguration::class)->assertForbidden();
    }

    public function test_superadmin_can_rotate_a_secret_without_logging_it(): void
    {
        $super = User::factory()->create(['role' => 'superadmin', 'status' => 'active']);
        $secret = 'siri-ya-webhook-123456';
        $seen = [];

        Log::listen(function ($event) use (&$seen) {
            $seen[] = $event->message;
        });

        $this->actingAs($super);

        $page = Livewire::test(SystemConfiguration::class)
            ->set('values.sms__webhook_secret', $secret)
            ->call('save');

        $page->assertHasNoErrors();

        $this->assertSame($secret, app(SettingsManager::class)->get('sms.webhook_secret'));
        $this->assertContains('SMS webhook secret rotated', $seen);
        $this->assertStringNotContainsString($secret, implode("\n", $seen));

        $raw = DB::table('config_settings')
            ->where('key', 'sms.webhook_secret')
            ->value('value');
        $this->assertNotSame($secret, $raw);

        Livewire::test(SystemConfiguration::class)
            ->set('values.sms__webhook_secret', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame($secret, app(SettingsManager::class)->get('sms.webhook_secret'));
        $this->assertSame($raw, DB::table('config_settings')->where('key', 'sms.webhook_secret')->value('value'));
    }

    public function test_contact_form_stores_a_row_and_web_login_uses_the_same_message(): void
    {
        Livewire::test(ContactForm::class)
            ->set('name', 'Amina')
            ->set('email', 'amina@example.com')
            ->set('subject', 'farmer')
            ->set('message', 'Nahitaji msaada wa bei.')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'amina@example.com',
            'subject' => 'farmer',
        ]);

        User::factory()->create([
            'email' => 'web@example.com',
            'password' => Hash::make('nywila-ndefu-sana'),
            'status' => 'active',
        ]);

        $known = Livewire::test(Login::class)
            ->set('email', 'web@example.com')
            ->set('password', 'wrong-password-here')
            ->call('login');

        $unknown = Livewire::test(Login::class)
            ->set('email', 'hakuna@example.com')
            ->set('password', 'wrong-password-here')
            ->call('login');

        $this->assertSame(
            $known->errors()->get('email'),
            $unknown->errors()->get('email'),
        );
    }

    public function test_password_reset_revokes_tokens(): void
    {
        $user = User::factory()->create([
            'email' => 'reset@example.com',
            'password' => Hash::make('nywila-ndefu-sana'),
            'status' => 'active',
        ]);
        $user->createToken('mobile-app');
        $token = Password::broker()->createToken($user);

        $this->postJson('/api/v1/auth/password/reset', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'nywila-mpya-sanaa',
            'password_confirmation' => 'nywila-mpya-sanaa',
        ])->assertOk()->assertJsonStructure(['data', 'meta']);

        $this->assertSame(0, $user->tokens()->count());
        $this->assertTrue(Hash::check('nywila-mpya-sanaa', $user->fresh()->password));
    }
}
