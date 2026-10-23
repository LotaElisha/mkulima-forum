<?php

namespace Database\Seeders;

use App\Models\ForumCategory;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

/**
 * The eight Swahili forum categories. Safe to run in production: it matches
 * on slug, sets the sort order, and does not create threads or replies.
 */
class ForumCategorySeeder extends Seeder
{
    /**
     * @var list<array{slug: string, name: string, description: string, sort_order: int}>
     */
    private const CATEGORIES = [
        ['slug' => 'mazao', 'name' => 'Mazao', 'description' => 'Mazao, mbegu, na mavuno', 'sort_order' => 10],
        ['slug' => 'mifugo', 'name' => 'Mifugo', 'description' => 'Mifugo, maziwa, na kuku', 'sort_order' => 20],
        ['slug' => 'wadudu-na-magonjwa', 'name' => 'Wadudu na magonjwa', 'description' => 'Wadudu na magonjwa ya mazao', 'sort_order' => 30],
        ['slug' => 'udongo-na-mbolea', 'name' => 'Udongo na mbolea', 'description' => 'Udongo, mbolea, na rutuba', 'sort_order' => 40],
        ['slug' => 'hali-ya-hewa', 'name' => 'Hali ya hewa', 'description' => 'Hali ya hewa na misimu', 'sort_order' => 50],
        ['slug' => 'masoko-na-bei', 'name' => 'Masoko na bei', 'description' => 'Masoko na bei zilizorekodiwa', 'sort_order' => 60],
        ['slug' => 'teknolojia-na-zana', 'name' => 'Teknolojia na zana', 'description' => 'Zana na teknolojia ya kilimo', 'sort_order' => 70],
        ['slug' => 'maswali-ya-jumla', 'name' => 'Maswali ya jumla', 'description' => 'Maswali mengine ya kilimo', 'sort_order' => 80],
    ];

    public function run(): void
    {
        $tenantId = Tenant::query()->where('country_code', 'tz')->value('id')
            ?? Tenant::query()->value('id');

        if ($tenantId === null) {
            return;
        }

        foreach (self::CATEGORIES as $row) {
            $existing = ForumCategory::query()->where('slug', $row['slug'])->first();

            if ($existing) {
                $existing->sort_order = $row['sort_order'];
                $existing->save();

                continue;
            }

            ForumCategory::query()->create([
                'tenant_id' => $tenantId,
                'name' => $row['name'],
                'slug' => $row['slug'],
                'description' => $row['description'],
                'sort_order' => $row['sort_order'],
                'is_active' => true,
            ]);
        }
    }
}
