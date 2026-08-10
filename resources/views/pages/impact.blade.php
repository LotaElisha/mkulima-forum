@extends('layouts.public')

@section('title', 'MkulimaForum Impact | Technology Creating Measurable Agricultural Change')
@section('meta_description', 'Discover the impact MkulimaForum creates for East African smallholder farmers — from knowledge access and crop protection to market transparency and digital inclusion.')
@section('og_title', 'MkulimaForum Impact | Technology for African Farmers')
@section('og_description', 'AI technology creating measurable agricultural impact for Tanzania smallholder farmers.')

@section('head_extra')
<style>
  .impact-area-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:24px; }
  @media(max-width:960px){ .impact-area-grid{ grid-template-columns:repeat(2,1fr); } }
  @media(max-width:560px)  { .impact-area-grid{ grid-template-columns:1fr; } }
  .impact-card {
    background:var(--surface-card); border:1px solid var(--border-light); border-radius:var(--radius-xl);
    padding:32px; transition:all .25s ease; border-top:4px solid transparent;
  }
  .impact-card:hover { transform:translateY(-4px); box-shadow:var(--shadow-md); }
  .impact-card.color-0 { border-top-color: var(--leaf-green); }
  .impact-card.color-1 { border-top-color: var(--sun-gold); }
  .impact-card.color-2 { border-top-color: #4A90D9; }
  .impact-card.color-3 { border-top-color: #E07B39; }
  .impact-card.color-4 { border-top-color: #9B59B6; }
  .impact-card.color-5 { border-top-color: #27AE60; }
  .metric-grid { display:grid; grid-template-columns:repeat(5,1fr); gap:0; }
  @media(max-width:960px){ .metric-grid{ grid-template-columns:repeat(3,1fr); } }
  @media(max-width:560px) { .metric-grid{ grid-template-columns:1fr 1fr; } }
  .metric-cell {
    padding:32px 20px; text-align:center; border-right:1px solid var(--border-light);
    border-bottom:1px solid var(--border-light);
  }
  .metric-cell:nth-child(5n){ border-right:none; }
  .metric-num { font-family:'Outfit',sans-serif; font-size:2.2rem; font-weight:900; color:var(--forest-dark); }
  .metric-label { font-size:.78rem; font-weight:700; color:var(--ink-muted); text-transform:uppercase; letter-spacing:.08em; margin-top:4px; }
  .metric-note { font-size:.68rem; color:var(--ink-faint); margin-top:4px; font-style:italic; }

  .region-map {
    background:linear-gradient(145deg, #EFF7E9, #D6EEC8); border-radius:var(--radius-2xl);
    padding:48px; text-align:center; border:1px solid var(--border-mid);
  }
  .region-chips { display:flex; flex-wrap:wrap; gap:10px; justify-content:center; margin-top:20px; }
  .region-chip {
    padding:8px 16px; background:var(--surface-card); border:1px solid var(--border-light); border-radius:999px;
    font-size:.85rem; font-weight:600; color:var(--ink-body); display:flex; align-items:center; gap:6px;
  }
  .region-chip.active { background:var(--forest-dark); color:#fff; border-color:var(--forest-dark); }
</style>
@endsection

@section('content')

{{-- Hero --}}
<section class="page-hero">
  <div class="wrap fade-up" style="max-width:680px;">
    <span class="eyebrow" data-i18n="impact_eyebrow">ATHARI YETU</span>
    <h1 class="page-title" data-i18n="impact_title">Teknolojia Inayounda Mabadiliko ya Kweli ya Kilimo</h1>
    <p class="section-lead" data-i18n="impact_sub">MkulimaForum iliundwa kuunda athari ya moja kwa moja ya kuonekana kwa maisha ya wakulima wadogo wadogo Tanzania na Afrika Mashariki.</p>
  </div>
</section>

{{-- Impact Areas --}}
<section>
  <div class="wrap">
    <span class="eyebrow" data-i18n="ia_eyebrow">MAENEO YA ATHARI</span>
    <h2 class="section-title" style="margin-bottom:40px;" data-i18n="ia_title">Tunaathiri Sehemu Sita Muhimu</h2>
    <div class="impact-area-grid">
      @foreach([
        ['📚','Upatikanaji wa Maarifa','Knowledge Access','Kuwasaidia wakulima kupata taarifa za kilimo zilizothibitishwa wakati wanapoihitaji zaidi.','Helping farmers access verified agronomic information when they need it most.','ia0'],
        ['🛡️','Ulinzi wa Mazao','Crop Protection','Kutambua matatizo ya mazao mapema ili kupunguza hasara za mazao na gharama za kutibu.','Earlier identification of crop problems to reduce losses and treatment costs.','ia1'],
        ['📊','Uwazi wa Masoko','Market Transparency','Kuboresha ufikiaji wa bei za masoko na wanunuzi ili wakulima wapate thamani nzuri.','Improved access to prices and buyers so farmers receive fair market value.','ia2'],
        ['✅','Uaminifu wa Pembejeo','Input Trust','Kupunguza hatari ya pembejeo feki zinazosababisha kupoteza fedha na mazao.','Reducing the risk from counterfeit or unverified inputs causing financial loss.','ia3'],
        ['📱','Ushirikishaji wa Kidijitali','Digital Inclusion','Kusaidia wakulima walio katika maeneo yenye mtandao mdogo kupata huduma za kilimo.','Supporting farmers with weak internet connections to access agricultural services.','ia4'],
        ['💰','Mapato ya Mkulima','Farmer Income','Kuwasaidia wakulima kufanya maamuzi bora ya uzalishaji na kuuza ili kupata zaidi.','Helping farmers make better production and selling decisions to earn more.','ia5'],
      ] as $i => $area)
      <div class="impact-card color-{{ $i }} fade-up">
        <div class="card-icon">{{ $area[0] }}</div>
        <h3 data-i18n="{{ $area[5] }}_title">{{ $area[1] }}</h3>
        <p data-i18n="{{ $area[5] }}_desc">{{ $area[3] }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- Impact Metrics --}}
<section style="background:var(--surface-card); border-top:1px solid var(--border-light); padding:80px 0;">
  <div class="wrap">
    <div style="text-align:center; margin-bottom:48px;">
      <span class="eyebrow" data-i18n="metrics_eyebrow">VIPIMO VYA ATHARI</span>
      <h2 class="section-title" data-i18n="metrics_title">Tunafuatilia Kutoka Uzinduzi</h2>
      <p class="section-lead" style="margin:0 auto; text-align:center;" data-i18n="metrics_sub">Takwimu zifuatazo zitatokana na data halisi ya mfumo. Zinaanza kuhesabu tangu uzinduzi.</p>
    </div>
    <div style="border:1px solid var(--border-light); border-radius:var(--radius-xl); overflow:hidden;">
      <div class="metric-grid">
        @php
          $impactMetrics = [
            ['key'=>'metric_farmers','sw'=>'Wakulima Waliojisajili','en'=>'Farmers Registered','icon'=>'👨‍🌾'],
            ['key'=>'metric_scans','sw'=>'Plant Scans Zilizofanywa','en'=>'Plant Scans Completed','icon'=>'📷'],
            ['key'=>'metric_queries','sw'=>'Maswali ya AI Yalijibiwa','en'=>'AI Queries Answered','icon'=>'🤖'],
            ['key'=>'metric_regions','sw'=>'Mikoa Iliyofikiwa','en'=>'Regions Reached','icon'=>'🗺️'],
            ['key'=>'metric_markets','sw'=>'Miamala ya Soko','en'=>'Marketplace Transactions','icon'=>'🛒'],
          ];
        @endphp
        @foreach($impactMetrics as $m)
          @php $val = $settings[$m['key']] ?? null; @endphp
          <div class="metric-cell fade-up">
            <div style="font-size:2rem; margin-bottom:8px;">{{ $m['icon'] }}</div>
            <div class="metric-num">{{ $val ?? '—' }}</div>
            <div class="metric-label" data-i18n="im_{{ $m['key'] }}">{{ $m['sw'] }}</div>
            @if(!$val)
              <div class="metric-note" data-i18n="tracking_launch">Inaanza kuhesabu tangu uzinduzi</div>
            @endif
          </div>
        @endforeach
      </div>
    </div>
    <p style="text-align:center; font-size:.82rem; color:var(--ink-faint); margin-top:16px;" data-i18n="metrics_note">
      Vipimo hivi vinaonekana wakati wa uzinduzi rasmi wa mfumo na kusasishwa moja kwa moja.
    </p>
  </div>
</section>

{{-- Tanzania Map Section --}}
<section>
  <div class="wrap">
    <div class="region-map fade-up">
      <span class="eyebrow">MIKOA YA TANZANIA</span>
      <h2 class="section-title" data-i18n="map_title">Maeneo ya Athari ya MkulimaForum</h2>
      <p style="color:var(--ink-muted); max-width:36rem; margin:0 auto 8px; font-size:.92rem;" data-i18n="map_sub">Tunaendelea kupanua mfumo wetu Tanzania kote. Mikoa hii itatimia data ya kweli kutoka uzinduzi.</p>

      <div class="region-chips">
        @foreach(['Dodoma','Arusha','Dar es Salaam','Morogoro','Mbeya','Iringa','Kilimanjaro','Manyara','Tanga','Mwanza','Mara','Tabora','Shinyanga','Singida','Rukwa','Ruvuma','Lindi','Mtwara','Kagera','Kigoma'] as $region)
        <div class="region-chip">📍 {{ $region }}</div>
        @endforeach
      </div>
      <p style="margin-top:20px; font-size:.78rem; color:var(--ink-faint);" data-i18n="map_note">Ramani kamili ya athari itapatikana baada ya uzinduzi rasmi.</p>
    </div>
  </div>
</section>

{{-- Methodology --}}
<section style="background:var(--leaf-pale); padding:72px 0;">
  <div class="wrap" style="max-width:760px; text-align:center;">
    <span class="eyebrow" data-i18n="method_eyebrow">MBINU YETU</span>
    <h2 class="section-title" data-i18n="method_title">Tunakusudia Kupima Athari Kwa Uwazi</h2>
    <p class="section-lead" style="text-align:center; margin:0 auto 24px;" data-i18n="method_desc">
      MkulimaForum inaamini katika uwazi wa data. Vipimo vyetu vya athari vitatokana na data halisi ya mfumo — si makadirio au takwimu zilizobuniwa. Tunaendelea kushirikiana na washirika wa utafiti kufuatilia athari ya muda mrefu kwa wakulima.
    </p>
    <a href="/contact" class="btn btn-primary" data-i18n="method_cta">Shirikiana na Utafiti Wetu →</a>
  </div>
</section>

{{-- Investor CTA --}}
<section style="background:linear-gradient(135deg,#0E4220,var(--forest-dark)); color:#fff; padding:80px 0;">
  <div class="wrap" style="text-align:center; max-width:600px;">
    <h2 style="font-size:clamp(1.8rem,4vw,2.6rem); font-weight:900; color:#fff; margin-bottom:14px;" data-i18n="inv_title">Wekezaji: Unatafuta Athari ya Kweli?</h2>
    <p style="color:rgba(255,255,255,.82); margin-bottom:28px;" data-i18n="inv_sub">Tazama Pitch Deck yetu yenye maelezo ya muundo wa biashara, mkakati wa ukuaji, na athari tunazotarajiwa kwa wakulima wa Afrika Mashariki.</p>
    <div style="display:flex; gap:14px; flex-wrap:wrap; justify-content:center;">
      <a href="/pitch-deck" class="btn btn-gold btn-lg" data-i18n="inv_pitch_btn">📊 Tazama Pitch Deck</a>
      <a href="/contact" class="btn btn-ghost btn-lg" data-i18n="inv_contact_btn">Wasiliana Nasi →</a>
    </div>
  </div>
</section>

@endsection

@section('page_scripts')
<script>
const mkPageTranslations = {
  sw: {
    impact_eyebrow:'ATHARI YETU', impact_title:'Teknolojia Inayounda Mabadiliko ya Kweli ya Kilimo',
    impact_sub:'MkulimaForum iliundwa kuunda athari ya moja kwa moja ya kuonekana kwa maisha ya wakulima wadogo wadogo Tanzania na Afrika Mashariki.',
    ia_eyebrow:'MAENEO YA ATHARI', ia_title:'Tunaathiri Sehemu Sita Muhimu',
    ia0_title:'Upatikanaji wa Maarifa', ia0_desc:'Kuwasaidia wakulima kupata taarifa za kilimo zilizothibitishwa wakati wanapoihitaji zaidi.',
    ia1_title:'Ulinzi wa Mazao', ia1_desc:'Kutambua matatizo ya mazao mapema ili kupunguza hasara za mazao na gharama za kutibu.',
    ia2_title:'Uwazi wa Masoko', ia2_desc:'Kuboresha ufikiaji wa bei za masoko na wanunuzi ili wakulima wapate thamani nzuri.',
    ia3_title:'Uaminifu wa Pembejeo', ia3_desc:'Kupunguza hatari ya pembejeo feki zinazosababisha kupoteza fedha na mazao.',
    ia4_title:'Ushirikishaji wa Kidijitali', ia4_desc:'Kusaidia wakulima walio katika maeneo yenye mtandao mdogo kupata huduma za kilimo.',
    ia5_title:'Mapato ya Mkulima', ia5_desc:'Kuwasaidia wakulima kufanya maamuzi bora ya uzalishaji na kuuza ili kupata zaidi.',
    metrics_eyebrow:'VIPIMO VYA ATHARI', metrics_title:'Tunafuatilia Kutoka Uzinduzi',
    metrics_sub:'Takwimu zifuatazo zitatokana na data halisi ya mfumo. Zinaanza kuhesabu tangu uzinduzi.',
    im_metric_farmers:'Wakulima Waliojisajili', im_metric_scans:'Plant Scans Zilizofanywa',
    im_metric_queries:'Maswali ya AI Yalijibiwa', im_metric_regions:'Mikoa Iliyofikiwa', im_metric_markets:'Miamala ya Soko',
    tracking_launch:'Inaanza kuhesabu tangu uzinduzi',
    metrics_note:'Vipimo hivi vinaonekana wakati wa uzinduzi rasmi wa mfumo na kusasishwa moja kwa moja.',
    map_title:'Maeneo ya Athari ya MkulimaForum', map_sub:'Tunaendelea kupanua mfumo wetu Tanzania kote.',
    map_note:'Ramani kamili ya athari itapatikana baada ya uzinduzi rasmi.',
    method_eyebrow:'MBINU YETU', method_title:'Tunakusudia Kupima Athari Kwa Uwazi',
    method_desc:'MkulimaForum inaamini katika uwazi wa data. Vipimo vyetu vya athari vitatokana na data halisi ya mfumo — si makadirio au takwimu zilizobuniwa.',
    method_cta:'Shirikiana na Utafiti Wetu →',
    inv_title:'Wekezaji: Unatafuta Athari ya Kweli?',
    inv_sub:'Tazama Pitch Deck yetu yenye maelezo ya muundo wa biashara, mkakati wa ukuaji, na athari tunazotarajiwa.',
    inv_pitch_btn:'📊 Tazama Pitch Deck', inv_contact_btn:'Wasiliana Nasi →',
  },
  en: {
    impact_eyebrow:'OUR IMPACT', impact_title:'Technology Creating Measurable Agricultural Change',
    impact_sub:'MkulimaForum was designed to create direct, measurable impact on the livelihoods of Tanzania and East African smallholder farmers.',
    ia_eyebrow:'IMPACT AREAS', ia_title:'We Focus on Six Critical Impact Areas',
    ia0_title:'Knowledge Access', ia0_desc:'Helping farmers access verified agronomic information when they need it most.',
    ia1_title:'Crop Protection', ia1_desc:'Earlier identification of crop problems to reduce losses and treatment costs.',
    ia2_title:'Market Transparency', ia2_desc:'Improved access to prices and buyers so farmers receive fair market value.',
    ia3_title:'Input Trust', ia3_desc:'Reducing the risk from counterfeit or unverified inputs causing financial loss.',
    ia4_title:'Digital Inclusion', ia4_desc:'Supporting farmers with weak internet connections to access agricultural services.',
    ia5_title:'Farmer Income', ia5_desc:'Helping farmers make better production and selling decisions to earn more.',
    metrics_eyebrow:'IMPACT METRICS', metrics_title:'Tracking from Launch',
    metrics_sub:'The following figures are drawn from live system data and begin populating at launch.',
    im_metric_farmers:'Farmers Registered', im_metric_scans:'Plant Scans Completed',
    im_metric_queries:'AI Queries Answered', im_metric_regions:'Regions Reached', im_metric_markets:'Market Transactions',
    tracking_launch:'Tracking from launch',
    metrics_note:'These metrics populate automatically at official system launch and update in real time.',
    map_title:'MkulimaForum Reach Across Tanzania', map_sub:'We are expanding our platform across Tanzania. These regions will be populated with verified impact data from launch.',
    map_note:'Full interactive impact map will be available after official launch.',
    method_eyebrow:'OUR APPROACH', method_title:'We Measure Impact with Transparency',
    method_desc:'MkulimaForum believes in data transparency. Our impact metrics are drawn from live system data — not estimates or fabricated figures. We partner with research institutions to track long-term farmer impact.',
    method_cta:'Partner in Our Research →',
    inv_title:'Investors: Looking for Measurable Impact?',
    inv_sub:'View our pitch deck covering business model, growth strategy, and projected impact for East African smallholder farmers.',
    inv_pitch_btn:'📊 View Pitch Deck', inv_contact_btn:'Contact Us →',
  }
};
</script>
@endsection
