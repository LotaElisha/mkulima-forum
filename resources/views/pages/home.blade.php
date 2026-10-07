@extends('layouts.public')

@section('title', 'MkulimaForum | Jukwaa la Kidigitali la Wakulima wa Tanzania')
@section('meta_description', 'MkulimaForum inaunganisha wakulima wa Tanzania na maarifa, masoko, huduma na Mkulima AI.')

@section('head_extra')
<style>
  /* Tokens, buttons, header and footer come from layouts/public.blade.php.
     This block only lays out the home page's own sections. */
  .ico { width:1.15em; height:1.15em; vertical-align:-.2em; flex:none; stroke-width:2; }

  /* Hero: copy on white, photograph to the right (below the copy on phones). */
  .editorial-hero { min-height:620px; position:relative; overflow:hidden; border-bottom:1px solid var(--border-light); }
  .hero-art { position:absolute; inset:0; background:url('/images/home/hero-composite.webp') right center/auto 92% no-repeat; }
  .hero-art::before { content:''; position:absolute; inset:0; background:linear-gradient(90deg,#fff 0%,#fff 36%,rgba(255,255,255,.6) 48%,transparent 64%); }
  .editorial-hero .wrap { min-height:620px; display:flex; align-items:center; position:relative; z-index:2; }
  .hero-copy { width:min(48%,540px); padding:48px 0; }
  .hero-kicker { display:inline-flex; align-items:center; min-height:32px; padding:0 14px; border-radius:999px; background:var(--leaf-pale); color:var(--forest-dark); font-size:13px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:20px; }
  .editorial-title { color:var(--ink-dark); font-size:clamp(34px,4.8vw,56px); font-weight:800; letter-spacing:-.03em; line-height:1.06; max-width:640px; margin-bottom:20px; }
  .hero-summary { font-size:17px; max-width:34rem; line-height:1.65; margin-bottom:22px; color:var(--ink-body); }
  .benefit-list { display:grid; gap:10px; list-style:none; margin:0 0 28px; }
  .benefit-list li { display:flex; align-items:center; gap:10px; font-size:15px; color:var(--ink-body); }
  .benefit-list .ico { color:var(--forest-mid); width:20px; height:20px; }
  .hero-actions { display:flex; align-items:center; gap:20px; flex-wrap:wrap; }
  .availability { display:flex; gap:8px; margin-top:24px; align-items:center; flex-wrap:wrap; }
  .availability small { width:100%; font-size:13px; color:var(--ink-muted); margin-bottom:2px; }
  .store-pill { display:inline-flex; align-items:center; min-height:32px; border:1px solid var(--border-light); padding:0 12px; border-radius:999px; background:#fff; font-size:13px; color:var(--ink-body); }

  /* Testimonial */
  .story-band { border-bottom:1px solid var(--border-light); background:#fff; padding:40px 0; }
  .story-grid { max-width:980px; margin:auto; display:grid; grid-template-columns:380px 1fr; align-items:center; gap:48px; padding:0 24px; }
  .story-image { position:relative; }
  .story-image img { width:100%; height:180px; object-fit:cover; border-radius:16px; }
  .story-proof { position:absolute; left:-20px; bottom:18px; background:#fff; border:1px solid var(--border-light); box-shadow:var(--shadow-md); padding:12px 16px; border-radius:14px; width:150px; }
  .story-proof small { display:block; font-size:13px; font-weight:700; letter-spacing:.06em; color:var(--forest-mid); }
  .story-proof strong { display:block; font-size:24px; font-weight:800; color:var(--ink-dark); line-height:1.2; }
  .story-proof span { display:block; font-size:13px; line-height:1.35; color:var(--ink-muted); }
  blockquote { font-size:clamp(20px,2.2vw,26px); font-weight:600; line-height:1.4; color:var(--ink-dark); letter-spacing:-.01em; }
  .quote-by { margin-top:14px; font-size:15px; color:var(--ink-muted); }

  /* Three steps */
  .journey { padding:80px 0; background:#fff; }
  .journey-head { max-width:1100px; margin:0 auto 40px; }
  .journey-title { font-size:clamp(26px,3.2vw,36px); font-weight:700; color:var(--ink-dark); margin-bottom:8px; letter-spacing:-.02em; }
  .journey-head p { color:var(--ink-muted); font-size:17px; }
  .journey-steps { max-width:1100px; margin:auto; display:grid; grid-template-columns:repeat(3,1fr); gap:20px; }
  .journey-step { border:1px solid var(--border-light); border-radius:var(--radius-xl); padding:24px; display:flex; flex-direction:column; }
  .step-number { display:inline-flex; align-items:center; justify-content:center; width:40px; height:40px; border-radius:12px; background:var(--leaf-pale); color:var(--forest-dark); font-weight:800; font-size:16px; margin-bottom:16px; }
  .journey-step h3 { font-size:19px; font-weight:700; margin-bottom:8px; }
  .journey-step p { font-size:15px; line-height:1.6; color:var(--ink-muted); flex:1; }
  .journey-step img { width:100%; height:170px; object-fit:cover; border-radius:12px; margin-top:18px; }
  .journey-step:nth-child(3) img { object-position:center 40%; }
  .journey-close { text-align:center; font-size:17px; font-weight:600; color:var(--ink-dark); margin-top:36px; }
  .journey-close span { display:block; color:var(--forest-mid); font-size:15px; font-weight:600; margin-top:4px; }

  /* Capabilities */
  .light-capabilities { background:var(--surface-soft); border-top:1px solid var(--border-light); border-bottom:1px solid var(--border-light); padding:72px 0; }
  .cap-head { display:flex; justify-content:space-between; gap:30px; align-items:end; margin-bottom:28px; }
  .cap-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
  .cap-item { padding:24px; background:#fff; border:1px solid var(--border-light); border-radius:var(--radius-xl); }
  .cap-item .ico { color:var(--forest-mid); width:24px; height:24px; margin-bottom:14px; display:block; }
  .cap-item h3 { font-size:17px; margin-bottom:6px; }
  .cap-item p { font-size:15px; line-height:1.55; color:var(--ink-muted); }

  .final-cta { padding:80px 0; text-align:center; background:#fff; }
  .final-cta h2 { font-size:clamp(26px,3.6vw,38px); font-weight:800; margin-bottom:12px; letter-spacing:-.02em; }
  .final-cta p { max-width:540px; margin:0 auto 24px; color:var(--ink-muted); font-size:17px; }
  .final-cta .btn { width:auto; }

  @media(max-width:900px) {
    .story-grid { grid-template-columns:1fr; gap:28px; }
    .story-proof { left:12px; }
    .journey-steps { grid-template-columns:1fr; gap:12px; }
    .cap-grid { grid-template-columns:1fr 1fr; }
  }

  /* Phones: photograph sits in its own band below the copy, never under text. */
  .quick-access { display:none; }
  @media (max-width:700px) {
    .editorial-hero { display:flex; flex-direction:column; overflow:visible; min-height:0; }
    .editorial-hero .wrap { order:1; min-height:0; }
    .hero-art { position:relative; order:2; inset:auto; height:190px; margin:0 16px 20px; border-radius:16px; background-size:cover; background-position:center 62%; }
    .hero-art::before { display:none; }
    .hero-copy { width:100%; padding:24px 0 20px; }
    .editorial-title { font-size:30px; line-height:1.14; }
    .hero-summary { font-size:16px; margin-bottom:16px; }
    .benefit-list { gap:8px; margin-bottom:20px; }
    .hero-actions { gap:4px; }
    .availability { margin-top:16px; }

    /* The main services within one tap of landing. */
    .quick-access { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; padding:20px 16px 4px; }
    .qa-tile {
      display:flex; flex-direction:column; align-items:center; gap:6px;
      padding:14px 4px 12px; min-height:104px;
      background:#fff; border:1px solid var(--border-light); border-radius:14px; text-align:center;
    }
    .qa-tile:active { background:var(--leaf-pale); border-color:var(--forest-mid); }
    .qa-ico { width:40px; height:40px; border-radius:12px; display:grid; place-items:center; background:var(--leaf-pale); color:var(--forest-mid); }
    .qa-label { font-size:13px; font-weight:700; color:var(--ink-dark); line-height:1.2; }
    .qa-sub   { font-size:13px; color:var(--ink-muted); line-height:1.2; }

    .story-band { padding:28px 0; }
    .story-grid { padding:0 16px; }
    .story-image img { height:160px; }
    .journey { padding:44px 0; }
    .journey-step { padding:18px; }
    .journey-step img { height:150px; }
    .journey-close { margin-top:20px; font-size:16px; }
    .light-capabilities { padding:44px 0; }
    .cap-head { align-items:start; flex-direction:column; gap:4px; }
    .cap-grid { grid-template-columns:1fr; gap:10px; }
    .cap-item { padding:18px; }
    .final-cta { padding:48px 0; }
    .final-cta .btn { width:100%; }
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
          <li><x-icon name="check-circle" class="ico" /><span data-i18n="benefit_1">Maarifa sahihi na kwa wakati</span></li>
          <li><x-icon name="check-circle" class="ico" /><span data-i18n="benefit_2">Ufikiaji wa masoko na wanunuzi</span></li>
          <li><x-icon name="check-circle" class="ico" /><span data-i18n="benefit_3">Zana za kisasa za kilimo</span></li>
          <li><x-icon name="check-circle" class="ico" /><span data-i18n="benefit_4">Jumuiya ya wakulima na ushauri</span></li>
        </ul>
        <div class="hero-actions">
          <a href="/download" class="btn btn-primary"><x-icon name="download" class="ico" /><span data-i18n="download_app">Pakua App ya Mkulima</span></a>
          <a href="/pitch-deck" class="text-link" data-i18n="view_pitch">Tazama Pitch Deck →</a>
        </div>
        <div class="availability" aria-label="App availability">
          <small data-i18n="available_on">Inapatikana kwenye</small>
          <span class="store-pill">Google Play</span><span class="store-pill">Android APK</span><span class="store-pill">Web App</span>
        </div>
      </div>
    </div>
  </section>

  {{-- Mobile quick access.

       The brief asks that the home page say what MkulimaForum does and put the
       main services within one to three taps. Before this, the capabilities
       grid was the fourth section down — roughly 3,500px on a 390px screen —
       so a farmer arriving on a phone saw a hero, a testimonial and a
       three-step explainer before anything they could actually use.

       Hidden above 700px, where the editorial layout and the header nav
       already do this job. --}}
  <nav class="quick-access" aria-label="Huduma kuu">
    <a href="/solutions#mkulima-ai" class="qa-tile">
      <span class="qa-ico"><x-icon name="scan" :size="22" /></span>
      <span class="qa-label" data-i18n="qa_ai">Mkulima AI</span>
      <span class="qa-sub" data-i18n="qa_ai_sub">Tambua ugonjwa</span>
    </a>
    <a href="/verify" class="qa-tile">
      <span class="qa-ico"><x-icon name="verified" :size="22" /></span>
      <span class="qa-label" data-i18n="qa_verify">Kagua Pembejeo</span>
      <span class="qa-sub" data-i18n="qa_verify_sub">Epuka bandia</span>
    </a>
    <a href="/solutions#soko" class="qa-tile">
      <span class="qa-ico"><x-icon name="storefront" :size="22" /></span>
      <span class="qa-label" data-i18n="qa_market">Soko la Mazao</span>
      <span class="qa-sub" data-i18n="qa_market_sub">Nunua na uuze</span>
    </a>
    <a href="/solutions#bei" class="qa-tile">
      <span class="qa-ico"><x-icon name="chart" :size="22" /></span>
      <span class="qa-label" data-i18n="qa_prices">Bei za Soko</span>
      <span class="qa-sub" data-i18n="qa_prices_sub">Bei za leo</span>
    </a>
    <a href="/solutions#hali-ya-hewa" class="qa-tile">
      <span class="qa-ico"><x-icon name="sun" :size="22" /></span>
      <span class="qa-label" data-i18n="qa_weather">Hali ya Hewa</span>
      <span class="qa-sub" data-i18n="qa_weather_sub">Utabiri wa mvua</span>
    </a>
    <a href="/community" class="qa-tile">
      <span class="qa-ico"><x-icon name="groups" :size="22" /></span>
      <span class="qa-label" data-i18n="qa_community">Jamii</span>
      <span class="qa-sub" data-i18n="qa_community_sub">Uliza wakulima</span>
    </a>
  </nav>

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
        <article class="cap-item"><x-icon name="scan" class="ico" /><h3>Mkulima AI</h3><p>Tambua matatizo ya mimea na pata ushauri wa vitendo.</p></article>
        <article class="cap-item"><x-icon name="verified" class="ico" /><h3>Kagua Pembejeo</h3><p>Thibitisha ubora kabla ya kununua mbegu, dawa au mbolea.</p></article>
        <article class="cap-item"><x-icon name="storefront" class="ico" /><h3>Soko la Kilimo</h3><p>Fikia wanunuzi, wauzaji na bei za mazao kwa urahisi.</p></article>
        <article class="cap-item"><x-icon name="groups" class="ico" /><h3>Jamii ya Wakulima</h3><p>Jifunze, uliza maswali na shiriki uzoefu na wengine.</p></article>
      </div>
    </div>
  </section>

  <section class="final-cta" aria-labelledby="cta-title"><div class="wrap"><span class="eyebrow">ANZA LEO</span><h2 id="cta-title">Kilimo bora kiko mikononi mwako.</h2><p>Pakua MkulimaForum na upate maarifa, masoko na msaada unaohitaji kila siku.</p><a href="/download" class="btn btn-primary btn-lg"><x-icon name="download" class="ico" /> Pakua App ya Mkulima</a></div></section>
</div>
@endsection

@section('page_scripts')
<script nonce="{{ $cspNonce ?? '' }}">
mkPageTranslations = {
  sw:{home_kicker:'SHIRIKISHO · ELIMU · BIASHARA',home_title:'Jukwaa la Kidigitali la Wakulima wa Tanzania.',home_summary:'Tunaunganisha maarifa, masoko na huduma muhimu kwenye jukwaa moja. Kutoka shambani hadi sokoni, kila hatua inawezekana na MkulimaForum.',benefit_1:'Maarifa sahihi na kwa wakati',benefit_2:'Ufikiaji wa masoko na wanunuzi',benefit_3:'Zana za kisasa za kilimo',benefit_4:'Jumuiya ya wakulima na ushauri',download_app:'Pakua App ya Mkulima',view_pitch:'Tazama Pitch Deck →',available_on:'Inapatikana kwenye',journey_kicker:'ANZIA HAPA',journey_title:'Hatua 3 tu – wezesha kilimo chako',journey_sub:'Rahisi, haraka na yenye matokeo halisi.',step1_title:'Gundua',step1_desc:'Piga picha ya tatizo la mmea au uliza swali lolote la kilimo.',step2_title:'Pata suluhisho',step2_desc:'Pata majibu sahihi kutoka Mkulima AI na ushauri wa kitaalam kwa lugha rahisi.',step3_title:'Tekeleza na faidika',step3_desc:'Tumia maarifa, nunue pembejeo bora na uuze mazao kwa bei nzuri kupitia masoko ya uhakika.',cap_kicker:'KILA KITU MAHALI PAMOJA',cap_title:'Zana za kukusaidia kutoka shambani hadi sokoni',all_solutions:'Angalia suluhisho zote →',qa_ai:'Mkulima AI',qa_ai_sub:'Tambua ugonjwa',qa_verify:'Kagua Pembejeo',qa_verify_sub:'Epuka bandia',qa_market:'Soko la Mazao',qa_market_sub:'Nunua na uuze',qa_prices:'Bei za Soko',qa_prices_sub:'Bei za leo',qa_weather:'Hali ya Hewa',qa_weather_sub:'Utabiri wa mvua',qa_community:'Jamii',qa_community_sub:'Uliza wakulima'},
  en:{home_kicker:'COMMUNITY · KNOWLEDGE · TRADE',home_title:'The Digital Platform for Tanzanian Farmers.',home_summary:'Knowledge, markets and essential services in one place. From field to market, every step is easier with MkulimaForum.',benefit_1:'Reliable, timely farming knowledge',benefit_2:'Access to markets and buyers',benefit_3:'Modern tools for better farming',benefit_4:'A farmer community and expert advice',download_app:'Download the Farmer App',view_pitch:'View Pitch Deck →',available_on:'Available on',journey_kicker:'START HERE',journey_title:'Three steps to strengthen your farm',journey_sub:'Simple, fast and built for real results.',step1_title:'Discover',step1_desc:'Photograph a crop problem or ask any farming question.',step2_title:'Get a solution',step2_desc:'Receive clear answers from Mkulima AI and practical expert guidance.',step3_title:'Act and benefit',step3_desc:'Apply the advice, buy trusted inputs and sell produce through reliable markets.',cap_kicker:'EVERYTHING IN ONE PLACE',cap_title:'Tools that support you from field to market',all_solutions:'View all solutions →',qa_ai:'Mkulima AI',qa_ai_sub:'Diagnose disease',qa_verify:'Verify Inputs',qa_verify_sub:'Avoid fakes',qa_market:'Produce Market',qa_market_sub:'Buy and sell',qa_prices:'Market Prices',qa_prices_sub:'Prices today',qa_weather:'Weather',qa_weather_sub:'Rain forecast',qa_community:'Community',qa_community_sub:'Ask farmers'}
};
</script>
@endsection
