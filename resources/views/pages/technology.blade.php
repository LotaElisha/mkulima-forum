@extends('layouts.public')

@section('title', 'MkulimaForum Technology | AI Built for Real Farming Conditions')
@section('meta_description', 'Explore the technology powering MkulimaForum: Gemini 3 Flash, Gemini 3 Pro, Gemma 2B Edge AI, Computer Vision, SMS/USSD offline architecture, and agricultural intelligence.')
@section('og_title', 'MkulimaForum Technology | AI for East African Agriculture')
@section('og_description', 'Google Gemini 3 AI, on-device Gemma 2B, Computer Vision, and offline-first architecture built for East African farmers.')

@section('head_extra')
<style>
  /* Tokens, buttons, header and footer come from layouts/public.blade.php. */
  .tech-stack-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:20px; }
  @media(max-width:760px){ .tech-stack-grid{ grid-template-columns:minmax(0,1fr); gap:12px; } }
  .tech-card {
    background:#fff; border:1px solid var(--border-light); border-radius:var(--radius-xl);
    padding:28px; transition:border-color .15s ease, box-shadow .15s ease;
  }
  .tech-card:hover { border-color:var(--border-mid); box-shadow:var(--shadow-md); }
  .tech-card-head { display:flex; align-items:center; gap:14px; margin-bottom:14px; }
  .tech-card-head .card-icon { margin:0; }
  .tech-card h3 { font-size:19px; font-weight:700; color:var(--ink-dark); margin:0; }
  .tech-card p  { color:var(--ink-muted); line-height:1.65; font-size:15px; }
  .tech-card .tech-tag-list { display:flex; flex-wrap:wrap; gap:8px; margin-top:16px; }

  /* Gemini: a soft grey panel, not a coloured band */
  .google-band { background:#fff; padding:0 0 80px; }
  .google-ai-panel { background:var(--surface-soft); border:1px solid var(--border-light); border-radius:var(--radius-2xl); padding:48px; }
  .google-head { text-align:center; margin-bottom:32px; }
  .google-gemini-badge {
    display:inline-flex; align-items:center; gap:2px; min-height:40px; padding:6px 18px; margin:0 auto 20px;
    background:#fff; border:1px solid var(--border-light); border-radius:999px; font-weight:700; font-size:15px;
  }
  .g-letter { font-size:20px; font-weight:800; }
  .g-e { color:#4285F4; } .g-o1 { color:#EA4335; } .g-o2 { color:#B37F00; } .g-g { color:#34A853; } .g-l { color:#4285F4; } .g-i { color:#EA4335; }
  .g-text { color:var(--ink-dark); margin-left:6px; }
  .google-uses-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:16px; }
  .google-use { background:#fff; border:1px solid var(--border-light); border-radius:var(--radius-xl); padding:24px; }
  .google-use h4 { font-size:17px; font-weight:700; color:var(--ink-dark); margin-bottom:6px; }
  .google-use p { font-size:15px; color:var(--ink-muted); line-height:1.6; }
  .google-note { text-align:center; font-size:13px; color:var(--ink-muted); margin:24px auto 0; max-width:48rem; line-height:1.55; }

  /* Architecture flow */
  .arch-flow-container { background:#fff; border:1px solid var(--border-light); border-radius:var(--radius-2xl); padding:40px 24px; text-align:center; }
  .flow-row { display:flex; align-items:center; justify-content:center; gap:10px; flex-wrap:wrap; margin:10px 0; }
  .flow-node {
    display:inline-flex; align-items:center; gap:6px; min-height:44px; padding:10px 18px; border-radius:12px;
    font-size:15px; font-weight:600; border:1px solid var(--border-mid); color:var(--ink-dark); background:#fff;
  }
  .flow-node.farmer { background:var(--leaf-pale); color:var(--forest-dark); border-color:var(--forest-mid); font-weight:700; font-size:16px; padding:12px 24px; }
  .flow-node.platform { background:var(--leaf-pale); color:var(--forest-dark); border-color:var(--forest-mid); font-weight:700; padding:12px 28px; }
  .flow-node.ai { background:var(--surface-soft); border-color:var(--border-light); }
  .flow-node.output { background:var(--leaf-pale); color:var(--forest-dark); border:1.5px solid var(--forest-mid); font-weight:700; font-size:16px; padding:14px 28px; }
  .flow-arrow { display:flex; justify-content:center; color:var(--forest-mid); margin-top:6px; }
  .flow-label { font-size:13px; font-weight:600; color:var(--ink-muted); margin:2px 0; }

  .soft-band { background:var(--surface-soft); border-top:1px solid var(--border-light); border-bottom:1px solid var(--border-light); padding:64px 0; }

  .final-cta { padding:80px 0; text-align:center; background:#fff; }
  .final-cta h2 { font-size:clamp(26px,3.6vw,36px); font-weight:800; margin-bottom:12px; letter-spacing:-.02em; }
  .final-cta p { max-width:540px; margin:0 auto 24px; color:var(--ink-muted); font-size:17px; }
  .btn-row { display:flex; gap:12px; flex-wrap:wrap; justify-content:center; }

  @media(max-width:860px){ .google-uses-grid{ grid-template-columns:minmax(0,1fr); gap:12px; } }
  @media(max-width:700px){
    .tech-card { padding:18px; border-radius:14px; }
    .tech-card:hover { box-shadow:none; }
    .tech-card h3 { font-size:17px; }
    .google-band { padding-bottom:44px; }
    .google-ai-panel { padding:24px 16px; border-radius:16px; }
    .google-use { padding:18px; }
    .arch-flow-container { padding:24px 12px; border-radius:16px; }
    .flow-node { font-size:14px; padding:8px 12px; }
    .flow-node.output { font-size:15px; padding:12px 16px; }
    .soft-band { padding:44px 0; }
    .final-cta { padding:48px 0; }
    .final-cta p { font-size:16px; }
  }
</style>
@endsection

@section('content')

{{-- Hero --}}
<section class="page-hero">
  <div class="wrap fade-up">
    <span class="eyebrow" data-i18n="tech_eyebrow">TEKNOLOJIA</span>
    <h1 class="page-title" data-i18n="tech_title">AI Iliyojengwa kwa Hali Halisi ya Kilimo</h1>
    <p class="section-lead" data-i18n="tech_sub">Tunatumia teknolojia za kisasa za ulimwengu na kuzirekebisha kwa hali ya wakulima wa Tanzania na Afrika Mashariki — bila kuacha wale wanaoishi mbali na mtandao.</p>
  </div>
</section>

{{-- Tech Stack --}}
<section>
  <div class="wrap">
    <span class="eyebrow" data-i18n="stack_eyebrow">NGUZO ZA TEKNOLOJIA</span>
    <h2 class="section-title" style="margin-bottom:32px;" data-i18n="stack_title">Teknolojia Inayofanya Kazi Nyuma ya Pazia</h2>
    <div class="tech-stack-grid">
      @foreach([
        ['🤖','Gemini Cloud AI','Gemini Cloud AI','Gemini 3 Flash na Gemini 3 Pro ndio nguvu kuu za akili ya AI ya MkulimaForum. Gemini 3 Flash inashughulikia utambuzi wa haraka wa magonjwa ya mimea, maswali ya Mkulima AI, na utafutaji wa soko. Gemini 3 Pro inashughulikia ushauri wa hali ya hewa na mazao kwa kutumia Google Search Grounding.','Gemini 3 Flash and Gemini 3 Pro power MkulimaForum\'s core AI capabilities. Flash handles plant diagnosis, Mkulima AI queries, and marketplace search. Pro handles weather and crop advisory with Google Search Grounding.','tc0',['Gemini 3 Flash','Gemini 3 Pro','Google AI Studio']],
        ['🧠','Gemma Edge AI','Gemma Edge AI','Gemma 2B (INT4 Quantized) inafanya kazi moja kwa moja kwenye simu za wakulima bila kuhitaji intaneti. Imetumia MediaPipe LLM Inference (Google AI Edge SDK) kufanya uamuzi wa AI ndani ya simu kwenye GPU/NPU ya simu.','Gemma 2B (INT4 Quantized) runs locally on farmer smartphones without requiring internet. Implemented via MediaPipe LLM Inference (Google AI Edge SDK) for on-device inference on mobile GPU/NPU.','tc1',['Gemma 2B INT4','MediaPipe','Google AI Edge SDK']],
        ['👁️','Computer Vision','Computer Vision','Mfumo wa utambuzi wa picha wa AI unaotumia uwezo wa mfano mkubwa wa lugha wa Gemini na uwezo wa kuona wa multimodal kuchambua picha za mazao yaliyoathirika na kutoa matokeo ya utambuzi wa ugonjwa.','AI image recognition system using Gemini\'s multimodal large language model capabilities to analyze crop images and provide accurate disease or pest identification results.','tc2',['Multimodal Vision','Image Analysis','Disease Classification']],
        ['📴','Mfumo wa Offline','Offline Architecture','Mfumo wa usanifu unaounga mkono maeneo ya uunganisho mdogo kupitia SMS (kwa geti la SMS), USSD, maarifa yaliyohifadhiwa kwenye simu, na Gemma 2B inayofanya kazi nje ya mtandao. Wakulima wanapata huduma muhimu hata bila data.','An architecture supporting low-connectivity areas via SMS gateway, USSD shortcodes, device-cached agronomy knowledge, and offline Gemma 2B. Farmers access critical services even without mobile data.','tc3',['SMS Gateway','USSD','Cached Knowledge','Offline-First']],
        ['🗄️','Msingi wa Data','Data Infrastructure','PostgreSQL iliyo na pgvector inashughulikia utafutaji wa mfano wa kulingana (semantic search) kwa maktaba ya maarifa ya kilimo. Redis inashughulikia akiba, vipindi vya mtumiaji, na foleni za kazi za usuli.','PostgreSQL with pgvector extension handles semantic search across the agronomy knowledge base. Redis manages caching, user sessions, and background job queues for scalable performance.','tc4',['PostgreSQL + pgvector','Redis Cache','Semantic Search']],
        ['💳','Malipo na Fedha','Payments & Finance','Mfumo wa mauzo wenye uwezo wa M-Pesa na Tigo Pesa, Escrow ya Mkulima inayolinda wanunuzi na wauzaji, na muundo wa usanifu unaoruhusu kuongeza njia za malipo za ziada.','Payment infrastructure supporting M-Pesa and Tigo Pesa, Mkulima Escrow protecting buyers and sellers in marketplace transactions, and an architecture designed to add additional payment channels.','tc5',['M-Pesa','Tigo Pesa','Mkulima Escrow']],
        ['🔐','Usalama na Faragha','Security & Privacy','Laravel Sanctum inashughulikia uthibitisho wa mtumiaji na kuzuia API. Maelezo ya kibinafsi ya mtumiaji yanalindwa na sera kali ya faragha. Hifadhidata imeundwa kwa muundo wa data za hatari ndogo.','Laravel Sanctum handles user authentication and API token management. User personal data is protected by strict privacy policies. Database is designed with minimal personally-identifiable data architecture.','tc6',['Sanctum Auth','API Security','Data Privacy']],
        ['📱','Mfumo wa App ya Simu','Mobile App Platform','App ya MkulimaForum imejengwa kwa Flutter, inayoruhusu kutolewa kwa Android na iOS kutoka msingi mmoja wa msimbo. Imeboreshwa kwa simu za android za bei ya chini zinazotumika zaidi Tanzania.','MkulimaForum app is built with Flutter, enabling Android and iOS deployment from a single codebase. Optimized for lower-end Android devices common among Tanzanian smallholder farmers.','tc7',['Flutter','Android','iOS','Material 3']],
      ] as $t)
      <div class="tech-card fade-up">
        <div class="tech-card-head">
          <div class="card-icon">@if($t[0] === '🤖')<x-icon name="globe" :size="22" />@elseif($t[0] === '🧠')<svg class="ico" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="5" y="5" width="14" height="14" rx="2"/><rect x="9" y="9" width="6" height="6"/><path d="M9 2v3M15 2v3M9 19v3M15 19v3M2 9h3M2 15h3M19 9h3M19 15h3"/></svg>@elseif($t[0] === '👁️')<x-icon name="scan" :size="22" />@elseif($t[0] === '📴')<svg class="ico" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 20h.01"/><path d="M8.5 16.4a5 5 0 0 1 7 0"/><path d="M5 12.9a10 10 0 0 1 5.2-2.7"/><path d="M19 12.9a10 10 0 0 0-2.1-1.5"/><path d="M2 8.8a15 15 0 0 1 4.2-2.6"/><path d="M22 8.8A15 15 0 0 0 11 4.6"/><path d="m2 2 20 20"/></svg>@elseif($t[0] === '🗄️')<svg class="ico" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14a9 3 0 0 0 18 0V5"/><path d="M3 12a9 3 0 0 0 18 0"/></svg>@elseif($t[0] === '💳')<svg class="ico" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>@elseif($t[0] === '🔐')<x-icon name="shield" :size="22" />@elseif($t[0] === '📱')<x-icon name="phone" :size="22" />@endif</div>
          <h3 data-i18n="{{ $t[5] }}_title">{{ $t[1] }}</h3>
        </div>
        <p data-i18n="{{ $t[5] }}_desc">{{ $t[3] }}</p>
        <div class="tech-tag-list">
          @foreach($t[6] as $tag)
          <span class="tag">{{ $tag }}</span>
          @endforeach
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- Google AI Section --}}
<section class="google-band">
  <div class="wrap">
    <div class="google-ai-panel fade-up">
      <div class="google-head">
        <div class="google-gemini-badge">
          <span class="g-letter g-g">G</span><span class="g-letter g-e">e</span><span class="g-letter g-o1">m</span><span class="g-letter g-o2">i</span><span class="g-letter g-g">n</span><span class="g-letter g-l">i</span>
          <span class="g-text">3 Flash &amp; Pro</span>
        </div>
        <span class="eyebrow" style="display:block;" data-i18n="google_eyebrow">TEKNOLOJIA YA GOOGLE AI</span>
        <h2 class="section-title" data-i18n="google_title">Jinsi Tunavyotumia Gemini AI</h2>
        <p class="section-lead" style="text-align:center; margin:0 auto; max-width:44rem;" data-i18n="google_sub">
          MkulimaForum inatumia mifano ya Gemini ya Google katika mtiririko mbalimbali wa AI. Toleo la awali la mfumo wetu lilibuniwa kwa kutumia Google AI Studio.
        </p>
      </div>

      <div class="google-uses-grid">
        @foreach([
          ['⚡','Gemini 3 Flash','AI Plant Scanner, Mkulima AI mazungumzo, Utafutaji wa Soko','Gemini 3 Flash','AI Plant Scanner, Mkulima AI conversations, Smart Marketplace Search','gc0'],
          ['🔍','Gemini 3 Pro + Grounding','Ushauri wa Hali ya Hewa na Mazao ukitumia Google Search Grounding','Gemini 3 Pro + Grounding','Weather & Crop Advisory Engine using real-time Google Search Grounding','gc1'],
          ['🛠️','Google AI Studio','Ubunifu wa kwanza na majaribio ya mfano wa AI ya MkulimaForum','Google AI Studio','Used for initial AI prototype development and model experimentation','gc2'],
        ] as $g)
        <div class="google-use">
          <div class="card-icon">@if($g[0] === '⚡')<x-icon name="sun" :size="22" />@elseif($g[0] === '🔍')<x-icon name="search" :size="22" />@elseif($g[0] === '🛠️')<svg class="ico" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.8-3.8a6 6 0 0 1-7.9 7.9l-6.9 6.9a2.1 2.1 0 0 1-3-3l6.9-6.9a6 6 0 0 1 7.9-7.9z"/></svg>@endif</div>
          <h4 data-i18n="{{ $g[5] }}_title">{{ $g[1] }}</h4>
          <p data-i18n="{{ $g[5] }}_desc">{{ $g[2] }}</p>
        </div>
        @endforeach
      </div>

      <p class="google-note" data-i18n="google_note">
        Teknolojia za Google zinazotajwa hapa (Gemini, Google AI Studio) zinawakilisha zana tunazotumia katika ujenzi wa mfumo. Hii haikusudiwa kuwakilisha ushirikiano rasmi au uidhinisho wowote na Google.
      </p>
    </div>
  </div>
</section>

{{-- AI Architecture Flow --}}
<section>
  <div class="wrap">
    <div style="text-align:center; margin-bottom:32px;">
      <span class="eyebrow" data-i18n="arch_eyebrow">MTIRIRIKO WA AI</span>
      <h2 class="section-title" data-i18n="arch_title">Jinsi AI Inavyofikia Mkulima</h2>
    </div>

    <div class="arch-flow-container fade-up">
      {{-- Farmer --}}
      <div class="flow-row">
        <div class="flow-node farmer">👨‍🌾 <span data-i18n="arch_farmer">Mkulima</span></div>
      </div>
      <div class="flow-arrow"><x-icon name="chevron-down" :size="24" /></div>
      <div class="flow-label" data-i18n="arch_via">Kupitia</div>

      {{-- Channels --}}
      <div class="flow-row">
        <div class="flow-node">📱 <span data-i18n="arch_app">App ya Simu</span></div>
        <div class="flow-node">🌐 <span data-i18n="arch_web">Wavuti</span></div>
        <div class="flow-node">📲 SMS / USSD</div>
      </div>
      <div class="flow-arrow"><x-icon name="chevron-down" :size="24" /></div>
      <div class="flow-label" data-i18n="arch_platform">MkulimaForum Platform (Laravel API + Flutter)</div>

      {{-- Platform --}}
      <div class="flow-row">
        <div class="flow-node platform">MkulimaForum Platform</div>
      </div>
      <div class="flow-arrow"><x-icon name="chevron-down" :size="24" /></div>
      <div class="flow-label" data-i18n="arch_routed">Inaelekeza kwa</div>

      {{-- AI Layer --}}
      <div class="flow-row">
        <div class="flow-node ai">🤖 Gemini 3 Flash</div>
        <div class="flow-node ai">🔍 Gemini 3 Pro + Search</div>
        <div class="flow-node ai">🧠 Gemma 2B (Offline)</div>
        <div class="flow-node ai">📊 Agri Knowledge Base</div>
        <div class="flow-node ai">⛅ Weather Data</div>
        <div class="flow-node ai">🛒 Market Data</div>
      </div>
      <div class="flow-arrow"><x-icon name="chevron-down" :size="24" /></div>

      {{-- Output --}}
      <div class="flow-row">
        <div class="flow-node output" data-i18n="arch_output">✅ Ushauri unaoweza kutumika kwa Mkulima</div>
      </div>
    </div>
  </div>
</section>

{{-- Privacy & Responsibility --}}
<section class="soft-band">
  <div class="wrap" style="max-width:700px; text-align:center;">
    <span class="eyebrow" data-i18n="privacy_eyebrow">FARAGHA NA UWAJIBIKAJI</span>
    <h2 class="section-title" data-i18n="privacy_title">AI Inayowajibika</h2>
    <p class="section-lead" style="text-align:center; margin:0 auto 24px;" data-i18n="privacy_desc">MkulimaForum inaamini katika uwazi wa AI na ulinzi wa data za wakulima. Hatuzidishi wala kuuza data za kibinafsi za wakulima. Mifano yetu yote ya AI inajaribiwa kwa usahihi kabla ya kutolewa kwa mazao halisi.</p>
    <a href="/contact" class="btn btn-outline" data-i18n="privacy_cta">Jifunze Zaidi Kuhusu Sera Yetu →</a>
  </div>
</section>

{{-- CTA --}}
<section class="final-cta">
  <div class="wrap">
    <h2 data-i18n="t_cta_title">Una Swali Kuhusu Teknolojia Yetu?</h2>
    <p data-i18n="t_cta_sub">Tupo tayari kuzungumza na watafiti, washirika wa teknolojia, na watengenezaji wa mfumo kuhusu mfumo wetu wa AI.</p>
    <div class="btn-row">
      <a href="/contact" class="btn btn-primary btn-lg" data-i18n="t_cta_contact">Wasiliana Nasi →</a>
      <a href="/pitch-deck" class="btn btn-outline btn-lg" data-i18n="t_cta_pitch">📊 Pitch Deck</a>
    </div>
  </div>
</section>

@endsection

@section('page_scripts')
<script nonce="{{ $cspNonce ?? '' }}">
mkPageTranslations = {
  sw: {
    tech_eyebrow:'TEKNOLOJIA', tech_title:'AI Iliyojengwa kwa Hali Halisi ya Kilimo',
    tech_sub:'Tunatumia teknolojia za kisasa za ulimwengu na kuzirekebisha kwa hali ya wakulima wa Tanzania na Afrika Mashariki — bila kuacha wale wanaoishi mbali na mtandao.',
    stack_eyebrow:'NGUZO ZA TEKNOLOJIA', stack_title:'Teknolojia Inayofanya Kazi Nyuma ya Pazia',
    tc0_title:'Gemini Cloud AI', tc0_desc:'Gemini 3 Flash na Gemini 3 Pro ndio nguvu kuu za akili ya AI ya MkulimaForum. Flash inashughulikia utambuzi wa haraka wa magonjwa, maswali ya Mkulima AI, na utafutaji wa soko. Pro inashughulikia ushauri wa hali ya hewa na mazao.',
    tc1_title:'Gemma Edge AI', tc1_desc:'Gemma 2B (INT4 Quantized) inafanya kazi moja kwa moja kwenye simu za wakulima bila kuhitaji intaneti kupitia MediaPipe LLM Inference (Google AI Edge SDK).',
    tc2_title:'Computer Vision', tc2_desc:'Mfumo wa utambuzi wa picha wa AI unaotumia uwezo wa multimodal wa Gemini kuchambua picha za mazao yaliyoathirika na kutoa matokeo ya utambuzi wa ugonjwa.',
    tc3_title:'Mfumo wa Offline', tc3_desc:'Mfumo wa usanifu unaounga mkono maeneo ya uunganisho mdogo kupitia SMS, USSD, maarifa yaliyohifadhiwa, na Gemma 2B inayofanya kazi nje ya mtandao.',
    tc4_title:'Msingi wa Data', tc4_desc:'PostgreSQL iliyo na pgvector inashughulikia utafutaji wa semantic wa maktaba ya maarifa ya kilimo. Redis inashughulikia akiba na foleni za kazi.',
    tc5_title:'Malipo na Fedha', tc5_desc:'M-Pesa, Tigo Pesa, Escrow ya Mkulima inayolinda wanunuzi na wauzaji katika miamala ya soko.',
    tc6_title:'Usalama na Faragha', tc6_desc:'Laravel Sanctum inashughulikia uthibitisho wa mtumiaji na kuzuia API. Maelezo ya kibinafsi ya mtumiaji yanalindwa na sera kali ya faragha.',
    tc7_title:'App ya Simu', tc7_desc:'App ya MkulimaForum imejengwa kwa Flutter, inayoruhusu kutolewa kwa Android na iOS. Imeboreshwa kwa simu za android za bei ya chini.',
    google_eyebrow:'TEKNOLOJIA YA GOOGLE AI', google_title:'Jinsi Tunavyotumia Gemini AI',
    google_sub:'MkulimaForum inatumia mifano ya Gemini ya Google katika mtiririko mbalimbali wa AI. Toleo la awali la mfumo wetu lilibuniwa kwa kutumia Google AI Studio.',
    gc0_title:'Gemini 3 Flash', gc0_desc:'AI Plant Scanner, Mkulima AI mazungumzo, Utafutaji wa Soko',
    gc1_title:'Gemini 3 Pro + Grounding', gc1_desc:'Ushauri wa Hali ya Hewa na Mazao ukitumia Google Search Grounding',
    gc2_title:'Google AI Studio', gc2_desc:'Ubunifu wa kwanza na majaribio ya mfano wa AI ya MkulimaForum',
    google_note:'Teknolojia za Google zinazotajwa hapa zinawakilisha zana tunazotumia. Hii haikusudiwa kuwakilisha ushirikiano rasmi au uidhinisho wowote na Google.',
    arch_eyebrow:'MTIRIRIKO WA AI', arch_title:'Jinsi AI Inavyofikia Mkulima',
    arch_farmer:'Mkulima', arch_via:'Kupitia', arch_app:'App ya Simu', arch_web:'Wavuti',
    arch_platform:'Jukwaa la MkulimaForum (Laravel API + Flutter)', arch_routed:'Inaelekeza kwa',
    arch_output:'✅ Ushauri unaoweza kutumika kwa Mkulima',
    privacy_eyebrow:'FARAGHA NA UWAJIBIKAJI', privacy_title:'AI Inayowajibika',
    privacy_desc:'MkulimaForum inaamini katika uwazi wa AI na ulinzi wa data za wakulima. Hatuzidishi wala kuuza data za kibinafsi za wakulima.',
    privacy_cta:'Jifunze Zaidi Kuhusu Sera Yetu →',
    t_cta_title:'Una Swali Kuhusu Teknolojia Yetu?',
    t_cta_sub:'Tupo tayari kuzungumza na watafiti, washirika wa teknolojia, na watengenezaji wa mfumo kuhusu mfumo wetu wa AI.',
    t_cta_contact:'Wasiliana Nasi →', t_cta_pitch:'📊 Pitch Deck',
  },
  en: {
    tech_eyebrow:'TECHNOLOGY', tech_title:'AI Built for Real Farming Conditions',
    tech_sub:'We apply world-class technology, adapted for the realities of Tanzania and East African farmers — including those in areas with little to no internet connectivity.',
    stack_eyebrow:'TECHNOLOGY STACK', stack_title:'The Technology Working Behind the Scenes',
    tc0_title:'Gemini Cloud AI', tc0_desc:'Gemini 3 Flash and Gemini 3 Pro power MkulimaForum\'s core AI capabilities. Flash handles plant diagnosis, Mkulima AI queries, and marketplace search. Pro handles weather and crop advisory with Google Search Grounding.',
    tc1_title:'Gemma Edge AI', tc1_desc:'Gemma 2B (INT4 Quantized) runs locally on farmer smartphones without requiring internet, implemented via MediaPipe LLM Inference (Google AI Edge SDK) for on-device inference.',
    tc2_title:'Computer Vision', tc2_desc:'AI image recognition using Gemini\'s multimodal capabilities to analyze crop images and provide accurate disease or pest identification with treatment recommendations.',
    tc3_title:'Offline Architecture', tc3_desc:'An architecture supporting low-connectivity areas via SMS gateway, USSD shortcodes, device-cached agronomy knowledge, and offline Gemma 2B inference.',
    tc4_title:'Data Infrastructure', tc4_desc:'PostgreSQL with pgvector handles semantic search across the agronomy knowledge base. Redis manages caching, user sessions, and background job queues for scalable performance.',
    tc5_title:'Payments & Finance', tc5_desc:'M-Pesa and Tigo Pesa payment integration, Mkulima Escrow protecting buyers and sellers in marketplace transactions, and an extensible payment architecture.',
    tc6_title:'Security & Privacy', tc6_desc:'Laravel Sanctum handles user authentication and API token management. User personal data is protected by strict privacy policies and a minimal data architecture.',
    tc7_title:'Mobile App Platform', tc7_desc:'MkulimaForum app built with Flutter for Android and iOS deployment from a single codebase. Optimized for lower-end Android devices common among Tanzanian farmers.',
    google_eyebrow:'GOOGLE AI TECHNOLOGY', google_title:'How We Use Gemini AI',
    google_sub:"MkulimaForum uses Google's Gemini models across several AI-assisted workflows. Early versions of the platform were prototyped using Google AI Studio.",
    gc0_title:'Gemini 3 Flash', gc0_desc:'AI Plant Scanner, Mkulima AI conversations, Smart Marketplace Search',
    gc1_title:'Gemini 3 Pro + Grounding', gc1_desc:'Weather & Crop Advisory Engine using real-time Google Search Grounding',
    gc2_title:'Google AI Studio', gc2_desc:'Used for initial AI prototype development and model experimentation for MkulimaForum',
    google_note:'Google technologies mentioned here represent tools we use in building our system. This is not intended to represent a formal partnership or endorsement by Google.',
    arch_eyebrow:'AI FLOW', arch_title:'How AI Reaches the Farmer',
    arch_farmer:'Farmer', arch_via:'Via', arch_app:'Mobile App', arch_web:'Web',
    arch_platform:'MkulimaForum Platform (Laravel API + Flutter)', arch_routed:'Routes to',
    arch_output:'✅ Actionable farmer recommendation delivered',
    privacy_eyebrow:'PRIVACY & RESPONSIBILITY', privacy_title:'Responsible AI',
    privacy_desc:'MkulimaForum believes in AI transparency and farmer data protection. We do not sell or misuse personal farmer data. All AI models are tested for accuracy before deployment on real crops.',
    privacy_cta:'Learn More About Our Policy →',
    t_cta_title:'Questions About Our Technology?',
    t_cta_sub:"We are happy to speak with researchers, technology partners, and system developers about our AI architecture.",
    t_cta_contact:'Contact Us →', t_cta_pitch:'📊 Pitch Deck',
  }
};
</script>
@endsection
