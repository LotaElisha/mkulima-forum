@extends('layouts.public')

@section('title', 'MkulimaForum | Jukwaa la Kidigitali la Wakulima wa Tanzania')
@section('meta_description', 'MkulimaForum inaunganisha wakulima wa Tanzania na maarifa, masoko, huduma na Mkulima AI.')

@section('head_extra')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Material+Symbols+Rounded:opsz,wght,FILL@20..48,400,0&display=swap" rel="stylesheet">
<style>
  :root {
    --cream-bg:#fffdf8; --surface-card:#fffefa; --ink-dark:#181711;
    --ink-body:#4b4942; --ink-muted:#6f6b61; --ink-faint:#9c968b;
    --forest-dark:#264f27; --forest-mid:#356d33; --forest-light:#477a42;
    --leaf-green:#477a42; --leaf-bright:#67935b; --leaf-pale:#f3f6ec;
    --sun-gold:#efa91f; --sun-amber:#f6b83a;
    --border-light:#e9e3d8; --border-mid:#d7cdbc;
    --shadow-sm:0 8px 26px rgba(50,42,24,.06); --shadow-md:0 16px 38px rgba(50,42,24,.1);
  }
  body { background:var(--cream-bg); color:var(--ink-body); }
  #site-header { background:rgba(255,253,248,.96); border-color:var(--border-light); }
  .nav-links a:hover,.nav-links a.active,.nav-dropdown-trigger:hover { background:transparent; color:var(--ink-dark); }
  .nav-links a.active { box-shadow:inset 0 -2px var(--sun-gold); border-radius:0; }
  .lang-pill { background:#fff; border-color:var(--border-mid); }
  .lang-btn.active { background:#fff; color:var(--ink-dark); box-shadow:none; outline:1px solid var(--border-mid); }
  .btn-gold { background:var(--sun-gold); color:#181711; box-shadow:none; }
  .btn-gold:hover { background:#e19a12; }
  .material-symbols-rounded { font-size:1.2em; line-height:1; vertical-align:-.18em; }

  .editorial-hero { min-height:650px; position:relative; overflow:hidden; border-bottom:1px solid var(--border-light); }
  .hero-art { position:absolute; inset:0; background:url('/images/home/hero-composite.webp') right center/auto 92% no-repeat; }
  .hero-art::before { content:''; position:absolute; inset:0; background:linear-gradient(90deg,var(--cream-bg) 0%,rgba(255,253,248,.98) 35%,rgba(255,253,248,.55) 48%,transparent 66%); }
  .editorial-hero .wrap { min-height:650px; display:flex; align-items:center; position:relative; z-index:2; }
  .hero-copy { width:min(48%,550px); padding:48px 0; }
  .hero-kicker { display:inline-flex; align-items:center; padding:7px 16px; border:1px solid #87a57a; border-radius:999px; color:var(--forest-mid); font-size:.74rem; font-weight:800; letter-spacing:.15em; text-transform:uppercase; margin-bottom:22px; background:rgba(255,253,248,.72); }
  .editorial-title { font-family:'DM Serif Display',Georgia,serif; color:var(--ink-dark); font-size:clamp(3.1rem,5.4vw,4.8rem); font-weight:400; letter-spacing:-.035em; line-height:.99; max-width:720px; margin-bottom:24px; }
  .hero-summary { font-size:1.02rem; max-width:34rem; line-height:1.72; margin-bottom:24px; color:var(--ink-body); }
  .benefit-list { display:grid; gap:11px; list-style:none; margin:0 0 30px; }
  .benefit-list li { display:flex; align-items:center; gap:10px; font-size:.9rem; color:var(--ink-body); }
  .benefit-list .material-symbols-rounded { color:var(--forest-mid); font-size:17px; }
  .hero-actions { display:flex; align-items:center; gap:22px; flex-wrap:wrap; }
  .text-link { font-weight:700; color:var(--ink-dark); border-bottom:1px solid var(--ink-dark); padding-bottom:2px; }
  .availability { display:flex; gap:8px; margin-top:26px; align-items:center; flex-wrap:wrap; }
  .availability small { width:100%; color:var(--ink-faint); margin-bottom:2px; }
  .store-pill { border:1px solid var(--border-mid); padding:6px 10px; border-radius:6px; background:rgba(255,255,255,.8); font-size:.76rem; color:var(--ink-body); }

  .story-band { border-bottom:1px solid var(--border-light); background:#fcf8ef; padding:30px 0; }
  .story-grid { max-width:980px; margin:auto; display:grid; grid-template-columns:390px 1fr; align-items:center; gap:52px; }
  .story-image { position:relative; }
  .story-image img { width:100%; height:170px; object-fit:cover; border-radius:12px; }
  .story-proof { position:absolute; left:-26px; bottom:20px; background:var(--forest-mid); color:#fff; padding:14px 18px; border-radius:10px; width:132px; }
  .story-proof small { display:block; font-size:.64rem; font-weight:800; letter-spacing:.12em; }
  .story-proof strong { display:block; font-family:'DM Serif Display',serif; font-size:1.55rem; font-weight:400; }
  .story-proof span { display:block; font-size:.62rem; line-height:1.35; opacity:.84; }
  blockquote { font-family:'DM Serif Display',Georgia,serif; font-size:clamp(1.3rem,2.2vw,1.75rem); line-height:1.34; color:var(--ink-dark); }
  .quote-by { margin-top:18px; font-size:.84rem; color:var(--ink-muted); }

  .journey { padding:84px 0 92px; background:var(--cream-bg); }
  .journey-head { max-width:900px; margin:0 auto 44px; }
  .journey-head .eyebrow { color:var(--forest-mid); }
  .journey-title { font-family:'DM Serif Display',Georgia,serif; font-size:clamp(2.25rem,4vw,3.25rem); font-weight:400; color:var(--ink-dark); margin-bottom:8px; }
  .journey-steps { max-width:1100px; margin:auto; display:grid; grid-template-columns:repeat(3,1fr); }
  .journey-step { padding:0 38px; border-right:1px solid #e3d7c4; }
  .journey-step:first-child { padding-left:0; }
  .journey-step:last-child { border:0; padding-right:0; }
  .step-number { font-family:'DM Serif Display',serif; color:#e6cc98; font-size:3rem; line-height:1; display:block; margin-bottom:15px; }
  .journey-step h3 { color:var(--ink-dark); font-family:'DM Serif Display',serif; font-size:1.55rem; font-weight:400; margin-bottom:10px; }
  .journey-step p { font-size:.88rem; line-height:1.65; min-height:76px; }
  .journey-step img { width:100%; height:175px; object-fit:cover; border-radius:10px; margin-top:20px; }
  .journey-step:nth-child(2) img { object-position:center; }
  .journey-step:nth-child(3) img { object-position:center 40%; }
  .journey-close { text-align:center; font-family:'DM Serif Display',serif; font-size:1.15rem; color:var(--ink-dark); margin-top:42px; }
  .journey-close span { display:block; font-family:'Plus Jakarta Sans',sans-serif; color:var(--forest-mid); font-size:.9rem; font-weight:700; margin-top:5px; }

  .light-capabilities { background:#fffaf0; border-top:1px solid var(--border-light); border-bottom:1px solid var(--border-light); padding:72px 0; }
  .cap-head { display:flex; justify-content:space-between; gap:30px; align-items:end; margin-bottom:34px; }
  .cap-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:0; border-top:1px solid var(--border-light); border-bottom:1px solid var(--border-light); }
  .cap-item { padding:28px 24px; border-right:1px solid var(--border-light); }
  .cap-item:last-child { border:0; }
  .cap-item .material-symbols-rounded { color:var(--forest-mid); font-size:25px; margin-bottom:14px; display:block; }
  .cap-item h3 { font-size:1rem; color:var(--ink-dark); margin-bottom:7px; }
  .cap-item p { font-size:.82rem; line-height:1.55; color:var(--ink-muted); }
  .final-cta { padding:72px 0; text-align:center; background:#fff; }
  .final-cta h2 { font-family:'DM Serif Display',serif; font-size:clamp(2.1rem,4vw,3rem); font-weight:400; color:var(--ink-dark); margin-bottom:12px; }
  .final-cta p { max-width:560px; margin:0 auto 26px; color:var(--ink-muted); }

  @media(max-width:900px) {
    .editorial-hero.hero-section { min-height:auto; padding-top:420px; }
    .hero-art { inset:0 0 auto; height:470px; background-position:65% center; }
    .hero-art::before { background:linear-gradient(0deg,var(--cream-bg),transparent 55%); }
    .editorial-hero .wrap { min-height:auto; }
    .hero-copy { width:100%; padding:50px 0 64px; }
    .story-grid { grid-template-columns:1fr; padding:0 28px; gap:34px; }
    .story-proof { left:14px; }
    .journey-steps { grid-template-columns:1fr; gap:42px; }
    .journey-step,.journey-step:first-child,.journey-step:last-child { padding:0; border:0; }
    .journey-step p { min-height:0; }
    .journey-step img { height:240px; }
    .cap-grid { grid-template-columns:1fr 1fr; }
    .cap-item:nth-child(2) { border-right:0; }
    .cap-item:nth-child(-n+2) { border-bottom:1px solid var(--border-light); }
  }
  @media(max-width:580px) {
    .editorial-hero.hero-section { padding-top:315px; }
    .hero-art { height:350px; background-position:61% center; }
    .editorial-title { font-size:2.8rem; }
    .hero-copy { padding-top:30px; }
    .story-band { padding:34px 0; }
    .story-grid { padding:0 24px; }
    .story-image img { height:190px; }
    .journey { padding:64px 0; }
    .cap-head { align-items:start; flex-direction:column; }
    .cap-grid { grid-template-columns:1fr; }
    .cap-item { border-right:0; border-bottom:1px solid var(--border-light); }
    .cap-item:last-child { border-bottom:0; }
  }
</style>
@endsection

@section('content')
<div>
  <section class="editorial-hero hero-section" aria-labelledby="home-title">
    <div class="hero-art" aria-hidden="true"></div>
    <div class="wrap">
      <div class="hero-copy">
        <p class="hero-kicker" data-i18n="home_kicker">SHIRIKISHO · ELIMU · BIASHARA</p>
        <h1 class="editorial-title" id="home-title" data-i18n="home_title">Jukwaa la Kidigitali la Wakulima wa Tanzania.</h1>
        <p class="hero-summary" data-i18n="home_summary">Tunaunganisha maarifa, masoko na huduma muhimu kwenye jukwaa moja. Kutoka shambani hadi sokoni, kila hatua inawezekana na MkulimaForum.</p>
        <ul class="benefit-list">
          <li><span class="material-symbols-rounded" aria-hidden="true">check_circle</span><span data-i18n="benefit_1">Maarifa sahihi na kwa wakati</span></li>
          <li><span class="material-symbols-rounded" aria-hidden="true">check_circle</span><span data-i18n="benefit_2">Ufikiaji wa masoko na wanunuzi</span></li>
          <li><span class="material-symbols-rounded" aria-hidden="true">check_circle</span><span data-i18n="benefit_3">Zana za kisasa za kilimo</span></li>
          <li><span class="material-symbols-rounded" aria-hidden="true">check_circle</span><span data-i18n="benefit_4">Jumuiya ya wakulima na ushauri</span></li>
        </ul>
        <div class="hero-actions">
          <a href="/download" class="btn btn-gold"><span class="material-symbols-rounded" aria-hidden="true">download</span><span data-i18n="download_app">Pakua App ya Mkulima</span></a>
          <a href="/pitch-deck" class="text-link" data-i18n="view_pitch">Tazama Pitch Deck →</a>
        </div>
        <div class="availability" aria-label="App availability">
          <small data-i18n="available_on">Inapatikana kwenye</small>
          <span class="store-pill">Google Play</span><span class="store-pill">Android APK</span><span class="store-pill">Web App</span>
        </div>
      </div>
    </div>
  </section>

  <section class="story-band" aria-labelledby="farmer-story">
    <div class="story-grid">
      <div class="story-image">
        <img src="/images/home/farmer-community.webp" alt="Wakulima wakitumia MkulimaForum pamoja" width="1200" height="700" loading="lazy">
        <div class="story-proof"><small>JAMII YETU</small><strong>120K+</strong><span>Wakulima wanaotumia jukwaa letu</span></div>
      </div>
      <div>
        <blockquote id="farmer-story">“Kupitia MkulimaForum, nimejifunza mengi, na sasa naongeza uzalishaji na kipato.”</blockquote>
        <p class="quote-by">— Asha, Mkulima wa Mahindi, Morogoro</p>
      </div>
    </div>
  </section>

  <section class="journey" id="jinsi" aria-labelledby="journey-title">
    <div class="wrap">
      <div class="journey-head">
        <span class="eyebrow" data-i18n="journey_kicker">ANZIA HAPA</span>
        <h2 class="journey-title" id="journey-title" data-i18n="journey_title">Hatua 3 tu – wezesha kilimo chako</h2>
        <p data-i18n="journey_sub">Rahisi, haraka na yenye matokeo halisi.</p>
      </div>
      <div class="journey-steps">
        <article class="journey-step"><span class="step-number">01</span><h3 data-i18n="step1_title">Gundua</h3><p data-i18n="step1_desc">Piga picha ya tatizo la mmea au uliza swali lolote la kilimo.</p><img src="/images/home/plant-scan.webp" alt="Mkulima akipiga picha ya jani lililoathirika" width="900" height="600" loading="lazy"></article>
        <article class="journey-step"><span class="step-number">02</span><h3 data-i18n="step2_title">Pata suluhisho</h3><p data-i18n="step2_desc">Pata majibu sahihi kutoka Mkulima AI na ushauri wa kitaalam kwa lugha rahisi.</p><img src="/images/home/hero-composite.webp" alt="Mkulima AI ikionyesha uchunguzi wa mmea" width="1400" height="1050" loading="lazy"></article>
        <article class="journey-step"><span class="step-number">03</span><h3 data-i18n="step3_title">Tekeleza na faidika</h3><p data-i18n="step3_desc">Tumia maarifa, nunue pembejeo bora na uuze mazao kwa bei nzuri kupitia masoko ya uhakika.</p><img src="/images/home/market-handshake.webp" alt="Mkulima na mnunuzi wakikamilisha biashara ya mazao" width="900" height="600" loading="lazy"></article>
      </div>
      <p class="journey-close">Kilimo chako. Maarifa bora. Masoko bora. Maisha bora.<span>Pamoja, tunajenga kilimo chenye tija kwa Tanzania.</span></p>
    </div>
  </section>

  <section class="light-capabilities" id="vipengele" aria-labelledby="cap-title">
    <div class="wrap">
      <div class="cap-head"><div><span class="eyebrow" data-i18n="cap_kicker">KILA KITU MAHALI PAMOJA</span><h2 class="journey-title" id="cap-title" data-i18n="cap_title">Zana za kukusaidia kutoka shambani hadi sokoni</h2></div><a href="/solutions" class="text-link" data-i18n="all_solutions">Angalia suluhisho zote →</a></div>
      <div class="cap-grid">
        <article class="cap-item"><span class="material-symbols-rounded" aria-hidden="true">document_scanner</span><h3>Mkulima AI</h3><p>Tambua matatizo ya mimea na pata ushauri wa vitendo.</p></article>
        <article class="cap-item"><span class="material-symbols-rounded" aria-hidden="true">verified</span><h3>Kagua Pembejeo</h3><p>Thibitisha ubora kabla ya kununua mbegu, dawa au mbolea.</p></article>
        <article class="cap-item"><span class="material-symbols-rounded" aria-hidden="true">storefront</span><h3>Soko la Kilimo</h3><p>Fikia wanunuzi, wauzaji na bei za mazao kwa urahisi.</p></article>
        <article class="cap-item"><span class="material-symbols-rounded" aria-hidden="true">groups</span><h3>Jamii ya Wakulima</h3><p>Jifunze, uliza maswali na shiriki uzoefu na wengine.</p></article>
      </div>
    </div>
  </section>

  <section class="final-cta" aria-labelledby="cta-title"><div class="wrap"><span class="eyebrow">ANZA LEO</span><h2 id="cta-title">Kilimo bora kiko mikononi mwako.</h2><p>Pakua MkulimaForum na upate maarifa, masoko na msaada unaohitaji kila siku.</p><a href="/download" class="btn btn-gold btn-lg"><span class="material-symbols-rounded" aria-hidden="true">download</span> Pakua App ya Mkulima</a></div></section>
</div>
@endsection

@section('page_scripts')
<script nonce="{{ $cspNonce ?? '' }}">
mkPageTranslations = {
  sw:{home_kicker:'SHIRIKISHO · ELIMU · BIASHARA',home_title:'Jukwaa la Kidigitali la Wakulima wa Tanzania.',home_summary:'Tunaunganisha maarifa, masoko na huduma muhimu kwenye jukwaa moja. Kutoka shambani hadi sokoni, kila hatua inawezekana na MkulimaForum.',benefit_1:'Maarifa sahihi na kwa wakati',benefit_2:'Ufikiaji wa masoko na wanunuzi',benefit_3:'Zana za kisasa za kilimo',benefit_4:'Jumuiya ya wakulima na ushauri',download_app:'Pakua App ya Mkulima',view_pitch:'Tazama Pitch Deck →',available_on:'Inapatikana kwenye',journey_kicker:'ANZIA HAPA',journey_title:'Hatua 3 tu – wezesha kilimo chako',journey_sub:'Rahisi, haraka na yenye matokeo halisi.',step1_title:'Gundua',step1_desc:'Piga picha ya tatizo la mmea au uliza swali lolote la kilimo.',step2_title:'Pata suluhisho',step2_desc:'Pata majibu sahihi kutoka Mkulima AI na ushauri wa kitaalam kwa lugha rahisi.',step3_title:'Tekeleza na faidika',step3_desc:'Tumia maarifa, nunue pembejeo bora na uuze mazao kwa bei nzuri kupitia masoko ya uhakika.',cap_kicker:'KILA KITU MAHALI PAMOJA',cap_title:'Zana za kukusaidia kutoka shambani hadi sokoni',all_solutions:'Angalia suluhisho zote →'},
  en:{home_kicker:'COMMUNITY · KNOWLEDGE · TRADE',home_title:'The Digital Platform for Tanzanian Farmers.',home_summary:'Knowledge, markets and essential services in one place. From field to market, every step is easier with MkulimaForum.',benefit_1:'Reliable, timely farming knowledge',benefit_2:'Access to markets and buyers',benefit_3:'Modern tools for better farming',benefit_4:'A farmer community and expert advice',download_app:'Download the Farmer App',view_pitch:'View Pitch Deck →',available_on:'Available on',journey_kicker:'START HERE',journey_title:'Three steps to strengthen your farm',journey_sub:'Simple, fast and built for real results.',step1_title:'Discover',step1_desc:'Photograph a crop problem or ask any farming question.',step2_title:'Get a solution',step2_desc:'Receive clear answers from Mkulima AI and practical expert guidance.',step3_title:'Act and benefit',step3_desc:'Apply the advice, buy trusted inputs and sell produce through reliable markets.',cap_kicker:'EVERYTHING IN ONE PLACE',cap_title:'Tools that support you from field to market',all_solutions:'View all solutions →'}
};
</script>
@endsection
