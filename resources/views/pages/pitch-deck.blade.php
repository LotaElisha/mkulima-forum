@extends('layouts.public')

@section('title', 'MkulimaForum Pitch Deck | AI Agriculture Investment Opportunity Tanzania')
@section('meta_description', 'View the MkulimaForum investor pitch deck — AI-powered agriculture platform for Tanzania. Our mission, business model, technology, market opportunity, and team.')
@section('og_title', 'MkulimaForum Pitch Deck | AI AgriTech Tanzania')
@section('og_description', 'MkulimaForum investor presentation — AI-powered digital agriculture ecosystem for East African smallholder farmers.')

@section('head_extra')
<style>
  /* Hero: white, like every other page. The layout already neutralises
     .pitch-hero; these rules only set spacing and the light deck preview. */
  .pitch-hero { padding: 72px 0 64px; }
  .pitch-hero-grid { display: grid; grid-template-columns: 1.2fr .8fr; gap: 40px; align-items: center; }
  .pitch-hero .section-lead { margin-bottom: 28px; }
  .pitch-actions { display: flex; gap: 12px; flex-wrap: wrap; }
  .deck-preview-wrap { display: flex; justify-content: flex-end; }
  @media (max-width: 860px) { .pitch-hero-grid { grid-template-columns: 1fr; } .deck-preview-wrap { display: none; } }
  .deck-preview {
    width: 300px; padding: 10px; background: #fff;
    border: 1px solid var(--border-light); border-radius: var(--radius-xl);
    box-shadow: var(--shadow-md);
  }
  .deck-inner {
    aspect-ratio: 4/3; padding: 24px; border-radius: 10px;
    background: var(--surface-soft); border: 1px solid var(--border-light);
    display: flex; flex-direction: column; justify-content: flex-end;
  }
  .deck-inner .card-icon { margin-bottom: 14px; }
  .deck-inner h3 { font-size: 18px; font-weight: 800; color: var(--ink-dark); margin-bottom: 4px; }
  .deck-inner p  { font-size: 14px; color: var(--ink-muted); line-height: 1.45; }

  /* What is in the deck */
  .deck-head { text-align: center; margin-bottom: 40px; }
  .highlights-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 16px; }
  @media (max-width: 960px) { .highlights-grid { grid-template-columns: repeat(2,1fr); } }
  @media (max-width: 560px) { .highlights-grid { grid-template-columns: 1fr; } }
  .highlight-card {
    background: #fff; border: 1px solid var(--border-light); border-radius: var(--radius-xl);
    padding: 24px; transition: border-color .15s ease;
  }
  .highlight-card:hover { border-color: var(--border-mid); }
  .highlight-card h3 { font-size: 17px; font-weight: 700; color: var(--ink-dark); margin-bottom: 6px; }
  .highlight-card p  { font-size: 15px; color: var(--ink-muted); line-height: 1.55; }

  /* Online viewer */
  .viewer-section { background: var(--surface-soft); border-top: 1px solid var(--border-light); border-bottom: 1px solid var(--border-light); }
  .viewer-frame { background: #fff; border: 1px solid var(--border-light); border-radius: var(--radius-xl); overflow: hidden; }
  .viewer-bar {
    display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
    padding: 10px 16px 10px 20px; border-bottom: 1px solid var(--border-light); background: #fff;
  }
  .viewer-file { font-size: 14px; font-weight: 600; color: var(--ink-muted); min-width: 0; overflow-wrap: anywhere; }
  .viewer-bar .btn { margin-left: auto; }
  .viewer-frame iframe { width: 100%; height: 78vh; border: none; display: block; }
  .no-pitch {
    background: #fff; border: 1px solid var(--border-light);
    border-radius: var(--radius-xl); padding: 48px; text-align: center;
  }
  .no-pitch .card-icon { margin: 0 auto 18px; }
  .no-pitch h2 { font-size: 24px; font-weight: 800; color: var(--ink-dark); margin-bottom: 12px; }
  .no-pitch p { color: var(--ink-muted); max-width: 32rem; margin: 0 auto 24px; line-height: 1.7; }
  .no-pitch-actions { display: flex; gap: 12px; flex-wrap: wrap; justify-content: center; }

  /* PDF modal */
  #pdfModal {
    position: fixed; inset: 0; z-index: 1000; display: none; align-items: center; justify-content: center;
    background: rgba(15,21,17,.55); padding: 20px;
  }
  #pdfModal.open { display: flex; }
  .pdf-modal-card {
    background: #fff; border-radius: var(--radius-xl); overflow: hidden;
    width: min(900px,96vw); max-height: 92vh; display: flex; flex-direction: column;
    box-shadow: var(--shadow-lg);
  }
  .pdf-modal-header {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    padding: 10px 12px 10px 20px; border-bottom: 1px solid var(--border-light); background: #fff;
  }
  .pdf-modal-header h4 { font-size: 16px; font-weight: 700; color: var(--ink-dark); }
  .pdf-modal-actions { display: flex; gap: 8px; align-items: center; }
  .pdf-close-btn {
    width: 44px; height: 44px; border-radius: 12px; background: #fff;
    border: 1px solid var(--border-light); color: var(--ink-dark);
    display: flex; align-items: center; justify-content: center; font-size: 18px;
  }
  .pdf-close-btn:hover { background: var(--surface-soft); }
  #pdfFrame { flex: 1; width: 100%; border: none; min-height: 70vh; }

  /* Investment panel */
  .investment-panel { display: grid; grid-template-columns: 1.2fr .8fr; gap: 40px; align-items: center; }
  .investment-panel h2 { font-size: clamp(22px, 2.6vw, 28px); }
  .investment-actions { display: flex; gap: 12px; flex-wrap: wrap; justify-content: flex-end; }
  @media (max-width: 760px) {
    .investment-panel { grid-template-columns: 1fr; gap: 20px; }
    .investment-actions { justify-content: flex-start; }
  }
  @media (max-width: 700px) {
    .deck-head { margin-bottom: 24px; }
    .highlight-card { padding: 18px; display: grid; grid-template-columns: 44px 1fr; column-gap: 14px; align-items: start; }
    .highlight-card .card-icon { margin-bottom: 0; grid-row: span 2; }
    .no-pitch { padding: 28px 18px; }
    .viewer-bar .btn { margin-left: 0; }
  }
</style>
@endsection

@section('content')

{{-- Hero --}}
<div class="pitch-hero">
  <div class="wrap pitch-hero-grid">
    <div>
      <div class="badge" style="margin-bottom:18px;" data-i18n="pitch_badge">INVESTOR PRESENTATION</div>
      <h1 class="page-title" data-i18n="pitch_hero_title">
        MkulimaForum — AI Agriculture Platform for East Africa
      </h1>
      <p class="section-lead" data-i18n="pitch_hero_sub">
        Tazama muhtasari kamili wa mradi wetu, fursa ya soko, muundo wa biashara, teknolojia ya AI, na athari tunazolenga.
      </p>
      <div class="pitch-actions">
        @if(isset($settings['pitch_deck_url']) && $settings['pitch_deck_url'])
        <button onclick="openPDF('{{ $settings['pitch_deck_url'] }}')" class="btn btn-primary btn-lg" data-i18n="pitch_view_btn">👁️ Tazama Pitch Deck Online</button>
        <a href="{{ $settings['pitch_deck_url'] }}" target="_blank" rel="noopener" download class="btn btn-outline btn-lg" data-i18n="pitch_dl_btn">⬇️ Pakua PDF</a>
        @else
        <a href="/contact" class="btn btn-primary btn-lg" data-i18n="pitch_request_btn">📬 Omba Nakala ya Pitch Deck</a>
        @endif
      </div>
    </div>
    <div class="deck-preview-wrap">
      <div class="deck-preview">
        <div class="deck-inner">
          <div class="card-icon"><x-icon name="leaf" size="24" /></div>
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
    <div class="deck-head">
      <span class="eyebrow" data-i18n="deck_eyebrow">KATIKA PITCH DECK</span>
      <h2 class="section-title" data-i18n="deck_title">Deck Inajumuisha Nini</h2>
    </div>
    <div class="highlights-grid">
      @foreach([
        ['globe','Tatizo na Fursa','The problem and East Africa market opportunity','pd0'],
        ['leaf','Dhamira na Maono','Our mission, vision, and approach','pd1'],
        ['phone','Suluhisho la MkulimaForum','Platform walkthrough and all 8 solutions','pd2'],
        ['scan','Mkakati wa Teknolojia','AI stack: Gemini 3, Mkulima AI Offline, offline architecture','pd3'],
        ['storefront','Muundo wa Biashara','Revenue model and monetization strategy','pd4'],
        ['chart','Fursa ya Soko','Market size and expansion roadmap','pd5'],
        ['book','Ramani ya Barabara','Development milestones and go-to-market plan','pd6'],
        ['groups','Timu','Leadership and advisory team','pd7'],
      ] as $item)
      <div class="highlight-card fade-up">
        <div class="card-icon"><x-icon :name="$item[0]" size="22" /></div>
        <h3 data-i18n="{{ $item[3] }}_title">{{ $item[1] }}</h3>
        <p data-i18n="{{ $item[3] }}_desc">{{ $item[2] }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- Online Viewer --}}
<section class="viewer-section">
  <div class="wrap">
    @if(isset($settings['pitch_deck_url']) && $settings['pitch_deck_url'])
    <div class="deck-head">
      <span class="eyebrow" data-i18n="viewer_eyebrow">TAZAMA ONLINE</span>
      <h2 class="section-title" data-i18n="viewer_title">Soma Pitch Deck Hapa</h2>
    </div>
    <div class="viewer-frame">
      <div class="viewer-bar">
        <span class="viewer-file">📄 MkulimaForum_Pitch_Deck.pdf</span>
        <a href="{{ $settings['pitch_deck_url'] }}" target="_blank" rel="noopener" download class="btn btn-outline btn-sm" data-i18n="viewer_dl_btn">⬇️ Pakua</a>
      </div>
      <iframe
        src="{{ $settings['pitch_deck_url'] }}#toolbar=1&view=FitH"
        title="MkulimaForum Pitch Deck"
        loading="lazy"
        sandbox="allow-scripts allow-same-origin allow-popups allow-forms">
      </iframe>
    </div>
    @else
    <div class="no-pitch fade-up">
      <div class="card-icon"><x-icon name="chart" size="24" /></div>
      <h2 data-i18n="no_deck_title">Pitch Deck Haijapakiwa Bado</h2>
      <p data-i18n="no_deck_desc">Admin ya MkulimaForum bado haijapakia faili ya Pitch Deck. Inaweza kupakiwa kupitia Admin Dashboard → Mipangilio → Ukurasa wa Kutua.</p>
      <div class="no-pitch-actions">
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
        <span class="badge" style="margin-bottom:14px;" data-i18n="nda_badge">UWEKEZAJI</span>
        <h2 data-i18n="nda_title">Unatafuta Taarifa Zaidi za Uwekezaji?</h2>
        <p style="margin-top:12px;" data-i18n="nda_desc">Tupo tayari kushiriki taarifa zaidi za kifedha, mkakati wa biashara, na maelezo ya kina zaidi kwa wawekezaji wanaovutika. Wasiliana nasi kupanga mazungumzo.</p>
      </div>
      <div class="investment-actions">
        <a href="/contact?type=investor" class="btn btn-primary" data-i18n="nda_contact_btn">📬 Wasiliana na Timu</a>
        <a href="/impact" class="btn btn-outline" data-i18n="nda_impact_btn">📊 Angalia Athari →</a>
      </div>
    </div>
  </div>
</section>

{{-- PDF Modal --}}
<div id="pdfModal" onclick="closePDFModal(event)" role="dialog" aria-label="Pitch Deck Viewer" aria-modal="true">
  <div class="pdf-modal-card" onclick="event.stopPropagation()">
    <div class="pdf-modal-header">
      <h4 data-i18n="pd_deck_modal_title">📊 MkulimaForum Pitch Deck</h4>
      <div class="pdf-modal-actions">
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
