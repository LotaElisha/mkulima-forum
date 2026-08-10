@extends('layouts.public')

@section('title', 'Farmer Stories | MkulimaForum Tanzania — Real Farming Challenges & Solutions')
@section('meta_description', 'Real farmers. Real challenges. Better decisions. Read how MkulimaForum is helping East African smallholder farmers through AI, markets, and knowledge access.')
@section('og_title', 'Farmer Stories | MkulimaForum Tanzania')
@section('og_description', 'Real farmers. Real challenges. Better farming decisions with MkulimaForum.')

@section('head_extra')
<style>
  .story-category-bar { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:32px; }
  .cat-btn { padding:9px 18px; border-radius:999px; font-size:.85rem; font-weight:700; border:1.5px solid var(--border-mid); background:transparent; color:var(--ink-muted); cursor:pointer; transition:all .18s ease; }
  .cat-btn:hover, .cat-btn.active { background:var(--forest-dark); color:#fff; border-color:var(--forest-dark); }
  .stories-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:28px; }
  @media(max-width:960px){ .stories-grid{ grid-template-columns:repeat(2,1fr); } }
  @media(max-width:560px) { .stories-grid{ grid-template-columns:1fr; } }
  .story-card {
    background:var(--surface-card); border:1px solid var(--border-light); border-radius:var(--radius-xl);
    overflow:hidden; transition:all .25s ease;
  }
  .story-card:hover { transform:translateY(-4px); box-shadow:var(--shadow-md); }
  .story-card-img { background:linear-gradient(145deg,var(--leaf-pale),#D6EEC8); height:180px; display:flex; align-items:center; justify-content:center; font-size:4rem; }
  .story-card-body { padding:28px; }
  .story-cat-badge { font-size:.7rem; font-weight:800; color:var(--forest-mid); text-transform:uppercase; letter-spacing:.1em; margin-bottom:10px; display:block; }
  .story-quote { font-style:italic; color:var(--ink-body); line-height:1.7; font-size:.95rem; margin-bottom:16px; position:relative; padding-left:16px; border-left:3px solid var(--leaf-green); }
  .story-person { display:flex; align-items:center; gap:12px; }
  .story-avatar { width:40px; height:40px; border-radius:50%; background:var(--leaf-pale); display:flex; align-items:center; justify-content:center; font-size:1.2rem; border:2px solid var(--border-light); flex-shrink:0; }
  .story-meta h4 { font-size:.88rem; font-weight:800; color:var(--ink-dark); }
  .story-meta p  { font-size:.78rem; color:var(--ink-muted); }

  .submit-story-panel { background:linear-gradient(145deg,var(--leaf-pale),#D6EEC8); border-radius:var(--radius-2xl); padding:56px; text-align:center; border:1.5px dashed var(--border-mid); }
</style>
@endsection

@section('content')

{{-- Hero --}}
<section class="page-hero">
  <div class="wrap fade-up" style="max-width:700px;">
    <span class="eyebrow" data-i18n="stories_eyebrow">HADITHI ZA WAKULIMA</span>
    <h1 class="page-title" data-i18n="stories_title" style="font-size:clamp(2.2rem,5vw,3.4rem);">
      Wakulima wa Kweli.<br>Changamoto za Kweli.<br>Maamuzi Bora.
    </h1>
    <p class="section-lead" data-i18n="stories_sub">Hadithi za kweli za wakulima ambao MkulimaForum imewasaidia kupata taarifa bora, kulinda mazao yao, na kupata mazao mazuri zaidi.</p>
  </div>
</section>

{{-- Category Filter --}}
<section style="padding-top:0; padding-bottom:20px;">
  <div class="wrap">
    <div class="story-category-bar">
      @foreach([
        ['all','Zote Zote','All Stories'],
        ['diagnosis','Utambuzi wa Magonjwa','Crop Diagnosis'],
        ['market','Ufikiaji wa Soko','Market Access'],
        ['weather','Hali ya Hewa','Weather'],
        ['input','Kagua Pembejeo','Input Verification'],
        ['advice','Ushauri wa Kilimo','Agronomy Advice'],
      ] as $cat)
      <button class="cat-btn {{ $loop->first ? 'active' : '' }}" onclick="filterStories('{{ $cat[0] }}')" data-i18n="cat_{{ $cat[0] }}">{{ $cat[1] }}</button>
      @endforeach
    </div>
  </div>
</section>

{{-- Coming-soon placeholder --}}
<section style="padding-top:20px;">
  <div class="wrap">
    <div class="submit-story-panel fade-up">
      <div style="font-size:4rem; margin-bottom:20px;">🌾</div>
      <h2 style="font-size:1.8rem; font-weight:800; color:var(--forest-dark); margin-bottom:14px;" data-i18n="soon_title">Hadithi za Wakulima Zinakusanywa</h2>
      <p style="color:var(--ink-muted); max-width:36rem; margin:0 auto 24px; font-size:1rem; line-height:1.7;" data-i18n="soon_sub">
        Tunakusanya na kuthibitisha hadithi halisi za wakulima wanaotumia MkulimaForum katika mazao yao ya kila siku. Hadithi zitaonekana hapa baada ya uthibitisho.
      </p>
      <div style="display:flex; gap:12px; flex-wrap:wrap; justify-content:center;">
        <a href="/contact" class="btn btn-primary" data-i18n="soon_share_btn">Shiriki Hadithi Yako</a>
        <a href="/solutions" class="btn btn-outline" data-i18n="soon_solutions_btn">Gundua Suluhisho Zetu →</a>
      </div>
    </div>
  </div>
</section>

{{-- Story card template (hidden, shows when DB stories are populated) --}}
{{-- Example structure for future CMS integration: --}}
{{--
@foreach($stories as $story)
<div class="story-card">
  <div class="story-card-img">🌾</div>
  <div class="story-card-body">
    <span class="story-cat-badge">{{ $story->category }}</span>
    <div class="story-quote">{{ $story->quote }}</div>
    <div class="story-person">
      <div class="story-avatar">👤</div>
      <div class="story-meta">
        <h4>{{ $story->farmer_name }}</h4>
        <p>{{ $story->location }} • {{ $story->primary_crop }}</p>
      </div>
    </div>
  </div>
</div>
@endforeach
--}}

{{-- How to be featured --}}
<section>
  <div class="wrap">
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:40px; align-items:start;">
      <div class="fade-up">
        <span class="eyebrow" data-i18n="format_eyebrow">MUUNDO WA HADITHI</span>
        <h2 class="section-title" data-i18n="format_title">Jinsi Tunavyoandika Hadithi</h2>
        <p style="color:var(--ink-muted); margin-bottom:24px; line-height:1.7;" data-i18n="format_sub">Kila hadithi ya mkulima inaelezea safari ya kweli — changamoto, suluhisho, na matokeo.</p>
        @foreach([
          ['🔍','Changamoto','Challenge','Mkulima alikuwa anakabiliwa na nini.','What the farmer was facing.','f0'],
          ['⚡','Suluhisho la MkulimaForum','MkulimaForum Solution','Kipengele gani kilisaidia.','Which feature helped.','f1'],
          ['✅','Matokeo','Outcome','Nini kilimabadilika.','What changed.','f2'],
        ] as $step)
        <div style="display:flex; gap:16px; margin-bottom:20px; padding:20px; background:var(--leaf-pale); border-radius:14px; border:1px solid var(--border-light);">
          <div style="font-size:1.8rem; flex-shrink:0; width:48px; text-align:center;">{{ $step[0] }}</div>
          <div>
            <h4 style="font-size:1rem; font-weight:800; color:var(--ink-dark); margin-bottom:4px;" data-i18n="{{ $step[5] }}_title">{{ $step[1] }}</h4>
            <p style="font-size:.88rem; color:var(--ink-muted);" data-i18n="{{ $step[5] }}_desc">{{ $step[3] }}</p>
          </div>
        </div>
        @endforeach
      </div>

      <div class="fade-up">
        <span class="eyebrow" data-i18n="share_eyebrow">SHIRIKI HADITHI YAKO</span>
        <h2 class="section-title" data-i18n="share_title">Je, Unatumia MkulimaForum?</h2>
        <p style="color:var(--ink-muted); margin-bottom:28px; line-height:1.7;" data-i18n="share_sub">Ungependa kushiriki uzoefu wako ili kuwasaidia wakulima wengine? Wasiliana nasi na hadithi yako.</p>
        <div style="display:flex; flex-direction:column; gap:14px;">
          <a href="/contact?type=story" class="btn btn-primary btn-lg" data-i18n="share_btn">🌾 Shiriki Hadithi Yangu</a>
          <a href="/solutions" class="btn btn-outline" data-i18n="share_solutions_btn">Gundua Suluhisho Zetu →</a>
        </div>
        <div style="margin-top:24px; padding:16px; background:var(--leaf-pale); border-radius:12px; border:1px solid var(--border-light);">
          <p style="font-size:.82rem; color:var(--ink-muted);" data-i18n="share_note">
            Hadithi zote zinathibitishwa kabla ya kuchapishwa. Taarifa za kibinafsi zinalindwa na sera ya faragha ya MkulimaForum.
          </p>
        </div>
      </div>
    </div>
    @media(max-width:760px){ .grid-2{ grid-template-columns:1fr !important; } }
  </div>
</section>

{{-- CTA --}}
<section style="background:linear-gradient(135deg,#0E4220,var(--forest-dark)); color:#fff; padding:72px 0;">
  <div class="wrap" style="text-align:center; max-width:580px;">
    <h2 style="font-size:clamp(1.8rem,4vw,2.4rem); font-weight:900; color:#fff; margin-bottom:14px;" data-i18n="s_cta_title">Kuwa Sehemu ya Safari Yetu</h2>
    <p style="color:rgba(255,255,255,.82); margin-bottom:28px;" data-i18n="s_cta_sub">Pakua app, anza kuitumia, na ushiriki uzoefu wako ili kusaidia wakulima wengine Tanzania.</p>
    <div style="display:flex; gap:14px; flex-wrap:wrap; justify-content:center;">
      <a href="/app/mkulima-forum.apk" class="btn btn-gold btn-lg" data-i18n="s_cta_dl">⬇️ Pakua App</a>
      <a href="/impact" class="btn btn-ghost btn-lg" data-i18n="s_cta_impact">Angalia Athari Zetu →</a>
    </div>
  </div>
</section>

@endsection

@section('page_scripts')
<script>
function filterStories(cat) {
  document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
  event.target.classList.add('active');
  // When stories are loaded from DB, filter here by data-category attribute
}

const mkPageTranslations = {
  sw: {
    stories_eyebrow:'HADITHI ZA WAKULIMA',
    stories_title:'Wakulima wa Kweli.\nChangamoto za Kweli.\nMaamuzi Bora.',
    stories_sub:'Hadithi za kweli za wakulima ambao MkulimaForum imewasaidia kupata taarifa bora, kulinda mazao yao, na kupata mazao mazuri zaidi.',
    cat_all:'Zote Zote', cat_diagnosis:'Utambuzi wa Magonjwa', cat_market:'Ufikiaji wa Soko',
    cat_weather:'Hali ya Hewa', cat_input:'Kagua Pembejeo', cat_advice:'Ushauri wa Kilimo',
    soon_title:'Hadithi za Wakulima Zinakusanywa',
    soon_sub:'Tunakusanya na kuthibitisha hadithi halisi za wakulima wanaotumia MkulimaForum. Hadithi zitaonekana hapa baada ya uthibitisho.',
    soon_share_btn:'Shiriki Hadithi Yako', soon_solutions_btn:'Gundua Suluhisho Zetu →',
    format_eyebrow:'MUUNDO WA HADITHI', format_title:'Jinsi Tunavyoandika Hadithi',
    format_sub:'Kila hadithi ya mkulima inaelezea safari ya kweli — changamoto, suluhisho, na matokeo.',
    f0_title:'Changamoto', f0_desc:'Mkulima alikuwa anakabiliwa na nini.',
    f1_title:'Suluhisho la MkulimaForum', f1_desc:'Kipengele gani kilisaidia.',
    f2_title:'Matokeo', f2_desc:'Nini kilimabadilika.',
    share_eyebrow:'SHIRIKI HADITHI YAKO', share_title:'Je, Unatumia MkulimaForum?',
    share_sub:'Ungependa kushiriki uzoefu wako ili kuwasaidia wakulima wengine? Wasiliana nasi na hadithi yako.',
    share_btn:'🌾 Shiriki Hadithi Yangu', share_solutions_btn:'Gundua Suluhisho Zetu →',
    share_note:'Hadithi zote zinathibitishwa kabla ya kuchapishwa. Taarifa za kibinafsi zinalindwa na sera ya faragha ya MkulimaForum.',
    s_cta_title:'Kuwa Sehemu ya Safari Yetu', s_cta_sub:'Pakua app, anza kuitumia, na ushiriki uzoefu wako ili kusaidia wakulima wengine Tanzania.',
    s_cta_dl:'⬇️ Pakua App', s_cta_impact:'Angalia Athari Zetu →',
  },
  en: {
    stories_eyebrow:'FARMER STORIES',
    stories_title:'Real Farmers.\nReal Challenges.\nBetter Decisions.',
    stories_sub:'Authentic accounts from farmers who MkulimaForum has helped to access better information, protect their crops, and make more from their harvests.',
    cat_all:'All Stories', cat_diagnosis:'Crop Diagnosis', cat_market:'Market Access',
    cat_weather:'Weather', cat_input:'Input Verification', cat_advice:'Agronomy Advice',
    soon_title:"Farmer Stories Are Being Collected",
    soon_sub:'We are collecting and verifying authentic stories from farmers using MkulimaForum in their daily farming. Stories will appear here after verification.',
    soon_share_btn:'Share Your Story', soon_solutions_btn:'Explore Our Solutions →',
    format_eyebrow:'STORY FORMAT', format_title:'How We Structure Each Story',
    format_sub:'Every farmer story describes a real journey — the challenge, the solution, and what changed.',
    f0_title:'Challenge', f0_desc:'What the farmer was facing.',
    f1_title:'MkulimaForum Solution', f1_desc:'Which feature or capability helped.',
    f2_title:'Outcome', f2_desc:'What changed as a result.',
    share_eyebrow:'SHARE YOUR STORY', share_title:'Are You Using MkulimaForum?',
    share_sub:"Would you like to share your experience to help other farmers? Get in touch with your story.",
    share_btn:'🌾 Share My Story', share_solutions_btn:'Explore Our Solutions →',
    share_note:'All stories are verified before publication. Personal information is protected by the MkulimaForum Privacy Policy.',
    s_cta_title:'Be Part of Our Journey', s_cta_sub:'Download the app, start using it, and share your experience to help other farmers across Tanzania.',
    s_cta_dl:'⬇️ Download App', s_cta_impact:'View Our Impact →',
  }
};
</script>
@endsection
