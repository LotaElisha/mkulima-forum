@extends('layouts.public')

@section('title', 'MkulimaForum | AI-Powered Agriculture Platform for Tanzania')
@section('meta_description', 'MkulimaForum connects Tanzania farmers with AI plant diagnosis, agri-markets, weather intelligence, and trusted farming support — in Swahili and English.')
@section('og_title', 'MkulimaForum | AI Agriculture Platform for Tanzania')
@section('og_description', 'AI-powered digital agriculture ecosystem for East African smallholder farmers.')

@section('head_extra')
<style>
  /* ---- Home page specific styles ---- */
  /* Hero */
  .hero-home {
    background: radial-gradient(circle at 75% 30%, #1F6B38 0%, var(--forest-dark) 65%);
    color: #fff; position: relative; overflow: hidden;
    padding: 80px 0 100px;
  }
  .hero-home::after {
    content:''; position:absolute; bottom:-1px; left:0; right:0; height:60px;
    background: var(--cream-bg); clip-path: ellipse(55% 100% at 50% 100%);
  }
  .hero-grid {
    display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 40px; align-items: center; position: relative; z-index: 2;
  }
  @media(max-width:860px){ .hero-grid{ grid-template-columns:1fr; } .hero-phone{ display:none; } }

  /* Stats strip */
  .stats-strip {
    background: var(--surface-card); border-bottom: 1px solid var(--border-light);
    border-top: 1px solid var(--border-light);
  }
  .stats-inner {
    display: flex; gap: 0; overflow-x: auto;
  }
  .stat-item {
    flex: 1; min-width: 160px; padding: 28px 24px; text-align: center;
    border-right: 1px solid var(--border-light);
  }
  .stat-item:last-child { border-right: none; }
  .stat-num { font-family:'Outfit',sans-serif; font-size:2rem; font-weight:900; color:var(--forest-dark); }
  .stat-label { font-size:.8rem; font-weight:600; color:var(--ink-muted); text-transform:uppercase; letter-spacing:.08em; margin-top:4px; }

  /* Phone mockup */
  .phone-wrap { display:flex; justify-content:center; }
  .phone-shell {
    width:272px; background:#07190C; border-radius:42px; padding:12px;
    border:2.5px solid rgba(255,255,255,.15); box-shadow:0 30px 70px rgba(0,0,0,.45);
    transform: perspective(900px) rotateY(-8deg) rotateX(3deg);
    transition: transform .5s ease;
  }
  .phone-shell:hover { transform:perspective(900px) rotateY(0deg) rotateX(0deg); }
  .phone-screen { background:#0B1E10; border-radius:32px; overflow:hidden; border:1px solid rgba(255,255,255,.1); }
  .phone-screen-hd { background:var(--forest-mid); padding:14px 12px 10px; text-align:center; }
  .phone-screen-hd h4 { font-size:.9rem; font-weight:800; color:#fff; }
  .phone-screen-hd p  { font-size:.62rem; color:var(--sun-amber); font-weight:700; letter-spacing:.08em; }
  .scan-area {
    margin:12px; height:160px; border-radius:18px;
    background:linear-gradient(150deg,#1C4223,#0F2D15);
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    position:relative; overflow:hidden; border:1px solid rgba(107,181,53,.3);
  }
  .scan-line {
    position:absolute; width:100%; height:2.5px;
    background:linear-gradient(90deg,transparent,var(--leaf-bright),transparent);
    box-shadow:0 0 10px var(--leaf-bright);
    animation:mkScan 2.4s infinite ease-in-out;
  }
  @keyframes mkScan { 0%{top:10%} 50%{top:85%} 100%{top:10%} }
  .scan-icon{ font-size:48px; margin-bottom:4px; }
  .scan-tag { font-size:.62rem; font-weight:700; background:rgba(76,155,39,.3); padding:3px 10px; border-radius:999px; color:#9FE870; }
  .scan-result { margin:0 12px 14px; background:rgba(255,255,255,.07); border-radius:14px; padding:10px; font-size:.7rem; color:rgba(255,255,255,.9); border:1px solid rgba(255,255,255,.1); }
  .scan-result b{ color:var(--sun-gold); display:block; margin-bottom:3px; font-size:.76rem; }

  /* Solutions preview grid */
  .solution-preview {
    display:grid; grid-template-columns:repeat(4,1fr); gap:20px;
  }
  @media(max-width:1060px){ .solution-preview{ grid-template-columns:repeat(2,1fr); } }
  @media(max-width:580px) { .solution-preview{ grid-template-columns:1fr; } }
  .sol-card {
    background:var(--surface-card); border:1px solid var(--border-light); border-radius:var(--radius-xl);
    padding:28px; transition:all .25s ease; display:flex; flex-direction:column; gap:14px;
  }
  .sol-card:hover { transform:translateY(-4px); box-shadow:var(--shadow-md); border-color:var(--border-mid); }
  .sol-num { font-family:'Outfit',sans-serif; font-size:.78rem; font-weight:900; color:var(--leaf-green); letter-spacing:.1em; }
  .sol-card h3 { font-size:1.05rem; font-weight:800; color:var(--ink-dark); }
  .sol-card p  { font-size:.85rem; color:var(--ink-muted); line-height:1.6; flex:1; }

  /* Partner strip */
  .ecosystem-strip {
    background:var(--surface-card); border:1px solid var(--border-light);
    border-radius:var(--radius-xl); padding:40px; text-align:center;
  }
  .ecosystem-chips { display:flex; flex-wrap:wrap; justify-content:center; gap:12px; margin:24px 0; }
  .eco-chip {
    display:flex; align-items:center; gap:8px; padding:10px 18px;
    background:var(--cream-bg); border:1px solid var(--border-light); border-radius:999px;
    font-size:.88rem; font-weight:700; color:var(--ink-body);
  }

  /* Stories teaser */
  .story-card {
    background:var(--surface-card); border:1px solid var(--border-light); border-radius:var(--radius-xl);
    padding:32px; position:relative;
  }
  .story-quote { font-size:3rem; line-height:1; color:var(--leaf-pale); font-family:'Outfit',sans-serif; font-weight:900; position:absolute; top:16px; left:24px; }
  .story-text  { font-style:italic; color:var(--ink-body); line-height:1.7; padding-top:20px; margin-bottom:20px; }
  .story-person{ display:flex; align-items:center; gap:12px; }
  .story-avatar { width:44px; height:44px; border-radius:50%; background:var(--leaf-pale); display:flex; align-items:center; justify-content:center; font-size:18px; }
  .story-meta h4 { font-size:.92rem; font-weight:800; color:var(--ink-dark); }
  .story-meta p  { font-size:.8rem; color:var(--ink-muted); }
</style>
@endsection

@section('content')

{{-- ============================================================
     HERO
     ============================================================ --}}
<div class="hero-home">
  <div class="wrap hero-grid">
    <div>
      <div class="badge dark" style="margin-bottom:22px;">
        <span class="pulse"></span>
        <span id="home_motto">{{ $settings['brand_motto'] ?? 'SHIRIKI • JIFUNZE • ENDELEA' }}</span>
      </div>

      <h1 style="font-size:clamp(2.4rem,5.5vw,3.8rem); font-weight:900; line-height:1.1; margin-bottom:18px; color:#fff;" data-i18n-html="home_hero_title">
        {!! $settings['hero_title'] ?? 'Jukwaa la Kidigitali la Wakulima wa <span class="gold">Tanzania</span>' !!}
      </h1>
      <div style="font-size:1rem; font-weight:800; color:var(--sun-amber); margin-bottom:14px; letter-spacing:.06em;" data-i18n="home_hero_tagline">
        {{ $settings['hero_tagline'] ?? 'SKANI • TAMBUA • TIBU' }}
      </div>
      <p style="font-size:1.08rem; color:rgba(255,255,255,.88); max-width:34rem; margin-bottom:30px; line-height:1.7;" data-i18n="home_hero_lead">
        {{ $settings['hero_lead'] ?? 'Utambuzi wa magonjwa ya mimea kwa Gemini 3 Flash AI, ushauri wa kilimo kwa Kiswahili, masoko ya mazao na usaidizi wa offline.' }}
      </p>

      {{-- Pillars --}}
      <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:32px;">
        <span class="badge dark"><span>{{ $settings['pillar_1'] ?? '🌱 Shiriki Maarifa' }}</span></span>
        <span class="badge dark"><span>{{ $settings['pillar_2'] ?? '📖 Jifunze Mbinu Bora' }}</span></span>
        <span class="badge dark"><span>{{ $settings['pillar_3'] ?? '👥 Jenga Jamii' }}</span></span>
        <span class="badge dark"><span>{{ $settings['pillar_4'] ?? '📈 Endelea Kukua' }}</span></span>
      </div>

      <div style="display:flex; gap:14px; flex-wrap:wrap; margin-bottom:32px;">
        <a href="/app/mkulima-forum.apk" class="btn btn-gold btn-lg" data-i18n="home_dl_btn">⬇️ Pakua App ya Mkulima</a>
        <a href="/pitch-deck" class="btn btn-ghost btn-lg" data-i18n="home_pitch_btn">📊 Pitch Deck</a>
      </div>

      <div style="display:flex; gap:18px; flex-wrap:wrap; font-size:.84rem; color:rgba(255,255,255,.72); border-top:1px solid rgba(255,255,255,.12); padding-top:18px;">
        <span>✓ <span data-i18n="trust_free">Bure kutumia</span></span>
        <span>✓ <span data-i18n="trust_lang">Kiswahili &amp; English</span></span>
        <span>✓ <span data-i18n="trust_offline">SMS &amp; USSD Offline</span></span>
        <span>✓ <span data-i18n="trust_ai">Gemini 3 AI</span></span>
      </div>
    </div>

    {{-- Phone mockup --}}
    <div class="hero-phone phone-wrap">
      <div class="phone-shell">
        <div class="phone-screen">
          <div class="phone-screen-hd">
            <h4>Mkulima Forum AI</h4>
            <p data-i18n="mockup_tagline">SKANI • TAMBUA • TIBU</p>
          </div>
          <div class="scan-area">
            <div class="scan-line"></div>
            <div class="scan-icon">🌿</div>
            <div class="scan-tag">Gemini 3 Flash Active</div>
          </div>
          <div class="scan-result">
            <b data-i18n="mockup_result">Matokeo ya Uchunguzi</b>
            <span data-i18n="mockup_disease">Ugonjwa: Kutu ya Majani (Leaf Rust)</span><br>
            <span style="color:#7BD48A; font-weight:700;" data-i18n="mockup_treatment">Tiba: Fungicide + Ondoa majani yaliyoathirika</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ============================================================
     STATS STRIP
     ============================================================ --}}
<div class="stats-strip">
  <div class="wrap">
    <div class="stats-inner">
      @php
        $metrics = [
          ['key'=>'metric_farmers','label_sw'=>'Wakulima Waliojisajili','label_en'=>'Farmers Registered','icon'=>'👨‍🌾'],
          ['key'=>'metric_regions','label_sw'=>'Mikoa','label_en'=>'Regions','icon'=>'🗺️'],
          ['key'=>'metric_scans','label_sw'=>'Plant Scans','label_en'=>'Plant Scans','icon'=>'📷'],
          ['key'=>'metric_queries','label_sw'=>'Maswali ya AI','label_en'=>'AI Queries','icon'=>'🤖'],
          ['key'=>'metric_markets','label_sw'=>'Miamala ya Soko','label_en'=>'Market Transactions','icon'=>'🛒'],
        ];
      @endphp
      @foreach($metrics as $m)
        @php $val = $settings[$m['key']] ?? null; @endphp
        <div class="stat-item">
          <div style="font-size:1.6rem; margin-bottom:4px;">{{ $m['icon'] }}</div>
          <div class="stat-num">{{ $val ?? '—' }}</div>
          <div class="stat-label" data-i18n="stat_{{ $m['key'] }}">{{ $m['label_sw'] }}</div>
          @if(!$val)
            <div style="font-size:.7rem; color:var(--ink-faint); margin-top:2px;" data-i18n="tracking_launch">Inaanza kuhesabu</div>
          @endif
        </div>
      @endforeach
    </div>
  </div>
</div>

{{-- ============================================================
     HOW IT WORKS
     ============================================================ --}}
<section>
  <div class="wrap">
    <div style="max-width:42rem; margin-bottom:48px;">
      <span class="eyebrow" data-i18n="how_eyebrow">{{ $settings['kicker_jinsi'] ?? 'Jinsi Inavyofanya Kazi' }}</span>
      <h2 class="section-title" data-i18n="how_title">{{ $settings['title_jinsi'] ?? 'Hatua 3 tu — chini ya dakika moja' }}</h2>
      <p class="section-lead" data-i18n="how_sub">{{ $settings['sub_jinsi'] ?? 'Huhitaji ujuzi wa kiufundi. Kama unaweza kupiga picha, unaweza kutumia Mkulima Forum.' }}</p>
    </div>

    <div class="grid-3" style="gap:24px;">
      @foreach([
        ['icon'=>'📷','num'=>'01','title_sw'=>'Piga Picha ya Mmea','title_en'=>'Snap a Plant Photo','desc_sw'=>'Fungua app, bonyeza kitufe cha Kagua Mmea, na upige picha ya jani lililoathirika.','desc_en'=>'Open the app, tap Scan Plant, and photograph the affected crop leaf in your field.','key'=>'step1'],
        ['icon'=>'⚡','num'=>'02','title_sw'=>'AI Inatambua Papo Hapo','title_en'=>'Instant AI Diagnosis','desc_sw'=>'Gemini 3 Flash inachambua picha kwa sekunde na kutambua ugonjwa au wadudu.','desc_en'=>'Gemini 3 Flash analyzes the image in seconds to identify crop diseases or pest infestations.','key'=>'step2'],
        ['icon'=>'💊','num'=>'03','title_sw'=>'Pata Tiba na Ushauri','title_en'=>'Get Treatment & Advice','desc_sw'=>'Pata maelekezo ya dawa zilizothibitishwa, hatua za kinga, na msaada wa mtalamu.','desc_en'=>'Receive TFRA-certified treatment steps, prevention guidelines, and expert agronomist support.','key'=>'step3'],
      ] as $step)
      <div class="card fade-up" style="position:relative;">
        <div style="position:absolute; top:18px; right:22px; font-family:'Outfit',sans-serif; font-size:3rem; font-weight:900; color:var(--leaf-pale); line-height:1;">{{ $step['num'] }}</div>
        <div class="card-icon">{{ $step['icon'] }}</div>
        <h3 data-i18n="{{ $step['key'] }}_title">{{ $step['title_sw'] }}</h3>
        <p data-i18n="{{ $step['key'] }}_desc">{{ $step['desc_sw'] }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ============================================================
     SOLUTIONS PREVIEW
     ============================================================ --}}
<section style="background:var(--leaf-pale); padding:80px 0;">
  <div class="wrap">
    <div style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:20px; margin-bottom:40px;">
      <div>
        <span class="eyebrow" data-i18n="sol_eyebrow">{{ $settings['kicker_vipengele'] ?? 'Vipengele' }}</span>
        <h2 class="section-title" data-i18n="sol_title" style="margin-bottom:0;">{{ $settings['title_vipengele'] ?? 'Zaidi ya Scanner — Mfumo Kamili wa Kilimo' }}</h2>
      </div>
      <a href="/solutions" class="btn btn-outline" data-i18n="sol_all_btn">Angalia Suluhisho Zote →</a>
    </div>

    <div class="solution-preview">
      @foreach([
        ['num'=>'01','icon'=>'📷','title_sw'=>'AI Plant Scanner','title_en'=>'AI Plant Scanner','desc_sw'=>'Utambuzi wa magonjwa ya mazao kwa picha — Gemini 3 Flash AI.','desc_en'=>'Crop disease diagnosis from photos — Gemini 3 Flash AI.'],
        ['num'=>'02','icon'=>'🤖','title_sw'=>'Mkulima Bot','title_en'=>'Mkulima Bot','desc_sw'=>'Msaidizi wa kilimo wa AI 24/7 kwa Kiswahili na Kiingereza.','desc_en'=>'24/7 AI agronomy assistant in Swahili and English.'],
        ['num'=>'03','icon'=>'🛒','title_sw'=>'Soko la Kilimo','title_en'=>'Agri Marketplace','desc_sw'=>'Nunua pembejeo za kweli, uuze mazao, na kulindwa na Mkulima Escrow.','desc_en'=>'Buy verified inputs, sell produce, secured by Mkulima Escrow.'],
        ['num'=>'04','icon'=>'🛡️','title_sw'=>'Kagua Pembejeo','title_en'=>'Input Verification','desc_sw'=>'Hakikisha pembejeo ni za kweli kupitia TFRA/TPRI na jamii.','desc_en'=>'Verify input authenticity via TFRA/TPRI registry and community reports.'],
        ['num'=>'05','icon'=>'⛅','title_sw'=>'Hali ya Hewa','title_en'=>'Weather Intelligence','desc_sw'=>'Utabiri wa hewa wa mkoa wako na ushauri wa msimu wa kupanda.','desc_en'=>'Regional micro-climate forecasts and seasonal planting advice.'],
        ['num'=>'06','icon'=>'📱','title_sw'=>'Offline AI Mode','title_en'=>'Offline AI Mode','desc_sw'=>'Gemma 2B INT4 inafanya kazi bila intaneti shambani.','desc_en'=>'Gemma 2B INT4 quantized inference works without internet connectivity.'],
        ['num'=>'07','icon'=>'👥','title_sw'=>'Jamii ya Wakulima','title_en'=>'Farmer Community','desc_sw'=>'Shiriki maarifa, uliza maswali, na upate majibu ya wataalamu.','desc_en'=>'Share knowledge, ask questions, and get answers from certified agronomists.'],
        ['num'=>'08','icon'=>'📈','title_sw'=>'Ujasiriamali wa Soko','title_en'=>'Market Intelligence','desc_sw'=>'Bei za mazao, mahitaji ya wanunuzi, na mwenendo wa soko.','desc_en'=>'Crop prices, buyer demand, and real-time market trends.'],
      ] as $i => $s)
      <div class="sol-card fade-up" style="animation-delay:{{ $i * 0.05 }}s;">
        <div style="display:flex; align-items:center; justify-content:space-between;">
          <span class="sol-num">{{ $s['num'] }}</span>
          <span style="font-size:1.8rem;">{{ $s['icon'] }}</span>
        </div>
        <h3 data-i18n="sol{{ $i }}_title">{{ $s['title_sw'] }}</h3>
        <p data-i18n="sol{{ $i }}_desc">{{ $s['desc_sw'] }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ============================================================
     FARMER STORIES TEASER
     ============================================================ --}}
<section>
  <div class="wrap">
    <div style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:20px; margin-bottom:40px;">
      <div>
        <span class="eyebrow" data-i18n="stories_eyebrow">HADITHI ZA WAKULIMA</span>
        <h2 class="section-title" style="margin-bottom:0;" data-i18n="stories_title">Zilizojengwa Kwa Changamoto za Kweli za Kilimo</h2>
      </div>
      <a href="/stories" class="btn btn-outline" data-i18n="stories_all_btn">Soma Hadithi Zaidi →</a>
    </div>

    {{-- Coming-soon placeholder --}}
    <div style="background:var(--leaf-pale); border:1.5px dashed var(--border-mid); border-radius:var(--radius-xl); padding:56px; text-align:center;">
      <div style="font-size:3rem; margin-bottom:16px;">🌾</div>
      <h3 style="font-size:1.3rem; color:var(--ink-dark); margin-bottom:10px;" data-i18n="stories_soon_title">Hadithi za Wakulima Zinakuja</h3>
      <p style="color:var(--ink-muted); max-width:26rem; margin:0 auto 24px;" data-i18n="stories_soon_desc">Tunakusanya hadithi halisi za wakulima wanaotumia MkulimaForum. Rudi hivi karibuni.</p>
      <a href="/stories" class="btn btn-outline btn-sm" data-i18n="stories_learn_btn">Jifunze Zaidi →</a>
    </div>
  </div>
</section>

{{-- ============================================================
     ECOSYSTEM / PARTNERS
     ============================================================ --}}
<section style="background:var(--surface-card); padding:72px 0; border-top:1px solid var(--border-light);">
  <div class="wrap" style="text-align:center;">
    <span class="eyebrow" data-i18n="eco_eyebrow">MFUMO WA KILIMO</span>
    <h2 class="section-title" style="max-width:40rem; margin:0 auto 12px;" data-i18n="eco_title">Imejengwa Kufanya Kazi Kwa Mfumo Wote wa Kilimo</h2>
    <p class="section-lead" style="margin:0 auto 36px; text-align:center;" data-i18n="eco_sub">Tunaunganisha kila mhusika katika mnyororo wa thamani wa kilimo.</p>

    <div class="ecosystem-chips">
      @foreach([
        ['🤖','Teknolojia ya AI'],['👨‍🌾','Wakulima'],['🌿','Wataalamu wa Kilimo'],
        ['🏪','Wauzaji wa Pembejeo'],['🏦','Huduma za Kifedha'],['🏛️','Serikali'],
        ['🔬','Utafiti'],['📦','Wanunuzi wa Mazao'],
      ] as $e)
      <div class="eco-chip">{{ $e[0] }} {{ $e[1] }}</div>
      @endforeach
    </div>

    <a href="/partners" class="btn btn-outline" data-i18n="eco_cta">Chunguza Ushirikiano →</a>
  </div>
</section>

{{-- ============================================================
     PITCH DECK BANNER
     ============================================================ --}}
<section class="tight">
  <div class="wrap">
    <div class="panel-dark" style="display:grid; grid-template-columns:1.2fr 0.8fr; gap:40px; align-items:center;">
      @media(max-width:700px){ grid-template-columns:1fr; }
      <div>
        <span class="badge dark" style="margin-bottom:16px;" data-i18n="pitch_eyebrow">INVESTOR PRESENTATION</span>
        <h2 style="font-size:clamp(1.6rem,3.5vw,2.2rem);" data-i18n="pitch_headline">Google Africa Applied AI Lab Pitch Deck</h2>
        <p style="margin-top:12px;" data-i18n="pitch_sub">Jifunze zaidi kuhusu mradi wetu, muundo wa biashara, na athari tunazolenga kwa wakulima wadogo wadogo.</p>
      </div>
      <div style="display:flex; gap:12px; flex-wrap:wrap; justify-content:flex-end;">
        <a href="/pitch-deck" class="btn btn-gold" data-i18n="pitch_view_btn">👁️ Tazama Online</a>
        <a href="{{ $settings['pitch_deck_url'] ?? '/docs/Mkulima_Forum_Pitch_Deck.pdf' }}" target="_blank" rel="noopener" class="btn btn-ghost" data-i18n="pitch_dl_btn">⬇️ Pakua PDF</a>
      </div>
    </div>
  </div>
</section>

{{-- ============================================================
     DOWNLOAD CTA
     ============================================================ --}}
<section style="background:linear-gradient(135deg, #0E4220, var(--forest-dark)); color:#fff; padding:80px 0;">
  <div class="wrap" style="text-align:center;">
    <div class="badge dark" style="margin:0 auto 20px;" data-i18n="dl_badge">BURE KUPAKUA</div>
    <h2 style="font-size:clamp(1.8rem,4vw,2.8rem); color:#fff; margin-bottom:14px;" data-i18n="dl_title">Anza Leo — Bila Malipo</h2>
    <p style="color:rgba(255,255,255,.82); max-width:30rem; margin:0 auto 32px; font-size:1rem;" data-i18n="dl_sub">Pakua app ya MkulimaForum kwenye simu yako ya Android na uanze kulinda mazao yako leo.</p>
    <div style="display:flex; gap:14px; flex-wrap:wrap; justify-content:center;">
      <a href="/app/mkulima-forum.apk" class="btn btn-gold btn-lg" data-i18n="dl_apk_btn">⬇️ Pakua APK (Android)</a>
      <a href="/solutions" class="btn btn-ghost btn-lg" data-i18n="dl_learn_btn">Jifunze Zaidi →</a>
    </div>
  </div>
</section>

@endsection

@section('page_scripts')
<script>
const mkPageTranslations = {
  sw: {
    home_hero_title:'{{ addslashes($settings["hero_title"] ?? "Jukwaa la Kidigitali la Wakulima wa Tanzania") }}',
    home_hero_tagline:'{{ $settings["hero_tagline"] ?? "SKANI • TAMBUA • TIBU" }}',
    home_hero_lead:'{{ $settings["hero_lead"] ?? "" }}',
    home_dl_btn:'⬇️ Pakua App ya Mkulima', home_pitch_btn:'📊 Pitch Deck',
    trust_free:'Bure kutumia', trust_lang:'Kiswahili & English', trust_offline:'SMS & USSD Offline', trust_ai:'Gemini 3 AI',
    mockup_tagline:'SKANI • TAMBUA • TIBU', mockup_result:'Matokeo ya Uchunguzi',
    mockup_disease:'Ugonjwa: Kutu ya Majani (Leaf Rust)', mockup_treatment:'Tiba: Fungicide + Ondoa majani yaliyoathirika',
    how_eyebrow:'{{ $settings["kicker_jinsi"] ?? "Jinsi Inavyofanya Kazi" }}',
    how_title:'{{ $settings["title_jinsi"] ?? "Hatua 3 tu — chini ya dakika moja" }}',
    how_sub:'{{ $settings["sub_jinsi"] ?? "" }}',
    step1_title:'1. Piga Picha ya Mmea', step1_desc:'Fungua app, bonyeza kitufe cha Kagua Mmea, na upige picha ya jani lililoathirika.',
    step2_title:'2. AI Inatambua Papo Hapo', step2_desc:'Gemini 3 Flash inachambua picha kwa sekunde na kutambua ugonjwa au wadudu.',
    step3_title:'3. Pata Tiba na Ushauri', step3_desc:'Pata maelekezo ya dawa zilizothibitishwa, hatua za kinga, na msaada wa mtalamu.',
    sol_eyebrow:'{{ $settings["kicker_vipengele"] ?? "Vipengele" }}',
    sol_title:'{{ $settings["title_vipengele"] ?? "Zaidi ya Scanner — Mfumo Kamili wa Kilimo" }}',
    sol_all_btn:'Angalia Suluhisho Zote →',
    sol0_title:'AI Plant Scanner', sol0_desc:'Utambuzi wa magonjwa ya mazao kwa picha — Gemini 3 Flash AI.',
    sol1_title:'Mkulima Bot', sol1_desc:'Msaidizi wa kilimo wa AI 24/7 kwa Kiswahili na Kiingereza.',
    sol2_title:'Soko la Kilimo', sol2_desc:'Nunua pembejeo za kweli, uuze mazao, na kulindwa na Mkulima Escrow.',
    sol3_title:'Kagua Pembejeo', sol3_desc:'Hakikisha pembejeo ni za kweli kupitia TFRA/TPRI na jamii.',
    sol4_title:'Hali ya Hewa', sol4_desc:'Utabiri wa hewa wa mkoa wako na ushauri wa msimu wa kupanda.',
    sol5_title:'Offline AI Mode', sol5_desc:'Gemma 2B INT4 inafanya kazi bila intaneti shambani.',
    sol6_title:'Jamii ya Wakulima', sol6_desc:'Shiriki maarifa, uliza maswali, na upate majibu ya wataalamu.',
    sol7_title:'Ujasiriamali wa Soko', sol7_desc:'Bei za mazao, mahitaji ya wanunuzi, na mwenendo wa soko.',
    stories_eyebrow:'HADITHI ZA WAKULIMA', stories_title:'Zilizojengwa Kwa Changamoto za Kweli za Kilimo',
    stories_all_btn:'Soma Hadithi Zaidi →', stories_soon_title:'Hadithi za Wakulima Zinakuja',
    stories_soon_desc:'Tunakusanya hadithi halisi za wakulima wanaotumia MkulimaForum. Rudi hivi karibuni.', stories_learn_btn:'Jifunze Zaidi →',
    eco_eyebrow:'MFUMO WA KILIMO', eco_title:'Imejengwa Kufanya Kazi Kwa Mfumo Wote wa Kilimo',
    eco_sub:'Tunaunganisha kila mhusika katika mnyororo wa thamani wa kilimo.', eco_cta:'Chunguza Ushirikiano →',
    pitch_eyebrow:'INVESTOR PRESENTATION', pitch_headline:'Google Africa Applied AI Lab Pitch Deck',
    pitch_sub:'Jifunze zaidi kuhusu mradi wetu, muundo wa biashara, na athari tunazolenga kwa wakulima wadogo wadogo.',
    pitch_view_btn:'👁️ Tazama Online', pitch_dl_btn:'⬇️ Pakua PDF',
    dl_badge:'BURE KUPAKUA', dl_title:'Anza Leo — Bila Malipo',
    dl_sub:'Pakua app ya MkulimaForum kwenye simu yako ya Android na uanze kulinda mazao yako leo.',
    dl_apk_btn:'⬇️ Pakua APK (Android)', dl_learn_btn:'Jifunze Zaidi →',
    stat_metric_farmers:'Wakulima Waliojisajili', stat_metric_regions:'Mikoa', stat_metric_scans:'Plant Scans',
    stat_metric_queries:'Maswali ya AI', stat_metric_markets:'Miamala ya Soko', tracking_launch:'Inaanza kuhesabu',
  },
  en: {
    home_hero_title:'Digital Agriculture Platform for <span class="gold">Tanzania</span>',
    home_hero_tagline:'SCAN • DIAGNOSE • TREAT',
    home_hero_lead:'AI-powered crop disease diagnosis, agronomy advice in Swahili & English, agri-markets, real-time prices, and offline support via Gemma 2B.',
    home_dl_btn:'⬇️ Download Farmer App', home_pitch_btn:'📊 Pitch Deck',
    trust_free:'Free to use', trust_lang:'Swahili & English', trust_offline:'SMS & USSD Offline', trust_ai:'Gemini 3 AI',
    mockup_tagline:'SCAN • DIAGNOSE • TREAT', mockup_result:'Diagnosis Result',
    mockup_disease:'Disease: Leaf Rust', mockup_treatment:'Treatment: Fungicide + Remove infected leaves',
    how_eyebrow:'HOW IT WORKS', how_title:'3 Simple Steps — Under One Minute',
    how_sub:'No technical expertise required. If you can take a photo, you can use Mkulima Forum.',
    step1_title:'1. Snap a Plant Photo', step1_desc:'Open the app, tap Scan Plant, and photograph the affected crop leaf in your field.',
    step2_title:'2. Instant AI Diagnosis', step2_desc:'Gemini 3 Flash analyzes the image in seconds to identify crop diseases or pest infestations.',
    step3_title:'3. Get Treatment & Advice', step3_desc:'Receive TFRA-certified treatment steps, prevention guidelines, and expert agronomist support.',
    sol_eyebrow:'SOLUTIONS', sol_title:'More Than a Scanner — Complete Agri Ecosystem',
    sol_all_btn:'View All Solutions →',
    sol0_title:'AI Plant Scanner', sol0_desc:'Crop disease diagnosis from photos — Gemini 3 Flash AI.',
    sol1_title:'Mkulima Bot', sol1_desc:'24/7 AI agronomy assistant in Swahili and English.',
    sol2_title:'Agri Marketplace', sol2_desc:'Buy verified inputs, sell produce, secured by Mkulima Escrow.',
    sol3_title:'Input Verification', sol3_desc:'Verify input authenticity via TFRA/TPRI registry and community reports.',
    sol4_title:'Weather Intelligence', sol4_desc:'Regional micro-climate forecasts and seasonal planting advice.',
    sol5_title:'Offline AI Mode', sol5_desc:'Gemma 2B INT4 quantized inference works without internet in the field.',
    sol6_title:'Farmer Community', sol6_desc:'Share knowledge, ask questions, and get expert agronomist answers.',
    sol7_title:'Market Intelligence', sol7_desc:'Crop prices, buyer demand, and real-time market trends.',
    stories_eyebrow:'FARMER STORIES', stories_title:'Built Around Real Farming Challenges',
    stories_all_btn:'Read All Stories →', stories_soon_title:'Farmer Stories Coming Soon',
    stories_soon_desc:'We are collecting verified stories from farmers using MkulimaForum. Check back soon.', stories_learn_btn:'Learn More →',
    eco_eyebrow:'ECOSYSTEM', eco_title:'Built to Work Across the Full Agricultural Ecosystem',
    eco_sub:'Connecting every participant in the agricultural value chain.', eco_cta:'Explore Partnerships →',
    pitch_eyebrow:'INVESTOR PRESENTATION', pitch_headline:'Google Africa Applied AI Lab Pitch Deck',
    pitch_sub:'Learn about our project, business model, and the impact we aim to create for smallholder farmers.',
    pitch_view_btn:'👁️ View Online', pitch_dl_btn:'⬇️ Download PDF',
    dl_badge:'FREE DOWNLOAD', dl_title:'Start Today — No Cost',
    dl_sub:'Download the MkulimaForum app on your Android device and start protecting your crops today.',
    dl_apk_btn:'⬇️ Download APK (Android)', dl_learn_btn:'Learn More →',
    stat_metric_farmers:'Farmers Registered', stat_metric_regions:'Regions', stat_metric_scans:'Plant Scans',
    stat_metric_queries:'AI Queries', stat_metric_markets:'Market Transactions', tracking_launch:'Tracking from launch',
  }
};
</script>
@endsection
