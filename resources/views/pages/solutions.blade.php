@extends('layouts.public')

@section('title', 'MkulimaForum Solutions | AI, Markets & Digital Farming Tools')
@section('meta_description', 'Explore MkulimaForum\'s complete agricultural solution ecosystem: AI Plant Scanner, Mkulima AI, Agri Marketplace, Input Verification, Weather Intelligence, Offline AI, and more.')
@section('og_title', 'MkulimaForum Solutions | Complete Agri Digital Ecosystem')
@section('og_description', 'One platform. Eight agricultural solutions designed for East African smallholder farmers.')

@section('head_extra')
<style>
  /* Tokens, buttons, header and footer come from layouts/public.blade.php.
     This block only lays out the solutions page's own sections. */
  .ico { width:1.15em; height:1.15em; flex:none; stroke-width:2; }
  .sol-hero-grid { display:grid; grid-template-columns:1fr 1fr; gap:48px; align-items:center; }
  .sol-hero-grid > * { min-width:0; }
  .hero-actions { display:flex; gap:12px; flex-wrap:wrap; margin-top:28px; }
  .hero-chips { display:flex; flex-wrap:wrap; gap:10px; padding:24px; background:var(--surface-soft); border:1px solid var(--border-light); border-radius:var(--radius-xl); }
  .hero-chips .tag { background:#fff; padding:8px 14px; font-size:14px; }

  /* One row per solution: copy on one side, a soft illustration panel on the other */
  .solution-row {
    display:grid; grid-template-columns:0.5fr 0.5fr; gap:56px; align-items:center;
    padding:64px 0; border-bottom:1px solid var(--border-light);
  }
  .solution-row > * { min-width:0; }
  .solution-row.reverse .sol-info { order:2; }
  .solution-row.reverse .sol-visual { order:1; }
  .sol-head { display:flex; align-items:center; gap:12px; margin-bottom:16px; }
  .sol-head .card-icon { margin-bottom:0; }
  .sol-number { display:inline-flex; align-items:center; min-height:28px; padding:0 10px; border-radius:999px; background:var(--leaf-pale); color:var(--forest-dark); font-size:13px; font-weight:700; letter-spacing:.06em; }
  .sol-info h2 { font-size:clamp(24px,3vw,32px); font-weight:800; color:var(--ink-dark); margin-bottom:12px; letter-spacing:-.02em; }
  .sol-info > p { color:var(--ink-muted); font-size:16px; line-height:1.7; margin-bottom:20px; }
  .sol-tags { display:flex; flex-wrap:wrap; gap:8px; }
  .sol-cap-list { list-style:none; display:flex; flex-direction:column; gap:10px; margin-bottom:24px; }
  .sol-cap-list li { display:flex; align-items:flex-start; gap:10px; font-size:15px; color:var(--ink-body); font-weight:500; line-height:1.5; }
  .sol-cap-list li::before {
    content:''; flex-shrink:0; width:18px; height:18px; margin-top:2px; border-radius:50%;
    background:var(--leaf-pale) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%231B7A3E' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 12 4 4 8-8'/%3E%3C/svg%3E") center/12px no-repeat;
  }

  .sol-visual {
    background:var(--surface-soft); border:1px solid var(--border-light); border-radius:var(--radius-xl);
    padding:40px; min-height:300px; display:flex; flex-direction:column; align-items:center; justify-content:center;
    text-align:center;
  }
  .sol-icon { width:72px; height:72px; border-radius:20px; background:#fff; border:1px solid var(--border-light); color:var(--forest-mid); display:flex; align-items:center; justify-content:center; margin-bottom:16px; }
  .sol-icon .ico { width:32px; height:32px; }
  .sol-visual h4 { font-size:18px; font-weight:700; color:var(--ink-dark); }
  .sol-visual p { font-size:15px; color:var(--ink-muted); margin-top:6px; max-width:260px; line-height:1.5; }
  .sol-mock { margin-top:20px; background:#fff; border:1px solid var(--border-light); border-radius:12px; padding:14px 16px; width:100%; max-width:340px; text-align:left; }
  .sol-mock-label { font-size:13px; font-weight:700; letter-spacing:.04em; color:var(--forest-mid); margin-bottom:6px; }
  .sol-mock-title { font-size:15px; color:var(--ink-dark); font-weight:700; }
  .sol-mock-meta { font-size:13px; color:var(--ink-muted); margin-top:3px; }
  .chat-mock { width:100%; max-width:340px; margin-top:16px; display:flex; flex-direction:column; gap:8px; }
  .chat-bubble { border-radius:14px 14px 14px 4px; padding:10px 14px; font-size:14px; line-height:1.5; text-align:left; max-width:88%; }
  .chat-bubble.user { background:#fff; border:1px solid var(--border-light); color:var(--ink-dark); }
  .chat-bubble.bot { background:var(--forest-mid); color:#fff; border-radius:14px 14px 4px 14px; align-self:flex-end; }

  /* Closing call to action */
  .final-cta { padding:80px 0; text-align:center; background:#fff; border-top:1px solid var(--border-light); }
  .final-cta .wrap { max-width:640px; }
  .final-cta h2 { font-size:clamp(26px,3.6vw,38px); font-weight:800; margin-bottom:12px; letter-spacing:-.02em; }
  .final-cta p { margin:0 auto 24px; color:var(--ink-muted); font-size:17px; }
  .cta-actions { display:flex; gap:12px; flex-wrap:wrap; justify-content:center; }

  @media(max-width:860px){
    .solution-row { grid-template-columns:1fr; gap:24px; }
    .solution-row.reverse .sol-info, .solution-row.reverse .sol-visual { order:unset; }
    .sol-hero-grid { grid-template-columns:1fr; gap:28px; }
  }
  @media(max-width:700px){
    .hero-actions { margin-top:20px; }
    .hero-chips { padding:16px; gap:8px; }
    .sol-info > p { font-size:15px; margin-bottom:16px; }
    .sol-cap-list { margin-bottom:18px; }
    .sol-visual { padding:24px 18px; min-height:0; }
    .sol-icon { width:56px; height:56px; border-radius:16px; margin-bottom:12px; }
    .sol-icon .ico { width:26px; height:26px; }
    .final-cta { padding:48px 0; }
    .final-cta p { font-size:16px; }
  }
</style>
@endsection

@section('content')

{{-- Hero --}}
<section class="page-hero">
  <div class="wrap">
    <div class="sol-hero-grid fade-up">
      <div>
        <span class="eyebrow" data-i18n="sol_eyebrow">MOJA. TISA. KARIBU.</span>
        <h1 class="page-title" data-i18n="sol_title">Jukwaa Moja. Suluhisho Nyingi za Kilimo.</h1>
        <p class="section-lead" data-i18n="sol_sub">Kuanzia utambuzi wa magonjwa hadi masoko, fedha, maarifa, na ufikio wa nje ya mtandao.</p>
        <div class="hero-actions">
          <a href="/contact" class="btn btn-primary btn-lg" data-i18n="sol_partner_btn">Shirikiana Nasi</a>
          <a href="/technology" class="btn btn-outline btn-lg" data-i18n="sol_tech_btn">Teknolojia Yetu →</a>
        </div>
      </div>
      <div class="hero-chips">
        @foreach(['📷 Plant Scanner','🤖 Mkulima AI','🛒 Soko','🛡️ Kagua','⛅ Hali ya Hewa','📱 Offline AI','👥 Jamii','📈 Masoko'] as $chip)
        <span class="tag">{{ $chip }}</span>
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
      <div class="sol-head">
        <div class="card-icon"><x-icon name="scan" size="24" /></div>
        <span class="sol-number">01</span>
      </div>
      <h2 data-i18n="s1_title">AI Plant Scanner</h2>
      <p data-i18n="s1_desc">Wakulima wanapiga picha ya mazao yaliyoathirika na kupata utambuzi wa ugonjwa au wadudu kwa msaada wa AI, pamoja na mapendekezo ya matibabu yanayoweza kutumika.</p>
      <ul class="sol-cap-list">
        <li data-i18n="s1_cap1">Utambuzi wa ugonjwa wa mazao kwa sekunde</li>
        <li data-i18n="s1_cap2">Mapendekezo ya dawa za TFRA zilizothibitishwa</li>
        <li data-i18n="s1_cap3">Ushauri wa kuzuia na hatua za dharura</li>
        <li data-i18n="s1_cap4">Historia ya skanning na mwelekeo wa magonjwa</li>
      </ul>
      <div class="sol-tags">
        <span class="tag">Mkulima AI</span>
        <span class="tag">Computer Vision</span>
        <span class="tag">Agronomy KB</span>
      </div>
    </div>
    <div class="sol-visual fade-up">
      <div class="sol-icon"><x-icon name="scan" /></div>
      <h4 data-i18n="s1_visual_title">Mkulima AI Vision</h4>
      <p data-i18n="s1_visual_sub">Inachambua picha ya mmea kwa muda wa sekunde 1–3</p>
      <div class="sol-mock">
        <div class="sol-mock-label">⚡ MATOKEO YA AI</div>
        <div class="sol-mock-title">Kutu ya Majani — Leaf Rust</div>
        <div class="sol-mock-meta">Uhakika: 94.7% • TFRA Tiba: Fungicide Z4</div>
      </div>
    </div>
  </div>

  {{-- Solution 02: Mkulima AI --}}
  <div class="solution-row reverse" id="mkulima-bot">
    <div class="sol-info fade-up">
      <div class="sol-head">
        <div class="card-icon"><x-icon name="book" size="24" /></div>
        <span class="sol-number">02</span>
      </div>
      <h2 data-i18n="s2_title">Mkulima AI</h2>
      <p data-i18n="s2_desc">Msaidizi wako wa kilimo wa AI 24/7 unaounga mkono Kiswahili na Kiingereza. Uliza maswali yoyote ya kilimo kupitia mazungumzo ya maandishi au sauti.</p>
      <ul class="sol-cap-list">
        <li data-i18n="s2_cap1">Maswali ya mazao na mbinu bora za kilimo</li>
        <li data-i18n="s2_cap2">Udhibiti wa wadudu na magonjwa</li>
        <li data-i18n="s2_cap3">Usimamizi wa udongo na mbolea</li>
        <li data-i18n="s2_cap4">Taarifa za masoko na bei za mazao</li>
        <li data-i18n="s2_cap5">Maswali ya hali ya hewa na mipango ya msimu</li>
      </ul>
      <div class="sol-tags">
        <span class="tag">Mkulima AI</span>
        <span class="tag">Swahili + English</span>
        <span class="tag">Virtual Agronomist</span>
      </div>
    </div>
    <div class="sol-visual fade-up">
      <div class="sol-icon"><x-icon name="book" /></div>
      <h4 data-i18n="s2_visual_title">Mazungumzo ya AI</h4>
      <div class="chat-mock">
        <div class="chat-bubble user">Jinsi ya kuzuia wadudu wa mahindi?</div>
        <div class="chat-bubble bot">Tumia Thiamethoxam au Chlorpyrifos kwenye msimu wa mapema. Angalia kila wiki 1 wiki 2...</div>
      </div>
    </div>
  </div>

  {{-- Solution 03: Marketplace --}}
  <div class="solution-row" id="marketplace">
    <div class="sol-info fade-up">
      <div class="sol-head">
        <div class="card-icon"><x-icon name="storefront" size="24" /></div>
        <span class="sol-number">03</span>
      </div>
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
      <div class="sol-icon"><x-icon name="storefront" /></div>
      <h4 data-i18n="s3_visual_title">Mkulima Escrow</h4>
      <p data-i18n="s3_visual_sub">Malipo yote yanalindwa hadi bidhaa iwasilishwe</p>
    </div>
  </div>

  {{-- Solution 04: Input Verification --}}
  <div class="solution-row reverse" id="input-verify">
    <div class="sol-info fade-up">
      <div class="sol-head">
        <div class="card-icon"><x-icon name="verified" size="24" /></div>
        <span class="sol-number">04</span>
      </div>
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
    <div class="sol-visual fade-up">
      <div class="sol-icon"><x-icon name="verified" /></div>
      <h4 data-i18n="s4_visual_title">Ulinzi wa Pembejeo</h4>
      <p data-i18n="s4_visual_sub">Funika wakulima dhidi ya bidhaa feki</p>
    </div>
  </div>

  {{-- Solution 05: Weather --}}
  <div class="solution-row" id="weather">
    <div class="sol-info fade-up">
      <div class="sol-head">
        <div class="card-icon"><x-icon name="sun" size="24" /></div>
        <span class="sol-number">05</span>
      </div>
      <h2 data-i18n="s5_title">Hali ya Hewa na Ujasiriamali wa Mazao</h2>
      <p data-i18n="s5_desc">Toa hali ya hewa ya eneo maalum na ushauri wa mazao unaotumia Mkulima AI Weather pamoja na Google Search Grounding kwa data ya hewa ya wakati halisi.</p>
      <ul class="sol-cap-list">
        <li data-i18n="s5_cap1">Tahadhari za hali ya hewa kwa mkoa wako</li>
        <li data-i18n="s5_cap2">Mapendekezo ya kupanda mazao kwa msimu</li>
        <li data-i18n="s5_cap3">Mipango ya hatari ya kilimo</li>
        <li data-i18n="s5_cap4">Taarifa za mwanzo wa mvua</li>
      </ul>
      <div class="sol-tags">
        <span class="tag">Mkulima AI Weather</span>
        <span class="tag">Google Search Grounding</span>
        <span class="tag">Real-time Data</span>
      </div>
    </div>
    <div class="sol-visual fade-up">
      <div class="sol-icon"><x-icon name="sun" /></div>
      <h4 data-i18n="s5_visual_title">Mkulima AI Weather Grounded</h4>
      <p data-i18n="s5_visual_sub">Utabiri wa wakati halisi kwa Google Search</p>
    </div>
  </div>

  {{-- Solution 06: Offline AI --}}
  <div class="solution-row reverse" id="offline">
    <div class="sol-info fade-up">
      <div class="sol-head">
        <div class="card-icon"><x-icon name="phone" size="24" /></div>
        <span class="sol-number">06</span>
      </div>
      <h2 data-i18n="s6_title">Akili ya Kilimo Bila Intaneti</h2>
      <p data-i18n="s6_desc">Unga mkono maeneo ya uunganisho mdogo kupitia SMS, USSD, maarifa yaliyohifadhiwa, na AI inayofanya kazi moja kwa moja kwenye simu.</p>
      <ul class="sol-cap-list">
        <li data-i18n="s6_cap1">Huduma ya SMS — uliza maswali bila data</li>
        <li data-i18n="s6_cap2">Msimbo wa USSD wa huduma mbalimbali</li>
        <li data-i18n="s6_cap3">Maarifa yaliyohifadhiwa kwenye simu</li>
        <li data-i18n="s6_cap4">Mkulima AI Offline — AI ndani ya simu bila intaneti</li>
      </ul>
      <div class="sol-tags">
        <span class="tag">Mkulima AI Offline</span>
        <span class="tag">Google AI Edge SDK</span>
        <span class="tag">SMS Gateway</span>
        <span class="tag">USSD</span>
      </div>
    </div>
    <div class="sol-visual fade-up">
      <div class="sol-icon"><x-icon name="phone" /></div>
      <h4 data-i18n="s6_visual_title">Offline-First Architecture</h4>
      <p data-i18n="s6_visual_sub">Inafanya kazi hata bila intaneti kabisa</p>
      <div class="sol-mock">
        <div class="sol-mock-label">📲 SMS: 15500</div>
        <div class="sol-mock-title">"BEI MAHINDI DODOMA"</div>
      </div>
    </div>
  </div>

  {{-- Solution 07: Community --}}
  <div class="solution-row" id="community">
    <div class="sol-info fade-up">
      <div class="sol-head">
        <div class="card-icon"><x-icon name="groups" size="24" /></div>
        <span class="sol-number">07</span>
      </div>
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
      <div class="sol-icon"><x-icon name="groups" /></div>
      <h4 data-i18n="s7_visual_title">Jamii Inayounganisha</h4>
      <p data-i18n="s7_visual_sub">Wakulima wanaosaidiana kwa maarifa</p>
    </div>
  </div>

  {{-- Solution 08: Market Intelligence --}}
  <div class="solution-row reverse" style="border-bottom:none;" id="market-intel">
    <div class="sol-info fade-up">
      <div class="sol-head">
        <div class="card-icon"><x-icon name="chart" size="24" /></div>
        <span class="sol-number">08</span>
      </div>
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
    <div class="sol-visual fade-up">
      <div class="sol-icon"><x-icon name="chart" /></div>
      <h4 data-i18n="s8_visual_title">Taarifa za Soko</h4>
      <p data-i18n="s8_visual_sub">Maamuzi bora ya kuuza mazao</p>
    </div>
  </div>

</div>{{-- /wrap --}}

{{-- Platform CTA --}}
<section class="final-cta">
  <div class="wrap">
    <h2 data-i18n="sol_cta_title">Anza Kutumia Mfumo Wetu</h2>
    <p data-i18n="sol_cta_sub">Pakua app ya MkulimaForum au wasiliana nasi kwa ushirikiano wa kibiashara au teknolojia.</p>
    <div class="cta-actions">
      <a href="/download" class="btn btn-primary btn-lg" data-i18n="sol_dl_btn">⬇️ Pakua App</a>
      <a href="/contact" class="btn btn-outline btn-lg" data-i18n="sol_contact_btn">Wasiliana Nasi →</a>
    </div>
  </div>
</section>

@endsection

@section('page_scripts')
<script nonce="{{ $cspNonce ?? '' }}">
mkPageTranslations = {
  sw: {
    sol_eyebrow:'MOJA. NANE. KAMILI.',
    sol_title:'Jukwaa Moja. Suluhisho Nane za Kilimo.',
    sol_sub:'Kuanzia utambuzi wa magonjwa hadi masoko, fedha, maarifa, na ufikio wa nje ya mtandao.',
    sol_partner_btn:'Shirikiana Nasi', sol_tech_btn:'Teknolojia Yetu →',
    s1_title:'AI Plant Scanner', s1_desc:'Wakulima wanapiga picha ya mazao yaliyoathirika na kupata utambuzi wa ugonjwa au wadudu kwa msaada wa AI, pamoja na mapendekezo ya matibabu.',
    s1_cap1:'Utambuzi wa ugonjwa wa mazao kwa sekunde', s1_cap2:'Mapendekezo ya dawa za TFRA zilizothibitishwa',
    s1_cap3:'Ushauri wa kuzuia na hatua za dharura', s1_cap4:'Historia ya skanning na mwelekeo wa magonjwa',
    s1_visual_title:'Mkulima AI Vision', s1_visual_sub:'Inachambua picha ya mmea kwa muda wa sekunde 1–3',
    s2_title:'Mkulima AI', s2_desc:'Msaidizi wako wa kilimo wa AI 24/7 unaounga mkono Kiswahili na Kiingereza.',
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
    s5_visual_title:'Mkulima AI Weather Grounded', s5_visual_sub:'Utabiri wa wakati halisi kwa Google Search',
    s6_title:'Akili ya Kilimo Bila Intaneti', s6_desc:'Unga mkono maeneo ya uunganisho mdogo kupitia SMS, USSD, na AI inayofanya kazi kwenye simu.',
    s6_cap1:'Huduma ya SMS — uliza maswali bila data', s6_cap2:'Msimbo wa USSD wa huduma mbalimbali',
    s6_cap3:'Maarifa yaliyohifadhiwa kwenye simu', s6_cap4:'Mkulima AI Offline — AI ndani ya simu bila intaneti',
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
    s1_visual_title:'Mkulima AI Vision', s1_visual_sub:'Analyzes crop photo within 1–3 seconds',
    s2_title:'Mkulima AI', s2_desc:'Your 24/7 AI agronomy assistant supporting Swahili and English conversations.',
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
    s5_title:'Weather & Crop Intelligence', s5_desc:'Provide location-specific weather and crop advice using Mkulima AI Weather with Google Search Grounding for real-time data.',
    s5_cap1:'Regional weather alerts for your area', s5_cap2:'Seasonal crop planting recommendations',
    s5_cap3:'Agricultural risk planning', s5_cap4:'Rain onset and dry spell alerts',
    s5_visual_title:'Mkulima AI Weather Grounded', s5_visual_sub:'Real-time data via Google Search grounding',
    s6_title:'Offline Farming Intelligence', s6_desc:'Support low-connectivity areas through SMS, USSD, cached knowledge, and on-device AI inference.',
    s6_cap1:'SMS service — ask questions without mobile data', s6_cap2:'USSD shortcodes for multiple services',
    s6_cap3:'Cached agronomy knowledge stored on device', s6_cap4:'Mkulima AI Offline — on-device AI without internet',
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
