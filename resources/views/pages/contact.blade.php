@extends('layouts.public')

@section('title', 'Contact MkulimaForum | Get in Touch — Partnerships, Press & Farmer Support')
@section('meta_description', 'Contact MkulimaForum for partnerships, investor relations, press inquiries, technical support, farmer feedback, or general questions about our AI agriculture platform.')
@section('og_title', 'Contact MkulimaForum | AgriTech Tanzania')
@section('og_description', 'Contact us for partnerships, investor relations, press, technical support, or farmer feedback.')

@section('head_extra')
<style>
  /* Tokens, buttons, form fields, header and footer come from layouts/public.blade.php. */
  .contact-grid { display:grid; grid-template-columns:minmax(0,.42fr) minmax(0,.58fr); gap:40px; align-items:start; }
  @media(max-width:860px){ .contact-grid{ grid-template-columns:minmax(0,1fr); gap:20px; } }
  .contact-grid > * { min-width:0; }
  .contact-info-card {
    background:var(--surface-soft); border:1px solid var(--border-light);
    border-radius:var(--radius-xl); padding:32px; position:sticky; top:calc(var(--nav-h) + 20px);
  }
  .c-info-title { font-size:20px; font-weight:700; color:var(--ink-dark); margin-bottom:20px; }
  .c-info-item { display:flex; align-items:flex-start; gap:14px; margin-bottom:18px; }
  .c-info-item > div:last-child { min-width:0; }
  .c-info-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
  .c-info-label { font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; margin-bottom:2px; }
  .c-info-value { font-size:15px; overflow-wrap:anywhere; }
  .c-divider { height:1px; margin:20px 0; }
  .c-dept-title { font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; margin-bottom:12px; }
  .c-dept { margin-bottom:12px; }
  .c-dept-name { font-size:15px; font-weight:600; color:var(--ink-dark); }
  .c-dept-mail { font-size:14px; color:var(--forest-dark); overflow-wrap:anywhere; }
  .c-note { font-size:14px; line-height:1.6; overflow-wrap:anywhere; }

  .contact-form-card { background:#fff; border:1px solid var(--border-light); border-radius:var(--radius-xl); padding:36px; }
  .c-form-title { font-size:20px; font-weight:700; color:var(--ink-dark); margin-bottom:20px; }
  .contact-form { display:flex; flex-direction:column; gap:4px; }
  .contact-form .form-group select { max-width:100%; text-overflow:ellipsis; }
  .contact-form textarea { resize:vertical; }
  .optional { color:var(--ink-muted); font-weight:400; }
  .form-grid-2 { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr); gap:16px; }
  @media(max-width:620px){ .form-grid-2{ grid-template-columns:minmax(0,1fr); gap:0; } }
  .cf-result { display:none; padding:14px 18px; border-radius:12px; font-size:15px; font-weight:600; }

  /* FAQ */
  .faq-band { background:var(--surface-soft); border-top:1px solid var(--border-light); padding:72px 0; }
  .faq-list { display:flex; flex-direction:column; gap:10px; }
  .faq-item { background:#fff; border:1px solid var(--border-light); border-radius:14px; overflow:hidden; }
  .faq-q {
    width:100%; min-height:56px; text-align:left; padding:16px 20px; background:#fff; border:none;
    font-family:inherit; font-size:16px; font-weight:600; color:var(--ink-dark);
    display:flex; justify-content:space-between; align-items:center; gap:12px;
    transition:background .15s;
  }
  .faq-q:hover { background:var(--surface-soft); }
  .faq-q svg { width:18px; height:18px; flex-shrink:0; transition:transform .25s; color:var(--forest-mid); }
  .faq-item.open .faq-q svg { transform:rotate(180deg); }
  .faq-a { display:none; padding:0 20px 18px; font-size:15px; color:var(--ink-muted); line-height:1.65; }
  .faq-item.open .faq-a { display:block; }

  @media (max-width:860px) { .contact-info-card { position:static; } }
  @media (max-width:700px) {
    .contact-form-card, .contact-info-card { border-radius:14px; }
    .faq-band { padding:44px 0; }
    .faq-q { padding:14px 16px; font-size:15px; }
    .faq-a { padding:0 16px 16px; }
  }
</style>
@endsection

@section('content')

{{-- Hero --}}
<section class="page-hero">
  <div class="wrap fade-up">
    <span class="eyebrow" data-i18n="contact_eyebrow">WASILIANA NASI</span>
    <h1 class="page-title" data-i18n="contact_title">Tutakaribisha Kushikana Nawe</h1>
    <p class="section-lead" data-i18n="contact_sub">Iwe ni ushirikiano, uwekezaji, msaada kwa wakulima, maswali ya vyombo vya habari, au maswali ya kiufundi — tuko hapa.</p>
  </div>
</section>

{{-- Contact Grid --}}
<section>
  <div class="wrap">
    <div class="contact-grid">
      {{-- Left info panel --}}
      <div class="contact-info-card fade-up">
        <h3 class="c-info-title" data-i18n="c_info_title">Njia za Kuwasiliana</h3>

        <div class="c-info-item">
          <div class="c-info-icon"><svg class="ico" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg></div>
          <div>
            <div class="c-info-label" data-i18n="c_label_email">Barua Pepe</div>
            <div class="c-info-value">{{ $settings['contact_email'] ?? 'hello@mkulimaforum.com' }}</div>
          </div>
        </div>

        <div class="c-info-item">
          <div class="c-info-icon"><x-icon name="globe" /></div>
          <div>
            <div class="c-info-label" data-i18n="c_label_web">Wavuti</div>
            <div class="c-info-value">mkulimaforum.com</div>
          </div>
        </div>

        <div class="c-info-item">
          <div class="c-info-icon"><svg class="ico" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg></div>
          <div>
            <div class="c-info-label" data-i18n="c_label_location">Mahali Tulipo</div>
            <div class="c-info-value" data-i18n="c_location_val">Tanzania 🇹🇿 — Afrika Mashariki 🌍</div>
          </div>
        </div>

        <div class="c-divider"></div>

        <h4 class="c-dept-title" data-i18n="c_dept_title">IDARA</h4>

        @foreach([
          ['🤝','Ushirikiano','Partnerships','partnerships@mkulimaforum.com'],
          ['💰','Uwekezaji','Investment','invest@mkulimaforum.com'],
          ['📰','Vyombo vya Habari','Press & Media','press@mkulimaforum.com'],
          ['🔧','Msaada wa Kiufundi','Technical Support','support@mkulimaforum.com'],
        ] as $dept)
        <div class="c-dept">
          <div class="c-dept-name" data-i18n="dept_{{ $loop->index }}">{{ $dept[0] }} {{ $dept[1] }}</div>
          <div class="c-dept-mail">{{ $dept[3] }}</div>
        </div>
        @endforeach

        <div class="c-divider"></div>

        <p class="c-note" data-i18n="c_response_note">
          Tunajibu barua pepe zote ndani ya siku 2 za kazi. Kwa maswali ya dharura ya kiufundi, tuma kwenye support@mkulimaforum.com.
        </p>
      </div>

      {{-- Right form --}}
      <div class="contact-form-card fade-up">
        <h3 class="c-form-title" data-i18n="c_form_title">Tuma Ujumbe Wako</h3>

        <form id="contactForm" class="contact-form" onsubmit="handleContactForm(event)">
          <div class="form-grid-2">
            <div class="form-group">
              <label for="cf_name" data-i18n="cf_name">Jina Lako Kamili</label>
              <input id="cf_name" type="text" required data-i18n-ph="cf_name_ph" placeholder="Jina Lako">
            </div>
            <div class="form-group">
              <label for="cf_email" data-i18n="cf_email">Barua Pepe</label>
              <input id="cf_email" type="email" required data-i18n-ph="cf_email_ph" placeholder="barua@mfano.com">
            </div>
          </div>

          <div class="form-group">
            <label for="cf_type" data-i18n="cf_type_label">Aina ya Uchunguzi</label>
            <select id="cf_type" required>
              <option value="" data-i18n="cf_select">-- Chagua Aina --</option>
              <option value="partnership" data-i18n="cf_opt_partner">🤝 Ushirikiano / Partnership</option>
              <option value="investor" data-i18n="cf_opt_invest">💰 Uwekezaji / Investment</option>
              <option value="press" data-i18n="cf_opt_press">📰 Vyombo vya Habari / Press</option>
              <option value="farmer" data-i18n="cf_opt_farmer">👨‍🌾 Msaada kwa Mkulima / Farmer Support</option>
              <option value="technical" data-i18n="cf_opt_tech">🔧 Msaada wa Kiufundi / Technical</option>
              <option value="story" data-i18n="cf_opt_story">🌾 Hadithi ya Mkulima / Farmer Story</option>
              <option value="general" data-i18n="cf_opt_gen">💬 Swali la Jumla / General Question</option>
            </select>
          </div>

          <div class="form-group">
            <label for="cf_org" data-i18n="cf_org">Shirika / Kampuni <span class="optional">(si lazima)</span></label>
            <input id="cf_org" type="text" data-i18n-ph="cf_org_ph" placeholder="Shirika lako (si lazima)">
          </div>

          <div class="form-group">
            <label for="cf_message" data-i18n="cf_message">Ujumbe Wako</label>
            <textarea id="cf_message" rows="5" required data-i18n-ph="cf_msg_ph" placeholder="Andika ujumbe wako hapa..."></textarea>
          </div>

          <button type="submit" class="btn btn-primary btn-lg" id="cf_submit_btn" data-i18n="cf_submit">
            ✉️ Tuma Ujumbe
          </button>

          <div id="cf_result" class="cf-result"></div>
        </form>
      </div>
    </div>
  </div>
</section>

{{-- FAQ --}}
<section class="faq-band">
  <div class="wrap-sm">
    <span class="eyebrow" data-i18n="faq_eyebrow">MASWALI YANAYOULIZWA MARA KWA MARA</span>
    <h2 class="section-title" style="margin-bottom:24px;" data-i18n="faq_title">Maswali ya Kawaida</h2>
    <div class="faq-list">

    @foreach([
      ['faq0','Je, MkulimaForum ni bure kuitumia?','Is MkulimaForum free to use?','Ndiyo — sehemu za msingi za mfumo (utambuzi wa magonjwa, Mkulima AI, jamii, hali ya hewa) zinaweza kutumiwa bila malipo. Huduma za malipo (masoko, pembejeo) zina ada ndogo.','Yes — the core features (plant diagnosis, Mkulima AI, community, weather) are free to use. Paid services (marketplace, inputs) carry a small fee.'],
      ['faq1','Je, MkulimaForum inafanya kazi bila intaneti?','Does MkulimaForum work without internet?','Ndiyo. Tumejumuisha Mkulima AI Offline kwa utambuzi wa AI bila intaneti, pamoja na huduma za SMS na USSD kwa maeneo ya uunganisho mdogo.','Yes. We have integrated Mkulima AI Offline for offline AI inference, plus SMS and USSD services for low-connectivity areas.'],
      ['faq2','Je, ninawezaje kuwa mshirika?','How can I become a partner?','Tembelea ukurasa wetu wa Washirika na ujaze fomu ya ushirikiano, au tuma barua pepe moja kwa moja kwenye partnerships@mkulimaforum.com.','Visit our Partners page and fill in the partnership request form, or email directly to partnerships@mkulimaforum.com.'],
      ['faq3','Je, MkulimaForum inafanya kazi nje ya Tanzania?','Does MkulimaForum work outside Tanzania?','Mfumo wa sasa unazingatia Tanzania. Tunapanga kupanua kwenda Kenya, Uganda, na nchi nyingine za Afrika Mashariki kulingana na mahitaji ya soko.','The current platform focuses on Tanzania. We plan to expand to Kenya, Uganda, and other East African markets based on traction and market need.'],
      ['faq4','Ni aina gani ya data ya kibinafsi mnayokusanya?','What personal data do you collect?','Tunakusanya taarifa za akaunti ya msingi (jina, nambari ya simu au barua pepe). Hatuzidishi au kuuza data za kibinafsi za wakulima kwa watu wengine.','We collect basic account information (name, phone number or email). We do not share or sell personal farmer data to third parties.'],
    ] as $faq)
    <div class="faq-item" id="{{ $faq[0] }}">
      <button class="faq-q" onclick="toggleFaq('{{ $faq[0] }}')" data-i18n="{{ $faq[0] }}_q">
        {{ $faq[1] }}
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
      </button>
      <div class="faq-a" data-i18n="{{ $faq[0] }}_a">{{ $faq[3] }}</div>
    </div>
    @endforeach
    </div>
  </div>
</section>

@endsection

@section('page_scripts')
<script nonce="{{ $cspNonce ?? '' }}">
function toggleFaq(id) {
  const el = document.getElementById(id);
  el.classList.toggle('open');
}

async function handleContactForm(e) {
  e.preventDefault();
  const btn = document.getElementById('cf_submit_btn');
  const result = document.getElementById('cf_result');
  btn.disabled = true;
  btn.textContent = '⏳ ' + (MK_LANG === 'sw' ? 'Inatuma...' : 'Sending...');
  await new Promise(r => setTimeout(r, 1000));
  result.style.display = 'block';
  result.style.background = 'var(--leaf-pale)';
  result.style.color = 'var(--forest-dark)';
  result.style.border = '1px solid var(--border-mid)';
  result.textContent = MK_LANG === 'sw'
    ? '✅ Asante sana! Tumepokea ujumbe wako. Tutawasiliana nawe ndani ya siku 2 za kazi.'
    : '✅ Thank you! We have received your message and will respond within 2 business days.';
  btn.disabled = false;
  btn.textContent = MK_LANG === 'sw' ? '✉️ Tuma Ujumbe' : '✉️ Send Message';
  e.target.reset();
}

mkPageTranslations = {
  sw: {
    contact_eyebrow:'WASILIANA NASI', contact_title:'Tutakaribisha Kushikana Nawe',
    contact_sub:'Iwe ni ushirikiano, uwekezaji, msaada kwa wakulima, maswali ya vyombo vya habari, au maswali ya kiufundi — tuko hapa.',
    c_info_title:'Njia za Kuwasiliana',
    c_label_email:'Barua Pepe', c_label_web:'Wavuti', c_label_location:'Mahali Tulipo',
    c_location_val:'Tanzania 🇹🇿 — Afrika Mashariki 🌍',
    c_dept_title:'IDARA',
    dept_0:'🤝 Ushirikiano', dept_1:'💰 Uwekezaji', dept_2:'📰 Vyombo vya Habari', dept_3:'🔧 Msaada wa Kiufundi',
    c_response_note:'Tunajibu barua pepe zote ndani ya siku 2 za kazi. Kwa maswali ya dharura ya kiufundi, tuma kwenye support@mkulimaforum.com.',
    c_form_title:'Tuma Ujumbe Wako',
    cf_name:'Jina Lako Kamili', cf_name_ph:'Jina Lako',
    cf_email:'Barua Pepe', cf_email_ph:'barua@mfano.com',
    cf_type_label:'Aina ya Uchunguzi', cf_select:'-- Chagua Aina --',
    cf_opt_partner:'🤝 Ushirikiano', cf_opt_invest:'💰 Uwekezaji', cf_opt_press:'📰 Vyombo vya Habari',
    cf_opt_farmer:'👨‍🌾 Msaada kwa Mkulima', cf_opt_tech:'🔧 Msaada wa Kiufundi',
    cf_opt_story:'🌾 Hadithi ya Mkulima', cf_opt_gen:'💬 Swali la Jumla',
    cf_org:'Shirika / Kampuni', cf_org_ph:'Shirika lako (si lazima)',
    cf_message:'Ujumbe Wako', cf_msg_ph:'Andika ujumbe wako hapa...',
    cf_submit:'✉️ Tuma Ujumbe',
    faq_eyebrow:'MASWALI YANAYOULIZWA MARA KWA MARA', faq_title:'Maswali ya Kawaida',
    faq0_q:'Je, MkulimaForum ni bure kuitumia?', faq0_a:'Ndiyo — sehemu za msingi za mfumo zinaweza kutumiwa bila malipo. Huduma za masoko na pembejeo zina ada ndogo.',
    faq1_q:'Je, MkulimaForum inafanya kazi bila intaneti?', faq1_a:'Ndiyo. Tumejumuisha Mkulima AI Offline kwa utambuzi wa AI bila intaneti, pamoja na huduma za SMS na USSD.',
    faq2_q:'Je, ninawezaje kuwa mshirika?', faq2_a:'Tembelea ukurasa wetu wa Washirika na ujaze fomu ya ushirikiano, au tuma barua pepe kwenye partnerships@mkulimaforum.com.',
    faq3_q:'Je, MkulimaForum inafanya kazi nje ya Tanzania?', faq3_a:'Mfumo wa sasa unazingatia Tanzania. Tunapanga kupanua kwenda Kenya, Uganda, na nchi nyingine za Afrika Mashariki.',
    faq4_q:'Ni aina gani ya data ya kibinafsi mnayokusanya?', faq4_a:'Tunakusanya taarifa za akaunti ya msingi tu. Hatuzidishi au kuuza data za kibinafsi za wakulima kwa watu wengine.',
  },
  en: {
    contact_eyebrow:'CONTACT', contact_title:"We Would Love to Hear From You",
    contact_sub:'Whether it is a partnership, investment, farmer support, press inquiry, or technical question — we are here.',
    c_info_title:'Ways to Get in Touch',
    c_label_email:'Email', c_label_web:'Website', c_label_location:'Where We Are',
    c_location_val:'Tanzania 🇹🇿 — East Africa 🌍',
    c_dept_title:'DEPARTMENTS',
    dept_0:'🤝 Partnerships', dept_1:'💰 Investment', dept_2:'📰 Press & Media', dept_3:'🔧 Technical Support',
    c_response_note:'We respond to all emails within 2 business days. For urgent technical issues, email support@mkulimaforum.com.',
    c_form_title:'Send Your Message',
    cf_name:'Full Name', cf_name_ph:'Your Name',
    cf_email:'Email Address', cf_email_ph:'email@example.com',
    cf_type_label:'Type of Inquiry', cf_select:'-- Select Type --',
    cf_opt_partner:'🤝 Partnership', cf_opt_invest:'💰 Investment',
    cf_opt_press:'📰 Press & Media', cf_opt_farmer:'👨‍🌾 Farmer Support',
    cf_opt_tech:'🔧 Technical Support', cf_opt_story:'🌾 Farmer Story', cf_opt_gen:'💬 General Question',
    cf_org:'Organization / Company', cf_org_ph:'Your organization (optional)',
    cf_message:'Your Message', cf_msg_ph:'Write your message here...',
    cf_submit:'✉️ Send Message',
    faq_eyebrow:'FREQUENTLY ASKED QUESTIONS', faq_title:'Common Questions',
    faq0_q:'Is MkulimaForum free to use?', faq0_a:'Yes — core features (plant diagnosis, Mkulima AI, community, weather) are free. Paid services (marketplace, inputs) carry a small fee.',
    faq1_q:'Does MkulimaForum work without internet?', faq1_a:'Yes. We have integrated Mkulima AI Offline for offline AI inference, plus SMS and USSD services for low-connectivity areas.',
    faq2_q:'How can I become a partner?', faq2_a:'Visit our Partners page and fill in the partnership request form, or email directly to partnerships@mkulimaforum.com.',
    faq3_q:'Does MkulimaForum work outside Tanzania?', faq3_a:'The current platform focuses on Tanzania. We plan to expand to Kenya, Uganda, and other East African markets based on traction.',
    faq4_q:'What personal data do you collect?', faq4_a:'We collect basic account information only. We do not share or sell personal farmer data to third parties.',
  }
};
</script>
@endsection
