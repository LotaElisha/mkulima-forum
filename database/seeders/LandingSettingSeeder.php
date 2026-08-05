<?php

namespace Database\Seeders;

use App\Models\LandingSetting;
use Illuminate\Database\Seeder;

class LandingSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'hero_title' => 'Daktari wa Mimea<br>Mfukoni <span class="accent">Mwako</span>',
            'hero_tagline' => 'SKANI &bull; TAMBUA &bull; TIBU',
            'hero_lead' => 'Piga picha ya mmea wako — <b>AI Plant Scanner</b> itambue magonjwa, wadudu na upungufu wa virutubisho papo hapo, na kukupa ushauri wa tiba kwa Kiswahili.',
            'badge_text' => 'AI kwa Wakulima wa Tanzania',
            'kicker_jinsi' => 'Jinsi Inavyofanya Kazi',
            'title_jinsi' => 'Hatua 3 tu — chini ya dakika moja',
            'sub_jinsi' => 'Huhitaji ujuzi wowote wa kiufundi. Kama unaweza kupiga picha, unaweza kutumia Mkulima Forum.',
            'kicker_vipengele' => 'Vipengele',
            'title_vipengele' => 'Zaidi ya scanner — mfumo kamili wa kilimo',
            'sub_vipengele' => 'Kila kitu mkulima anachohitaji, mahali pamoja, kwa Kiswahili.',
        ];

        foreach ($settings as $key => $value) {
            LandingSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
