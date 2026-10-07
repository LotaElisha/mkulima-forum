@extends('layouts.public')

@section('title', 'Partner With MkulimaForum | Agricultural Technology Partnership Tanzania')
@section('meta_description', 'MkulimaForum is designed to collaborate with organizations across agriculture, technology, finance, research, government, and development across East Africa.')
@section('og_title', 'Partner With MkulimaForum | AgriTech Tanzania')
@section('og_description', 'Transform agriculture together. Partner with MkulimaForum across technology, finance, markets, research, and farmer programs.')

@section('head_extra')
<style>
  /* Tokens, buttons, forms, header and footer come from layouts/public.blade.php.
     This block only lays out the partners page's own sections. */
  .ico { width:1.15em; height:1.15em; flex:none; stroke-width:2; }
  .hero-inner { max-width:720px; }
  .center-head { text-align:center; margin-bottom:40px; }
  .center-head .section-lead { margin:0 auto; }

  /* Partner categories */
  .partner-cat-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }
  .partner-cat-card { background:#fff; border:1px solid var(--border-light); border-radius:var(--radius-xl); padding:28px; display:flex; flex-direction:column; }
  .partner-cat-card h3 { font-size:18px; font-weight:700; color:var(--ink-dark); margin-bottom:8px; }
  .partner-cat-card p { font-size:15px; color:var(--ink-muted); line-height:1.6; }
  .partner-cat-card .examples { margin-top:16px; display:flex; flex-wrap:wrap; gap:6px; }

  /* Technology ecosystem */
  .eco-band { background:var(--surface-soft); border-top:1px solid var(--border-light); border-bottom:1px solid var(--border-light); }
  .tech-eco-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; }
  .tech-eco-card { background:#fff; border:1px solid var(--border-light); border-radius:var(--radius-xl); padding:20px; display:flex; align-items:center; gap:14px; }
  .tech-mark { width:44px; height:44px; border-radius:12px; background:var(--leaf-pale); color:var(--forest-dark); display:flex; align-items:center; justify-content:center; font-size:18px; font-weight:800; flex-shrink:0; }
  .tech-eco-card .tech-name { font-size:16px; font-weight:700; color:var(--ink-dark); line-height:1.3; }
  .tech-eco-card .tech-role { font-size:14px; color:var(--ink-muted); margin-top:2px; }
  .eco-note { text-align:center; font-size:14px; color:var(--ink-muted); margin:24px auto 0; max-width:44rem; }

  /* Become a partner */
  .grid-partner-form { display:grid; grid-template-columns:minmax(0,.45fr) minmax(0,.55fr); gap:56px; align-items:start; }
  .grid-partner-form > * { min-width:0; }
  .bp-sub { color:var(--ink-muted); margin-bottom:24px; line-height:1.7; font-size:16px; }
  .bp-options { display:flex; flex-direction:column; gap:10px; }
  .bp-option { display:flex; align-items:center; gap:12px; min-height:52px; padding:12px 16px; background:#fff; border:1px solid var(--border-light); border-radius:12px; }
  .bp-option .ico { color:var(--forest-mid); width:20px; height:20px; }
  .bp-option span { font-size:15px; font-weight:600; color:var(--ink-dark); }
  .partner-form { background:#fff; border:1px solid var(--border-light); border-radius:var(--radius-xl); padding:40px; }
  .partner-form h3 { font-size:22px; font-weight:700; color:var(--ink-dark); margin-bottom:24px; }
  .partner-form form { display:flex; flex-direction:column; gap:18px; }
  .partner-form .form-group { margin-bottom:0; }
  .form-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
  #pf_result { font-size:15px; }

  @media(max-width:960px){
    .partner-cat-grid { grid-template-columns:repeat(2,1fr); }
    .tech-eco-grid { grid-template-columns:repeat(2,1fr); }
  }
  @media(max-width:860px){ .grid-partner-form { grid-template-columns:1fr; gap:32px; } }
  @media(max-width:700px){
    .center-head { margin-bottom:24px; }
    .partner-cat-grid, .tech-eco-grid { grid-template-columns:1fr; gap:10px; }
    .partner-cat-card { padding:18px; }
    .tech-eco-card { padding:14px 16px; }
    .form-grid-2 { grid-template-columns:1fr; gap:18px; }
    .partner-form h3 { font-size:20px; margin-bottom:18px; }
    .partner-form .btn { white-space:normal; }
  }
</style>
@endsection

@section('content')

{{-- Hero --}}
<section class="page-hero">
  <div class="wrap fade-up"><div class="hero-inner">
    <span class="eyebrow" data-i18n="partners_eyebrow">WASHIRIKA</span>
    <h1 class="page-title" data-i18n="partners_title">Tushirikiane Kubadilisha Kilimo</h1>
    <p class="section-lead" data-i18n="partners_sub">MkulimaForum imejengwa kushirikiana na mashirika yanayofanya kazi katika kilimo, teknolojia, fedha, utafiti, serikali, na maendeleo.</p>
  </div></div>
</section>

{{-- Partner Categories --}}
<section>
  <div class="wrap">
    <span class="eyebrow" data-i18n="pcat_eyebrow">AINA ZA WASHIRIKA</span>
    <h2 class="section-title" style="margin-bottom:32px;" data-i18n="pcat_title">Tunashirikiana na Aina Hizi za Mashirika</h2>
    <div class="partner-cat-grid">
      @foreach([
        ['globe','Washirika wa Teknolojia','Technology Partners','Miundombinu ya AI, wingu, data, uunganisho, na vifaa.','AI infrastructure, cloud, data, connectivity, and hardware.','Gemini AI / Cloud / IoT / Connectivity','pc0'],
        ['chart','Washirika wa Fedha','Financial Partners','Pesa za simu, benki, FinTech, na fedha za kilimo.','Mobile money, banks, FinTech, and agricultural finance.','M-Pesa / Tigo Pesa / CRDB / Agricultural Finance','pc1'],
        ['leaf','Washirika wa Kilimo','Agricultural Partners','Wauzaji wa pembejeo, wazalishaji, wakusanyaji, wanunuzi wa mazao, na wataalamu.','Agro-dealers, input manufacturers, aggregators, buyers, and agronomists.','Agro-dealers / Input Manufacturers / Buyers','pc2'],
        ['verified','Serikali na Usimamizi','Government & Regulatory','Wizara za kilimo, mamlaka za mitaa, wasimamizi, na huduma za ugani.','Agricultural ministries, local governments, regulators, and extension services.','MAFC / TFRA / TPHPA / Local Authorities','pc3'],
        ['groups','Washirika wa Maendeleo','Development Partners','NGO, misingi ya fedha, na mashirika ya kimataifa ya maendeleo.','NGOs, foundations, and international development agencies.','NGOs / Development Agencies / Foundations','pc4'],
        ['book','Utafiti na Elimu','Research & Academia','Vyuo vikuu, taasisi za utafiti wa kilimo, na watafiti wa AI.','Universities, agricultural research institutes, and AI researchers.','SUA / UDSM / Research Institutes','pc5'],
      ] as $cat)
      <div class="partner-cat-card fade-up">
        <div class="card-icon"><x-icon :name="$cat[0]" size="24" /></div>
        <h3 data-i18n="{{ $cat[6] }}_title">{{ $cat[1] }}</h3>
        <p data-i18n="{{ $cat[6] }}_desc">{{ $cat[3] }}</p>
        <div class="examples">
          @foreach(explode(' / ', $cat[5]) as $ex)
          <span class="tag">{{ trim($ex) }}</span>
          @endforeach
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- Technology Ecosystem (NOT "Official Partners") --}}
<section class="eco-band">
  <div class="wrap">
    <div class="center-head">
      <span class="eyebrow" data-i18n="eco_eyebrow">MFUMO WA TEKNOLOJIA</span>
      <h2 class="section-title" data-i18n="eco_title">Teknolojia Tunazotumia Kujenga</h2>
      <p class="section-lead" data-i18n="eco_sub">
        MkulimaForum imejengwa juu ya teknolojia za kisasa za dunia. Hizi ni teknolojia tunazotumia katika mfumo wetu — si lazima washirika rasmi.
      </p>
    </div>
    <div class="tech-eco-grid">
      @foreach([
        ['🤖','Google Gemini 3','Cloud AI Engine','G'],
        ['🧠','Google Gemma 2B','On-Device AI','G'],
        ['☁️','Google Cloud','Infrastructure','G'],
        ['🔍','Google Search','Grounding / RAG','G'],
        ['📱','Flutter','Cross-Platform App','F'],
        ['⚡','Laravel','API Backend','L'],
        ['🗄️','PostgreSQL','Database (pgvector)','P'],
        ['🔐','Sanctum','Auth & Security','S'],
      ] as $tech)
      <div class="tech-eco-card fade-up">
        <div class="tech-mark" aria-hidden="true">{{ $tech[3] }}</div>
        <div>
          <div class="tech-name">{{ $tech[1] }}</div>
          <div class="tech-role">{{ $tech[2] }}</div>
        </div>
      </div>
      @endforeach
    </div>
    <p class="eco-note" data-i18n="eco_note">
      Google Gemini na bidhaa za Google ni teknolojia zinazotumika katika mfumo. Kutajwa kwao hapa hakumaanishi ushirikiano rasmi na Google.
    </p>
  </div>
</section>

{{-- Become a Partner --}}
<section id="become-partner">
  <div class="wrap">
    <div class="grid-partner-form">
      <div class="fade-up">
        <span class="eyebrow" data-i18n="bp_eyebrow">ANZA USHIRIKIANO</span>
        <h2 class="section-title" data-i18n="bp_title">Shirikiana na MkulimaForum</h2>
        <p class="bp-sub" data-i18n="bp_sub">Tuna nafasi za ushirikiano katika maeneo mbalimbali. Chagua aina ya ushirikiano inayokufaa na tutawasiliana nawe.</p>
        <div class="bp-options">
          @foreach([
            ['💻','Ushirikiano wa Teknolojia','Technology Partnership'],
            ['📦','Ufikiaji wa Soko','Market Access'],
            ['🔬','Utafiti wa Pamoja','Research Collaboration'],
            ['👨‍🌾','Mipango ya Wakulima','Farmer Programs'],
            ['🏪','Mtandao wa Wauzaji','Agro-dealer Network'],
            ['🏛️','Ushirikiano wa Serikali','Government Collaboration'],
          ] as $opt)
          <div class="bp-option">
            <x-icon name="check-circle" />
            <span data-i18n="bp_opt_{{ $loop->index }}">{{ $opt[1] }}</span>
          </div>
          @endforeach
        </div>
      </div>

      <div class="partner-form fade-up">
        <h3 data-i18n="form_title">Anza Mazungumzo ya Ushirikiano</h3>
        <form id="partnerForm" onsubmit="handlePartnerForm(event)">
          <div class="form-grid-2">
            <div class="form-group">
              <label for="pf_name" data-i18n="form_name">Jina Lako Kamili</label>
              <input id="pf_name" type="text" required data-i18n-ph="form_name_ph" placeholder="Jina Lako">
            </div>
            <div class="form-group">
              <label for="pf_org" data-i18n="form_org">Shirika / Kampuni</label>
              <input id="pf_org" type="text" required data-i18n-ph="form_org_ph" placeholder="Shirika / Kampuni">
            </div>
          </div>
          <div class="form-group">
            <label for="pf_email" data-i18n="form_email">Barua Pepe</label>
            <input id="pf_email" type="email" required data-i18n-ph="form_email_ph" placeholder="barua@mfano.com">
          </div>
          <div class="form-group">
            <label for="pf_type" data-i18n="form_type">Aina ya Ushirikiano</label>
            <select id="pf_type" required>
              <option value="">-- Chagua Aina --</option>
              <option value="technology" data-i18n="opt_tech">Ushirikiano wa Teknolojia</option>
              <option value="market" data-i18n="opt_market">Ufikiaji wa Soko</option>
              <option value="research" data-i18n="opt_research">Utafiti wa Pamoja</option>
              <option value="farmer" data-i18n="opt_farmer">Mipango ya Wakulima</option>
              <option value="agro-dealer" data-i18n="opt_agro">Mtandao wa Wauzaji</option>
              <option value="government" data-i18n="opt_govt">Ushirikiano wa Serikali</option>
              <option value="other" data-i18n="opt_other">Nyingine</option>
            </select>
          </div>
          <div class="form-group">
            <label for="pf_message" data-i18n="form_message">Ujumbe / Maelezo</label>
            <textarea id="pf_message" rows="4" required data-i18n-ph="form_msg_ph" placeholder="Eleza fursa ya ushirikiano au swali lako..."></textarea>
          </div>
          <button type="submit" class="btn btn-primary btn-lg" style="justify-content:center;" data-i18n="form_submit">
            🤝 Tuma Ombi la Ushirikiano
          </button>
          <div id="pf_result" style="display:none; padding:14px; border-radius:10px; font-size:.9rem; font-weight:600;"></div>
        </form>
      </div>
    </div>

  </div>
</section>

@endsection

@section('page_scripts')
<script nonce="{{ $cspNonce ?? '' }}">
async function handlePartnerForm(e) {
  e.preventDefault();
  const result = document.getElementById('pf_result');
  result.style.display = 'block';
  result.style.background = 'var(--leaf-pale)';
  result.style.color = 'var(--forest-dark)';
  result.textContent = MK_LANG === 'sw' ? '✓ Asante! Tumeipokea ombi lako. Tutawasiliana nawe hivi karibuni.' : '✓ Thank you! We have received your partnership request and will be in touch shortly.';
  e.target.reset();
}

mkPageTranslations = {
  sw: {
    partners_eyebrow:'WASHIRIKA', partners_title:'Tushirikiane Kubadilisha Kilimo',
    partners_sub:'MkulimaForum imejengwa kushirikiana na mashirika yanayofanya kazi katika kilimo, teknolojia, fedha, utafiti, serikali, na maendeleo.',
    pcat_eyebrow:'AINA ZA WASHIRIKA', pcat_title:'Tunashirikiana na Aina Hizi za Mashirika',
    pc0_title:'Washirika wa Teknolojia', pc0_desc:'Miundombinu ya AI, wingu, data, uunganisho, na vifaa.',
    pc1_title:'Washirika wa Fedha', pc1_desc:'Pesa za simu, benki, FinTech, na fedha za kilimo.',
    pc2_title:'Washirika wa Kilimo', pc2_desc:'Wauzaji wa pembejeo, wazalishaji, wakusanyaji, wanunuzi wa mazao, na wataalamu.',
    pc3_title:'Serikali na Usimamizi', pc3_desc:'Wizara za kilimo, mamlaka za mitaa, wasimamizi, na huduma za ugani.',
    pc4_title:'Washirika wa Maendeleo', pc4_desc:'NGO, misingi ya fedha, na mashirika ya kimataifa ya maendeleo.',
    pc5_title:'Utafiti na Elimu', pc5_desc:'Vyuo vikuu, taasisi za utafiti wa kilimo, na watafiti wa AI.',
    eco_eyebrow:'MFUMO WA TEKNOLOJIA', eco_title:'Teknolojia Tunazotumia Kujenga',
    eco_sub:'MkulimaForum imejengwa juu ya teknolojia za kisasa za dunia. Hizi ni teknolojia tunazotumia katika mfumo wetu — si lazima washirika rasmi.',
    eco_note:'Google Gemini na bidhaa za Google ni teknolojia zinazotumika katika mfumo. Kutajwa kwao hapa hakumaanishi ushirikiano rasmi na Google.',
    bp_eyebrow:'ANZA USHIRIKIANO', bp_title:'Shirikiana na MkulimaForum',
    bp_sub:'Tuna nafasi za ushirikiano katika maeneo mbalimbali. Chagua aina ya ushirikiano inayokufaa na tutawasiliana nawe.',
    bp_opt_0:'Ushirikiano wa Teknolojia', bp_opt_1:'Ufikiaji wa Soko', bp_opt_2:'Utafiti wa Pamoja',
    bp_opt_3:'Mipango ya Wakulima', bp_opt_4:'Mtandao wa Wauzaji', bp_opt_5:'Ushirikiano wa Serikali',
    form_title:'Anza Mazungumzo ya Ushirikiano',
    form_name:'Jina Lako Kamili', form_name_ph:'Jina Lako', form_org:'Shirika / Kampuni', form_org_ph:'Shirika / Kampuni',
    form_email:'Barua Pepe', form_email_ph:'barua@mfano.com', form_type:'Aina ya Ushirikiano',
    opt_tech:'Ushirikiano wa Teknolojia', opt_market:'Ufikiaji wa Soko', opt_research:'Utafiti wa Pamoja',
    opt_farmer:'Mipango ya Wakulima', opt_agro:'Mtandao wa Wauzaji', opt_govt:'Ushirikiano wa Serikali', opt_other:'Nyingine',
    form_message:'Ujumbe / Maelezo', form_msg_ph:'Eleza fursa ya ushirikiano au swali lako...',
    form_submit:'🤝 Tuma Ombi la Ushirikiano',
  },
  en: {
    partners_eyebrow:'PARTNERS', partners_title:"Let's Transform Agriculture Together",
    partners_sub:'MkulimaForum is designed to collaborate with organizations across agriculture, technology, finance, research, government, and international development.',
    pcat_eyebrow:'PARTNER CATEGORIES', pcat_title:'We Collaborate Across These Partner Types',
    pc0_title:'Technology Partners', pc0_desc:'AI infrastructure, cloud, data, connectivity, and hardware.',
    pc1_title:'Financial Partners', pc1_desc:'Mobile money, banks, FinTech, and agricultural finance.',
    pc2_title:'Agricultural Partners', pc2_desc:'Agro-dealers, input manufacturers, aggregators, buyers, and agronomists.',
    pc3_title:'Government & Regulatory', pc3_desc:'Agricultural ministries, local governments, regulators, and extension services.',
    pc4_title:'Development Partners', pc4_desc:'NGOs, foundations, and international development agencies.',
    pc5_title:'Research & Academia', pc5_desc:'Universities, agricultural research institutes, and AI researchers.',
    eco_eyebrow:'TECHNOLOGY ECOSYSTEM', eco_title:'Technologies We Build With',
    eco_sub:'MkulimaForum is built on world-class technologies. These are the tools we use — not necessarily formal partner endorsements.',
    eco_note:'Google Gemini and Google products are technologies used in our system. Their mention does not imply a formal partnership or endorsement by Google.',
    bp_eyebrow:'START A PARTNERSHIP', bp_title:'Partner With MkulimaForum',
    bp_sub:'We have partnership opportunities across multiple areas. Choose the type that fits and we will be in touch.',
    bp_opt_0:'Technology Partnership', bp_opt_1:'Market Access', bp_opt_2:'Research Collaboration',
    bp_opt_3:'Farmer Programs', bp_opt_4:'Agro-dealer Network', bp_opt_5:'Government Collaboration',
    form_title:'Start a Partnership Conversation',
    form_name:'Full Name', form_name_ph:'Your Name', form_org:'Organization / Company', form_org_ph:'Organization / Company',
    form_email:'Email Address', form_email_ph:'email@example.com', form_type:'Partnership Type',
    opt_tech:'Technology Partnership', opt_market:'Market Access', opt_research:'Research Collaboration',
    opt_farmer:'Farmer Programs', opt_agro:'Agro-dealer Network', opt_govt:'Government Collaboration', opt_other:'Other',
    form_message:'Message / Description', form_msg_ph:'Describe your partnership opportunity or question...',
    form_submit:'🤝 Send Partnership Request',
  }
};
</script>
@endsection
