@extends('layouts.public')

@section('title', 'Contact MkulimaForum | Get in Touch — Partnerships, Press & Farmer Support')
@section('meta_description', 'Contact MkulimaForum for partnerships, investor relations, press inquiries, technical support, farmer feedback, or general questions about our AI agriculture platform.')
@section('og_title', 'Contact MkulimaForum | AgriTech Tanzania')
@section('og_description', 'Contact us for partnerships, investor relations, press, technical support, or farmer feedback.')

@section('head_extra')
<style>
  .contact-grid { display:grid; grid-template-columns:0.42fr 0.58fr; gap:56px; align-items:start; }
  @media(max-width:860px){ .contact-grid{ grid-template-columns:1fr; } }
  .contact-info-card {
    background:linear-gradient(145deg,var(--forest-dark),#0A2A10); color:#fff;
    border-radius:var(--radius-2xl); padding:44px; position:sticky; top:calc(var(--nav-h) + 20px);
  }
  .c-info-item { display:flex; align-items:flex-start; gap:14px; margin-bottom:24px; }
  .c-info-icon { width:42px; height:42px; border-radius:12px; background:rgba(255,255,255,.12); display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0; }
  .c-info-label { font-size:.72rem; font-weight:700; color:var(--sun-gold); text-transform:uppercase; letter-spacing:.1em; margin-bottom:4px; }
  .c-info-value { font-size:.92rem; color:rgba(255,255,255,.9); }
  .c-divider { height:1px; background:rgba(255,255,255,.12); margin:20px 0; }
  .contact-form-card { background:var(--surface-card); border:1px solid var(--border-light); border-radius:var(--radius-2xl); padding:44px; }
  .form-group { display:flex; flex-direction:column; gap:6px; }
  .form-group label { font-size:.88rem; font-weight:700; color:var(--ink-dark); }
  .form-group input, .form-group select, .form-group textarea {
    padding:12px 16px; border:1.5px solid var(--border-light); border-radius:10px;
    font-family:inherit; font-size:.9rem; color:var(--ink-dark); background:var(--cream-bg);
    transition:border-color .2s ease; outline:none;
  }
  .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color:var(--forest-mid); background:#fff; }
  .form-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
  @media(max-width:620px){ .form-grid-2{ grid-template-columns:1fr; } }
  .faq-item { border:1px solid var(--border-light); border-radius:14px; overflow:hidden; }
  .faq-q {
    width:100%; text-align:left; padding:18px 22px; background:var(--surface-card); border:none;
    font-family:'Outfit',sans-serif; font-size:1rem; font-weight:700; color:var(--ink-dark);
    cursor:pointer; display:flex; justify-content:space-between; align-items:center; gap:12px;
    transition:background .15s;
  }
  .faq-q:hover { background:var(--leaf-pale); }
  .faq-q svg { width:18px; height:18px; flex-shrink:0; transition:transform .25s; color:var(--ink-muted); }
  .faq-item.open .faq-q svg { transform:rotate(180deg); }
  .faq-a { display:none; padding:4px 22px 18px; font-size:.92rem; color:var(--ink-muted); line-height:1.7; }
  .faq-item.open .faq-a { display:block; }
</style>
@endsection

@section('content')

{{-- Hero --}}
<section class="page-hero" style="padding-bottom:60px;">
  <div class="wrap fade-up" style="max-width:640px;">
    <span class="eyebrow" data-i18n="contact_eyebrow">WASILIANA NASI</span>
    <h1 class="page-title" data-i18n="contact_title">Tutakaribisha Kushikana Nawe</h1>
    <p class="section-lead" data-i18n="contact_sub">Iwe ni ushirikiano, uwekezaji, msaada kwa wakulima, maswali ya vyombo vya habari, au maswali ya kiufundi — tuko hapa.</p>
  </div>
</section>

{{-- Contact Grid --}}
<section style="padding-top:0;">
  <div class="wrap">
    <div class="contact-grid">
      {{-- Left info panel --}}
      <div class="contact-info-card fade-up">
        <h3 style="font-size:1.3rem; font-weight:800; color:#fff; margin-bottom:24px;" data-i18n="c_info_title">Njia za Kuwasiliana</h3>

        <div class="c-info-item">
          <div class="c-info-icon">✉️</div>
          <div>
            <div class="c-info-label" data-i18n="c_label_email">Barua Pepe</div>
            <div class="c-info-value">{{ $settings['contact_email'] ?? 'hello@mkulimaforum.app' }}</div>
          </div>
        </div>

        <div class="c-info-item">
          <div class="c-info-icon">🌐</div>
          <div>
            <div class="c-info-label" data-i18n="c_label_web">Wavuti</div>
            <div class="c-info-value">mkulimaforum.app</div>
          </div>
        </div>

        <div class="c-info-item">
          <div class="c-info-icon">📍</div>
          <div>
            <div class="c-info-label" data-i18n="c_label_location">Mahali Tulipo</div>
            <div class="c-info-value" data-i18n="c_location_val">Tanzania 🇹🇿 — Afrika Mashariki 🌍</div>
          </div>
        </div>

        <div class="c-divider"></div>

        <h4 style="font-size:.82rem; font-weight:800; color:rgba(255,255,255,.6); text-transform:uppercase; letter-spacing:.1em; margin-bottom:16px;" data-i18n="c_dept_title">IDARA</h4>

        @foreach([
          ['🤝','Ushirikiano','Partnerships','partnerships@mkulimaforum.app'],
          ['💰','Uwekezaji','Investment','invest@mkulimaforum.app'],
          ['📰','Vyombo vya Habari','Press & Media','press@mkulimaforum.app'],
          ['🔧','Msaada wa Kiufundi','Technical Support','support@mkulimaforum.app'],
        ] as $dept)
        <div style="margin-bottom:14px;">
          <div style="font-size:.8rem; font-weight:700; color:rgba(255,255,255,.65);" data-i18n="dept_{{ $loop->index }}">{{ $dept[0] }} {{ $dept[1] }}</div>
          <div style="font-size:.82rem; color:var(--sun-gold);">{{ $dept[3] }}</div>
        </div>
        @endforeach

        <div class="c-divider"></div>

        <p style="font-size:.8rem; color:rgba(255,255,255,.55); line-height:1.65;" data-i18n="c_response_note">
          Tunajibu barua pepe zote ndani ya siku 2 za kazi. Kwa maswali ya dharura ya kiufundi, tuma kwenye support@mkulimaforum.app.
        </p>
      </div>

      {{-- Right form --}}
      <div class="contact-form-card fade-up">
        <h3 style="font-size:1.3rem; font-weight:800; color:var(--ink-dark); margin-bottom:24px;" data-i18n="c_form_title">Tuma Ujumbe Wako</h3>

        <form id="contactForm" onsubmit="handleContactForm(event)" style="display:flex; flex-direction:column; gap:18px;">
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
            <label for="cf_org" data-i18n="cf_org">Shirika / Kampuni <span style="color:var(--ink-faint); font-weight:400;">(si lazima)</span></label>
            <input id="cf_org" type="text" data-i18n-ph="cf_org_ph" placeholder="Shirika lako (si lazima)">
          </div>

          <div class="form-group">
            <label for="cf_message" data-i18n="cf_message">Ujumbe Wako</label>
            <textarea id="cf_message" rows="5" required data-i18n-ph="cf_msg_ph" placeholder="Andika ujumbe wako hapa..."></textarea>
          </div>

          <button type="submit" class="btn btn-primary btn-lg" style="justify-content:center;" id="cf_submit_btn" data-i18n="cf_submit">
            ✉️ Tuma Ujumbe
          </button>

          <div id="cf_result" style="display:none; padding:14px 18px; border-radius:10px; font-size:.9rem; font-weight:600;"></div>
        </form>
      </div>
    </div>
  </div>
</section>

{{-- FAQ --}}
<section style="background:var(--leaf-pale); padding:72px 0;">
  <div class="wrap" style="max-width:760px;">
    <span class="eyebrow" data-i18n="faq_eyebrow">MASWALI YANAYOULIZWA MARA KWA MARA</span>
    <h2 class="section-title" style="margin-bottom:32px;" data-i18n="faq_title">Maswali ya Kawaida</h2>

    @foreach([
      ['faq0','Je, MkulimaForum ni bure kuitumia?','Is MkulimaForum free to use?','Ndiyo — sehemu za msingi za mfumo (utambuzi wa magonjwa, Mkulima Bot, jamii, hali ya hewa) zinaweza kutumiwa bila malipo. Huduma za malipo (masoko, pembejeo) zina ada ndogo.','Yes — the core features (plant diagnosis, Mkulima Bot, community, weather) are free to use. Paid services (marketplace, inputs) carry a small fee.'],
      ['faq1','Je, MkulimaForum inafanya kazi bila intaneti?','Does MkulimaForum work without internet?','Ndiyo. Tumejumuisha Gemma 2B INT4 kwa utambuzi wa AI bila intaneti, pamoja na huduma za SMS na USSD kwa maeneo ya uunganisho mdogo.','Yes. We have integrated Gemma 2B INT4 for offline AI inference, plus SMS and USSD services for low-connectivity areas.'],
      ['faq2','Je, ninawezaje kuwa mshirika?','How can I become a partner?','Tembelea ukurasa wetu wa Washirika na ujaze fomu ya ushirikiano, au tuma barua pepe moja kwa moja kwenye partnerships@mkulimaforum.app.','Visit our Partners page and fill in the partnership request form, or email directly to partnerships@mkulimaforum.app.'],
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
</section>

@endsection

@section('page_scripts')
<script>
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

const mkPageTranslations = {
  sw: {
    contact_eyebrow:'WASILIANA NASI', contact_title:'Tutakaribisha Kushikana Nawe',
    contact_sub:'Iwe ni ushirikiano, uwekezaji, msaada kwa wakulima, maswali ya vyombo vya habari, au maswali ya kiufundi — tuko hapa.',
    c_info_title:'Njia za Kuwasiliana',
    c_label_email:'Barua Pepe', c_label_web:'Wavuti', c_label_location:'Mahali Tulipo',
    c_location_val:'Tanzania 🇹🇿 — Afrika Mashariki 🌍',
    c_dept_title:'IDARA',
    dept_0:'🤝 Ushirikiano', dept_1:'💰 Uwekezaji', dept_2:'📰 Vyombo vya Habari', dept_3:'🔧 Msaada wa Kiufundi',
    c_response_note:'Tunajibu barua pepe zote ndani ya siku 2 za kazi. Kwa maswali ya dharura ya kiufundi, tuma kwenye support@mkulimaforum.app.',
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
    faq1_q:'Je, MkulimaForum inafanya kazi bila intaneti?', faq1_a:'Ndiyo. Tumejumuisha Gemma 2B INT4 kwa utambuzi wa AI bila intaneti, pamoja na huduma za SMS na USSD.',
    faq2_q:'Je, ninawezaje kuwa mshirika?', faq2_a:'Tembelea ukurasa wetu wa Washirika na ujaze fomu ya ushirikiano, au tuma barua pepe kwenye partnerships@mkulimaforum.app.',
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
    c_response_note:'We respond to all emails within 2 business days. For urgent technical issues, email support@mkulimaforum.app.',
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
    faq0_q:'Is MkulimaForum free to use?', faq0_a:'Yes — core features (plant diagnosis, Mkulima Bot, community, weather) are free. Paid services (marketplace, inputs) carry a small fee.',
    faq1_q:'Does MkulimaForum work without internet?', faq1_a:'Yes. We have integrated Gemma 2B INT4 for offline AI inference, plus SMS and USSD services for low-connectivity areas.',
    faq2_q:'How can I become a partner?', faq2_a:'Visit our Partners page and fill in the partnership request form, or email directly to partnerships@mkulimaforum.app.',
    faq3_q:'Does MkulimaForum work outside Tanzania?', faq3_a:'The current platform focuses on Tanzania. We plan to expand to Kenya, Uganda, and other East African markets based on traction.',
    faq4_q:'What personal data do you collect?', faq4_a:'We collect basic account information only. We do not share or sell personal farmer data to third parties.',
  }
};
</script>
@endsection
