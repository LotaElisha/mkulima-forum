@extends('layouts.public')

@section('title', 'MkulimaForum Impact | Technology Creating Measurable Agricultural Change')
@section('meta_description', 'Discover the impact MkulimaForum creates for East African smallholder farmers — from knowledge access and crop protection to market transparency and digital inclusion.')
@section('og_title', 'MkulimaForum Impact | Technology for African Farmers')
@section('og_description', 'AI technology creating measurable agricultural impact for Tanzania smallholder farmers.')

@section('head_extra')
<style>
  /* Tokens, buttons, header and footer come from layouts/public.blade.php.
     This block only lays out the impact page's own sections. */
  .ico { width:1.15em; height:1.15em; flex:none; stroke-width:2; }
  .hero-inner { max-width:720px; }
  .center-head { text-align:center; margin-bottom:40px; }
  .center-head .section-lead { margin:0 auto; }

  /* Impact areas */
  .impact-area-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }
  .impact-card { background:#fff; border:1px solid var(--border-light); border-radius:var(--radius-xl); padding:28px; }
  .impact-card h3 { font-size:18px; font-weight:700; color:var(--ink-dark); margin-bottom:8px; }
  .impact-card p { font-size:15px; color:var(--ink-muted); line-height:1.6; }

  /* Metrics */
  .metrics-band { background:var(--surface-soft); border-top:1px solid var(--border-light); border-bottom:1px solid var(--border-light); }
  .metric-grid { display:grid; grid-template-columns:repeat(5,1fr); gap:12px; }
  .metric-cell { background:#fff; border:1px solid var(--border-light); border-radius:var(--radius-xl); padding:24px 16px; text-align:center; display:flex; flex-direction:column; align-items:center; }
  .metric-cell .card-icon { margin-bottom:12px; }
  .metric-num { font-size:32px; font-weight:800; color:var(--forest-dark); line-height:1.2; }
  .metric-label { font-size:13px; font-weight:700; color:var(--ink-body); text-transform:uppercase; letter-spacing:.06em; margin-top:6px; line-height:1.4; }
  .metric-note { font-size:13px; color:var(--ink-muted); margin-top:6px; line-height:1.4; }
  .metrics-foot { text-align:center; font-size:14px; color:var(--ink-muted); margin-top:20px; }

  /* Regions */
  .region-map { background:#fff; border:1px solid var(--border-light); border-radius:var(--radius-xl); padding:48px; text-align:center; }
  .region-map > p { color:var(--ink-muted); max-width:36rem; margin:0 auto 8px; font-size:16px; }
  .region-chips { display:flex; flex-wrap:wrap; gap:8px; justify-content:center; margin-top:24px; }
  .region-chip { display:inline-flex; align-items:center; gap:8px; padding:6px 14px; background:var(--surface-soft); border:1px solid var(--border-light); border-radius:999px; font-size:14px; font-weight:600; color:var(--ink-body); }
  .region-chip::before { content:''; width:6px; height:6px; border-radius:50%; background:var(--forest-mid); flex-shrink:0; }
  .region-map .map-note { margin-top:20px; font-size:14px; color:var(--ink-muted); }

  /* Methodology */
  .method-band { background:var(--surface-soft); border-top:1px solid var(--border-light); border-bottom:1px solid var(--border-light); }
  .method-band .wrap { max-width:760px; text-align:center; }
  .method-band .section-lead { margin:0 auto 24px; }

  /* Closing call to action */
  .final-cta { padding:80px 0; text-align:center; background:#fff; }
  .final-cta .wrap { max-width:640px; }
  .final-cta h2 { font-size:clamp(26px,3.6vw,38px); font-weight:800; margin-bottom:12px; letter-spacing:-.02em; }
  .final-cta p { margin:0 auto 24px; color:var(--ink-muted); font-size:17px; }
  .cta-actions { display:flex; gap:12px; flex-wrap:wrap; justify-content:center; }

  @media(max-width:960px){
    .impact-area-grid { grid-template-columns:repeat(2,1fr); }
    .metric-grid { grid-template-columns:repeat(3,1fr); }
  }
  @media(max-width:700px){
    .center-head { margin-bottom:24px; }
    .impact-area-grid { grid-template-columns:1fr; gap:10px; }
    .impact-card { padding:18px; }
    .metric-grid { grid-template-columns:1fr 1fr; gap:10px; }
    .metric-cell { padding:16px 10px; }
    .metric-cell:last-child { grid-column:1 / -1; }
    .metric-num { font-size:26px; }
    .region-map { padding:24px 16px; }
    .region-map > p { font-size:15px; }
    .final-cta { padding:48px 0; }
    .final-cta p { font-size:16px; }
    .cta-actions .btn { white-space:normal; }
  }
</style>
@endsection

@section('content')

{{-- Hero --}}
<section class="page-hero">
  <div class="wrap fade-up"><div class="hero-inner">
    <span class="eyebrow" data-i18n="impact_eyebrow">ATHARI YETU</span>
    <h1 class="page-title" data-i18n="impact_title">Teknolojia Inayounda Mabadiliko ya Kweli ya Kilimo</h1>
    <p class="section-lead" data-i18n="impact_sub">MkulimaForum iliundwa kuunda athari ya moja kwa moja ya kuonekana kwa maisha ya wakulima wadogo wadogo Tanzania na Afrika Mashariki.</p>
  </div></div>
</section>

{{-- Impact Areas --}}
<section>
  <div class="wrap">
    <span class="eyebrow" data-i18n="ia_eyebrow">MAENEO YA ATHARI</span>
    <h2 class="section-title" style="margin-bottom:32px;" data-i18n="ia_title">Tunaathiri Sehemu Sita Muhimu</h2>
    <div class="impact-area-grid">
      @foreach([
        ['book','Upatikanaji wa Maarifa','Knowledge Access','Kuwasaidia wakulima kupata taarifa za kilimo zilizothibitishwa wakati wanapoihitaji zaidi.','Helping farmers access verified agronomic information when they need it most.','ia0'],
        ['shield','Ulinzi wa Mazao','Crop Protection','Kutambua matatizo ya mazao mapema ili kupunguza hasara za mazao na gharama za kutibu.','Earlier identification of crop problems to reduce losses and treatment costs.','ia1'],
        ['search','Uwazi wa Masoko','Market Transparency','Kuboresha ufikiaji wa bei za masoko na wanunuzi ili wakulima wapate thamani nzuri.','Improved access to prices and buyers so farmers receive fair market value.','ia2'],
        ['verified','Uaminifu wa Pembejeo','Input Trust','Kupunguza hatari ya pembejeo feki zinazosababisha kupoteza fedha na mazao.','Reducing the risk from counterfeit or unverified inputs causing financial loss.','ia3'],
        ['phone','Ushirikishaji wa Kidijitali','Digital Inclusion','Kusaidia wakulima walio katika maeneo yenye mtandao mdogo kupata huduma za kilimo.','Supporting farmers with weak internet connections to access agricultural services.','ia4'],
        ['chart','Mapato ya Mkulima','Farmer Income','Kuwasaidia wakulima kufanya maamuzi bora ya uzalishaji na kuuza ili kupata zaidi.','Helping farmers make better production and selling decisions to earn more.','ia5'],
      ] as $i => $area)
      <div class="impact-card fade-up">
        <div class="card-icon"><x-icon :name="$area[0]" size="24" /></div>
        <h3 data-i18n="{{ $area[5] }}_title">{{ $area[1] }}</h3>
        <p data-i18n="{{ $area[5] }}_desc">{{ $area[3] }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- Impact Metrics --}}
<section class="metrics-band">
  <div class="wrap">
    <div class="center-head">
      <span class="eyebrow" data-i18n="metrics_eyebrow">VIPIMO VYA ATHARI</span>
      <h2 class="section-title" data-i18n="metrics_title">Tunafuatilia Kutoka Uzinduzi</h2>
      <p class="section-lead" data-i18n="metrics_sub">Takwimu zifuatazo zitatokana na data halisi ya mfumo. Zinaanza kuhesabu tangu uzinduzi.</p>
    </div>
    <div>
      <div class="metric-grid">
        @php
          $impactMetrics = [
            ['key'=>'metric_farmers','sw'=>'Wakulima Waliojisajili','en'=>'Farmers Registered','icon'=>'groups'],
            ['key'=>'metric_scans','sw'=>'Plant Scans Zilizofanywa','en'=>'Plant Scans Completed','icon'=>'scan'],
            ['key'=>'metric_queries','sw'=>'Maswali ya AI Yalijibiwa','en'=>'AI Queries Answered','icon'=>'book'],
            ['key'=>'metric_regions','sw'=>'Mikoa Iliyofikiwa','en'=>'Regions Reached','icon'=>'globe'],
            ['key'=>'metric_markets','sw'=>'Miamala ya Soko','en'=>'Marketplace Transactions','icon'=>'storefront'],
          ];
        @endphp
        @foreach($impactMetrics as $m)
          @php $val = $settings[$m['key']] ?? null; @endphp
          <div class="metric-cell fade-up">
            <div class="card-icon"><x-icon :name="$m['icon']" size="24" /></div>
            <div class="metric-num">{{ $val ?? '—' }}</div>
            <div class="metric-label" data-i18n="im_{{ $m['key'] }}">{{ $m['sw'] }}</div>
            @if(!$val)
              <div class="metric-note" data-i18n="tracking_launch">Inaanza kuhesabu tangu uzinduzi</div>
            @endif
          </div>
        @endforeach
      </div>
    </div>
    <p class="metrics-foot" data-i18n="metrics_note">
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
      <p data-i18n="map_sub">Tunaendelea kupanua mfumo wetu Tanzania kote. Mikoa hii itatimia data ya kweli kutoka uzinduzi.</p>

      <div class="region-chips">
        @foreach(['Dodoma','Arusha','Dar es Salaam','Morogoro','Mbeya','Iringa','Kilimanjaro','Manyara','Tanga','Mwanza','Mara','Tabora','Shinyanga','Singida','Rukwa','Ruvuma','Lindi','Mtwara','Kagera','Kigoma'] as $region)
        <div class="region-chip">{{ $region }}</div>
        @endforeach
      </div>
      <p class="map-note" data-i18n="map_note">Ramani kamili ya athari itapatikana baada ya uzinduzi rasmi.</p>
    </div>
  </div>
</section>

{{-- Methodology --}}
<section class="method-band">
  <div class="wrap">
    <span class="eyebrow" data-i18n="method_eyebrow">MBINU YETU</span>
    <h2 class="section-title" data-i18n="method_title">Tunakusudia Kupima Athari Kwa Uwazi</h2>
    <p class="section-lead" data-i18n="method_desc">
      MkulimaForum inaamini katika uwazi wa data. Vipimo vyetu vya athari vitatokana na data halisi ya mfumo — si makadirio au takwimu zilizobuniwa. Tunaendelea kushirikiana na washirika wa utafiti kufuatilia athari ya muda mrefu kwa wakulima.
    </p>
    <a href="/contact" class="btn btn-primary" data-i18n="method_cta">Shirikiana na Utafiti Wetu →</a>
  </div>
</section>

{{-- Investor CTA --}}
<section class="final-cta">
  <div class="wrap">
    <h2 data-i18n="inv_title">Wekezaji: Unatafuta Athari ya Kweli?</h2>
    <p data-i18n="inv_sub">Tazama Pitch Deck yetu yenye maelezo ya muundo wa biashara, mkakati wa ukuaji, na athari tunazotarajiwa kwa wakulima wa Afrika Mashariki.</p>
    <div class="cta-actions">
      <a href="/pitch-deck" class="btn btn-primary btn-lg" data-i18n="inv_pitch_btn">📊 Tazama Pitch Deck</a>
      <a href="/contact" class="btn btn-outline btn-lg" data-i18n="inv_contact_btn">Wasiliana Nasi →</a>
    </div>
  </div>
</section>

@endsection

@section('page_scripts')
<script nonce="{{ $cspNonce ?? '' }}">
mkPageTranslations = {
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
