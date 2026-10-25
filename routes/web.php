<?php

use App\Actions\Forum\ListForumThreads;
use App\Actions\Forum\ShowForumThread;
use App\Actions\Home\PublicHome;
use App\Http\Controllers\Api\Auth\EmailVerificationController;
use App\Models\LandingSetting;
use App\Models\ShortLink;
use Illuminate\Support\Facades\Route;

Route::get('/web-app-assets/sqlite', function () {
    $path = storage_path('app/private/web/sqlite3.wasm');
    abort_unless(is_file($path), 404);

    return response()->file($path, [
        'Content-Type' => 'application/wasm',
        'Cache-Control' => 'public, max-age=31536000, immutable',
    ]);
});

Route::get('/web-app-assets/drift-worker', function () {
    $path = storage_path('app/private/web/drift_worker.js');
    abort_unless(is_file($path), 404);

    return response()->file($path, [
        'Content-Type' => 'application/javascript; charset=utf-8',
        'Cache-Control' => 'public, max-age=31536000, immutable',
    ]);
});

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
        'hero_title' => 'Jukwaa la Kidigitali la Wakulima wa Tanzania',
        'hero_tagline' => 'SKANI • TAMBUA • TIBU',
        'hero_lead' => 'Bei za masoko zilizoandikwa (pamoja na tarehe), hali ya hewa, na jamii ya wakulima kwa Kiswahili. Utambuzi wa magonjwa kwa AI, SMS na USSD bado havijawashwa.',
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
        'contact_email' => 'support@mkulimaforum.com',
        'contact_phone' => '+255714524007',
        // Impact metrics (empty until launch)
        'metric_farmers' => null,
        'metric_regions' => null,
        'metric_scans' => null,
        'metric_queries' => null,
        'metric_markets' => null,
    ];

    $settings = array_merge($defaults, $settings);
    $brief = app(PublicHome::class)();

    return view('pages.home', compact('settings', 'brief'));
});

// ─── Public Pages ────────────────────────────────────────────────────────────
// Shared settings resolver used by every public page route
$publicSettings = function () {
    $settings = [];
    try {
        $settings = LandingSetting::pluck('value', 'key')->toArray();
    } catch (Exception $e) {
    }

    return array_merge([
        'logo_url' => '/images/brand-banner.png',
        'banner_url' => '/images/brand-banner.png',
        'emblem_url' => '/images/logo-icon.jpg',
        'pitch_deck_url' => '/docs/Mkulima_Forum_Pitch_Deck.pdf',
        'brand_motto' => 'SHIRIKI • JIFUNZE • ENDELEA',
        'contact_email' => 'support@mkulimaforum.com',
        'contact_phone' => '+255714524007',
        'metric_farmers' => null,
        'metric_regions' => null,
        'metric_scans' => null,
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

Route::get('/forum', function () use ($publicSettings) {
    $settings = $publicSettings();
    $threads = app(ListForumThreads::class)(request())->items();

    return view('pages.forum.index', compact('settings', 'threads'));
})->name('forum.index');

Route::get('/forum/{uuid}', function (string $uuid) use ($publicSettings) {
    $settings = $publicSettings();
    $thread = app(ShowForumThread::class)($uuid);

    return view('pages.forum.show', compact('settings', 'thread'));
})->name('forum.show')->whereUuid('uuid');

Route::get('/community', function () use ($publicSettings) {
    $settings = $publicSettings();

    return view('pages.community', compact('settings'));
})->name('community');

Route::get('/privacy', function () use ($publicSettings) {
    $settings = $publicSettings();

    return view('pages.privacy', compact('settings'));
})->name('privacy');

Route::get('/terms', function () use ($publicSettings) {
    $settings = $publicSettings();

    return view('pages.terms', compact('settings'));
})->name('terms');

Route::get('/download', function () use ($publicSettings) {
    $settings = $publicSettings();

    return view('pages.download', compact('settings'));
})->name('download');

// ─── Short Link Resolver (1.9 / QR Service) ──────────────────────────────────
Route::get('/c/{slug}', function (string $slug) {
    $shortLink = ShortLink::where('slug', $slug)->where('is_active', true)->firstOrFail();
    $shortLink->increment('click_count');

    // Host allowlist. The target is admin-authored, so this is not a hole an
    // outsider can open — but without it, mkulimaforum.com/c/... is a
    // redirector that lends the platform's own domain to a phishing link, and
    // a single compromised or careless admin account is enough. Anything off
    // the list goes to an interstitial rather than straight out.
    $host = strtolower((string) parse_url($shortLink->target_url, PHP_URL_HOST));
    $allowed = collect(config('services.short_links.allowed_hosts', []))
        ->map(fn ($h) => strtolower(trim($h)))
        ->filter();

    $isAllowed = $allowed->contains(
        fn ($candidate) => $host === $candidate || str_ends_with($host, '.'.$candidate)
    );

    if (! $isAllowed) {
        return response()->view('pages.leaving', [
            'target' => $shortLink->target_url,
            'host' => $host,
        ], 200);
    }

    return redirect()->away($shortLink->target_url);
});

// ─── Auth ────────────────────────────────────────────────────────────────────
Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');
Route::view('/email/verify', 'auth.verify-notice')->name('verification.notice');

// Password recovery pages. These are web routes rather than API ones because
// they are opened from an email client on whatever device is to hand — the
// link has to render something a person can read and type into.
Route::view('/forgot-password', 'auth.forgot-password')->name('password.request');

Route::get('/reset-password/{token}', function (string $token) {
    return view('auth.reset-password', [
        'token' => $token,
        'email' => request()->string('email')->toString(),
    ]);
})->name('password.reset');

// Signed link from the verification mail. 'signed' proves the URL was not
// edited and has not expired; the throttle stops the id/hash space being
// walked. No session or token is required — the signature is the credential.
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

// /admin is the Filament panel. The old React bundle is not served from
// public/admin, so a built SPA cannot shadow these routes.
