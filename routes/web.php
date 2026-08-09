<?php

use App\Models\LandingSetting;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $settings = [];
    try {
        $settings = LandingSetting::pluck('value', 'key')->toArray();
    } catch (Exception $e) {
        // Fallback for when migrations haven't run yet
    }

    $defaults = [
        'logo_url' => '/images/brand-banner.png',
        'banner_url' => '/images/brand-banner.png',
        'emblem_url' => '/images/logo-icon.jpg',
        'brand_motto' => 'SHIRIKI • JIFUNZE • ENDELEA',
        'hero_title' => 'Jukwaa la Kidigitali la Wakulima wa <span class="gold">Tanzania</span>',
        'hero_tagline' => 'SKANI &bull; TAMBUA &bull; TIBU',
        'hero_lead' => 'Utambuzi wa magonjwa ya mimea kwa <b>Gemini 3 Flash AI</b>, ushauri wa kilimo kwa Kiswahili, masoko ya mazao, bei za masoko kwa wakati halisi, na usaidizi wa offline kupitia <b>Gemma 2B</b>.',
        'pillar_1' => '🌱 Shiriki Maarifa',
        'pillar_2' => '📖 Jifunze Mbinu Bora',
        'pillar_3' => '👥 Jenga Jamii',
        'pillar_4' => '📈 Endelea Kukua',
        'kicker_jinsi' => 'Jinsi Inavyofanya Kazi',
        'title_jinsi' => 'Hatua 3 tu — chini ya dakika moja',
        'sub_jinsi' => 'Huhitaji ujuzi wowote wa kiufundi. Kama unaweza kupiga picha, unaweza kutumia Mkulima Forum.',
        'kicker_vipengele' => 'Vipengele',
        'title_vipengele' => 'Zaidi ya scanner — mfumo kamili wa kilimo',
        'sub_vipengele' => 'Kila kitu mkulima anachohitaji, mahali pamoja, kwa Kiswahili.',
    ];

    $settings = array_merge($defaults, $settings);

    return view('landing', compact('settings'));
});

Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');
