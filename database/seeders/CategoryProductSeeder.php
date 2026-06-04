<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;

class CategoryProductSeeder extends Seeder
{
    public function run(): void
    {
        $seller = User::where('role', 'agrodealer')->first();
        if (!$seller) {
            $seller = User::factory()->create([
                'name' => 'Mkulima Agrovet',
                'email' => 'agrovet@demo.mkulima',
                'phone' => '255713000001',
                'role' => 'agrodealer',
                'tenant_id' => 1,
                'kyc_status' => 'verified',
                'status' => 'active',
            ]);
        }

        $categories = [
            ['name' => 'Seeds', 'slug' => 'seeds', 'icon' => 'seed'],
            ['name' => 'Fertilizers', 'slug' => 'fertilizers', 'icon' => 'flask'],
            ['name' => 'Pesticides', 'slug' => 'pesticides', 'icon' => 'spray'],
            ['name' => 'Tools & Equipment', 'slug' => 'tools', 'icon' => 'wrench'],
            ['name' => 'Animal Feed', 'slug' => 'animal-feed', 'icon' => 'bone'],
            ['name' => 'Irrigation', 'slug' => 'irrigation', 'icon' => 'droplet'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['slug' => $cat['slug'], 'tenant_id' => 1],
                [
                    'name' => $cat['name'],
                    'icon' => $cat['icon'],
                    'tenant_id' => 1,
                ]
            );
        }

        $products = [
            ['name' => 'Hybrid Maize Seeds - SC 719', 'category' => 'seeds', 'price' => 45000, 'stock' => 500, 'unit' => 'kg'],
            ['name' => 'Bean Seeds - Rosecoco', 'category' => 'seeds', 'price' => 12000, 'stock' => 300, 'unit' => 'kg'],
            ['name' => 'Rice Seeds - TXD 306', 'category' => 'seeds', 'price' => 18000, 'stock' => 200, 'unit' => 'kg'],
            ['name' => 'NPK Fertilizer 23-23-0', 'category' => 'fertilizers', 'price' => 65000, 'stock' => 150, 'unit' => '50kg bag'],
            ['name' => 'Urea Fertilizer 46%', 'category' => 'fertilizers', 'price' => 72000, 'stock' => 100, 'unit' => '50kg bag'],
            ['name' => 'CAN Fertilizer 26% N', 'category' => 'fertilizers', 'price' => 58000, 'stock' => 120, 'unit' => '50kg bag'],
            ['name' => 'DAP Fertilizer 18-46-0', 'category' => 'fertilizers', 'price' => 68000, 'stock' => 80, 'unit' => '50kg bag'],
            ['name' => 'Roundup Herbicide 1L', 'category' => 'pesticides', 'price' => 25000, 'stock' => 200, 'unit' => 'bottle'],
            ['name' => 'Actellic Dust 50g', 'category' => 'pesticides', 'price' => 8000, 'stock' => 500, 'unit' => 'sachet'],
            ['name' => 'Dudu Acelamectin 1L', 'category' => 'pesticides', 'price' => 35000, 'stock' => 100, 'unit' => 'bottle'],
            ['name' => 'Hoe - Heavy Duty', 'category' => 'tools', 'price' => 15000, 'stock' => 50, 'unit' => 'piece'],
            ['name' => 'Wheelbarrow', 'category' => 'tools', 'price' => 85000, 'stock' => 30, 'unit' => 'piece'],
            ['name' => 'Knapsack Sprayer 16L', 'category' => 'tools', 'price' => 45000, 'stock' => 40, 'unit' => 'piece'],
            ['name' => 'Dairy Meal 50kg', 'category' => 'animal-feed', 'price' => 35000, 'stock' => 100, 'unit' => 'bag'],
            ['name' => 'Layers Mash 50kg', 'category' => 'animal-feed', 'price' => 32000, 'stock' => 80, 'unit' => 'bag'],
            ['name' => 'Drip Irrigation Kit', 'category' => 'irrigation', 'price' => 180000, 'stock' => 20, 'unit' => 'set'],
            ['name' => 'Water Pump - 2HP', 'category' => 'irrigation', 'price' => 350000, 'stock' => 15, 'unit' => 'piece'],
            ['name' => 'Sprinkler Set', 'category' => 'irrigation', 'price' => 75000, 'stock' => 25, 'unit' => 'set'],
        ];

        foreach ($products as $prod) {
            $category = Category::where('slug', $prod['category'])->where('tenant_id', 1)->first();
            if ($category) {
                Product::firstOrCreate(
                    ['slug' => Str::slug($prod['name']), 'tenant_id' => 1],
                    [
                        'uuid' => Str::uuid(),
                        'name' => $prod['name'],
                        'category_id' => $category->id,
                        'user_id' => $seller->id,
                        'price' => $prod['price'],
                        'stock_quantity' => $prod['stock'],
                        'unit' => $prod['unit'],
                        'status' => 'active',
                        'tenant_id' => 1,
                        'description' => 'High quality ' . $prod['name'] . ' for Tanzanian farmers.',
                    ]
                );
            }
        }

        echo "Seeded " . Category::count() . " categories and " . Product::count() . " products\n";
    }
}
