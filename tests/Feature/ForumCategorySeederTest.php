<?php

namespace Tests\Feature;

use App\Models\ForumCategory;
use App\Models\ForumThread;
use App\Models\MarketPrice;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\ForumCategorySeeder;
use Database\Seeders\ForumSeeder;
use Database\Seeders\MarketPriceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForumCategorySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_swahili_categories_are_idempotent(): void
    {
        Tenant::query()->create([
            'country_code' => 'tz',
            'name' => 'Tanzania',
            'currency' => 'TZS',
            'timezone' => 'Africa/Dar_es_Salaam',
            'is_active' => true,
        ]);

        (new ForumCategorySeeder)->run();
        $uuid = ForumCategory::query()->where('slug', 'mazao')->value('uuid');
        (new ForumCategorySeeder)->run();

        $this->assertSame(8, ForumCategory::query()->count());
        $this->assertSame($uuid, ForumCategory::query()->where('slug', 'mazao')->value('uuid'));
        $this->assertSame(
            [10, 20, 30, 40, 50, 60, 70, 80],
            ForumCategory::query()->orderBy('sort_order')->pluck('sort_order')->all()
        );
        $this->assertSame(0, ForumThread::query()->count());
    }

    public function test_sample_forum_and_prices_do_not_seed_in_production(): void
    {
        Tenant::query()->create([
            'country_code' => 'tz',
            'name' => 'Tanzania',
            'currency' => 'TZS',
            'timezone' => 'Africa/Dar_es_Salaam',
            'is_active' => true,
        ]);
        User::factory()->create(['tenant_id' => 1, 'role' => 'admin']);
        $this->app->detectEnvironment(fn () => 'production');

        (new ForumSeeder)->run();
        (new MarketPriceSeeder)->run();

        $this->assertSame(0, ForumCategory::query()->count());
        $this->assertSame(0, ForumThread::query()->count());
        $this->assertSame(0, MarketPrice::query()->count());
        $this->assertStringNotContainsString('Mwea', file_get_contents(database_path('seeders/ForumCategorySeeder.php')));
    }
}
