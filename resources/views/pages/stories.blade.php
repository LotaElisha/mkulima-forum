@extends('layouts.public')

@section('title', 'Farmer Stories | MkulimaForum Tanzania — Real Farming Challenges & Solutions')
@section('meta_description', 'Real farmers. Real challenges. Better decisions. Read how MkulimaForum is helping East African smallholder farmers through AI, markets, and knowledge access.')
@section('og_title', 'Farmer Stories | MkulimaForum Tanzania')
@section('og_description', 'Real farmers. Real challenges. Better farming decisions with MkulimaForum.')

@section('head_extra')
<style>
  /* Tokens, buttons, header and footer come from layouts/public.blade.php. */
  .story-category-bar { display:flex; gap:8px; flex-wrap:wrap; }
  .cat-btn {
    display:inline-flex; align-items:center; min-height:44px; padding:10px 16px;
    border-radius:999px; font-size:14px; font-weight:600;
    border:1px solid var(--border-mid); background:#fff; color:var(--ink-body);
    transition:background .15s ease, border-color .15s ease, color .15s ease;
  }
  .cat-btn:hover { background:var(--surface-soft); }
  .cat-btn.active { background:var(--leaf-pale); color:var(--forest-dark); border-color:var(--forest-mid); }

  /* Future CMS story cards */
  .stories-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; }
  @media(max-width:960px){ .stories-grid{ grid-template-columns:repeat(2,1fr); } }
  @media(max-width:560px) { .stories-grid{ grid-template-columns:1fr; } }
  .story-card { background:#fff; border:1px solid var(--border-light); border-radius:var(--radius-xl); overflow:hidden; }
  .story-card-img { background:var(--surface-soft); height:180px; display:flex; align-items:center; justify-content:center; font-size:48px; }
  .story-card-body { padding:24px; }
  .story-cat-badge { font-size:13px; font-weight:700; color:var(--forest-mid); text-transform:uppercase; letter-spacing:.08em; margin-bottom:10px; display:block; }
  .story-quote { color:var(--ink-body); line-height:1.65; font-size:15px; margin-bottom:16px; padding-left:14px; border-left:3px solid var(--forest-mid); }
  .story-person { display:flex; align-items:center; gap:12px; }
  .story-avatar { width:40px; height:40px; border-radius:50%; background:var(--leaf-pale); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
  .story-meta h4 { font-size:15px; font-weight:700; color:var(--ink-dark); }
  .story-meta p  { font-size:13px; color:var(--ink-muted); }

  /* Coming-soon panel */
  .submit-story-panel { background:var(--surface-soft); border:1px solid var(--border-light); border-radius:var(--radius-2xl); padding:56px 40px; text-align:center; }
  .soon-icon { width:64px; height:64px; margin:0 auto 20px; border-radius:16px; background:#fff; border:1px solid var(--border-light); color:var(--forest-mid); display:flex; align-items:center; justify-content:center; }
  .soon-title { font-size:clamp(22px,3vw,28px); font-weight:800; color:var(--ink-dark); margin-bottom:12px; }
  .soon-sub { color:var(--ink-muted); max-width:36rem; margin:0 auto 24px; font-size:16px; line-height:1.65; }
  .btn-row { display:flex; gap:12px; flex-wrap:wrap; justify-content:center; }

  /* Story format + share */
  .story-share-grid { display:grid; grid-template-columns:1fr 1fr; gap:48px; align-items:start; }
  @media(max-width:760px){ .story-share-grid{ grid-template-columns:1fr; gap:36px; } }
  .share-lead { color:var(--ink-muted); font-size:16px; margin-bottom:24px; line-height:1.65; }
  .format-steps { display:flex; flex-direction:column; gap:12px; }
  .format-step { display:flex; gap:16px; align-items:flex-start; padding:20px; background:#fff; border:1px solid var(--border-light); border-radius:var(--radius-xl); }
  .format-step .card-icon { margin:0; }
  .format-step h4 { font-size:16px; font-weight:700; color:var(--ink-dark); margin-bottom:4px; }
  .format-step p { font-size:15px; color:var(--ink-muted); }
  .share-actions { display:flex; flex-direction:column; gap:12px; }
  .share-note { margin-top:20px; padding:16px; background:var(--surface-soft); border-radius:12px; border:1px solid var(--border-light); display:flex; gap:10px; align-items:flex-start; }
  .share-note .ico { color:var(--forest-mid); margin-top:2px; }
  .share-note p { font-size:14px; color:var(--ink-muted); line-height:1.55; }

  /* Closing call to action: white, like the home page */
  .final-cta { padding:80px 0; text-align:center; background:#fff; border-top:1px solid var(--border-light); }
  .final-cta h2 { font-size:clamp(26px,3.6vw,36px); font-weight:800; margin-bottom:12px; letter-spacing:-.02em; }
  .final-cta p { max-width:540px; margin:0 auto 24px; color:var(--ink-muted); font-size:17px; }

  @media (max-width:700px) {
    .submit-story-panel { padding-left:18px !important; padding-right:18px !important; border-radius:16px; }
    .format-step { padding:16px; }
    .final-cta { padding:48px 0; }
    .final-cta p { font-size:16px; }
  }
</style>
@endsection

@section('content')

{{-- Hero --}}
<section class="page-hero">
  <div class="wrap fade-up">
    <span class="eyebrow" data-i18n="stories_eyebrow">HADITHI ZA WAKULIMA</span>
    <h1 class="page-title" data-i18n="stories_title">
      Wakulima wa Kweli.<br>Changamoto za Kweli.<br>Maamuzi Bora.
    </h1>
    <p class="section-lead" data-i18n="stories_sub">Hadithi za kweli za wakulima ambao MkulimaForum imewasaidia kupata taarifa bora, kulinda mazao yao, na kupata mazao mazuri zaidi.</p>
  </div>
</section>

{{-- Category Filter --}}
<section style="padding-top:32px; padding-bottom:0;">
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
<section style="padding-top:20px; padding-bottom:0;">
  <div class="wrap">
    <div class="submit-story-panel fade-up">
      <div class="soon-icon"><x-icon name="leaf" :size="30" /></div>
      <h2 class="soon-title" data-i18n="soon_title">Hadithi za Wakulima Zinakusanywa</h2>
      <p class="soon-sub" data-i18n="soon_sub">
        Tunakusanya na kuthibitisha hadithi halisi za wakulima wanaotumia MkulimaForum katika mazao yao ya kila siku. Hadithi zitaonekana hapa baada ya uthibitisho.
      </p>
      <div class="btn-row">
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
    <div class="story-share-grid">
      <div class="fade-up">
        <span class="eyebrow" data-i18n="format_eyebrow">MUUNDO WA HADITHI</span>
        <h2 class="section-title" data-i18n="format_title">Jinsi Tunavyoandika Hadithi</h2>
        <p class="share-lead" data-i18n="format_sub">Kila hadithi ya mkulima inaelezea safari ya kweli — changamoto, suluhisho, na matokeo.</p>
        <div class="format-steps">
        @foreach([
          ['search','Changamoto','Challenge','Mkulima alikuwa anakabiliwa na nini.','What the farmer was facing.','f0'],
          ['leaf','Suluhisho la MkulimaForum','MkulimaForum Solution','Kipengele gani kilisaidia.','Which feature helped.','f1'],
          ['check-circle','Matokeo','Outcome','Nini kilimabadilika.','What changed.','f2'],
        ] as $step)
        <div class="format-step">
          <div class="card-icon"><x-icon :name="$step[0]" :size="22" /></div>
          <div>
            <h4 data-i18n="{{ $step[5] }}_title">{{ $step[1] }}</h4>
            <p data-i18n="{{ $step[5] }}_desc">{{ $step[3] }}</p>
          </div>
        </div>
        @endforeach
        </div>
      </div>

      <div class="fade-up">
        <span class="eyebrow" data-i18n="share_eyebrow">SHIRIKI HADITHI YAKO</span>
        <h2 class="section-title" data-i18n="share_title">Je, Unatumia MkulimaForum?</h2>
        <p class="share-lead" data-i18n="share_sub">Ungependa kushiriki uzoefu wako ili kuwasaidia wakulima wengine? Wasiliana nasi na hadithi yako.</p>
        <div class="share-actions">
          <a href="/contact?type=story" class="btn btn-primary btn-lg" data-i18n="share_btn">🌾 Shiriki Hadithi Yangu</a>
          <a href="/solutions" class="btn btn-outline" data-i18n="share_solutions_btn">Gundua Suluhisho Zetu →</a>
        </div>
        <div class="share-note">
          <x-icon name="shield" :size="18" />
          <p data-i18n="share_note">
            Hadithi zote zinathibitishwa kabla ya kuchapishwa. Taarifa za kibinafsi zinalindwa na sera ya faragha ya MkulimaForum.
          </p>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- CTA --}}
<section class="final-cta">
  <div class="wrap">
    <h2 data-i18n="s_cta_title">Kuwa Sehemu ya Safari Yetu</h2>
    <p data-i18n="s_cta_sub">Pakua app, anza kuitumia, na ushiriki uzoefu wako ili kusaidia wakulima wengine Tanzania.</p>
    <div class="btn-row">
      <a href="/download" class="btn btn-primary btn-lg" data-i18n="s_cta_dl">⬇️ Pakua App</a>
      <a href="/impact" class="btn btn-outline btn-lg" data-i18n="s_cta_impact">Angalia Athari Zetu →</a>
    </div>
  </div>
</section>

@endsection

@section('page_scripts')
<script nonce="{{ $cspNonce ?? '' }}">
function filterStories(cat) {
  document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
  event.target.classList.add('active');
  // When stories are loaded from DB, filter here by data-category attribute
}

mkPageTranslations = {
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
