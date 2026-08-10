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
        'pitch_deck_url' => '/docs/Mkulima_Forum_Pitch_Deck.pdf',
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
        'contact_email' => 'hello@mkulimaforum.app',
        // Impact metrics (empty until launch)
        'metric_farmers' => null,
        'metric_regions' => null,
        'metric_scans'   => null,
        'metric_queries' => null,
        'metric_markets' => null,
    ];

    $settings = array_merge($defaults, $settings);

    return view('pages.home', compact('settings'));
});

// ─── Public Pages ────────────────────────────────────────────────────────────
// Shared settings resolver used by every public page route
$publicSettings = function () {
    $settings = [];
    try {
        $settings = LandingSetting::pluck('value', 'key')->toArray();
    } catch (Exception $e) {}

    return array_merge([
        'logo_url'       => '/images/brand-banner.png',
        'banner_url'     => '/images/brand-banner.png',
        'emblem_url'     => '/images/logo-icon.jpg',
        'pitch_deck_url' => '/docs/Mkulima_Forum_Pitch_Deck.pdf',
        'brand_motto'    => 'SHIRIKI • JIFUNZE • ENDELEA',
        'contact_email'  => 'hello@mkulimaforum.app',
        'metric_farmers' => null,
        'metric_regions' => null,
        'metric_scans'   => null,
        'metric_queries' => null,
        'metric_markets' => null,
    ], $settings);
};

Route::get('/about', function () use ($publicSettings) {
    $settings = $publicSettings();
    return view('pages.about', compact('settings'));
})->name('about');

Route::get('/solutions', function () use ($publicSettings) {
    $settings = $publicSettings();
    return view('pages.solutions', compact('settings'));
})->name('solutions');

Route::get('/impact', function () use ($publicSettings) {
    $settings = $publicSettings();
    return view('pages.impact', compact('settings'));
})->name('impact');

Route::get('/partners', function () use ($publicSettings) {
    $settings = $publicSettings();
    return view('pages.partners', compact('settings'));
})->name('partners');

Route::get('/stories', function () use ($publicSettings) {
    $settings = $publicSettings();
    return view('pages.stories', compact('settings'));
})->name('stories');

Route::get('/technology', function () use ($publicSettings) {
    $settings = $publicSettings();
    return view('pages.technology', compact('settings'));
})->name('technology');

Route::get('/pitch-deck', function () use ($publicSettings) {
    $settings = $publicSettings();
    return view('pages.pitch-deck', compact('settings'));
})->name('pitch-deck');

Route::get('/contact', function () use ($publicSettings) {
    $settings = $publicSettings();
    return view('pages.contact', compact('settings'));
})->name('contact');

Route::get('/verify', function () use ($publicSettings) {
    $settings = $publicSettings();
    return view('pages.verify', compact('settings'));
})->name('verify');

Route::get('/community', function () use ($publicSettings) {
    $settings = $publicSettings();
    return view('pages.community', compact('settings'));
})->name('community');

// ─── Short Link Resolver (1.9 / QR Service) ──────────────────────────────────
Route::get('/c/{slug}', function (string $slug) {
    $shortLink = \App\Models\ShortLink::where('slug', $slug)->where('is_active', true)->firstOrFail();
    $shortLink->increment('click_count');

    return redirect()->away($shortLink->target_url);
});

// ─── Auth ────────────────────────────────────────────────────────────────────
Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');

