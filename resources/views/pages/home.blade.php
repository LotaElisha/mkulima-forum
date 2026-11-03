@extends('layouts.public')

@section('title', 'Mkulima Forum | App ya kilimo kwa wakulima wa Tanzania')
@section('meta_description', 'Mkulima Forum ni app ya kilimo kwa Kiswahili: kagua magonjwa ya mimea kwa picha, pata pembejeo, wataalamu, zana za kukodi na usafiri karibu nawe, na jadiliana na wakulima wengine.')
@section('og_title', 'Mkulima Forum — App ya kilimo kwa Kiswahili')
@section('og_description', 'Kagua mimea, pata pembejeo, wataalamu, zana na usafiri karibu nawe.')

@section('head_extra')
  {{-- Versioned by mtime so returning visitors get CSS changes. --}}
  <link rel="stylesheet" href="{{ asset('css/mk-landing.css') }}?v={{ @filemtime(public_path('css/mk-landing.css')) }}">
@endsection

@php
  // Promises come from the server, not the copy (see App\Actions\Home\PublicHome).
  $android = $brief['app']['android'];
  $hasWeb = $brief['app']['web'];
  $scannerAi = $brief['app']['scanner_ai'];

  $features = [
    ['icon' => 'scan', 'title' => 'Kagua mimea kwa picha',
     'body' => 'Piga picha ya jani lililoathirika; AI inatambua tatizo na kukupa ushauri wa tiba na kinga.',
     'live' => $scannerAi],
    ['icon' => 'storefront', 'title' => 'Pembejeo',
     'body' => 'Mbegu, mbolea na viuatilifu kutoka kwa wauzaji wa pembejeo, kwa bei za TSh.',
     'live' => true],
    ['icon' => 'user', 'title' => 'Wataalamu karibu nawe',
     'body' => 'Wataalamu wa kilimo waliothibitishwa, kwa umbali kutoka ulipo. Omba ushauri au kutembelewa shambani.',
     'live' => true],
    ['icon' => 'tractor', 'title' => 'Kodi zana',
     'body' => 'Trekta, power tiller, pampu na zaidi kwa siku. Mmiliki anakubali, kisha mnawasiliana.',
     'live' => true],
    ['icon' => 'truck', 'title' => 'Usafirishaji',
     'body' => 'Omba usafiri wa mazao, pembejeo au zana. Wasafirishaji waliothibitishwa wanatuma bei.',
     'live' => true],
    ['icon' => 'message', 'title' => 'Jukwaa',
     'body' => 'Uliza maswali na ujifunze kutoka kwa wakulima wenzako na wataalamu.',
     'live' => true],
    ['icon' => 'chart', 'title' => 'Bei za soko',
     'body' => 'Bei za mazao zilizoandikwa kwenye masoko makuu, kila moja na tarehe yake.',
     'live' => true],
    ['icon' => 'cloud-sun', 'title' => 'Hali ya hewa',
     'body' => 'Utabiri wa hali ya hewa kwa eneo lako, kupanga kupanda na kunyunyizia.',
     'live' => true],
  ];
@endphp

@section('content')
<div class="mk-landing">

  {{-- ── Hero ─────────────────────────────────────────────────────────── --}}
  <section class="ml-hero">
    <div class="wrap ml-hero-grid">
      <div class="ml-hero-copy">
        <x-mk.badge live>App ya Android · Kiswahili</x-mk.badge>
        <h1>Kilimo bora, mfukoni mwako.</h1>
        <p class="ml-lead">
          Mkulima Forum ni app ya kilimo kwa wakulima wa Tanzania. Kagua magonjwa ya mimea kwa picha,
          pata pembejeo, wataalamu, zana za kukodi na usafiri karibu nawe, na jadiliana na wakulima wengine.
        </p>
        <div class="mk-actions">
          @if ($android)
            <a class="mk-btn" href="{{ $android['url'] }}" download="{{ $android['filename'] }}">
              <x-icon name="download" :size="20" /> Pakua app ({{ $android['human'] }})
            </a>
          @else
            <a class="mk-btn" href="{{ route('register') }}">Jisajili sasa</a>
          @endif
          @if ($hasWeb)
            <a class="mk-btn mk-btn-quiet" href="{{ \App\Support\AppDownload::webUrl() }}">Fungua kwenye kivinjari</a>
          @else
            <a class="mk-btn mk-btn-quiet" href="#huduma">Ona inachofanya</a>
          @endif
        </div>
        @unless ($android)
          <p class="ml-note">
            <x-icon name="info" :size="16" />
            App ya Android inakuja hivi karibuni. Jisajili sasa; akaunti yako itafanya kazi kwenye app.
            <a href="{{ url('/download') }}">Jinsi ya kupata app</a>
          </p>
        @endunless
      </div>

      {{-- A drawing of the app's real home screen: labels only, no numbers. --}}
      <div class="ml-phone" aria-hidden="true">
        <div class="ml-phone-screen">
          <div class="ml-app-head">
            <img src="{{ $settings['emblem_url'] ?? '/images/logo-icon.jpg' }}" alt="" width="28" height="28">
            <div><strong>Karibu Mkulima</strong><span>Kilimo bora huanza na taarifa sahihi</span></div>
          </div>
          <div class="ml-app-search">Tafuta mbegu, mbolea, mijadala…</div>
          <div class="ml-app-title">Huduma karibu nawe</div>
          <div class="ml-app-grid">
            @foreach ([['user','Wataalamu'],['tractor','Kodi zana'],['storefront','Pembejeo'],['truck','Usafiri'],['chart','Bei'],['cloud-sun','Hewa'],['message','Jukwaa'],['leaf','Shamba']] as [$i, $l])
              <div><span><x-icon :name="$i" :size="16" /></span>{{ $l }}</div>
            @endforeach
          </div>
          <div class="ml-app-nav">
            <span><x-icon name="leaf" :size="18" /></span>
            <span><x-icon name="storefront" :size="18" /></span>
            <span class="ml-scan"><x-icon name="scan" :size="16" /></span>
            <span><x-icon name="message" :size="18" /></span>
            <span><x-icon name="user" :size="18" /></span>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ── What the app does ────────────────────────────────────────────── --}}
  <section class="ml-section" id="huduma" aria-labelledby="huduma-title">
    <div class="wrap">
      <span class="eyebrow">Inachofanya</span>
      <h2 id="huduma-title" class="section-title">Huduma zote za kilimo, app moja</h2>
      <p class="section-lead">Kila kitu kimeandikwa kwa Kiswahili na kimejengwa kwa simu za bei nafuu.</p>
      <div class="ml-features">
        @foreach ($features as $f)
          <article class="ml-feature">
            <span class="ml-feature-icon"><x-icon :name="$f['icon']" :size="22" /></span>
            <h3>{{ $f['title'] }}</h3>
            <p>{{ $f['body'] }}</p>
            @unless ($f['live'])
              <x-mk.badge>Inakuja hivi karibuni</x-mk.badge>
            @endunless
          </article>
        @endforeach
      </div>
    </div>
  </section>

  {{-- ── Built for farmers' conditions ────────────────────────────────── --}}
  <section class="ml-section ml-soft" aria-labelledby="hali-title">
    <div class="wrap">
      <span class="eyebrow">Imejengwa kwa ajili yako</span>
      <h2 id="hali-title" class="section-title">Kwa hali halisi ya mkulima</h2>
      <div class="ml-points">
        <div>
          <x-icon name="wifi-off" :size="22" />
          <h3>Mtandao ukikatika</h3>
          <p>Piga picha ya mmea hata bila mtandao; inahifadhiwa kwenye simu na kukaguliwa ukirudi mtandaoni. Maktaba ya magonjwa inasomeka bila mtandao.</p>
        </div>
        <div>
          <x-icon name="phone" :size="22" />
          <h3>Simu ya kawaida, data kidogo</h3>
          <p>Imetengenezwa kwa simu za Android za bei nafuu, kwa maandishi makubwa na vitufe rahisi kubonyeza.</p>
        </div>
        <div>
          <x-icon name="lock" :size="22" />
          <h3>Namba yako ni yako</h3>
          <p>Namba ya simu haionyeshwi kwenye matangazo. Inatolewa kwa mtu mmoja tu, baada ya kukubaliana naye.</p>
        </div>
        <div>
          <x-icon name="shield" :size="22" />
          <h3>Wataalamu waliothibitishwa</h3>
          <p>Wataalamu na wasafirishaji wanaonekana baada ya kuthibitishwa na timu yetu. Hatuandiki "imethibitishwa" pasipo ukaguzi.</p>
        </div>
      </div>
    </div>
  </section>

  {{-- ── How it works ─────────────────────────────────────────────────── --}}
  <section class="ml-section" aria-labelledby="jinsi-title">
    <div class="wrap">
      <span class="eyebrow">Jinsi inavyofanya kazi</span>
      <h2 id="jinsi-title" class="section-title">Hatua tatu</h2>
      <ol class="ml-steps">
        <li><span>1</span><div><h3>Jisajili</h3><p>Fungua akaunti kwa barua pepe. Inachukua chini ya dakika moja.</p></div></li>
        <li><span>2</span><div><h3>Chagua huduma</h3><p>Kagua mmea, tafuta pembejeo, mtaalamu, zana au usafiri karibu nawe.</p></div></li>
        <li><span>3</span><div><h3>Pata msaada</h3><p>Tuma ombi; aliyethibitishwa anajibu, na mnakubaliana moja kwa moja.</p></div></li>
      </ol>
    </div>
  </section>

  {{-- ── Live today (real data only) ──────────────────────────────────── --}}
  <section class="ml-section ml-soft" aria-labelledby="leo-title">
    <div class="wrap">
      <span class="eyebrow">Leo kwenye Mkulima Forum</span>
      <h2 id="leo-title" class="section-title">Taarifa zinazopatikana sasa</h2>
      <div class="mk-grid mk-grid-3">
        <section class="mk-card" aria-labelledby="hewa-title">
          <x-mk.badge live>Hali ya hewa</x-mk.badge>
          <h3 id="hewa-title">{{ $brief['weather']['location'] }}</h3>
          @if ($brief['weather']['available'])
            <p>{{ $brief['weather']['summary'] }}</p>
          @else
            <x-mk.empty title="Hakuna taarifa sasa">Hali ya hewa itaonekana hapa ikiwa seva ina kumbukumbu. Hatutungi namba.</x-mk.empty>
          @endif
        </section>
        <section class="mk-card" aria-labelledby="bei-title">
          <x-mk.badge live>Bei zilizoandikwa</x-mk.badge>
          <h3 id="bei-title">Bei za soko</h3>
          @forelse ($brief['prices'] as $price)
            <div class="mk-row">
              <div>
                <strong>{{ $price['commodity'] }}</strong>
                <span>{{ $price['market'] }} · {{ $price['date'] }} · {{ $price['price'] }}</span>
              </div>
            </div>
          @empty
            <x-mk.empty title="Bado hakuna bei">Bei zinaonekana baada ya kuandikwa, pamoja na tarehe.</x-mk.empty>
          @endforelse
        </section>
        <section class="mk-card" aria-labelledby="jukwaa-title">
          <x-mk.badge live>Jukwaa</x-mk.badge>
          <h3 id="jukwaa-title">Mijadala</h3>
          @forelse ($brief['threads'] as $thread)
            <a class="mk-row" href="{{ route('forum.show', $thread['uuid']) }}">
              <div>
                <strong>{{ $thread['title'] }}</strong>
                <span>{{ $thread['replies'] }} majibu</span>
              </div>
            </a>
          @empty
            <x-mk.empty title="Bado hakuna mijadala">Mijadala ya wakulima itaonekana hapa.</x-mk.empty>
          @endforelse
          <p class="ml-card-link"><a href="{{ route('forum.index') }}">Fungua jukwaa</a></p>
        </section>
      </div>
    </div>
  </section>

  {{-- ── Providers ────────────────────────────────────────────────────── --}}
  <section class="ml-section" aria-labelledby="washirika-title">
    <div class="wrap ml-providers">
      <div>
        <span class="eyebrow">Kwa watoa huduma</span>
        <h2 id="washirika-title" class="section-title">Unahudumia wakulima?</h2>
        <p class="section-lead">
          Wauzaji wa pembejeo, wataalamu wa kilimo, wamiliki wa zana na wasafirishaji:
          jisajili ili wakulima wa eneo lako wakupate.
        </p>
      </div>
      <div class="mk-actions">
        <a class="mk-btn" href="{{ route('register') }}">Jisajili</a>
        <a class="mk-btn mk-btn-quiet" href="{{ url('/contact') }}">Wasiliana nasi</a>
      </div>
    </div>
  </section>

  {{-- ── Questions ────────────────────────────────────────────────────── --}}
  <section class="ml-section ml-soft" aria-labelledby="maswali-title">
    <div class="wrap ml-faq">
      <span class="eyebrow">Maswali</span>
      <h2 id="maswali-title" class="section-title">Maswali yanayoulizwa mara nyingi</h2>
      <details>
        <summary>Ninapataje app?</summary>
        @if ($android)
          <p>Pakua faili la Android ({{ $android['human'] }}) kutoka ukurasa huu au <a href="{{ url('/download') }}">ukurasa wa kupakua</a>, kisha lifungue kwenye simu yako.</p>
        @else
          <p>App ya Android bado haijachapishwa. Jisajili sasa; akaunti hiyo hiyo itafanya kazi kwenye app ikitoka. Taarifa zaidi kwenye <a href="{{ url('/download') }}">ukurasa wa kupakua</a>.</p>
        @endif
      </details>
      <details>
        <summary>Je, inahitaji intaneti?</summary>
        <p>Huduma nyingi zinahitaji intaneti. Bila mtandao unaweza kupiga picha ya mmea (itakaguliwa ukirudi mtandaoni) na kusoma maktaba ya magonjwa.</p>
      </details>
      <details>
        <summary>Je, AI inatambua magonjwa bila mtandao?</summary>
        <p>Hapana. Utambuzi wa picha unafanyika kwenye seva yetu, kwa hiyo unahitaji mtandao. Hatutoi utambuzi ambao hatujaufanya.</p>
      </details>
      <details>
        <summary>Malipo yanafanyikaje?</summary>
        <p>Kwa sasa hakuna malipo kwenye app. Unakubaliana bei na muuzaji, mtaalamu, mmiliki wa zana au msafirishaji, kisha mnalipana moja kwa moja.</p>
      </details>
      <details>
        <summary>Taarifa zangu ziko salama?</summary>
        <p>Namba yako ya simu haionyeshwi hadharani. Soma <a href="{{ url('/privacy') }}">sera ya faragha</a>, au <a href="{{ url('/delete-account') }}">omba kufuta akaunti</a> wakati wowote.</p>
      </details>
    </div>
  </section>

  {{-- ── Closing call to action ───────────────────────────────────────── --}}
  <section class="ml-cta" aria-labelledby="anza-title">
    <div class="wrap">
      <img src="{{ $settings['emblem_url'] ?? '/images/logo-icon.jpg' }}" alt="" width="64" height="64">
      <h2 id="anza-title">Anza leo</h2>
      <p>Shiriki. Jifunze. Endelea.</p>
      <div class="mk-actions ml-center">
        @if ($android)
          <a class="mk-btn" href="{{ $android['url'] }}" download="{{ $android['filename'] }}"><x-icon name="download" :size="20" /> Pakua app</a>
        @else
          <a class="mk-btn" href="{{ route('register') }}">Jisajili sasa</a>
        @endif
        <a class="mk-btn mk-btn-quiet" href="{{ url('/about') }}">Kuhusu sisi</a>
      </div>
    </div>
  </section>
</div>
@endsection
