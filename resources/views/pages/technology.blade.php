@extends('layouts.public')

@section('title', 'MkulimaForum Technology | AI Built for Real Farming Conditions')
@section('meta_description', 'MkulimaForum inaendesha Laravel na PostgreSQL. Utambuzi wa magonjwa, SMS, USSD, na AI bila intaneti havijawashwa.')
@section('og_title', 'MkulimaForum Technology | AI for East African Agriculture')
@section('og_description', 'Tovuti na API zinaendesha Laravel na PostgreSQL. Utambuzi wa magonjwa, SMS, USSD, na AI bila intaneti havijawashwa.')

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
<x-mk.note />

{{-- Hero --}}
<section class="page-hero">
  <div class="wrap fade-up">
    <span class="eyebrow" data-i18n="tech_eyebrow">TEKNOLOJIA</span>
    <h1 class="page-title" data-i18n="tech_title">Kinachofanya kazi sasa</h1>
    <p class="section-lead" data-i18n="tech_sub">Tovuti na API zinaendesha Laravel na PostgreSQL. Utambuzi wa magonjwa, Gemini, Gemma, SMS, USSD, na malipo havijawashwa.</p>
  </div>
</section>

{{-- Tech Stack --}}
<section>
  <div class="wrap">
    <span class="eyebrow" data-i18n="stack_eyebrow">NGUZO ZA TEKNOLOJIA</span>
    <h2 class="section-title" style="margin-bottom:32px;" data-i18n="stack_title">Teknolojia Inayofanya Kazi Nyuma ya Pazia</h2>
    <div class="tech-stack-grid">
      @foreach([
        ['🤖','AI ya wingu — haijawashwa','Cloud AI — not switched on','Utambuzi wa magonjwa na mazungumzo ya AI havijawashwa. Hatuwasilishi Gemini kama huduma inayofanya kazi.','Disease diagnosis and AI chat are not switched on. Gemini is not a live service here.','tc0',['Haijawashwa','Si huduma ya sasa']],
        ['🧠','AI kwenye simu — haijawashwa','On-device AI — not switched on','Hakuna modeli inayoendesha ndani ya simu bila intaneti.','No model runs on the phone without internet.','tc1',['Haijawashwa','Inahitaji intaneti']],
        ['👁️','Utambuzi wa picha — haijawashwa','Image diagnosis — not switched on','Kupiga picha ya jani na kupata utambuzi bado hakufanyi kazi.','Photographing a leaf and receiving a diagnosis does not work yet.','tc2',['Haijawashwa']],
        ['📴','SMS na USSD — haijawashwa','SMS and USSD — not switched on','Huduma za SMS na USSD hazitumwi. Programu inahitaji intaneti.','SMS and USSD are not sent. The app needs an internet connection.','tc3',['Haijawashwa','Inahitaji intaneti']],
        ['🗄️','Msingi wa Data','Data Infrastructure','PostgreSQL iliyo na pgvector inashughulikia utafutaji wa mfano wa kulingana (semantic search) kwa maktaba ya maarifa ya kilimo. Redis inashughulikia akiba, vipindi vya mtumiaji, na foleni za kazi za usuli.','PostgreSQL with pgvector extension handles semantic search across the agronomy knowledge base. Redis manages caching, user sessions, and background job queues for scalable performance.','tc4',['PostgreSQL + pgvector','Redis Cache','Semantic Search']],
        ['💳','Malipo — haijawashwa','Payments — not switched on','M-Pesa, Tigo Pesa, na escrow havijawashwa kwenye toleo hili.','M-Pesa, Tigo Pesa, and escrow are not switched on in this release.','tc5',['Haijawashwa']],
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
          <span class="g-text">haijawashwa</span>
        </div>
        <span class="eyebrow" style="display:block;" data-i18n="google_eyebrow">TEKNOLOJIA YA GOOGLE AI</span>
        <h2 class="section-title" data-i18n="google_title">AI ya magonjwa haijawashwa</h2>
        <p class="section-lead" style="text-align:center; margin:0 auto; max-width:44rem;" data-i18n="google_sub">
          Hatutumii Gemini wala Gemma kwenye toleo hili. Mipango hiyo haijawashwa.
        </p>
      </div>

      <div class="google-uses-grid">
        @foreach([
          ['⚡','Scanner — haijawashwa','Utambuzi wa magonjwa kwa picha haujawashwa.','Scanner — not switched on','Photo diagnosis is not switched on.','gc0'],
          ['🔍','Ushauri wa AI — haijawashwa','Ushauri wa mazao unaotumia AI haujawashwa. Hali ya hewa inayopatikana ni ya huduma ya hali ya hewa, si AI.','AI advice — not switched on','AI crop advice is not switched on. Weather that is available comes from a weather service, not from an AI model.','gc1'],
          ['🛠️','Google AI Studio','Ubunifu wa kwanza na majaribio ya mfano. Si huduma inayotolewa kwa wakulima.','Google AI Studio','Prototype experiments only. Not a service offered to farmers.','gc2'],
        ] as $g)
        <div class="google-use">
          <div class="card-icon">@if($g[0] === '⚡')<x-icon name="sun" :size="22" />@elseif($g[0] === '🔍')<x-icon name="search" :size="22" />@elseif($g[0] === '🛠️')<svg class="ico" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.8-3.8a6 6 0 0 1-7.9 7.9l-6.9 6.9a2.1 2.1 0 0 1-3-3l6.9-6.9a6 6 0 0 1 7.9-7.9z"/></svg>@endif</div>
          <h4 data-i18n="{{ $g[5] }}_title">{{ $g[1] }}</h4>
          <p data-i18n="{{ $g[5] }}_desc">{{ $g[2] }}</p>
        </div>
        @endforeach
      </div>

      <p class="google-note" data-i18n="google_note">
        Hatuna ushirikiano na Google. Gemini na Gemma havijawashwa. Google AI Studio, ikiwa imetajwa, ni majaribio ya ndani na si huduma kwa wakulima.
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
        <div class="flow-node">📲 SMS / USSD — haijawashwa</div>
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
        <div class="flow-node ai">AI ya magonjwa — haijawashwa</div>
        <div class="flow-node ai">SMS — haijawashwa</div>
        <div class="flow-node ai">Malipo — haijawashwa</div>
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
    <p class="section-lead" style="text-align:center; margin:0 auto 24px;" data-i18n="privacy_desc">MkulimaForum inaamini katika uwazi na ulinzi wa data za wakulima. Hatuzidishi wala kuuza data za kibinafsi za wakulima.</p>
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
    tech_eyebrow:'TEKNOLOJIA', tech_title:'Kinachofanya kazi sasa',
    tech_sub:'Tovuti na API zinaendesha Laravel na PostgreSQL. Utambuzi wa magonjwa, Gemini, Gemma, SMS, USSD, na malipo havijawashwa.',
    stack_eyebrow:'NGUZO ZA TEKNOLOJIA', stack_title:'Kilichoandikwa hapa si ahadi',
    tc0_title:'AI ya wingu — haijawashwa', tc0_desc:'Utambuzi wa magonjwa na mazungumzo ya AI havijawashwa. Hatuwasilishi Gemini kama huduma inayofanya kazi.',
    tc1_title:'AI kwenye simu — haijawashwa', tc1_desc:'Hakuna modeli inayoendesha ndani ya simu bila intaneti.',
    tc2_title:'Utambuzi wa picha — haijawashwa', tc2_desc:'Kupiga picha ya jani na kupata utambuzi bado hakufanyi kazi.',
    tc3_title:'SMS na USSD — haijawashwa', tc3_desc:'Huduma za SMS na USSD hazitumwi. Programu inahitaji intaneti.',
    tc4_title:'Msingi wa Data', tc4_desc:'PostgreSQL iliyo na pgvector inashughulikia utafutaji wa semantic wa maktaba ya maarifa ya kilimo. Redis inashughulikia akiba na foleni za kazi.',
    tc5_title:'Malipo — haijawashwa', tc5_desc:'M-Pesa, Tigo Pesa, na escrow havijawashwa kwenye toleo hili.',
    tc6_title:'Usalama na Faragha', tc6_desc:'Laravel Sanctum inashughulikia uthibitisho wa mtumiaji na kuzuia API. Maelezo ya kibinafsi ya mtumiaji yanalindwa na sera kali ya faragha.',
    tc7_title:'App ya Simu', tc7_desc:'App ya MkulimaForum imejengwa kwa Flutter, inayoruhusu kutolewa kwa Android na iOS. Imeboreshwa kwa simu za android za bei ya chini.',
    google_eyebrow:'HALI YA AI', google_title:'AI ya magonjwa haijawashwa',
    google_sub:'Hatutumii Gemini wala Gemma kwenye toleo hili. Mipango hiyo haijawashwa.',
    gc0_title:'Scanner — haijawashwa', gc0_desc:'Utambuzi wa magonjwa kwa picha haujawashwa.',
    gc1_title:'Ushauri wa AI — haijawashwa', gc1_desc:'Ushauri wa mazao unaotumia AI haujawashwa. Hali ya hewa inayopatikana ni ya huduma ya hali ya hewa, si AI.',
    gc2_title:'Google AI Studio', gc2_desc:'Ubunifu wa kwanza na majaribio ya mfano. Si huduma inayotolewa kwa wakulima.',
    google_note:'Hatuna ushirikiano na Google. Gemini na Gemma havijawashwa.',
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
    tech_eyebrow:'TECHNOLOGY', tech_title:'What works today',
    tech_sub:'The site and API run on Laravel and PostgreSQL. Disease diagnosis, Gemini, Gemma, SMS, USSD, and payments are not switched on.',
    stack_eyebrow:'TECHNOLOGY STACK', stack_title:'What is written here is not a promise',
    tc0_title:'Cloud AI — not switched on', tc0_desc:'Disease diagnosis and AI chat are not switched on. Gemini is not a live service here.',
    tc1_title:'On-device AI — not switched on', tc1_desc:'No model runs on the phone without internet.',
    tc2_title:'Image diagnosis — not switched on', tc2_desc:'Photographing a leaf and receiving a diagnosis does not work yet.',
    tc3_title:'SMS and USSD — not switched on', tc3_desc:'SMS and USSD are not sent. The app needs an internet connection.',
    tc4_title:'Data Infrastructure', tc4_desc:'PostgreSQL with pgvector handles semantic search across the agronomy knowledge base. Redis manages caching, user sessions, and background job queues for scalable performance.',
    tc5_title:'Payments — not switched on', tc5_desc:'M-Pesa, Tigo Pesa, and escrow are not switched on in this release.',
    tc6_title:'Security & Privacy', tc6_desc:'Laravel Sanctum handles user authentication and API token management. User personal data is protected by strict privacy policies and a minimal data architecture.',
    tc7_title:'Mobile App Platform', tc7_desc:'MkulimaForum app built with Flutter for Android and iOS deployment from a single codebase. Optimized for lower-end Android devices common among Tanzanian farmers.',
    google_eyebrow:'AI STATUS', google_title:'Disease AI is not switched on',
    google_sub:'This release does not use Gemini or Gemma. Those plans are not switched on.',
    gc0_title:'Scanner — not switched on', gc0_desc:'Photo diagnosis is not switched on.',
    gc1_title:'AI advice — not switched on', gc1_desc:'AI crop advice is not switched on. Weather that is available comes from a weather service, not from an AI model.',
    gc2_title:'Google AI Studio', gc2_desc:'Prototype experiments only. Not a service offered to farmers.',
    google_note:'There is no Google partnership. Gemini and Gemma are not switched on.',
    arch_eyebrow:'AI FLOW', arch_title:'How AI Reaches the Farmer',
    arch_farmer:'Farmer', arch_via:'Via', arch_app:'Mobile App', arch_web:'Web',
    arch_platform:'MkulimaForum Platform (Laravel API + Flutter)', arch_routed:'Routes to',
    arch_output:'✅ Actionable farmer recommendation delivered',
    privacy_eyebrow:'PRIVACY & RESPONSIBILITY', privacy_title:'Responsible AI',
    privacy_desc:'MkulimaForum does not sell or misuse personal farmer data.',
    privacy_cta:'Learn More About Our Policy →',
    t_cta_title:'Questions About Our Technology?',
    t_cta_sub:"We are happy to speak with researchers, technology partners, and system developers about our AI architecture.",
    t_cta_contact:'Contact Us →', t_cta_pitch:'📊 Pitch Deck',
  }
};
</script>
@endsection
