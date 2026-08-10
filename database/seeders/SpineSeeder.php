<?php

namespace Database\Seeders;

use App\Models\Crop;
use App\Models\GeoUnit;
use App\Models\ProductCategory;
use App\Models\Topic;
use Illuminate\Database\Seeder;

class SpineSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tanzania Regions & Sample Districts
        $regions = [
            'Arusha' => ['Meru', 'Arusha Urban', 'Karatu', 'Monduli', 'Ngorongoro'],
            'Dar es Salaam' => ['Ilala', 'Kinondoni', 'Temeke', 'Ubungo', 'Kigamboni'],
            'Dodoma' => ['Dodoma Urban', 'Bahi', 'Chamwino', 'Kondoa', 'Kongwa'],
            'Iringa' => ['Iringa Urban', 'Kilolo', 'Mufindi'],
            'Kagera' => ['Bukoba Urban', 'Biharamulo', 'Karagwe', 'Muleba', 'Ngara'],
            'Kilimanjaro' => ['Moshi Urban', 'Hai', 'Rombo', 'Same', 'Siha'],
            'MBeya' => ['Mbeya Urban', 'Chunya', 'Kyela', 'Rungwe'],
            'Morogoro' => ['Morogoro Urban', 'Gairo', 'Kilombero', 'Kilosa', 'Mvomero'],
            'Mwanza' => ['Nyamagana', 'Ilemela', 'Kwimba', 'Magu', 'Sengerema'],
            'Ruvuma' => ['Songea Urban', 'Mbinga', 'Namtumbo', 'Nyasa', 'Tunduru'],
        ];

        $country = GeoUnit::create(['type' => 'country', 'name' => 'Tanzania', 'code' => 'TZ']);

        foreach ($regions as $regName => $districts) {
            $reg = GeoUnit::create([
                'type' => 'region',
                'name' => $regName,
                'parent_id' => $country->id,
            ]);

            foreach ($districts as $distName) {
                GeoUnit::create([
                    'type' => 'district',
                    'name' => $distName,
                    'parent_id' => $reg->id,
                ]);
            }
        }

        // 2. Crops
        $crops = [
            ['name' => 'Maize', 'swahili_name' => 'Mahindi', 'category' => 'cereal'],
            ['name' => 'Rice', 'swahili_name' => 'Mchele', 'category' => 'cereal'],
            ['name' => 'Coffee', 'swahili_name' => 'Kahawa', 'category' => 'cash_crop'],
            ['name' => 'Cashew', 'swahili_name' => 'Korosho', 'category' => 'cash_crop'],
            ['name' => 'Beans', 'swahili_name' => 'Maharage', 'category' => 'legume'],
            ['name' => 'Cassava', 'swahili_name' => 'Muhogo', 'category' => 'tuber'],
            ['name' => 'Sunflower', 'swahili_name' => 'Alizeti', 'category' => 'cash_crop'],
            ['name' => 'Horticulture', 'swahili_name' => 'Mboga na Matunda', 'category' => 'vegetable'],
        ];

        foreach ($crops as $c) {
            Crop::firstOrCreate(['name' => $c['name']], $c);
        }

        // 3. Agricultural Topics
        $topics = [
            ['name' => 'Crop Diseases', 'swahili_name' => 'Magonjwa ya Mimea'],
            ['name' => 'Market Prices', 'swahili_name' => 'Bei za Masoko'],
            ['name' => 'Agro-Inputs & Fertilizers', 'swahili_name' => 'Pembejeo na Mbolea'],
            ['name' => 'Agronomy Best Practices', 'swahili_name' => 'Mbinu Bora za Kilimo'],
            ['name' => 'Young Farmers Network', 'swahili_name' => 'Mtandao wa Vijana'],
            ['name' => 'Women in Agriculture', 'swahili_name' => 'Wanawake katika Kilimo'],
        ];

        foreach ($topics as $t) {
            Topic::firstOrCreate(['name' => $t['name']], $t);
        }

        // 4. Product Categories
        $categories = [
            ['name' => 'Certified Seeds', 'swahili_name' => 'Mbegu Zilizothibitishwa', 'code' => 'SEED'],
            ['name' => 'Fertilizer & Soil Inputs', 'swahili_name' => 'Mbolea na Rutuba', 'code' => 'FERTILIZER'],
            ['name' => 'Pesticides & Agrochemicals', 'swahili_name' => 'Dawa za Mimea', 'code' => 'PESTICIDE'],
            ['name' => 'Veterinary & Livestock', 'swahili_name' => 'Dawa za Mifugo', 'code' => 'VET'],
            ['name' => 'Farm Machinery & Tools', 'swahili_name' => 'Zana za Kilimo', 'code' => 'EQUIP'],
            ['name' => 'Other Agricultural Products', 'swahili_name' => 'Bidhaa Nyingine', 'code' => 'OTHER'],
        ];

        foreach ($categories as $cat) {
            ProductCategory::firstOrCreate(['code' => $cat['code']], $cat);
        }
    }
}
