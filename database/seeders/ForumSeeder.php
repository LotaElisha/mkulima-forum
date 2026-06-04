<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ForumCategory;
use App\Models\ForumThread;
use App\Models\ForumReply;
use App\Models\User;
use Illuminate\Support\Str;

class ForumSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (!$user) return;

        $categories = [
            ['name' => 'Crop Farming', 'slug' => 'crop-farming', 'description' => 'Discussions about maize, rice, beans, and other crops'],
            ['name' => 'Livestock', 'slug' => 'livestock', 'description' => 'Cattle, goats, poultry, and dairy farming'],
            ['name' => 'Market Prices', 'slug' => 'market-prices', 'description' => 'Latest market prices for agricultural products'],
            ['name' => 'Pest Control', 'slug' => 'pest-control', 'description' => 'Share pest control methods and solutions'],
            ['name' => 'Weather Updates', 'slug' => 'weather', 'description' => 'Weather forecasts and farming seasons'],
            ['name' => 'Equipment', 'slug' => 'equipment', 'description' => 'Tools, machinery, and irrigation systems'],
        ];

        foreach ($categories as $cat) {
            ForumCategory::firstOrCreate(
                ['slug' => $cat['slug'], 'tenant_id' => 1],
                [
                    'name' => $cat['name'],
                    'description' => $cat['description'],
                    'tenant_id' => 1,
                ]
            );
        }

        $threads = [
            ['category' => 'crop-farming', 'title' => 'Best maize varieties for Morogoro region?', 'body' => 'I am planning to plant maize this season. Which varieties perform best in Morogoro? I have heard SC 719 is good.'],
            ['category' => 'crop-farming', 'title' => 'Rice farming tips for Mwea', 'body' => 'What are the best practices for rice farming in lowland areas? I need advice on water management.'],
            ['category' => 'livestock', 'title' => 'Dairy cow feeding schedule', 'body' => 'How many times should I feed my dairy cow per day? Currently doing 3 times but milk production is low.'],
            ['category' => 'market-prices', 'title' => 'Current maize prices in Dar es Salaam', 'body' => 'Anyone knows the current wholesale price for maize per 100kg bag in Dar?'],
            ['category' => 'pest-control', 'title' => 'Fall armyworm control methods', 'body' => 'Fall armyworm is destroying my maize. What pesticides are effective and affordable?'],
            ['category' => 'weather', 'title' => 'Rain forecast for this planting season', 'body' => 'When is the expected start of the long rains? I want to plan my planting.'],
        ];

        foreach ($threads as $t) {
            $cat = ForumCategory::where('slug', $t['category'])->where('tenant_id', 1)->first();
            if ($cat) {
                $thread = ForumThread::firstOrCreate(
                    ['title' => $t['title'], 'tenant_id' => 1],
                    [
                        'uuid' => Str::uuid(),
                        'user_id' => $user->id,
                        'forum_category_id' => $cat->id,
                        'title' => $t['title'],
                        'body' => $t['body'],
                        'tenant_id' => 1,
                    ]
                );

                // Add replies
                $replies = [
                    'I have used SC 719 for 2 seasons now. Very good yield!',
                    'Try also SC 620, it matures faster.',
                    'Make sure you prepare the soil well before planting.',
                ];

                foreach ($replies as $i => $reply) {
                    ForumReply::firstOrCreate(
                        ['forum_thread_id' => $thread->id, 'body' => $reply],
                        [
                            'user_id' => $user->id,
                            'body' => $reply,
                            'tenant_id' => 1,
                        ]
                    );
                }
            }
        }

        echo "Seeded " . ForumCategory::count() . " forum categories, " . ForumThread::count() . " threads, " . ForumReply::count() . " replies\n";
    }
}
