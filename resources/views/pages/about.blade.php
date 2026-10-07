@extends('layouts.public')

@section('title', 'About MkulimaForum | Building Digital Agriculture for Africa')
@section('meta_description', 'MkulimaForum is an AI-powered digital agriculture ecosystem. Learn about our mission, vision, principles, and why we are building for East African farmers.')
@section('og_title', 'About MkulimaForum | Digital Agriculture for Africa')
@section('og_description', 'Our mission: make practical agricultural intelligence accessible to every farmer, regardless of location, income, language, or internet connectivity.')

@section('head_extra')
<style>
  /* Tokens, buttons, header and footer come from layouts/public.blade.php.
     This block only lays out the about page's own sections. */
  .ico { width:1.15em; height:1.15em; flex:none; stroke-width:2; }
  .about-hero-inner { max-width: 720px; }
  .center-head { text-align:center; margin-bottom:40px; }
  .center-head .section-lead { margin:0 auto; }

  /* Story */
  .tz-grid { display:grid; grid-template-columns:1fr 1fr; gap:48px; align-items:center; }
  .tz-grid > * { min-width:0; }
  .story-copy p { color:var(--ink-body); font-size:16px; line-height:1.75; }
  .story-copy p + p { margin-top:16px; }
  .problem-card { background:var(--surface-soft); border:1px solid var(--border-light); border-radius:var(--radius-xl); padding:32px; }
  .problem-card h3 { font-size:20px; font-weight:700; color:var(--ink-dark); margin-bottom:16px; }
  .problem-list { display:flex; flex-direction:column; gap:10px; }
  .problem-item { display:flex; align-items:center; gap:12px; padding:12px 14px; background:#fff; border:1px solid var(--border-light); border-radius:12px; }
  .problem-item .ico { color:var(--forest-mid); width:20px; height:20px; }
  .problem-item span { font-size:15px; font-weight:600; color:var(--ink-dark); line-height:1.4; }

  /* Mission & vision */
  .mv-band { background:var(--surface-soft); border-top:1px solid var(--border-light); border-bottom:1px solid var(--border-light); }
  .mission-vision-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
  .mv-card { background:#fff; border:1px solid var(--border-light); border-left:4px solid var(--forest-mid); border-radius:var(--radius-xl); padding:32px; }
  .mv-card.vision { border-left-color:var(--sun-amber); }
  .mv-card h3 { font-size:13px; font-weight:700; color:var(--forest-mid); margin-bottom:12px; text-transform:uppercase; letter-spacing:.08em; }
  .mv-card.vision h3 { color:var(--sun-gold); }
  .mv-card p { color:var(--ink-body); line-height:1.7; font-size:17px; }

  /* Principles */
  .principle-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
  .principle-card { background:#fff; border:1px solid var(--border-light); border-radius:var(--radius-xl); padding:24px; }
  .principle-card h3 { font-size:17px; font-weight:700; color:var(--ink-dark); margin-bottom:6px; }
  .principle-card p { font-size:15px; color:var(--ink-muted); line-height:1.6; }

  /* Why Tanzania */
  .tz-card { background:var(--surface-soft); border:1px solid var(--border-light); border-radius:var(--radius-xl); padding:40px; }
  .tz-card h3 { font-size:24px; font-weight:800; color:var(--ink-dark); margin-bottom:12px; letter-spacing:-.02em; }
  .tz-card p { color:var(--ink-muted); font-size:16px; line-height:1.7; }
  .tz-pills { display:flex; flex-wrap:wrap; gap:10px; margin-top:20px; }
  .tz-pill { display:inline-flex; align-items:center; padding:8px 14px; background:#fff; border:1px solid var(--border-light); border-radius:999px; font-size:14px; font-weight:600; color:var(--ink-body); line-height:1.35; }

  /* Technology */
  .tech-band { background:var(--surface-soft); border-top:1px solid var(--border-light); border-bottom:1px solid var(--border-light); }
  .tech-approach-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-top:32px; }
  .tech-chip { display:flex; align-items:center; gap:12px; min-height:56px; padding:12px 16px; background:#fff; border:1px solid var(--border-light); border-radius:var(--radius-lg); font-size:15px; font-weight:600; color:var(--ink-dark); }
  .tech-chip .ico { color:var(--forest-mid); width:20px; height:20px; }
  .tech-cta { margin-top:28px; }

  /* Team */
  .team-head { display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:16px; margin-bottom:32px; }
  .team-head .section-title { margin-bottom:0; }
  .team-note { font-size:14px; color:var(--ink-muted); max-width:28rem; }
  .team-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }
  .team-card { background:#fff; border:1px solid var(--border-light); border-radius:var(--radius-xl); padding:28px; text-align:center; }
  .team-avatar { width:64px; height:64px; border-radius:50%; background:var(--leaf-pale); color:var(--forest-mid); margin:0 auto 14px; display:flex; align-items:center; justify-content:center; }
  .team-avatar .ico { width:28px; height:28px; }
  .team-card h4 { font-size:17px; font-weight:700; color:var(--ink-dark); margin-bottom:4px; }
  .team-card .role { font-size:13px; font-weight:700; color:var(--forest-mid); margin-bottom:8px; text-transform:uppercase; letter-spacing:.06em; }
  .team-card p { font-size:14px; color:var(--ink-muted); line-height:1.55; }

  /* Closing call to action */
  .final-cta { padding:80px 0; text-align:center; background:#fff; border-top:1px solid var(--border-light); }
  .final-cta .wrap { max-width:680px; }
  .final-cta h2 { font-size:clamp(26px,3.6vw,38px); font-weight:800; margin-bottom:12px; letter-spacing:-.02em; }
  .final-cta p { margin:0 auto 24px; color:var(--ink-muted); font-size:17px; }
  .cta-actions { display:flex; gap:12px; flex-wrap:wrap; justify-content:center; }

  @media(max-width:1020px){ .principle-grid{ grid-template-columns:repeat(2,1fr); } }
  @media(max-width:900px) { .tech-approach-grid{ grid-template-columns:repeat(2,1fr); } .team-grid{ grid-template-columns:repeat(2,1fr); } }
  @media(max-width:780px) { .tz-grid{ grid-template-columns:1fr; gap:28px; } .mission-vision-grid{ grid-template-columns:1fr; } }
  @media(max-width:700px) {
    .center-head { margin-bottom:24px; }
    .problem-card, .tz-card { padding:20px; }
    .mv-card { padding:20px; }
    .mv-card p { font-size:16px; }
    .principle-grid, .tech-approach-grid, .team-grid { grid-template-columns:1fr; gap:10px; }
    .tz-card h3 { font-size:21px; }
    .tz-flip > .tz-card { order:2; }
    .tech-approach-grid { margin-top:20px; }
    .team-head { margin-bottom:20px; }
    .final-cta { padding:48px 0; }
    .final-cta p { font-size:16px; }
    .cta-actions .btn { white-space:normal; }
  }
</style>
@endsection

@section('content')

{{-- Hero --}}
<section class="page-hero">
  <div class="wrap">
    <div class="about-hero-inner fade-up">
      <span class="eyebrow" data-i18n="about_eyebrow">KUHUSU MKULIMAFORUM</span>
      <h1 class="page-title" data-i18n="about_hero_title">Teknolojia Inayojengwa Kumzunguka Mkulima</h1>
      <p class="section-lead" data-i18n="about_hero_sub">MkulimaForum ni mfumo wa kidigitali wa kilimo unaotumia AI, uliobuniwa kuunganisha wakulima na maarifa, masoko, pembejeo zinazoaminika, utabiri wa hali ya hewa, na msaada wa kilimo.</p>
    </div>
  </div>
</section>

{{-- Our Story --}}
<section>
  <div class="wrap">
    <div class="tz-grid fade-up">
      <div class="story-copy">
        <span class="eyebrow" data-i18n="story_eyebrow">HADITHI YETU</span>
        <h2 class="section-title" data-i18n="story_title">Kwa Nini Tulijenga MkulimaForum</h2>
        <p data-i18n="story_p1">
          Mamilioni ya wakulima wadogo wadogo nchini Tanzania na Afrika Mashariki bado hufanya maamuzi muhimu ya kilimo bila ufikio wa wataalamu wa kilimo, taarifa za masoko kwa wakati halisi, pembejeo zilizothibitishwa, na utambuzi wa magonjwa wa mazao.
        </p>
        <p data-i18n="story_p2">
          MkulimaForum inayaleta huduma hizi zote katika mfumo mmoja wa kidigitali unaoweza kufikia — kuanzia utambuzi wa magonjwa ya mimea kwa AI, masoko ya mazao, jamii za wakulima, huduma za SMS bila intaneti, na biashara ya pembejeo zinazoaminika.
        </p>
      </div>
      <div class="problem-card">
        <div class="card-icon"><x-icon name="globe" size="24" /></div>
        <h3 data-i18n="story_stat_title">Tatizo Tunalosuluhisha</h3>
        <div class="problem-list">
          @foreach([
            ['Upatikanaji mdogo wa wataalamu wa kilimo', 'Limited access to certified agronomists'],
            ['Upotevu wa mazao kwa magonjwa yasiyotambuliwa', 'Crop losses from undiagnosed diseases'],
            ['Pembejeo feki na zisizothibitishwa', 'Counterfeit and unverified agricultural inputs'],
            ['Ukosefu wa taarifa za masoko kwa wakati halisi', 'No real-time market price information'],
          ] as $p)
          <div class="problem-item">
            <x-icon name="check-circle" />
            <span data-i18n="prob_{{ $loop->index }}">{{ $p[0] }}</span>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</section>

{{-- Mission & Vision --}}
<section class="mv-band">
  <div class="wrap">
    <div class="center-head">
      <span class="eyebrow" data-i18n="mv_eyebrow">DHAMIRA NA MAONO</span>
      <h2 class="section-title" style="margin-bottom:0;" data-i18n="mv_title">Tunachotoa na Tunapotaka Kufikia</h2>
    </div>
    <div class="mission-vision-grid fade-up">
      <div class="mv-card">
        <h3 data-i18n="mission_label">DHAMIRA (MISSION)</h3>
        <p data-i18n="mission_text">Kufanya ujuzi wa kilimo unaoweza kutumika kufikika kwa kila mkulima, bila kujali mahali walipo, kipato, lugha, au uunganisho wa mtandao.</p>
      </div>
      <div class="mv-card vision">
        <h3 data-i18n="vision_label">MAONO (VISION)</h3>
        <p data-i18n="vision_text">Mfumo wa kilimo wa Afrika uliounganishwa ambapo kila mkulima anaweza kupata maarifa, zana, masoko, na teknolojia zinazohitajika kuzalisha zaidi, kupata zaidi, na kulima kwa njia endelevu.</p>
      </div>
    </div>
  </div>
</section>

{{-- Principles --}}
<section>
  <div class="wrap">
    <span class="eyebrow" data-i18n="principles_eyebrow">KANUNI ZETU</span>
    <h2 class="section-title" style="margin-bottom:32px;" data-i18n="principles_title">Tunaongozwa na Nini</h2>
    <div class="principle-grid">
      @foreach([
        ['leaf','Mkulima Kwanza','Farmer First','Teknolojia lazima isuluhishe matatizo ya kweli ya kilimo.','Technology must solve real farming problems.','p_farmer'],
        ['phone','Kwa Wote','Accessible by Design','Suluhisho zetu zifanye kazi kwenye simu za kisasa, simu za kawaida, na mazingira ya muunganisho mdogo.','Our solutions work on smartphones, feature phones, and low-connectivity environments.','p_access'],
        ['globe','Akili ya Ndani','Local Intelligence','Kwa mazao ya Afrika, masoko, lugha, kanuni, na hali ya kilimo.','Built for African crops, markets, languages, regulations, and farming realities.','p_local'],
        ['shield','Uaminifu','Trust','Tunakuza taarifa zilizothibitishwa, pembejeo zinazoaminika, masoko ya uwazi, na AI inayowajibika.','We promote verified information, trusted inputs, transparent markets, and responsible AI.','p_trust'],
      ] as $p)
      <div class="principle-card fade-up">
        <div class="card-icon"><x-icon :name="$p[0]" size="24" /></div>
        <h3 data-i18n="{{ $p[5] }}_title">{{ $p[1] }}</h3>
        <p data-i18n="{{ $p[5] }}_desc">{{ $p[3] }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- Why Tanzania --}}
<section style="border-top:1px solid var(--border-light);">
  <div class="wrap">
    <div class="tz-grid tz-flip fade-up">
      <div class="tz-card">
        <div class="card-icon"><x-icon name="globe" size="24" /></div>
        <h3 data-i18n="tz_card_title">Tanzania: Soko Bora la Kuanza</h3>
        <p data-i18n="tz_card_desc">Tanzania ni moja ya nchi zenye idadi kubwa ya wakulima wadogo wadogo Afrika Mashariki, na kilimo ni msingi mkuu wa uchumi na maisha ya watu wake.</p>
      </div>
      <div>
        <span class="eyebrow" data-i18n="tz_eyebrow">KWA NINI TANZANIA</span>
        <h2 class="section-title" data-i18n="tz_title">Fursa ya Kubadilisha Kilimo</h2>
        <div class="tz-pills">
          @foreach([
            ['🌾','Wakulima wengi wadogo wadogo','Large smallholder farming population'],
            ['📱','Ukuaji wa matumizi ya simu','Growing smartphone adoption'],
            ['💳','Miundombinu ya pesa za simu','Strong mobile money infrastructure'],
            ['🗣️','Kiswahili kama lugha ya pamoja','Swahili as a shared language'],
            ['📊','Masoko yaliyogawanyika','Fragmented produce markets'],
            ['🌍','Uwezekano wa kupanuka Afrika Mashariki','Scalable across East Africa'],
          ] as $pill)
          <div class="tz-pill" data-i18n="tz_pill_{{ $loop->index }}">{{ $pill[0] }} {{ $pill[1] }}</div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</section>

{{-- Technology Approach --}}
<section class="tech-band">
  <div class="wrap">
    <span class="eyebrow" data-i18n="tech_eyebrow">MKAKATI WA TEKNOLOJIA</span>
    <h2 class="section-title" data-i18n="tech_title">Teknolojia Inayotumika</h2>
    <p class="section-lead" data-i18n="tech_sub">Tunatumia teknolojia bora duniani na kuzirekebisha kwa hali ya wakulima wa Tanzania.</p>
    <div class="tech-approach-grid fade-up">
      @foreach([
        ['globe','Gemini Cloud AI'],['phone','Gemma Edge AI'],['scan','Computer Vision'],
        ['phone','SMS / USSD'],['sun','Weather Intelligence'],['storefront','Marketplace Infrastructure'],
        ['shield','Mobile Money'],['chart','Agricultural Data'],
      ] as $t)
      <div class="tech-chip"><x-icon :name="$t[0]" /><span>{{ $t[1] }}</span></div>
      @endforeach
    </div>
    <div class="tech-cta">
      <a href="/technology" class="btn btn-outline" data-i18n="tech_cta">Gundua Teknolojia Yetu →</a>
    </div>
  </div>
</section>

{{-- Team --}}
<section>
  <div class="wrap">
    <div class="team-head">
      <div>
        <span class="eyebrow" data-i18n="team_eyebrow">TIMU YETU</span>
        <h2 class="section-title" data-i18n="team_title">Watu Nyuma ya MkulimaForum</h2>
      </div>
      <p class="team-note" data-i18n="team_note">Wasifu wa timu utapatikana kwenye portal ya admin na utajaza hapa mara unapoongezwa.</p>
    </div>

    {{-- Placeholder team cards - admin-editable later --}}
    <div class="team-grid">
      @foreach([
        ['👤','Founder & CEO','Mkurugenzi Mtendaji na Mwanzilishi'],
        ['👤','CTO — Head of Technology','Mkuu wa Teknolojia'],
        ['👤','Head of Agronomy','Mkuu wa Taaluma za Kilimo'],
        ['👤','Head of Partnerships','Mkuu wa Ushirikiano'],
        ['👤','Lead Mobile Developer','Msanidi Mkuu wa App'],
        ['👤','AI / ML Engineer','Mhandisi wa AI na ML'],
      ] as $member)
      <div class="team-card fade-up">
        <div class="team-avatar"><x-icon name="groups" /></div>
        <h4 data-i18n="team_role_{{ $loop->index }}">{{ $member[1] }}</h4>
        <div class="role" data-i18n="team_role_sw_{{ $loop->index }}">{{ $member[2] }}</div>
        <p data-i18n="team_bio">Wasifu utaongezwa hivi karibuni. Mfumo huu unaweza kujaza kutoka Admin Dashboard → Settings.</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- Final CTA --}}
<section class="final-cta">
  <div class="wrap">
    <h2 data-i18n="about_cta_title">Kujenga Miundombinu ya Kidijitali kwa Kilimo cha Afrika</h2>
    <p data-i18n="about_cta_sub">Jiunge nasi katika safari ya kubadilisha jinsi wakulima wa Afrika wanavyopata maarifa, masoko, na teknolojia.</p>
    <div class="cta-actions">
      <a href="/solutions" class="btn btn-primary btn-lg" data-i18n="about_cta_sol">Gundua Suluhisho Zetu</a>
      <a href="/contact" class="btn btn-outline btn-lg" data-i18n="about_cta_partner">Shirikiana Nasi</a>
    </div>
  </div>
</section>

@endsection

@section('page_scripts')
<script nonce="{{ $cspNonce ?? '' }}">
mkPageTranslations = {
  sw: {
    about_eyebrow:'KUHUSU MKULIMAFORUM',
    about_hero_title:'Teknolojia Inayojengwa Kumzunguka Mkulima',
    about_hero_sub:'MkulimaForum ni mfumo wa kidigitali wa kilimo unaotumia AI, uliobuniwa kuunganisha wakulima na maarifa, masoko, pembejeo zinazoaminika, utabiri wa hali ya hewa, na msaada wa kilimo.',
    story_eyebrow:'HADITHI YETU', story_title:'Kwa Nini Tulijenga MkulimaForum',
    story_p1:'Mamilioni ya wakulima wadogo wadogo nchini Tanzania na Afrika Mashariki bado hufanya maamuzi muhimu ya kilimo bila ufikio wa wataalamu wa kilimo, taarifa za masoko, pembejeo zilizothibitishwa, na utambuzi wa magonjwa wa mazao.',
    story_p2:'MkulimaForum inayaleta huduma hizi katika mfumo mmoja wa kidigitali unaoweza kufikia — kuanzia utambuzi wa magonjwa ya mimea kwa AI, masoko ya mazao, jamii za wakulima, huduma za SMS, na biashara ya pembejeo zinazoaminika.',
    story_stat_title:'Tatizo Tunalosuluhisha',
    prob_0:'Upatikanaji mdogo wa wataalamu wa kilimo', prob_1:'Upotevu wa mazao kwa magonjwa yasiyotambuliwa',
    prob_2:'Pembejeo feki na zisizothibitishwa', prob_3:'Ukosefu wa taarifa za masoko kwa wakati halisi',
    mv_eyebrow:'DHAMIRA NA MAONO', mv_title:'Tunachotoa na Tunapotaka Kufikia',
    mission_label:'DHAMIRA (MISSION)', mission_text:'Kufanya ujuzi wa kilimo unaoweza kutumika kufikika kwa kila mkulima, bila kujali mahali walipo, kipato, lugha, au uunganisho wa mtandao.',
    vision_label:'MAONO (VISION)', vision_text:'Mfumo wa kilimo wa Afrika uliounganishwa ambapo kila mkulima anaweza kupata maarifa, zana, masoko, na teknolojia zinazohitajika kuzalisha zaidi, kupata zaidi, na kulima kwa njia endelevu.',
    principles_eyebrow:'KANUNI ZETU', principles_title:'Tunaongozwa na Nini',
    p_farmer_title:'Mkulima Kwanza', p_farmer_desc:'Teknolojia lazima isuluhishe matatizo ya kweli ya kilimo.',
    p_access_title:'Kwa Wote', p_access_desc:'Suluhisho zetu zifanye kazi kwenye simu za kisasa, simu za kawaida, na mazingira ya muunganisho mdogo.',
    p_local_title:'Akili ya Ndani', p_local_desc:'Kwa mazao ya Afrika, masoko, lugha, kanuni, na hali ya kilimo.',
    p_trust_title:'Uaminifu', p_trust_desc:'Tunakuza taarifa zilizothibitishwa, pembejeo zinazoaminika, masoko ya uwazi, na AI inayowajibika.',
    tz_eyebrow:'KWA NINI TANZANIA', tz_title:'Fursa ya Kubadilisha Kilimo',
    tz_card_title:'Tanzania: Soko Bora la Kuanza', tz_card_desc:'Tanzania ni moja ya nchi zenye idadi kubwa ya wakulima wadogo wadogo Afrika Mashariki, na kilimo ni msingi mkuu wa uchumi na maisha.',
    tz_pill_0:'🌾 Wakulima wengi wadogo wadogo', tz_pill_1:'📱 Ukuaji wa matumizi ya simu',
    tz_pill_2:'💳 Miundombinu ya pesa za simu', tz_pill_3:'🗣️ Kiswahili kama lugha ya pamoja',
    tz_pill_4:'📊 Masoko yaliyogawanyika', tz_pill_5:'🌍 Uwezekano wa kupanuka Afrika Mashariki',
    tech_eyebrow:'MKAKATI WA TEKNOLOJIA', tech_title:'Teknolojia Inayotumika',
    tech_sub:'Tunatumia teknolojia bora duniani na kuzirekebisha kwa hali ya wakulima wa Tanzania.',
    tech_cta:'Gundua Teknolojia Yetu →',
    team_eyebrow:'TIMU YETU', team_title:'Watu Nyuma ya MkulimaForum',
    team_note:'Wasifu wa timu utapatikana kwenye portal ya admin na utajaza hapa mara unapoongezwa.',
    team_bio:'Wasifu utaongezwa hivi karibuni. Mfumo huu unaweza kujaza kutoka Admin Dashboard → Settings.',
    about_cta_title:'Kujenga Miundombinu ya Kidijitali kwa Kilimo cha Afrika',
    about_cta_sub:'Jiunge nasi katika safari ya kubadilisha jinsi wakulima wa Afrika wanavyopata maarifa, masoko, na teknolojia.',
    about_cta_sol:'Gundua Suluhisho Zetu', about_cta_partner:'Shirikiana Nasi',
  },
  en: {
    about_eyebrow:'ABOUT MKULIMAFORUM',
    about_hero_title:'Technology Built Around the African Farmer',
    about_hero_sub:'MkulimaForum is an AI-powered digital agriculture ecosystem designed to connect farmers with knowledge, markets, trusted agricultural inputs, weather intelligence, and practical farming support.',
    story_eyebrow:'OUR STORY', story_title:'Why We Built MkulimaForum',
    story_p1:'Millions of smallholder farmers across Tanzania and East Africa still make critical farming decisions with limited access to agronomists, timely market information, verified agricultural inputs, and reliable crop diagnostics.',
    story_p2:'MkulimaForum brings these services into one accessible digital ecosystem — from AI-powered plant diagnosis to market intelligence, farmer communities, offline SMS services, and trusted agricultural commerce.',
    story_stat_title:'The Problem We Are Solving',
    prob_0:'Limited access to certified agronomists', prob_1:'Crop losses from undiagnosed diseases',
    prob_2:'Counterfeit and unverified agricultural inputs', prob_3:'No real-time market price information',
    mv_eyebrow:'MISSION & VISION', mv_title:'What We Deliver and Where We Are Going',
    mission_label:'MISSION', mission_text:'To make practical agricultural intelligence accessible to every farmer, regardless of location, income, language, or internet connectivity.',
    vision_label:'VISION', vision_text:'A connected African agricultural ecosystem where every farmer can access the knowledge, tools, markets, and technology required to produce more, earn more, and farm sustainably.',
    principles_eyebrow:'OUR PRINCIPLES', principles_title:'What Guides Us',
    p_farmer_title:'Farmer First', p_farmer_desc:'Technology must solve real farming problems.',
    p_access_title:'Accessible by Design', p_access_desc:'Our solutions work on smartphones, feature phones, and low-connectivity environments.',
    p_local_title:'Local Intelligence', p_local_desc:'Built for African crops, markets, languages, regulations, and farming realities.',
    p_trust_title:'Trust', p_trust_desc:'We promote verified information, trusted agricultural inputs, transparent markets, and responsible AI.',
    tz_eyebrow:'WHY TANZANIA', tz_title:'An Ideal Market to Transform',
    tz_card_title:'Tanzania: An Ideal Launch Market', tz_card_desc:'Tanzania has one of East Africa\'s largest smallholder farming populations, with agriculture as a primary economic and livelihood driver.',
    tz_pill_0:'🌾 Large smallholder farming population', tz_pill_1:'📱 Growing smartphone adoption',
    tz_pill_2:'💳 Strong mobile money infrastructure', tz_pill_3:'🗣️ Swahili as a shared language',
    tz_pill_4:'📊 Fragmented produce markets', tz_pill_5:'🌍 Scalable across East Africa',
    tech_eyebrow:'TECHNOLOGY APPROACH', tech_title:'Technologies We Use',
    tech_sub:'We apply world-class technology, adapted for the realities of Tanzanian and East African farmers.',
    tech_cta:'Explore Our Technology →',
    team_eyebrow:'OUR TEAM', team_title:'The People Behind MkulimaForum',
    team_note:'Team profiles are managed via Admin Dashboard and will appear here once added.',
    team_bio:'Profile coming soon. This section is populated from Admin Dashboard → Settings.',
    about_cta_title:'Building the Digital Infrastructure for African Agriculture',
    about_cta_sub:'Join us in transforming how African farmers access knowledge, markets, and technology.',
    about_cta_sol:'Explore Our Solutions', about_cta_partner:'Partner With Us',
  }
};
</script>
@endsection
