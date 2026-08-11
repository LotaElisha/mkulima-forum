@extends('layouts.public')

@section('title', 'MkulimaForum Pitch Deck | AI Agriculture Investment Opportunity Tanzania')
@section('meta_description', 'View the MkulimaForum investor pitch deck — AI-powered agriculture platform for Tanzania. Our mission, business model, technology, market opportunity, and team.')
@section('og_title', 'MkulimaForum Pitch Deck | AI AgriTech Tanzania')
@section('og_description', 'MkulimaForum investor presentation — AI-powered digital agriculture ecosystem for East African smallholder farmers.')

@section('head_extra')
<style>
  /* PDF Viewer Modal */
  .pitch-hero {
    background:radial-gradient(circle at 70% 40%, #1F6B38 0%, var(--forest-dark) 60%);
    color:#fff; padding:80px 0 100px; position:relative; overflow:hidden;
  }
  .pitch-hero::after {
    content:''; position:absolute; bottom:-1px; left:0; right:0; height:60px;
    background: var(--cream-bg); clip-path:ellipse(55% 100% at 50% 100%);
  }
  .pitch-hero-grid { display:grid; grid-template-columns:1.2fr 0.8fr; gap:40px; align-items:center; position:relative; z-index:2; }
  @media(max-width:860px){ .pitch-hero-grid{ grid-template-columns:1fr; } .deck-preview-wrap{ display:none; } }

  .deck-preview-wrap {
    display:flex; justify-content:flex-end;
  }
  .deck-preview {
    background:#1A2E1F; border:2px solid rgba(255,255,255,.15); border-radius:16px;
    width:280px; padding:10px; box-shadow:0 30px 60px rgba(0,0,0,.5);
    transform: perspective(900px) rotateY(-6deg) rotateX(2deg);
    transition: transform .4s ease;
  }
  .deck-preview:hover { transform: perspective(900px) rotateY(0deg) rotateX(0deg); }
  .deck-inner { background:linear-gradient(160deg,#0C3619,#1A5C2A); border-radius:10px; padding:28px; aspect-ratio:4/3; display:flex; flex-direction:column; justify-content:flex-end; }
  .deck-inner h3 { font-size:.9rem; font-weight:900; color:#fff; margin-bottom:4px; }
  .deck-inner p { font-size:.68rem; color:rgba(255,255,255,.6); }
  .deck-inner .deck-logo { font-size:1.8rem; margin-bottom:12px; }

  /* Viewer */
  #pdfModal {
    position:fixed; inset:0; z-index:1000; display:none; align-items:center; justify-content:center;
    background:rgba(0,0,0,.75); backdrop-filter:blur(6px); padding:20px;
  }
  #pdfModal.open { display:flex; }
  .pdf-modal-card {
    background:var(--surface-card); border-radius:var(--radius-2xl); overflow:hidden;
    width:min(900px,96vw); max-height:92vh; display:flex; flex-direction:column;
    box-shadow:0 40px 80px rgba(0,0,0,.4);
  }
  .pdf-modal-header {
    display:flex; align-items:center; justify-content:space-between; padding:16px 24px;
    border-bottom:1px solid var(--border-light); background:var(--cream-bg);
  }
  .pdf-modal-header h4 { font-size:1rem; font-weight:800; color:var(--ink-dark); }
  .pdf-close-btn {
    width:36px; height:36px; background:var(--leaf-pale); border-radius:50%;
    display:flex; align-items:center; justify-content:center; cursor:pointer;
    font-size:1.1rem; color:var(--ink-dark); border:none; transition:background .15s;
  }
  .pdf-close-btn:hover { background:var(--border-mid); }
  #pdfFrame { flex:1; width:100%; border:none; min-height:70vh; }
  .pdf-fallback { flex:1; display:flex; align-items:center; justify-content:center; flex-direction:column; gap:16px; padding:40px; text-align:center; }
  .no-pitch { background:var(--leaf-pale); border:1.5px dashed var(--border-mid); border-radius:var(--radius-2xl); padding:56px; text-align:center; margin-top:40px; }

  /* Highlights grid */
  .highlights-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:20px; }
  @media(max-width:960px){ .highlights-grid{ grid-template-columns:repeat(2,1fr); } }
  @media(max-width:560px) { .highlights-grid{ grid-template-columns:1fr; } }
  .highlight-card { background:var(--surface-card); border:1px solid var(--border-light); border-radius:var(--radius-xl); padding:28px; text-align:center; transition:all .25s ease; }
  .highlight-card:hover { transform:translateY(-4px); box-shadow:var(--shadow-md); }
  .highlight-card h3 { font-size:1rem; font-weight:800; color:var(--ink-dark); margin:12px 0 8px; }
  .highlight-card p  { font-size:.85rem; color:var(--ink-muted); }
  .investment-panel { display:grid; grid-template-columns:1.2fr .8fr; gap:40px; align-items:center; }
  @media(max-width:760px){
    .investment-panel { grid-template-columns:1fr; }
    .investment-panel > div:last-child { justify-content:flex-start !important; }
  }
</style>
@endsection

@section('content')

{{-- Hero --}}
<div class="pitch-hero">
  <div class="wrap pitch-hero-grid">
    <div>
      <div class="badge dark" style="margin-bottom:20px;" data-i18n="pitch_badge">INVESTOR PRESENTATION</div>
      <h1 style="font-size:clamp(2.2rem,5vw,3.4rem); font-weight:900; color:#fff; line-height:1.12; margin-bottom:16px;" data-i18n="pitch_hero_title">
        MkulimaForum — AI Agriculture Platform for East Africa
      </h1>
      <p style="font-size:1.05rem; color:rgba(255,255,255,.88); max-width:36rem; line-height:1.7; margin-bottom:28px;" data-i18n="pitch_hero_sub">
        Tazama muhtasari kamili wa mradi wetu, fursa ya soko, muundo wa biashara, teknolojia ya AI, na athari tunazolenga.
      </p>
      <div style="display:flex; gap:14px; flex-wrap:wrap;">
        @if(isset($settings['pitch_deck_url']) && $settings['pitch_deck_url'])
        <button onclick="openPDF('{{ $settings['pitch_deck_url'] }}')" class="btn btn-gold btn-lg" data-i18n="pitch_view_btn">👁️ Tazama Pitch Deck Online</button>
        <a href="{{ $settings['pitch_deck_url'] }}" target="_blank" rel="noopener" download class="btn btn-ghost btn-lg" data-i18n="pitch_dl_btn">⬇️ Pakua PDF</a>
        @else
        <a href="/contact" class="btn btn-gold btn-lg" data-i18n="pitch_request_btn">📬 Omba Nakala ya Pitch Deck</a>
        @endif
      </div>
    </div>
    <div class="deck-preview-wrap">
      <div class="deck-preview">
        <div class="deck-inner">
          <div class="deck-logo">🌾</div>
          <h3>MkulimaForum</h3>
          <p>AI Agriculture Platform for East Africa</p>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- What is in the deck --}}
<section>
  <div class="wrap">
    <div style="text-align:center; margin-bottom:40px;">
      <span class="eyebrow" data-i18n="deck_eyebrow">KATIKA PITCH DECK</span>
      <h2 class="section-title" data-i18n="deck_title">Deck Inajumuisha Nini</h2>
    </div>
    <div class="highlights-grid">
      @foreach([
        ['🌍','Tatizo na Fursa','The problem and East Africa market opportunity','pd0'],
        ['🎯','Dhamira na Maono','Our mission, vision, and approach','pd1'],
        ['⚡','Suluhisho la MkulimaForum','Platform walkthrough and all 8 solutions','pd2'],
        ['🤖','Mkakati wa Teknolojia','AI stack: Gemini 3, Mkulima AI Offline, offline architecture','pd3'],
        ['💰','Muundo wa Biashara','Revenue model and monetization strategy','pd4'],
        ['📈','Fursa ya Soko','Market size and expansion roadmap','pd5'],
        ['🗓️','Ramani ya Barabara','Development milestones and go-to-market plan','pd6'],
        ['👥','Timu','Leadership and advisory team','pd7'],
      ] as $item)
      <div class="highlight-card fade-up">
        <div style="font-size:2.5rem;">{{ $item[0] }}</div>
        <h3 data-i18n="{{ $item[3] }}_title">{{ $item[1] }}</h3>
        <p data-i18n="{{ $item[3] }}_desc">{{ $item[2] }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- Online Viewer --}}
<section style="background:var(--surface-card); border-top:1px solid var(--border-light); padding:72px 0;">
  <div class="wrap">
    @if(isset($settings['pitch_deck_url']) && $settings['pitch_deck_url'])
    <div style="text-align:center; margin-bottom:28px;">
      <span class="eyebrow" data-i18n="viewer_eyebrow">TAZAMA ONLINE</span>
      <h2 class="section-title" data-i18n="viewer_title">Soma Pitch Deck Hapa</h2>
    </div>
    <div style="border:1px solid var(--border-light); border-radius:var(--radius-xl); overflow:hidden; box-shadow:var(--shadow-sm);">
      <div style="background:var(--cream-bg); border-bottom:1px solid var(--border-light); padding:14px 20px; display:flex; align-items:center; gap:12px;">
        <span style="font-size:.85rem; font-weight:700; color:var(--ink-muted);">📄 MkulimaForum_Pitch_Deck.pdf</span>
        <a href="{{ $settings['pitch_deck_url'] }}" target="_blank" rel="noopener" download class="btn btn-outline btn-sm" style="margin-left:auto;" data-i18n="viewer_dl_btn">⬇️ Pakua</a>
      </div>
      <iframe
        src="{{ $settings['pitch_deck_url'] }}#toolbar=1&view=FitH"
        style="width:100%; height:78vh; border:none;"
        title="MkulimaForum Pitch Deck"
        loading="lazy"
        sandbox="allow-scripts allow-same-origin allow-popups allow-forms">
      </iframe>
    </div>
    @else
    <div class="no-pitch fade-up">
      <div style="font-size:4rem; margin-bottom:20px;">📊</div>
      <h2 style="font-size:1.6rem; font-weight:800; color:var(--forest-dark); margin-bottom:14px;" data-i18n="no_deck_title">Pitch Deck Haijapakiwa Bado</h2>
      <p style="color:var(--ink-muted); max-width:32rem; margin:0 auto 24px; line-height:1.7;" data-i18n="no_deck_desc">Admin ya MkulimaForum bado haijapakia faili ya Pitch Deck. Inaweza kupakiwa kupitia Admin Dashboard → Mipangilio → Ukurasa wa Kutua.</p>
      <div style="display:flex; gap:12px; flex-wrap:wrap; justify-content:center;">
        <a href="/contact" class="btn btn-primary" data-i18n="no_deck_request_btn">📬 Omba Nakala ya Pitch Deck</a>
        <a href="/admin" class="btn btn-outline btn-sm" data-i18n="no_deck_admin_btn">🔐 Admin Dashboard</a>
      </div>
    </div>
    @endif
  </div>
</section>

{{-- Request NDA --}}
<section>
  <div class="wrap">
    <div class="panel-dark investment-panel">
      <div>
        <span class="badge dark" style="margin-bottom:16px;" data-i18n="nda_badge">UWEKEZAJI</span>
        <h2 data-i18n="nda_title">Unatafuta Taarifa Zaidi za Uwekezaji?</h2>
        <p style="margin-top:12px;" data-i18n="nda_desc">Tupo tayari kushiriki taarifa zaidi za kifedha, mkakati wa biashara, na maelezo ya kina zaidi kwa wawekezaji wanaovutika. Wasiliana nasi kupanga mazungumzo.</p>
      </div>
      <div style="display:flex; gap:12px; flex-wrap:wrap; justify-content:flex-end;">
        <a href="/contact?type=investor" class="btn btn-gold" data-i18n="nda_contact_btn">📬 Wasiliana na Timu</a>
        <a href="/impact" class="btn btn-ghost" data-i18n="nda_impact_btn">📊 Angalia Athari →</a>
      </div>
    </div>
  </div>
</section>

{{-- PDF Modal --}}
<div id="pdfModal" onclick="closePDFModal(event)" role="dialog" aria-label="Pitch Deck Viewer" aria-modal="true">
  <div class="pdf-modal-card" onclick="event.stopPropagation()">
    <div class="pdf-modal-header">
      <h4 data-i18n="pd_deck_modal_title">📊 MkulimaForum Pitch Deck</h4>
      <div style="display:flex; gap:8px; align-items:center;">
        <a id="pdfDownloadBtn" href="#" target="_blank" rel="noopener" download class="btn btn-outline btn-sm" data-i18n="pd_download_btn">⬇️ Download</a>
        <button class="pdf-close-btn" onclick="closePDF()" aria-label="Close PDF viewer">✕</button>
      </div>
    </div>
    <iframe id="pdfFrame" title="Pitch Deck Viewer"></iframe>
  </div>
</div>

@endsection

@section('page_scripts')
<script nonce="{{ $cspNonce ?? '' }}">
function openPDF(url) {
  const modal = document.getElementById('pdfModal');
  const frame = document.getElementById('pdfFrame');
  const dl    = document.getElementById('pdfDownloadBtn');
  frame.src = url + '#toolbar=1&view=FitH';
  if(dl) dl.href = url;
  modal.classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closePDF() {
  const modal = document.getElementById('pdfModal');
  const frame = document.getElementById('pdfFrame');
  modal.classList.remove('open');
  frame.src = '';
  document.body.style.overflow = '';
}
function closePDFModal(e) { if(e.target === document.getElementById('pdfModal')) closePDF(); }
document.addEventListener('keydown', e => { if(e.key === 'Escape') closePDF(); });

mkPageTranslations = {
  sw: {
    pitch_badge:'INVESTOR PRESENTATION', pitch_hero_title:'MkulimaForum — AI Agriculture Platform for East Africa',
    pitch_hero_sub:'Tazama muhtasari kamili wa mradi wetu, fursa ya soko, muundo wa biashara, teknolojia ya AI, na athari tunazolenga.',
    pitch_view_btn:'👁️ Tazama Pitch Deck Online', pitch_dl_btn:'⬇️ Pakua PDF', pitch_request_btn:'📬 Omba Nakala ya Pitch Deck',
    deck_eyebrow:'KATIKA PITCH DECK', deck_title:'Deck Inajumuisha Nini',
    pd0_title:'Tatizo na Fursa', pd0_desc:'Tatizo na fursa ya soko ya Afrika Mashariki',
    pd1_title:'Dhamira na Maono', pd1_desc:'Dhamira yetu, maono, na mkakati',
    pd2_title:'Suluhisho la MkulimaForum', pd2_desc:'Mtiririko wa jukwaa na suluhisho zote 8',
    pd3_title:'Mkakati wa Teknolojia', pd3_desc:'Mfumo wa AI: Gemini 3, Mkulima AI Offline, muundo wa offline',
    pd4_title:'Muundo wa Biashara', pd4_desc:'Mfano wa mapato na mkakati wa kutengeneza pesa',
    pd5_title:'Fursa ya Soko', pd5_desc:'Ukubwa wa soko na ramani ya upanuzi',
    pd6_title:'Ramani ya Barabara', pd6_desc:'Hatua za maendeleo na mpango wa kwenda sokoni',
    pd7_title:'Timu', pd7_desc:'Timu ya uongozi na washauri',
    viewer_eyebrow:'TAZAMA ONLINE', viewer_title:'Soma Pitch Deck Hapa', viewer_dl_btn:'⬇️ Pakua',
    no_deck_title:'Pitch Deck Haijapakiwa Bado',
    no_deck_desc:'Admin ya MkulimaForum bado haijapakia faili ya Pitch Deck. Inaweza kupakiwa kupitia Admin Dashboard → Mipangilio.',
    no_deck_request_btn:'📬 Omba Nakala ya Pitch Deck', no_deck_admin_btn:'🔐 Admin Dashboard',
    nda_badge:'UWEKEZAJI', nda_title:'Unatafuta Taarifa Zaidi za Uwekezaji?',
    nda_desc:'Tupo tayari kushiriki taarifa zaidi za kifedha, mkakati wa biashara, na maelezo ya kina zaidi kwa wawekezaji wanaovutika.',
    nda_contact_btn:'📬 Wasiliana na Timu', nda_impact_btn:'📊 Angalia Athari →',
    pd_deck_modal_title:'📊 Pitch Deck ya MkulimaForum', pd_download_btn:'⬇️ Pakua',
  },
  en: {
    pitch_badge:'INVESTOR PRESENTATION', pitch_hero_title:'MkulimaForum — AI Agriculture Platform for East Africa',
    pitch_hero_sub:"View a complete overview of our project, market opportunity, business model, AI technology, and the impact we're targeting for smallholder farmers.",
    pitch_view_btn:'👁️ View Pitch Deck Online', pitch_dl_btn:'⬇️ Download PDF', pitch_request_btn:'📬 Request a Copy',
    deck_eyebrow:'INSIDE THE DECK', deck_title:'What the Pitch Deck Covers',
    pd0_title:'Problem & Opportunity', pd0_desc:'The problem and East Africa market opportunity',
    pd1_title:'Mission & Vision', pd1_desc:'Our mission, vision, and strategic approach',
    pd2_title:'MkulimaForum Solution', pd2_desc:'Platform walkthrough and all 8 solutions',
    pd3_title:'Technology Strategy', pd3_desc:'AI stack: Gemini 3, Mkulima AI Offline, offline architecture',
    pd4_title:'Business Model', pd4_desc:'Revenue model and monetization strategy',
    pd5_title:'Market Opportunity', pd5_desc:'Market size and expansion roadmap',
    pd6_title:'Roadmap', pd6_desc:'Development milestones and go-to-market plan',
    pd7_title:'Team', pd7_desc:'Leadership and advisory team',
    viewer_eyebrow:'VIEW ONLINE', viewer_title:'Read the Pitch Deck Here', viewer_dl_btn:'⬇️ Download',
    no_deck_title:'Pitch Deck Not Yet Uploaded',
    no_deck_desc:'The MkulimaForum admin has not yet uploaded the Pitch Deck file. It can be uploaded via Admin Dashboard → Settings → Landing Page.',
    no_deck_request_btn:'📬 Request a Copy of the Deck', no_deck_admin_btn:'🔐 Admin Dashboard',
    nda_badge:'INVESTMENT', nda_title:'Looking for More Detailed Investment Information?',
    nda_desc:"We're happy to share detailed financial information, business strategy, and in-depth materials with interested investors. Contact us to schedule a conversation.",
    nda_contact_btn:'📬 Contact the Team', nda_impact_btn:'📊 View Our Impact →',
    pd_deck_modal_title:'📊 MkulimaForum Pitch Deck', pd_download_btn:'⬇️ Download',
  }
};
</script>
@endsection
