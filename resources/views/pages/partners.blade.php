@extends('layouts.public')

@section('title', 'Partner With MkulimaForum | Agricultural Technology Partnership Tanzania')
@section('meta_description', 'MkulimaForum is designed to collaborate with organizations across agriculture, technology, finance, research, government, and development across East Africa.')
@section('og_title', 'Partner With MkulimaForum | AgriTech Tanzania')
@section('og_description', 'Transform agriculture together. Partner with MkulimaForum across technology, finance, markets, research, and farmer programs.')

@section('head_extra')
<style>
  .partner-cat-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:24px; }
  @media(max-width:960px){ .partner-cat-grid{ grid-template-columns:repeat(2,1fr); } }
  @media(max-width:560px) { .partner-cat-grid{ grid-template-columns:1fr; } }
  .partner-cat-card {
    background:var(--surface-card); border:1px solid var(--border-light); border-radius:var(--radius-xl);
    padding:32px; transition:all .25s ease;
  }
  .partner-cat-card:hover { transform:translateY(-4px); box-shadow:var(--shadow-md); border-color:var(--border-mid); }
  .partner-cat-card h3 { font-size:1.1rem; font-weight:800; color:var(--ink-dark); margin-bottom:10px; }
  .partner-cat-card p  { font-size:.88rem; color:var(--ink-muted); line-height:1.65; }
  .partner-cat-card .examples {
    margin-top:14px; display:flex; flex-wrap:wrap; gap:6px;
  }
  .tech-eco-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
  @media(max-width:900px){ .tech-eco-grid{ grid-template-columns:repeat(2,1fr); } }
  @media(max-width:540px){ .tech-eco-grid{ grid-template-columns:1fr; } }
  .tech-eco-card {
    background:var(--surface-card); border:1px solid var(--border-light); border-radius:var(--radius-lg);
    padding:24px; text-align:center; transition:all .22s ease;
  }
  .tech-eco-card:hover { box-shadow:var(--shadow-md); }
  .tech-eco-card .tech-name { font-size:.9rem; font-weight:800; color:var(--ink-dark); margin-top:10px; }
  .tech-eco-card .tech-role { font-size:.75rem; color:var(--ink-muted); margin-top:4px; }
  .partner-form { background:var(--surface-card); border:1px solid var(--border-light); border-radius:var(--radius-2xl); padding:48px; }
  .form-group { display:flex; flex-direction:column; gap:6px; }
  .form-group label { font-size:.88rem; font-weight:700; color:var(--ink-dark); }
  .form-group input, .form-group select, .form-group textarea {
    padding:12px 16px; border:1.5px solid var(--border-light); border-radius:10px;
    font-family:inherit; font-size:.9rem; color:var(--ink-dark); background:var(--cream-bg);
    transition:border-color .2s ease; outline:none;
  }
  .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color:var(--forest-mid); background:#fff; }
  .form-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
  @media(max-width:640px){ .form-grid-2{ grid-template-columns:1fr; } }
</style>
@endsection

@section('content')

{{-- Hero --}}
<section class="page-hero">
  <div class="wrap fade-up" style="max-width:700px;">
    <span class="eyebrow" data-i18n="partners_eyebrow">WASHIRIKA</span>
    <h1 class="page-title" data-i18n="partners_title">Tushirikiane Kubadilisha Kilimo</h1>
    <p class="section-lead" data-i18n="partners_sub">MkulimaForum imejengwa kushirikiana na mashirika yanayofanya kazi katika kilimo, teknolojia, fedha, utafiti, serikali, na maendeleo.</p>
  </div>
</section>

{{-- Partner Categories --}}
<section>
  <div class="wrap">
    <span class="eyebrow" data-i18n="pcat_eyebrow">AINA ZA WASHIRIKA</span>
    <h2 class="section-title" style="margin-bottom:40px;" data-i18n="pcat_title">Tunashirikiana na Aina Hizi za Mashirika</h2>
    <div class="partner-cat-grid">
      @foreach([
        ['🤖','Washirika wa Teknolojia','Technology Partners','Miundombinu ya AI, wingu, data, uunganisho, na vifaa.','AI infrastructure, cloud, data, connectivity, and hardware.','Gemini AI / Cloud / IoT / Connectivity','pc0'],
        ['💳','Washirika wa Fedha','Financial Partners','Pesa za simu, benki, FinTech, na fedha za kilimo.','Mobile money, banks, FinTech, and agricultural finance.','M-Pesa / Tigo Pesa / CRDB / Agricultural Finance','pc1'],
        ['🌾','Washirika wa Kilimo','Agricultural Partners','Wauzaji wa pembejeo, wazalishaji, wakusanyaji, wanunuzi wa mazao, na wataalamu.','Agro-dealers, input manufacturers, aggregators, buyers, and agronomists.','Agro-dealers / Input Manufacturers / Buyers','pc2'],
        ['🏛️','Serikali na Usimamizi','Government & Regulatory','Wizara za kilimo, mamlaka za mitaa, wasimamizi, na huduma za ugani.','Agricultural ministries, local governments, regulators, and extension services.','MAFC / TFRA / TPRI / Local Authorities','pc3'],
        ['🌍','Washirika wa Maendeleo','Development Partners','NGO, misingi ya fedha, na mashirika ya kimataifa ya maendeleo.','NGOs, foundations, and international development agencies.','NGOs / Development Agencies / Foundations','pc4'],
        ['🔬','Utafiti na Elimu','Research & Academia','Vyuo vikuu, taasisi za utafiti wa kilimo, na watafiti wa AI.','Universities, agricultural research institutes, and AI researchers.','SUA / UDSM / Research Institutes','pc5'],
      ] as $cat)
      <div class="partner-cat-card fade-up">
        <div class="card-icon">{{ $cat[0] }}</div>
        <h3 data-i18n="{{ $cat[6] }}_title">{{ $cat[1] }}</h3>
        <p data-i18n="{{ $cat[6] }}_desc">{{ $cat[3] }}</p>
        <div class="examples">
          @foreach(explode(' / ', $cat[4]) as $ex)
          <span class="tag" style="font-size:.72rem;">{{ trim($ex) }}</span>
          @endforeach
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- Technology Ecosystem (NOT "Official Partners") --}}
<section style="background:var(--leaf-pale); padding:80px 0;">
  <div class="wrap">
    <div style="text-align:center; margin-bottom:40px;">
      <span class="eyebrow" data-i18n="eco_eyebrow">MFUMO WA TEKNOLOJIA</span>
      <h2 class="section-title" data-i18n="eco_title">Teknolojia Tunazotumia Kujenga</h2>
      <p class="section-lead" style="text-align:center; margin:0 auto; max-width:40rem;" data-i18n="eco_sub">
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
        <div style="font-size:2rem;">{{ $tech[0] }}</div>
        <div class="tech-name">{{ $tech[1] }}</div>
        <div class="tech-role">{{ $tech[2] }}</div>
      </div>
      @endforeach
    </div>
    <p style="text-align:center; font-size:.8rem; color:var(--ink-faint); margin-top:24px;" data-i18n="eco_note">
      Google Gemini na bidhaa za Google ni teknolojia zinazotumika katika mfumo. Kutajwa kwao hapa hakumaanishi ushirikiano rasmi na Google.
    </p>
  </div>
</section>

{{-- Become a Partner --}}
<section id="become-partner">
  <div class="wrap">
    <div style="display:grid; grid-template-columns:0.45fr 0.55fr; gap:56px; align-items:start;">
      <div class="fade-up">
        <span class="eyebrow" data-i18n="bp_eyebrow">ANZA USHIRIKIANO</span>
        <h2 class="section-title" data-i18n="bp_title">Shirikiana na MkulimaForum</h2>
        <p style="color:var(--ink-muted); margin-bottom:24px; line-height:1.7;" data-i18n="bp_sub">Tuna nafasi za ushirikiano katika maeneo mbalimbali. Chagua aina ya ushirikiano inayokufaa na tutawasiliana nawe.</p>
        <div style="display:flex; flex-direction:column; gap:12px;">
          @foreach([
            ['💻','Ushirikiano wa Teknolojia','Technology Partnership'],
            ['📦','Ufikiaji wa Soko','Market Access'],
            ['🔬','Utafiti wa Pamoja','Research Collaboration'],
            ['👨‍🌾','Mipango ya Wakulima','Farmer Programs'],
            ['🏪','Mtandao wa Wauzaji','Agro-dealer Network'],
            ['🏛️','Ushirikiano wa Serikali','Government Collaboration'],
          ] as $opt)
          <div style="display:flex; align-items:center; gap:10px; padding:12px 16px; background:var(--leaf-pale); border-radius:10px; border:1px solid var(--border-light);">
            <span style="font-size:1.2rem;">{{ $opt[0] }}</span>
            <span style="font-size:.9rem; font-weight:700; color:var(--ink-dark);" data-i18n="bp_opt_{{ $loop->index }}">{{ $opt[1] }}</span>
          </div>
          @endforeach
        </div>
      </div>

      <div class="partner-form fade-up">
        <h3 style="font-size:1.3rem; font-weight:800; color:var(--ink-dark); margin-bottom:24px;" data-i18n="form_title">Anza Mazungumzo ya Ushirikiano</h3>
        <form id="partnerForm" onsubmit="handlePartnerForm(event)" style="display:flex; flex-direction:column; gap:18px;">
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

    @media(max-width:860px){ .grid-partner-form{ grid-template-columns:1fr; } }
  </div>
</section>

@endsection

@section('page_scripts')
<script>
async function handlePartnerForm(e) {
  e.preventDefault();
  const result = document.getElementById('pf_result');
  result.style.display = 'block';
  result.style.background = 'var(--leaf-pale)';
  result.style.color = 'var(--forest-dark)';
  result.textContent = MK_LANG === 'sw' ? '✓ Asante! Tumeipokea ombi lako. Tutawasiliana nawe hivi karibuni.' : '✓ Thank you! We have received your partnership request and will be in touch shortly.';
  e.target.reset();
}

const mkPageTranslations = {
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
