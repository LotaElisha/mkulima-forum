@extends('layouts.public')

@section('title', 'MkulimaForum Solutions | AI, Markets & Digital Farming Tools')
@section('meta_description', 'Explore MkulimaForum\'s complete agricultural solution ecosystem: AI Plant Scanner, Mkulima Bot, Agri Marketplace, Input Verification, Weather Intelligence, Offline AI, and more.')
@section('og_title', 'MkulimaForum Solutions | Complete Agri Digital Ecosystem')
@section('og_description', 'One platform. Eight agricultural solutions designed for East African smallholder farmers.')

@section('head_extra')
<style>
  .sol-hero-grid { display:grid; grid-template-columns:1fr 1fr; gap:40px; align-items:center; }
  @media(max-width:780px){ .sol-hero-grid{ grid-template-columns:1fr; } }

  .solution-row {
    display:grid; grid-template-columns:0.42fr 0.58fr; gap:56px; align-items:start;
    padding:64px 0; border-bottom:1px solid var(--border-light);
  }
  .solution-row.reverse { grid-template-columns:0.58fr 0.42fr; }
  .solution-row.reverse .sol-info { order:2; }
  .solution-row.reverse .sol-visual { order:1; }
  @media(max-width:860px){ .solution-row,.solution-row.reverse { grid-template-columns:1fr; } .solution-row.reverse .sol-info,.solution-row.reverse .sol-visual { order:unset; } }

  .sol-number { font-family:'Outfit',sans-serif; font-size:5rem; font-weight:900; color:var(--leaf-pale); line-height:1; margin-bottom:8px; }
  .sol-info h2 { font-size:clamp(1.6rem,3vw,2.2rem); font-weight:900; color:var(--ink-dark); margin-bottom:14px; }
  .sol-info p  { color:var(--ink-muted); line-height:1.75; margin-bottom:20px; }
  .sol-tags    { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:24px; }
  .sol-cap-list { list-style:none; display:flex; flex-direction:column; gap:8px; margin-bottom:24px; }
  .sol-cap-list li { display:flex; align-items:center; gap:10px; font-size:.9rem; color:var(--ink-body); font-weight:600; }
  .sol-cap-list li::before { content:'✓'; color:var(--leaf-green); font-weight:900; flex-shrink:0; }

  .sol-visual {
    background:linear-gradient(145deg, var(--leaf-pale), #E1F0D8); border-radius:var(--radius-2xl);
    padding:40px; min-height:280px; display:flex; flex-direction:column; align-items:center; justify-content:center;
    border:1px solid var(--border-mid); text-align:center; position:relative; overflow:hidden;
  }
  .sol-visual .sol-icon { font-size:4.5rem; margin-bottom:16px; }
  .sol-visual h4 { font-size:1.1rem; font-weight:800; color:var(--forest-dark); }
  .sol-visual p  { font-size:.85rem; color:var(--ink-muted); margin-top:6px; max-width:200px; }

  /* Architecture flow */
  .arch-flow { display:flex; align-items:center; gap:0; flex-wrap:wrap; justify-content:center; margin:32px 0; }
  .arch-node { padding:12px 20px; background:var(--surface-card); border:1px solid var(--border-mid); border-radius:12px; font-size:.85rem; font-weight:700; color:var(--ink-dark); text-align:center; }
  .arch-node.primary { background:var(--forest-dark); color:#fff; }
  .arch-node.accent  { background:var(--leaf-pale); color:var(--forest-mid); border-color:var(--border-mid); }
  .arch-arrow { font-size:1.4rem; color:var(--leaf-green); padding:0 8px; }
</style>
@endsection

@section('content')

{{-- Hero --}}
<section class="page-hero" style="padding-bottom:60px;">
  <div class="wrap">
    <div class="sol-hero-grid fade-up">
      <div>
        <span class="eyebrow" data-i18n="sol_eyebrow">MOJA. TISA. KARIBU.</span>
        <h1 class="page-title" data-i18n="sol_title">Jukwaa Moja. Suluhisho Nyingi za Kilimo.</h1>
        <p class="section-lead" data-i18n="sol_sub">Kuanzia utambuzi wa magonjwa hadi masoko, fedha, maarifa, na ufikio wa nje ya mtandao.</p>
        <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:28px;">
          <a href="/contact" class="btn btn-primary btn-lg" data-i18n="sol_partner_btn">Shirikiana Nasi</a>
          <a href="/technology" class="btn btn-outline btn-lg" data-i18n="sol_tech_btn">Teknolojia Yetu →</a>
        </div>
      </div>
      <div style="display:flex; flex-wrap:wrap; gap:12px; padding:20px; background:var(--surface-card); border-radius:var(--radius-2xl); border:1px solid var(--border-light);">
        @foreach(['📷 Plant Scanner','🤖 Mkulima Bot','🛒 Soko','🛡️ Kagua','⛅ Hali ya Hewa','📱 Offline AI','👥 Jamii','📈 Masoko'] as $chip)
        <span class="tag" style="font-size:.85rem; padding:8px 14px;">{{ $chip }}</span>
        @endforeach
      </div>
    </div>
  </div>
</section>

{{-- ============================================================
     SOLUTIONS
     ============================================================ --}}
<div class="wrap">

  {{-- Solution 01: AI Plant Scanner --}}
  <div class="solution-row" id="plant-scanner">
    <div class="sol-info fade-up">
      <div class="sol-number">01</div>
      <div class="card-icon">📷</div>
      <h2 data-i18n="s1_title">AI Plant Scanner</h2>
      <p data-i18n="s1_desc">Wakulima wanapiga picha ya mazao yaliyoathirika na kupata utambuzi wa ugonjwa au wadudu kwa msaada wa AI, pamoja na mapendekezo ya matibabu yanayoweza kutumika.</p>
      <ul class="sol-cap-list">
        <li data-i18n="s1_cap1">Utambuzi wa ugonjwa wa mazao kwa sekunde</li>
        <li data-i18n="s1_cap2">Mapendekezo ya dawa za TFRA zilizothibitishwa</li>
        <li data-i18n="s1_cap3">Ushauri wa kuzuia na hatua za dharura</li>
        <li data-i18n="s1_cap4">Historia ya skanning na mwelekeo wa magonjwa</li>
      </ul>
      <div class="sol-tags">
        <span class="tag">Gemini 3 Flash AI</span>
        <span class="tag">Computer Vision</span>
        <span class="tag">Agronomy KB</span>
      </div>
    </div>
    <div class="sol-visual fade-up">
      <div class="sol-icon">📷</div>
      <h4 data-i18n="s1_visual_title">Gemini 3 Flash Vision</h4>
      <p data-i18n="s1_visual_sub">Inachambua picha ya mmea kwa muda wa sekunde 1–3</p>
      <div style="margin-top:20px; background:rgba(255,255,255,.8); border-radius:12px; padding:14px; width:100%; border:1px solid var(--border-light);">
        <div style="font-size:.72rem; font-weight:700; color:var(--forest-mid); margin-bottom:6px;">⚡ MATOKEO YA AI</div>
        <div style="font-size:.82rem; color:var(--ink-dark); font-weight:700;">Kutu ya Majani — Leaf Rust</div>
        <div style="font-size:.75rem; color:var(--ink-muted); margin-top:3px;">Uhakika: 94.7% • TFRA Tiba: Fungicide Z4</div>
      </div>
    </div>
  </div>

  {{-- Solution 02: Mkulima Bot --}}
  <div class="solution-row reverse" id="mkulima-bot">
    <div class="sol-info fade-up">
      <div class="sol-number">02</div>
      <div class="card-icon">🤖</div>
      <h2 data-i18n="s2_title">Mkulima Bot AI</h2>
      <p data-i18n="s2_desc">Msaidizi wako wa kilimo wa AI 24/7 unaounga mkono Kiswahili na Kiingereza. Uliza maswali yoyote ya kilimo kupitia mazungumzo ya maandishi au sauti.</p>
      <ul class="sol-cap-list">
        <li data-i18n="s2_cap1">Maswali ya mazao na mbinu bora za kilimo</li>
        <li data-i18n="s2_cap2">Udhibiti wa wadudu na magonjwa</li>
        <li data-i18n="s2_cap3">Usimamizi wa udongo na mbolea</li>
        <li data-i18n="s2_cap4">Taarifa za masoko na bei za mazao</li>
        <li data-i18n="s2_cap5">Maswali ya hali ya hewa na mipango ya msimu</li>
      </ul>
      <div class="sol-tags">
        <span class="tag">Gemini 3 Flash AI</span>
        <span class="tag">Swahili + English</span>
        <span class="tag">Virtual Agronomist</span>
      </div>
    </div>
    <div class="sol-visual fade-up" style="background:linear-gradient(145deg,#F0F8FF,#E4F0FF);">
      <div class="sol-icon">🤖</div>
      <h4 data-i18n="s2_visual_title">Mazungumzo ya AI</h4>
      <div style="width:100%; margin-top:16px; display:flex; flex-direction:column; gap:8px;">
        <div style="background:var(--leaf-pale); border-radius:12px 12px 12px 4px; padding:10px 14px; font-size:.78rem; color:var(--ink-dark); text-align:left;">Jinsi ya kuzuia wadudu wa mahindi?</div>
        <div style="background:var(--forest-dark); border-radius:12px 12px 4px 12px; padding:10px 14px; font-size:.78rem; color:#fff; text-align:left;">Tumia Thiamethoxam au Chlorpyrifos kwenye msimu wa mapema. Angalia kila wiki 1 wiki 2...</div>
      </div>
    </div>
  </div>

  {{-- Solution 03: Marketplace --}}
  <div class="solution-row" id="marketplace">
    <div class="sol-info fade-up">
      <div class="sol-number">03</div>
      <div class="card-icon">🛒</div>
      <h2 data-i18n="s3_title">Soko la Pembejeo na Mazao</h2>
      <p data-i18n="s3_desc">Unganisha wakulima, wauzaji wa pembejeo, wanunuzi, wakusanyaji, na wasambazaji wa pembejeo katika soko moja salama.</p>
      <ul class="sol-cap-list">
        <li data-i18n="s3_cap1">Wauzaji walioidhinishwa na TFRA/TPHPA</li>
        <li data-i18n="s3_cap2">Pembejeo: dawa, mbegu, mbolea</li>
        <li data-i18n="s3_cap3">Soko la mazao ya wakulima</li>
        <li data-i18n="s3_cap4">Mfumo wa Escrow wa Mkulima</li>
        <li data-i18n="s3_cap5">M-Pesa, Tigo Pesa, na malipo mengine</li>
      </ul>
      <div class="sol-tags">
        <span class="tag">Escrow Secured</span>
        <span class="tag">M-Pesa / Tigo Pesa</span>
        <span class="tag">TFRA Verified</span>
      </div>
    </div>
    <div class="sol-visual fade-up">
      <div class="sol-icon">🛒</div>
      <h4 data-i18n="s3_visual_title">Mkulima Escrow</h4>
      <p data-i18n="s3_visual_sub">Malipo yote yanalindwa hadi bidhaa iwasilishwe</p>
    </div>
  </div>

  {{-- Solution 04: Input Verification --}}
  <div class="solution-row reverse" id="input-verify">
    <div class="sol-info fade-up">
      <div class="sol-number">04</div>
      <div class="card-icon">🛡️</div>
      <h2 data-i18n="s4_title">Kagua Pembejeo za Kilimo</h2>
      <p data-i18n="s4_desc">Saidia wakulima kuthibitisha bidhaa za kilimo na kupunguza mfiduo wa pembejeo feki zinazosababisha hasara kubwa za mazao.</p>
      <ul class="sol-cap-list">
        <li data-i18n="s4_cap1">Angalia lebo za dawa na mbolea</li>
        <li data-i18n="s4_cap2">Uthibitisho wa rejesta ya TFRA/TPHPA</li>
        <li data-i18n="s4_cap3">Ukaguzi wa QR code ya bidhaa</li>
        <li data-i18n="s4_cap4">Ripoti za jamii za bidhaa za shaka</li>
        <li data-i18n="s4_cap5">Uthibitisho wa wakala wa mauzo</li>
      </ul>
      <div class="sol-tags">
        <span class="tag">TFRA</span>
        <span class="tag">TPHPA</span>
        <span class="tag">Community Reports</span>
      </div>
    </div>
    <div class="sol-visual fade-up" style="background:linear-gradient(145deg,#FFF8F0,#FFE8CC);">
      <div class="sol-icon">🛡️</div>
      <h4 data-i18n="s4_visual_title">Ulinzi wa Pembejeo</h4>
      <p data-i18n="s4_visual_sub">Funika wakulima dhidi ya bidhaa feki</p>
    </div>
  </div>

  {{-- Solution 05: Weather --}}
  <div class="solution-row" id="weather">
    <div class="sol-info fade-up">
      <div class="sol-number">05</div>
      <div class="card-icon">⛅</div>
      <h2 data-i18n="s5_title">Hali ya Hewa na Ujasiriamali wa Mazao</h2>
      <p data-i18n="s5_desc">Toa hali ya hewa ya eneo maalum na ushauri wa mazao unaotumia Gemini 3 Pro pamoja na Google Search Grounding kwa data ya hewa ya wakati halisi.</p>
      <ul class="sol-cap-list">
        <li data-i18n="s5_cap1">Tahadhari za hali ya hewa kwa mkoa wako</li>
        <li data-i18n="s5_cap2">Mapendekezo ya kupanda mazao kwa msimu</li>
        <li data-i18n="s5_cap3">Mipango ya hatari ya kilimo</li>
        <li data-i18n="s5_cap4">Taarifa za mwanzo wa mvua</li>
      </ul>
      <div class="sol-tags">
        <span class="tag">Gemini 3 Pro</span>
        <span class="tag">Google Search Grounding</span>
        <span class="tag">Real-time Data</span>
      </div>
    </div>
    <div class="sol-visual fade-up" style="background:linear-gradient(145deg,#EEF4FF,#D8E8FF);">
      <div class="sol-icon">⛅</div>
      <h4 data-i18n="s5_visual_title">Gemini 3 Pro Grounded</h4>
      <p data-i18n="s5_visual_sub">Utabiri wa wakati halisi kwa Google Search</p>
    </div>
  </div>

  {{-- Solution 06: Offline AI --}}
  <div class="solution-row reverse" id="offline">
    <div class="sol-info fade-up">
      <div class="sol-number">06</div>
      <div class="card-icon">📱</div>
      <h2 data-i18n="s6_title">Akili ya Kilimo Bila Intaneti</h2>
      <p data-i18n="s6_desc">Unga mkono maeneo ya uunganisho mdogo kupitia SMS, USSD, maarifa yaliyohifadhiwa, na AI inayofanya kazi moja kwa moja kwenye simu.</p>
      <ul class="sol-cap-list">
        <li data-i18n="s6_cap1">Huduma ya SMS — uliza maswali bila data</li>
        <li data-i18n="s6_cap2">Msimbo wa USSD wa huduma mbalimbali</li>
        <li data-i18n="s6_cap3">Maarifa yaliyohifadhiwa kwenye simu</li>
        <li data-i18n="s6_cap4">Gemma 2B INT4 — AI ndani ya simu bila intaneti</li>
      </ul>
      <div class="sol-tags">
        <span class="tag">Gemma 2B INT4</span>
        <span class="tag">Google AI Edge SDK</span>
        <span class="tag">SMS Gateway</span>
        <span class="tag">USSD</span>
      </div>
    </div>
    <div class="sol-visual fade-up" style="background:linear-gradient(145deg,#0F2D15,#1A4A22);">
      <div class="sol-icon">📱</div>
      <h4 style="color:#fff;" data-i18n="s6_visual_title">Offline-First Architecture</h4>
      <p style="color:rgba(255,255,255,.7);" data-i18n="s6_visual_sub">Inafanya kazi hata bila intaneti kabisa</p>
      <div style="margin-top:16px; background:rgba(255,255,255,.1); border-radius:10px; padding:12px; width:100%; border:1px solid rgba(255,255,255,.15);">
        <div style="font-size:.72rem; color:var(--sun-amber); font-weight:700; margin-bottom:4px;">📲 SMS: 15500</div>
        <div style="font-size:.78rem; color:#fff;">"BEI MAHINDI DODOMA"</div>
      </div>
    </div>
  </div>

  {{-- Solution 07: Community --}}
  <div class="solution-row" id="community">
    <div class="sol-info fade-up">
      <div class="sol-number">07</div>
      <div class="card-icon">👥</div>
      <h2 data-i18n="s7_title">Jamii ya Wakulima</h2>
      <p data-i18n="s7_desc">Mfumo wa kushiriki maarifa unaounganisha wakulima, wataalamu wa kilimo, na vikundi vya kikanda vya kilimo.</p>
      <ul class="sol-cap-list">
        <li data-i18n="s7_cap1">Maswali na majibu kati ya wakulima na wataalamu</li>
        <li data-i18n="s7_cap2">Vikundi vya kilimo vya mazao maalum</li>
        <li data-i18n="s7_cap3">Vikundi vya kikanda vya kilimo</li>
        <li data-i18n="s7_cap4">Uzoefu wa wakulima wenzao</li>
      </ul>
      <div class="sol-tags">
        <span class="tag">Community Platform</span>
        <span class="tag">Expert Moderation</span>
      </div>
    </div>
    <div class="sol-visual fade-up">
      <div class="sol-icon">👥</div>
      <h4 data-i18n="s7_visual_title">Jamii Inayounganisha</h4>
      <p data-i18n="s7_visual_sub">Wakulima wanaosaidiana kwa maarifa</p>
    </div>
  </div>

  {{-- Solution 08: Market Intelligence --}}
  <div class="solution-row reverse" style="border-bottom:none;" id="market-intel">
    <div class="sol-info fade-up">
      <div class="sol-number">08</div>
      <div class="card-icon">📈</div>
      <h2 data-i18n="s8_title">Ujasiriamali wa Soko</h2>
      <p data-i18n="s8_desc">Toa bei za mazao, mahitaji ya wanunuzi, mwenendo wa masoko, na mipango ya mavuno kusaidia wakulima kupata zaidi kwa mazao yao.</p>
      <ul class="sol-cap-list">
        <li data-i18n="s8_cap1">Bei za mazao kwa wakati halisi kwa mkoa</li>
        <li data-i18n="s8_cap2">Mahitaji ya wanunuzi na wenye masoko</li>
        <li data-i18n="s8_cap3">Mwenendo wa bei kwa historia</li>
        <li data-i18n="s8_cap4">Mipango ya misimu ya mavuno</li>
      </ul>
      <div class="sol-tags">
        <span class="tag">Real-time Prices</span>
        <span class="tag">Buyer Demand</span>
        <span class="tag">Price Trends</span>
      </div>
    </div>
    <div class="sol-visual fade-up" style="background:linear-gradient(145deg,#FFF8E1,#FFF0B8);">
      <div class="sol-icon">📈</div>
      <h4 data-i18n="s8_visual_title">Taarifa za Soko</h4>
      <p data-i18n="s8_visual_sub">Maamuzi bora ya kuuza mazao</p>
    </div>
  </div>

</div>{{-- /wrap --}}

{{-- Platform CTA --}}
<section style="background:linear-gradient(135deg,#0E4220,var(--forest-dark)); color:#fff; padding:80px 0; margin-top:40px;">
  <div class="wrap" style="text-align:center; max-width:600px;">
    <h2 style="font-size:clamp(1.8rem,4vw,2.6rem); font-weight:900; color:#fff; margin-bottom:16px;" data-i18n="sol_cta_title">Anza Kutumia Mfumo Wetu</h2>
    <p style="color:rgba(255,255,255,.82); margin-bottom:32px;" data-i18n="sol_cta_sub">Pakua app ya MkulimaForum au wasiliana nasi kwa ushirikiano wa kibiashara au teknolojia.</p>
    <div style="display:flex; gap:14px; flex-wrap:wrap; justify-content:center;">
      <a href="/app/mkulima-forum.apk" class="btn btn-gold btn-lg" data-i18n="sol_dl_btn">⬇️ Pakua App</a>
      <a href="/contact" class="btn btn-ghost btn-lg" data-i18n="sol_contact_btn">Wasiliana Nasi →</a>
    </div>
  </div>
</section>

@endsection

@section('page_scripts')
<script>
const mkPageTranslations = {
  sw: {
    sol_eyebrow:'MOJA. NANE. KAMILI.',
    sol_title:'Jukwaa Moja. Suluhisho Nane za Kilimo.',
    sol_sub:'Kuanzia utambuzi wa magonjwa hadi masoko, fedha, maarifa, na ufikio wa nje ya mtandao.',
    sol_partner_btn:'Shirikiana Nasi', sol_tech_btn:'Teknolojia Yetu →',
    s1_title:'AI Plant Scanner', s1_desc:'Wakulima wanapiga picha ya mazao yaliyoathirika na kupata utambuzi wa ugonjwa au wadudu kwa msaada wa AI, pamoja na mapendekezo ya matibabu.',
    s1_cap1:'Utambuzi wa ugonjwa wa mazao kwa sekunde', s1_cap2:'Mapendekezo ya dawa za TFRA zilizothibitishwa',
    s1_cap3:'Ushauri wa kuzuia na hatua za dharura', s1_cap4:'Historia ya skanning na mwelekeo wa magonjwa',
    s1_visual_title:'Gemini 3 Flash Vision', s1_visual_sub:'Inachambua picha ya mmea kwa muda wa sekunde 1–3',
    s2_title:'Mkulima Bot AI', s2_desc:'Msaidizi wako wa kilimo wa AI 24/7 unaounga mkono Kiswahili na Kiingereza.',
    s2_cap1:'Maswali ya mazao na mbinu bora za kilimo', s2_cap2:'Udhibiti wa wadudu na magonjwa',
    s2_cap3:'Usimamizi wa udongo na mbolea', s2_cap4:'Taarifa za masoko na bei za mazao',
    s2_cap5:'Maswali ya hali ya hewa na mipango ya msimu',
    s2_visual_title:'Mazungumzo ya AI', s3_title:'Soko la Pembejeo na Mazao',
    s3_desc:'Unganisha wakulima, wauzaji wa pembejeo, wanunuzi, wakusanyaji, na wasambazaji wa pembejeo katika soko moja salama.',
    s3_cap1:'Wauzaji walioidhinishwa na TFRA/TPHPA', s3_cap2:'Pembejeo: dawa, mbegu, mbolea',
    s3_cap3:'Soko la mazao ya wakulima', s3_cap4:'Mfumo wa Escrow wa Mkulima', s3_cap5:'M-Pesa, Tigo Pesa, na malipo mengine',
    s3_visual_title:'Mkulima Escrow', s3_visual_sub:'Malipo yote yanalindwa hadi bidhaa iwasilishwe',
    s4_title:'Kagua Pembejeo za Kilimo', s4_desc:'Saidia wakulima kuthibitisha bidhaa za kilimo na kupunguza mfiduo wa pembejeo feki.',
    s4_cap1:'Angalia lebo za dawa na mbolea', s4_cap2:'Uthibitisho wa rejesta ya TFRA/TPHPA',
    s4_cap3:'Ukaguzi wa QR code ya bidhaa', s4_cap4:'Ripoti za jamii za bidhaa za shaka', s4_cap5:'Uthibitisho wa wakala wa mauzo',
    s4_visual_title:'Ulinzi wa Pembejeo', s4_visual_sub:'Funika wakulima dhidi ya bidhaa feki',
    s5_title:'Hali ya Hewa na Ujasiriamali wa Mazao', s5_desc:'Toa hali ya hewa ya eneo maalum na ushauri wa mazao kwa wakati halisi.',
    s5_cap1:'Tahadhari za hali ya hewa kwa mkoa wako', s5_cap2:'Mapendekezo ya kupanda mazao kwa msimu',
    s5_cap3:'Mipango ya hatari ya kilimo', s5_cap4:'Taarifa za mwanzo wa mvua',
    s5_visual_title:'Gemini 3 Pro Grounded', s5_visual_sub:'Utabiri wa wakati halisi kwa Google Search',
    s6_title:'Akili ya Kilimo Bila Intaneti', s6_desc:'Unga mkono maeneo ya uunganisho mdogo kupitia SMS, USSD, na AI inayofanya kazi kwenye simu.',
    s6_cap1:'Huduma ya SMS — uliza maswali bila data', s6_cap2:'Msimbo wa USSD wa huduma mbalimbali',
    s6_cap3:'Maarifa yaliyohifadhiwa kwenye simu', s6_cap4:'Gemma 2B INT4 — AI ndani ya simu bila intaneti',
    s6_visual_title:'Offline-First Architecture', s6_visual_sub:'Inafanya kazi hata bila intaneti kabisa',
    s7_title:'Jamii ya Wakulima', s7_desc:'Mfumo wa kushiriki maarifa unaounganisha wakulima, wataalamu, na vikundi vya kikanda.',
    s7_cap1:'Maswali na majibu kati ya wakulima na wataalamu', s7_cap2:'Vikundi vya kilimo vya mazao maalum',
    s7_cap3:'Vikundi vya kikanda vya kilimo', s7_cap4:'Uzoefu wa wakulima wenzao',
    s7_visual_title:'Jamii Inayounganisha', s7_visual_sub:'Wakulima wanaosaidiana kwa maarifa',
    s8_title:'Ujasiriamali wa Soko', s8_desc:'Toa bei za mazao, mahitaji ya wanunuzi, mwenendo wa masoko, na mipango ya mavuno.',
    s8_cap1:'Bei za mazao kwa wakati halisi kwa mkoa', s8_cap2:'Mahitaji ya wanunuzi na wenye masoko',
    s8_cap3:'Mwenendo wa bei kwa historia', s8_cap4:'Mipango ya misimu ya mavuno',
    s8_visual_title:'Taarifa za Soko', s8_visual_sub:'Maamuzi bora ya kuuza mazao',
    sol_cta_title:'Anza Kutumia Mfumo Wetu',
    sol_cta_sub:'Pakua app ya MkulimaForum au wasiliana nasi kwa ushirikiano wa kibiashara au teknolojia.',
    sol_dl_btn:'⬇️ Pakua App', sol_contact_btn:'Wasiliana Nasi →',
  },
  en: {
    sol_eyebrow:'ONE PLATFORM. MANY SOLUTIONS.',
    sol_title:'One Platform. Eight Agricultural Solutions.',
    sol_sub:'From crop diagnosis to markets, finance, knowledge, and offline access.',
    sol_partner_btn:'Partner With Us', sol_tech_btn:'Our Technology →',
    s1_title:'AI Plant Scanner', s1_desc:'Farmers photograph affected crops and receive AI-assisted disease or pest identification with actionable treatment recommendations.',
    s1_cap1:'Crop disease identification within seconds', s1_cap2:'TFRA-certified treatment recommendations',
    s1_cap3:'Prevention advice and emergency response steps', s1_cap4:'Scan history and disease trend tracking',
    s1_visual_title:'Gemini 3 Flash Vision', s1_visual_sub:'Analyzes crop photo within 1–3 seconds',
    s2_title:'Mkulima Bot AI', s2_desc:'Your 24/7 AI agronomy assistant supporting Swahili and English conversations.',
    s2_cap1:'Crop management and best practice questions', s2_cap2:'Pest and disease management',
    s2_cap3:'Soil management and fertilizer guidance', s2_cap4:'Market information and crop prices',
    s2_cap5:'Weather questions and seasonal planning',
    s2_visual_title:'AI Conversations', s3_title:'Agri-Input & Produce Marketplace',
    s3_desc:'Connect farmers, agro-dealers, buyers, aggregators, and input suppliers in one secure marketplace.',
    s3_cap1:'TFRA/TPHPA verified vendors', s3_cap2:'Inputs: pesticides, seeds, fertilizers',
    s3_cap3:'Farmer produce marketplace', s3_cap4:'Mkulima Escrow payment protection', s3_cap5:'M-Pesa, Tigo Pesa, and other payment channels',
    s3_visual_title:'Mkulima Escrow', s3_visual_sub:'All payments secured until delivery confirmed',
    s4_title:'Agri-Input Verification', s4_desc:'Help farmers verify agricultural products and reduce exposure to counterfeit inputs causing significant crop losses.',
    s4_cap1:'Inspect pesticide and fertilizer labels', s4_cap2:'TFRA/TPHPA registry cross-reference',
    s4_cap3:'Product QR code scanning', s4_cap4:'Community reports on suspicious products', s4_cap5:'Dealer and distributor verification',
    s4_visual_title:'Input Protection', s4_visual_sub:'Shield farmers from counterfeit products',
    s5_title:'Weather & Crop Intelligence', s5_desc:'Provide location-specific weather and crop advice using Gemini 3 Pro with Google Search Grounding for real-time data.',
    s5_cap1:'Regional weather alerts for your area', s5_cap2:'Seasonal crop planting recommendations',
    s5_cap3:'Agricultural risk planning', s5_cap4:'Rain onset and dry spell alerts',
    s5_visual_title:'Gemini 3 Pro Grounded', s5_visual_sub:'Real-time data via Google Search grounding',
    s6_title:'Offline Farming Intelligence', s6_desc:'Support low-connectivity areas through SMS, USSD, cached knowledge, and on-device AI inference.',
    s6_cap1:'SMS service — ask questions without mobile data', s6_cap2:'USSD shortcodes for multiple services',
    s6_cap3:'Cached agronomy knowledge stored on device', s6_cap4:'Gemma 2B INT4 — on-device AI without internet',
    s6_visual_title:'Offline-First Architecture', s6_visual_sub:'Fully functional even without connectivity',
    s7_title:'Farmer Community', s7_desc:'A knowledge-sharing ecosystem connecting farmers, agronomists, and regional agricultural groups.',
    s7_cap1:'Q&A between farmers and agronomists', s7_cap2:'Crop-specific community groups',
    s7_cap3:'Regional farming communities', s7_cap4:'Peer farmer experiences and advice',
    s7_visual_title:'Connected Community', s7_visual_sub:'Farmers helping farmers with knowledge',
    s8_title:'Market Intelligence', s8_desc:'Provide crop prices, buyer demand, market trends, and harvest planning to help farmers earn more from their produce.',
    s8_cap1:'Real-time crop prices by region', s8_cap2:'Buyer demand and market availability',
    s8_cap3:'Historical price trends', s8_cap4:'Harvest season planning',
    s8_visual_title:'Market Intelligence', s8_visual_sub:'Better decisions for selling your harvest',
    sol_cta_title:'Start Using Our Platform',
    sol_cta_sub:'Download the MkulimaForum app or contact us for commercial or technology partnerships.',
    sol_dl_btn:'⬇️ Download App', sol_contact_btn:'Contact Us →',
  }
};
</script>
@endsection
