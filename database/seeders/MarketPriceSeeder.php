<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MarketPrice;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MarketPriceSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::role('admin')->first() ?? User::first();

        $records = [
            ['commodity' => 'Mahindi (Maize)', 'market' => 'Kariakoo', 'region' => 'Dar es Salaam', 'min_price' => 65000, 'max_price' => 78000, 'avg_price' => 72000, 'unit' => 'sack 100kg', 'source' => 'Wiki ya Wakulima'],
            ['commodity' => 'Mahindi (Maize)', 'market' => 'Ifakara', 'region' => 'Morogoro', 'min_price' => 55000, 'max_price' => 65000, 'avg_price' => 60000, 'unit' => 'sack 100kg', 'source' => 'Wiki ya Wakulima'],
            ['commodity' => 'Mpunga (Rice)', 'market' => 'Kariakoo', 'region' => 'Dar es Salaam', 'min_price' => 120000, 'max_price' => 150000, 'avg_price' => 135000, 'unit' => 'sack 100kg', 'source' => 'Wiki ya Wakulima'],
            ['commodity' => 'Mpunga (Rice)', 'market' => 'Mwea', 'region' => 'Kilombero', 'min_price' => 100000, 'max_price' => 125000, 'avg_price' => 112000, 'unit' => 'sack 100kg', 'source' => 'Wiki ya Wakulima'],
            ['commodity' => 'Maharage (Beans)', 'market' => 'Kariakoo', 'region' => 'Dar es Salaam', 'min_price' => 90000, 'max_price' => 115000, 'avg_price' => 102000, 'unit' => 'sack 100kg', 'source' => 'Wiki ya Wakulima'],
            ['commodity' => 'Maharage (Beans)', 'market' => 'Sumbawanga', 'region' => 'Rukwa', 'min_price' => 75000, 'max_price' => 95000, 'avg_price' => 85000, 'unit' => 'sack 100kg', 'source' => 'Wiki ya Wakulima'],
            ['commodity' => 'Mbaazi (Pigeon Peas)', 'market' => 'Kariakoo', 'region' => 'Dar es Salaam', 'min_price' => 80000, 'max_price' => 100000, 'avg_price' => 90000, 'unit' => 'sack 100kg', 'source' => 'Wiki ya Wakulima'],
            ['commodity' => 'Alizeti (Sunflower Seeds)', 'market' => 'Singida', 'region' => 'Singida', 'min_price' => 45000, 'max_price' => 55000, 'avg_price' => 50000, 'unit' => 'sack 100kg', 'source' => 'Wiki ya Wakulima'],
            ['commodity' => 'Mchele (Paddy)', 'market' => 'Kilombero', 'region' => 'Morogoro', 'min_price' => 85000, 'max_price' => 105000, 'avg_price' => 95000, 'unit' => 'sack 100kg', 'source' => 'Wiki ya Wakulima'],
            ['commodity' => 'Viazi (Irish Potatoes)', 'market' => 'Arusha', 'region' => 'Arusha', 'min_price' => 60000, 'max_price' => 80000, 'avg_price' => 70000, 'unit' => 'sack 100kg', 'source' => 'Wiki ya Wakulima'],
            ['commodity' => 'Ndizi (Cooking Bananas)', 'market' => 'Arusha', 'region' => 'Arusha', 'min_price' => 35000, 'max_price' => 50000, 'avg_price' => 42000, 'unit' => 'bunch', 'source' => 'Wiki ya Wakulima'],
            ['commodity' => 'Mchele (Paddy)', 'market' => 'Mbeya', 'region' => 'Mbeya', 'min_price' => 80000, 'max_price' => 100000, 'avg_price' => 90000, 'unit' => 'sack 100kg', 'source' => 'Wiki ya Wakulima'],
            ['commodity' => 'Soya (Soya Beans)', 'market' => 'Kariakoo', 'region' => 'Dar es Salaam', 'min_price' => 110000, 'max_price' => 140000, 'avg_price' => 125000, 'unit' => 'sack 100kg', 'source' => 'Wiki ya Wakulima'],
            ['commodity' => 'Soya (Soya Beans)', 'market' => 'Iringa', 'region' => 'Iringa', 'min_price' => 95000, 'max_price' => 120000, 'avg_price' => 107000, 'unit' => 'sack 100kg', 'source' => 'Wiki ya Wakulima'],
            ['commodity' => 'Mafuta ya Nazi (Coconut Oil)', 'market' => 'Kariakoo', 'region' => 'Dar es Salaam', 'min_price' => 2500, 'max_price' => 3500, 'avg_price' => 3000, 'unit' => 'litre', 'source' => 'Wiki ya Wakulima'],
            ['commodity' => 'Mazao ya Mchicha (Amaranth)', 'market' => 'Tandale', 'region' => 'Dar es Salaam', 'min_price' => 1000, 'max_price' => 2000, 'avg_price' => 1500, 'unit' => 'bunch', 'source' => 'Wiki ya Wakulima'],
            ['commodity' => 'Matunda (Mangoes)', 'market' => 'Dodoma', 'region' => 'Dodoma', 'min_price' => 1500, 'max_price' => 3000, 'avg_price' => 2200, 'unit' => 'kg', 'source' => 'Wiki ya Wakulima'],
            ['commodity' => 'Matunda (Oranges)', 'market' => 'Tanga', 'region' => 'Tanga', 'min_price' => 1200, 'max_price' => 2500, 'avg_price' => 1800, 'unit' => 'kg', 'source' => 'Wiki ya Wakulima'],
            ['commodity' => 'Maziwa (Milk)', 'market' => 'Arusha', 'region' => 'Arusha', 'min_price' => 1200, 'max_price' => 1800, 'avg_price' => 1500, 'unit' => 'litre', 'source' => 'Wiki ya Wakulima'],
            ['commodity' => 'Maziwa (Milk)', 'market' => 'Mufindi', 'region' => 'Iringa', 'min_price' => 1100, 'max_price' => 1600, 'avg_price' => 1350, 'unit' => 'litre', 'source' => 'Wiki ya Wakulima'],
            ['commodity' => 'Nyama ya Ng\'ombe (Beef)', 'market' => 'Kariakoo', 'region' => 'Dar es Salaam', 'min_price' => 6000, 'max_price' => 8500, 'avg_price' => 7500, 'unit' => 'kg', 'source' => 'Wiki ya Wakulima'],
            ['commodity' => 'Nyama ya Kuku (Chicken)', 'market' => 'Tandale', 'region' => 'Dar es Salaam', 'min_price' => 7000, 'max_price' => 10000, 'avg_price' => 8500, 'unit' => 'kg', 'source' => 'Wiki ya Wakulima'],
            ['commodity' => 'Mayai (Eggs)', 'market' => 'Arusha', 'region' => 'Arusha', 'min_price' => 12000, 'max_price' => 18000, 'avg_price' => 15000, 'unit' => 'tray 30', 'source' => 'Wiki ya Wakulima'],
            ['commodity' => 'Kahawa (Coffee)', 'market' => 'Moshi', 'region' => 'Kilimanjaro', 'min_price' => 2500, 'max_price' => 3500, 'avg_price' => 3000, 'unit' => 'kg', 'source' => 'Wiki ya Wakulima'],
            ['commodity' => 'Kahawa (Coffee)', 'market' => 'Mbeya', 'region' => 'Mbeya', 'min_price' => 2300, 'max_price' => 3200, 'avg_price' => 2750, 'unit' => 'kg', 'source' => 'Wiki ya Wakulima'],
            ['commodity' => 'Chai (Tea)', 'market' => 'Mufindi', 'region' => 'Iringa', 'min_price' => 1800, 'max_price' => 2500, 'avg_price' => 2150, 'unit' => 'kg', 'source' => 'Wiki ya Wakulima'],
            ['commodity' => 'Tikiti (Watermelon)', 'market' => 'Kariakoo', 'region' => 'Dar es Salaam', 'min_price' => 1500, 'max_price' => 3000, 'avg_price' => 2200, 'unit' => 'piece', 'source' => 'Wiki ya Wakulima'],
            ['commodity' => 'Tikiti (Watermelon)', 'market' => 'Tanga', 'region' => 'Tanga', 'min_price' => 1200, 'max_price' => 2500, 'avg_price' => 1800, 'unit' => 'piece', 'source' => 'Wiki ya Wakulima'],
            ['commodity' => 'Vitunguu (Onions)', 'market' => 'Arusha', 'region' => 'Arusha', 'min_price' => 25000, 'max_price' => 40000, 'avg_price' => 32000, 'unit' => 'sack 50kg', 'source' => 'Wiki ya Wakulima'],
            ['commodity' => 'Vitunguu (Onions)', 'market' => 'Dodoma', 'region' => 'Dodoma', 'min_price' => 22000, 'max_price' => 35000, 'avg_price' => 28000, 'unit' => 'sack 50kg', 'source' => 'Wiki ya Wakulima'],
            ['commodity' => 'Tangawizi (Ginger)', 'market' => 'Morogoro', 'region' => 'Morogoro', 'min_price' => 40000, 'max_price' => 60000, 'avg_price' => 50000, 'unit' => 'sack 50kg', 'source' => 'Wiki ya Wakulima'],
        ];

        $today = Carbon::today();

        foreach ($records as $r) {
            MarketPrice::firstOrCreate(
                [
                    'commodity' => $r['commodity'],
                    'market' => $r['market'],
                    'price_date' => $today,
                ],
                [
                    'uuid' => (string) Str::uuid(),
                    'region' => $r['region'],
                    'min_price' => $r['min_price'],
                    'max_price' => $r['max_price'],
                    'avg_price' => $r['avg_price'],
                    'unit' => $r['unit'],
                    'currency' => 'TZS',
                    'source' => $r['source'],
                    'recorded_by' => $admin?->id,
                ]
            );
        }

        echo "Seeded " . MarketPrice::count() . " market prices\n";
    }
}
