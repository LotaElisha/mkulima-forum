<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ForumCategory;
use App\Models\ForumThread;
use App\Models\MarketPrice;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Tenant::firstOrCreate(['id' => 1], ['name' => 'Tanzania', 'country_code' => 'tz']);

        $seller = User::factory()->create(['role' => 'seller', 'tenant_id' => 1]);
        $category = Category::create([
            'tenant_id' => 1,
            'uuid' => (string) Str::uuid(),
            'name' => 'Mbegu',
            'slug' => 'mbegu',
        ]);

        Product::create([
            'tenant_id' => 1,
            'user_id' => $seller->id,
            'category_id' => $category->id,
            'uuid' => (string) Str::uuid(),
            'name' => 'Mbegu za Mahindi DK8031',
            'slug' => 'mbegu-za-mahindi-dk8031',
            'description' => 'Mbegu bora za mahindi kwa ukanda wa kati',
            'price' => 15000,
            'stock_quantity' => 50,
            'status' => 'active',
        ]);

        $forumCategory = ForumCategory::create([
            'tenant_id' => 1,
            'uuid' => (string) Str::uuid(),
            'name' => 'Kilimo',
            'slug' => 'kilimo',
        ]);

        ForumThread::create([
            'tenant_id' => 1,
            'uuid' => (string) Str::uuid(),
            'forum_category_id' => $forumCategory->id,
            'user_id' => $seller->id,
            'title' => 'Best maize varieties for Morogoro',
            'body' => 'Which maize seed performs best?',
            'status' => 'active',
        ]);

        ForumThread::create([
            'tenant_id' => 1,
            'uuid' => (string) Str::uuid(),
            'forum_category_id' => $forumCategory->id,
            'user_id' => $seller->id,
            'title' => 'Hidden misleading thread about mahindi',
            'body' => 'hidden content',
            'status' => 'hidden',
        ]);

        User::factory()->create([
            'role' => 'agronomist',
            'tenant_id' => 1,
            'name' => 'Dkt. Neema Mtaalamu',
            'is_verified_expert' => true,
            'expert_title' => 'Afisa Ugani — Mahindi',
        ]);

        MarketPrice::create([
            'commodity' => 'mahindi',
            'market' => 'Kariakoo',
            'region' => 'Dar es Salaam',
            'min_price' => 42000,
            'max_price' => 48000,
            'avg_price' => 45000,
            'unit' => 'gunia',
            'price_date' => now()->toDateString(),
        ]);
    }

    public function test_swahili_query_finds_grouped_results(): void
    {
        $response = $this->getJson('/api/search?q=mahindi');

        $response->assertOk()
            ->assertJsonPath('results.products.0.name', 'Mbegu za Mahindi DK8031')
            ->assertJsonPath('results.market_prices.0.commodity', 'mahindi')
            ->assertJsonPath('results.experts.0.name', 'Dkt. Neema Mtaalamu');

        $this->assertGreaterThanOrEqual(3, $response->json('total'));
    }

    public function test_english_synonym_finds_swahili_content_and_vice_versa(): void
    {
        // "maize" must find the "mahindi" product via synonym expansion
        $this->getJson('/api/search?q=maize')
            ->assertOk()
            ->assertJsonPath('results.products.0.name', 'Mbegu za Mahindi DK8031');

        // "mahindi" must find the English-titled thread
        $threads = $this->getJson('/api/search?q=mahindi')->json('results.threads');
        $this->assertContains(
            'Best maize varieties for Morogoro',
            array_column($threads, 'title')
        );
    }

    public function test_hidden_content_is_excluded(): void
    {
        $threads = $this->getJson('/api/search?q=mahindi')->json('results.threads');

        $this->assertNotContains(
            'Hidden misleading thread about mahindi',
            array_column($threads, 'title')
        );
    }

    public function test_no_results_returns_suggestions(): void
    {
        $response = $this->getJson('/api/search?q=zzzhaipatikani');

        $response->assertOk()
            ->assertJsonPath('total', 0);

        $this->assertNotEmpty($response->json('suggestions'));
    }

    public function test_short_query_rejected(): void
    {
        $this->getJson('/api/search?q=a')->assertStatus(422);
    }
}
