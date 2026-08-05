<?php

use Illuminate\Support\Facades\Route;

use App\Models\LandingSetting;

Route::get('/', function () {
    $settings = [];
    try {
        $settings = LandingSetting::pluck('value', 'key')->toArray();
    } catch (\Exception $e) {
        // Fallback for when migrations haven't run yet
    }

    $defaults = [
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

    $settings = array_merge($defaults, $settings);

    return view('landing', compact('settings'));
});

Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');
