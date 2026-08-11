@extends('layouts.public')

@section('title', 'About MkulimaForum | Building Digital Agriculture for Africa')
@section('meta_description', 'MkulimaForum is an AI-powered digital agriculture ecosystem. Learn about our mission, vision, principles, and why we are building for East African farmers.')
@section('og_title', 'About MkulimaForum | Digital Agriculture for Africa')
@section('og_description', 'Our mission: make practical agricultural intelligence accessible to every farmer, regardless of location, income, language, or internet connectivity.')

@section('head_extra')
<style>
  .about-hero-inner { max-width: 700px; }
  .mission-vision-grid { display:grid; grid-template-columns:1fr 1fr; gap:24px; }
  @media(max-width:680px){ .mission-vision-grid{ grid-template-columns:1fr; } }
  .mv-card {
    padding:36px; border-radius:var(--radius-xl);
    border-left:4px solid var(--leaf-green);
    background:var(--surface-card); box-shadow:var(--shadow-sm);
  }
  .mv-card h3 { font-size:1.1rem; font-weight:800; color:var(--forest-mid); margin-bottom:12px; text-transform:uppercase; letter-spacing:.08em; }
  .mv-card p  { color:var(--ink-body); line-height:1.7; font-size:1rem; }
  .principle-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:20px; }
  @media(max-width:1020px){ .principle-grid{ grid-template-columns:repeat(2,1fr); } }
  @media(max-width:560px)  { .principle-grid{ grid-template-columns:1fr; } }
  .principle-card { background:var(--surface-card); border:1px solid var(--border-light); border-radius:var(--radius-xl); padding:28px; transition:all .25s ease; }
  .principle-card:hover { transform:translateY(-4px); box-shadow:var(--shadow-md); }
  .principle-card .p-icon { font-size:2rem; margin-bottom:14px; }
  .principle-card h3 { font-size:1.05rem; font-weight:800; color:var(--ink-dark); margin-bottom:8px; }
  .principle-card p  { font-size:.88rem; color:var(--ink-muted); line-height:1.65; }
  .tz-grid { display:grid; grid-template-columns:1fr 1fr; gap:40px; align-items:center; }
  @media(max-width:780px){ .tz-grid{ grid-template-columns:1fr; } }
  .tz-pills { display:flex; flex-wrap:wrap; gap:10px; }
  .tz-pill { padding:8px 16px; background:var(--leaf-pale); border:1px solid var(--border-mid); border-radius:999px; font-size:.85rem; font-weight:700; color:var(--forest-mid); }
  .tech-approach-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
  @media(max-width:900px) { .tech-approach-grid{ grid-template-columns:repeat(2,1fr); } }
  @media(max-width:520px)  { .tech-approach-grid{ grid-template-columns:1fr; } }
  .tech-chip { display:flex; align-items:center; gap:10px; padding:14px 18px; background:var(--surface-card); border:1px solid var(--border-light); border-radius:var(--radius-lg); font-size:.9rem; font-weight:700; color:var(--ink-dark); }
  .team-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:24px; }
  @media(max-width:840px){ .team-grid{ grid-template-columns:repeat(2,1fr); } }
  @media(max-width:540px){ .team-grid{ grid-template-columns:1fr; } }
  .team-card { background:var(--surface-card); border:1px solid var(--border-light); border-radius:var(--radius-xl); padding:28px; text-align:center; transition:all .25s ease; }
  .team-card:hover { box-shadow:var(--shadow-md); }
  .team-avatar { width:72px; height:72px; border-radius:50%; background:var(--leaf-pale); margin:0 auto 16px; display:flex; align-items:center; justify-content:center; font-size:2rem; border:3px solid var(--border-light); }
  .team-card h4 { font-size:1rem; font-weight:800; color:var(--ink-dark); margin-bottom:4px; }
  .team-card .role { font-size:.82rem; font-weight:700; color:var(--forest-mid); margin-bottom:8px; text-transform:uppercase; letter-spacing:.06em; }
  .team-card p { font-size:.82rem; color:var(--ink-muted); line-height:1.5; }
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
      <div>
        <span class="eyebrow" data-i18n="story_eyebrow">HADITHI YETU</span>
        <h2 class="section-title" data-i18n="story_title">Kwa Nini Tulijenga MkulimaForum</h2>
        <p style="color:var(--ink-body); margin-bottom:18px; line-height:1.75;" data-i18n="story_p1">
          Mamilioni ya wakulima wadogo wadogo nchini Tanzania na Afrika Mashariki bado hufanya maamuzi muhimu ya kilimo bila ufikio wa wataalamu wa kilimo, taarifa za masoko kwa wakati halisi, pembejeo zilizothibitishwa, na utambuzi wa magonjwa wa mazao.
        </p>
        <p style="color:var(--ink-body); line-height:1.75;" data-i18n="story_p2">
          MkulimaForum inayaleta huduma hizi zote katika mfumo mmoja wa kidigitali unaoweza kufikia — kuanzia utambuzi wa magonjwa ya mimea kwa AI, masoko ya mazao, jamii za wakulima, huduma za SMS bila intaneti, na biashara ya pembejeo zinazoaminika.
        </p>
      </div>
      <div style="background:linear-gradient(145deg, #EFF7E9, #D6EEC8); border-radius:var(--radius-2xl); padding:48px; border:1px solid var(--border-mid);">
        <div style="font-size:2.8rem; margin-bottom:18px;">🌍</div>
        <h3 style="font-size:1.4rem; font-weight:800; color:var(--forest-dark); margin-bottom:12px;" data-i18n="story_stat_title">Tatizo Tunalosuluhisha</h3>
        <div style="display:flex; flex-direction:column; gap:12px;">
          @foreach([
            ['Upatikanaji mdogo wa wataalamu wa kilimo', 'Limited access to certified agronomists'],
            ['Upotevu wa mazao kwa magonjwa yasiyotambuliwa', 'Crop losses from undiagnosed diseases'],
            ['Pembejeo feki na zisizothibitishwa', 'Counterfeit and unverified agricultural inputs'],
            ['Ukosefu wa taarifa za masoko kwa wakati halisi', 'No real-time market price information'],
          ] as $p)
          <div style="display:flex; align-items:center; gap:10px; padding:12px; background:rgba(255,255,255,.7); border-radius:10px;">
            <span style="color:var(--leaf-green); font-size:1.2rem; flex-shrink:0;">✓</span>
            <span style="font-size:.9rem; font-weight:600; color:var(--ink-dark);" data-i18n="prob_{{ $loop->index }}">{{ $p[0] }}</span>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</section>

{{-- Mission & Vision --}}
<section style="background:var(--leaf-pale); padding:80px 0;">
  <div class="wrap">
    <span class="eyebrow" data-i18n="mv_eyebrow" style="text-align:center; display:block;">DHAMIRA NA MAONO</span>
    <h2 class="section-title" style="text-align:center; margin-bottom:40px;" data-i18n="mv_title">Tunachotoa na Tunapotaka Kufikia</h2>
    <div class="mission-vision-grid fade-up">
      <div class="mv-card">
        <h3 data-i18n="mission_label">DHAMIRA (MISSION)</h3>
        <p data-i18n="mission_text">Kufanya ujuzi wa kilimo unaoweza kutumika kufikika kwa kila mkulima, bila kujali mahali walipo, kipato, lugha, au uunganisho wa mtandao.</p>
      </div>
      <div class="mv-card" style="border-left-color: var(--sun-gold);">
        <h3 style="color:var(--sun-gold);" data-i18n="vision_label">MAONO (VISION)</h3>
        <p data-i18n="vision_text">Mfumo wa kilimo wa Afrika uliounganishwa ambapo kila mkulima anaweza kupata maarifa, zana, masoko, na teknolojia zinazohitajika kuzalisha zaidi, kupata zaidi, na kulima kwa njia endelevu.</p>
      </div>
    </div>
  </div>
</section>

{{-- Principles --}}
<section>
  <div class="wrap">
    <span class="eyebrow" data-i18n="principles_eyebrow">KANUNI ZETU</span>
    <h2 class="section-title" style="margin-bottom:40px;" data-i18n="principles_title">Tunaongozwa na Nini</h2>
    <div class="principle-grid">
      @foreach([
        ['🌾','Mkulima Kwanza','Farmer First','Teknolojia lazima isuluhishe matatizo ya kweli ya kilimo.','Technology must solve real farming problems.','p_farmer'],
        ['📱','Kwa Wote','Accessible by Design','Suluhisho zetu zifanye kazi kwenye simu za kisasa, simu za kawaida, na mazingira ya muunganisho mdogo.','Our solutions work on smartphones, feature phones, and low-connectivity environments.','p_access'],
        ['🌍','Akili ya Ndani','Local Intelligence','Kwa mazao ya Afrika, masoko, lugha, kanuni, na hali ya kilimo.','Built for African crops, markets, languages, regulations, and farming realities.','p_local'],
        ['🛡️','Uaminifu','Trust','Tunakuza taarifa zilizothibitishwa, pembejeo zinazoaminika, masoko ya uwazi, na AI inayowajibika.','We promote verified information, trusted inputs, transparent markets, and responsible AI.','p_trust'],
      ] as $p)
      <div class="principle-card fade-up">
        <div class="p-icon">{{ $p[0] }}</div>
        <h3 data-i18n="{{ $p[5] }}_title">{{ $p[1] }}</h3>
        <p data-i18n="{{ $p[5] }}_desc">{{ $p[3] }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- Why Tanzania --}}
<section style="background:var(--surface-card); border-top:1px solid var(--border-light);">
  <div class="wrap">
    <div class="tz-grid fade-up">
      <div style="background:linear-gradient(145deg,#0C3619,#175429); border-radius:var(--radius-2xl); padding:48px; color:#fff; position:relative; overflow:hidden;">
        <div style="font-size:5rem; position:absolute; right:24px; top:16px; opacity:.15; line-height:1;">🇹🇿</div>
        <h3 style="font-size:1.6rem; font-weight:800; color:#fff; margin-bottom:14px;" data-i18n="tz_card_title">Tanzania: Soko Bora la Kuanza</h3>
        <p style="color:rgba(255,255,255,.82); font-size:.95rem; line-height:1.7;" data-i18n="tz_card_desc">Tanzania ni moja ya nchi zenye idadi kubwa ya wakulima wadogo wadogo Afrika Mashariki, na kilimo ni msingi mkuu wa uchumi na maisha ya watu wake.</p>
      </div>
      <div>
        <span class="eyebrow" data-i18n="tz_eyebrow">KWA NINI TANZANIA</span>
        <h2 class="section-title" data-i18n="tz_title">Fursa ya Kubadilisha Kilimo</h2>
        <div class="tz-pills" style="margin-top:20px;">
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
<section style="background:var(--leaf-pale); padding:80px 0;">
  <div class="wrap">
    <span class="eyebrow" data-i18n="tech_eyebrow">MKAKATI WA TEKNOLOJIA</span>
    <h2 class="section-title" data-i18n="tech_title">Teknolojia Inayotumika</h2>
    <p class="section-lead" style="margin-bottom:36px;" data-i18n="tech_sub">Tunatumia teknolojia bora duniani na kuzirekebisha kwa hali ya wakulima wa Tanzania.</p>
    <div class="tech-approach-grid fade-up">
      @foreach([
        ['🤖','Gemini Cloud AI'],['🧠','Gemma Edge AI'],['👁️','Computer Vision'],
        ['📱','SMS / USSD'],['⛅','Weather Intelligence'],['🛒','Marketplace Infrastructure'],
        ['💳','Mobile Money'],['📊','Agricultural Data'],
      ] as $t)
      <div class="tech-chip">{{ $t[0] }}<span>{{ $t[1] }}</span></div>
      @endforeach
    </div>
    <div style="margin-top:28px;">
      <a href="/technology" class="btn btn-outline" data-i18n="tech_cta">Gundua Teknolojia Yetu →</a>
    </div>
  </div>
</section>

{{-- Team --}}
<section>
  <div class="wrap">
    <div style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:16px; margin-bottom:40px;">
      <div>
        <span class="eyebrow" data-i18n="team_eyebrow">TIMU YETU</span>
        <h2 class="section-title" style="margin-bottom:0;" data-i18n="team_title">Watu Nyuma ya MkulimaForum</h2>
      </div>
      <p style="font-size:.85rem; color:var(--ink-muted); max-width:28rem;" data-i18n="team_note">Wasifu wa timu utapatikana kwenye portal ya admin na utajaza hapa mara unapoongezwa.</p>
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
        <div class="team-avatar">{{ $member[0] }}</div>
        <h4 data-i18n="team_role_{{ $loop->index }}">{{ $member[1] }}</h4>
        <div class="role" data-i18n="team_role_sw_{{ $loop->index }}">{{ $member[2] }}</div>
        <p data-i18n="team_bio">Wasifu utaongezwa hivi karibuni. Mfumo huu unaweza kujaza kutoka Admin Dashboard → Settings.</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- Final CTA --}}
<section style="background:linear-gradient(135deg,#0E4220,var(--forest-dark)); color:#fff; padding:80px 0;">
  <div class="wrap" style="text-align:center; max-width:640px;">
    <h2 style="font-size:clamp(1.8rem,4vw,2.6rem); font-weight:900; color:#fff; margin-bottom:16px;" data-i18n="about_cta_title">Kujenga Miundombinu ya Kidijitali kwa Kilimo cha Afrika</h2>
    <p style="color:rgba(255,255,255,.82); margin-bottom:32px; font-size:1rem;" data-i18n="about_cta_sub">Jiunge nasi katika safari ya kubadilisha jinsi wakulima wa Afrika wanavyopata maarifa, masoko, na teknolojia.</p>
    <div style="display:flex; gap:14px; flex-wrap:wrap; justify-content:center;">
      <a href="/solutions" class="btn btn-gold btn-lg" data-i18n="about_cta_sol">Gundua Suluhisho Zetu</a>
      <a href="/contact" class="btn btn-ghost btn-lg" data-i18n="about_cta_partner">Shirikiana Nasi</a>
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
