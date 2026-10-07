@extends('layouts.public')

@section('title', 'Mkulima Verify | Scan. Verify. Protect. — Agricultural Input Anti-Counterfeit Tanzania')
@section('meta_description', 'Mkulima Verify protects Tanzanian farmers against fake seeds, pesticides, and fertilizers. Scan registration numbers, verify agrodealers, and report suspicious inputs.')
@section('og_title', 'Mkulima Verify | Scan. Verify. Protect. — AgriTech Tanzania')
@section('og_description', 'Changanua. Thibitisha. Linda. Protect your farm against counterfeit inputs with Mkulima Verify.')

@section('head_extra')
<style>
  /* Tokens, buttons, header and footer come from layouts/public.blade.php. */
  .verify-hero { padding:72px 0 88px; text-align:center; background:#fff; }
  .verify-title { font-size:clamp(32px,5vw,52px); font-weight:800; line-height:1.1; letter-spacing:-.025em; color:var(--ink-dark); margin:0 auto 16px; }
  .verify-sub { font-size:17px; line-height:1.65; max-width:40rem; margin:0 auto; }

  .scan-box-wrap {
    background:#fff; border:1px solid var(--border-light); border-radius:var(--radius-xl);
    padding:32px; box-shadow:var(--shadow-md); max-width:640px; margin:-44px auto 64px; position:relative; z-index:10;
  }
  .scan-title { font-size:20px; font-weight:700; color:var(--ink-dark); margin-bottom:16px; }
  .scan-form { display:flex; flex-direction:column; gap:12px; }
  .scan-row { display:flex; gap:10px; }
  .scan-row input {
    flex:1; min-width:0; min-height:52px; padding:12px 16px;
    border:1.5px solid var(--border-mid); border-radius:12px; color:var(--ink-dark); background:#fff;
  }
  .scan-row input:focus { outline:none; border-color:var(--forest-mid); box-shadow:0 0 0 3px var(--leaf-pale); }
  .scan-help { font-size:13px; color:var(--ink-muted); text-align:left; line-height:1.5; }
  .scan-error { display:none; color:#9B1C1C; text-align:left; font-size:15px; }

  .scan-result { display:none; margin-top:20px; text-align:left; padding:20px; border-radius:14px; border:1px solid var(--border-light); background:#fff; }
  .scan-result-head { display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; margin-bottom:12px; }
  .scan-result h4 { font-size:18px; font-weight:700; color:var(--ink-dark); margin-bottom:8px; }
  .res-reasons { font-size:15px; color:var(--ink-muted); line-height:1.6; margin-bottom:14px; }
  .res-action { padding:12px 16px; border-radius:12px; font-size:15px; font-weight:600; background:var(--leaf-pale); color:var(--forest-dark); }
  .badge.amber { background:var(--accent-soft); color:var(--sun-gold); }

  .provenance-tag {
    display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:999px;
    font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:.04em;
    background:var(--surface-soft); color:var(--ink-body); border:1px solid var(--border-light);
  }
  .provenance-regulatory { background:var(--leaf-pale); color:var(--forest-dark); border-color:transparent; }
  .provenance-platform   { background:var(--surface-soft); color:var(--ink-body); }
  .provenance-ai         { background:#FDECEC; color:#9B1C1C; border-color:transparent; }
  .provenance-community  { background:var(--accent-soft); color:var(--sun-gold); border-color:transparent; }

  .verify-feature-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; }
  .verify-feature-grid .card-icon svg { width:24px; height:24px; }

  @media(max-width:860px){ .verify-feature-grid { grid-template-columns:1fr; gap:12px; } }
  @media(max-width:700px){
    .verify-hero { padding:32px 0 64px !important; }
    .verify-title { font-size:30px; line-height:1.15; }
    .verify-sub { font-size:16px; }
    .scan-box-wrap { padding:20px 16px; margin:-36px 0 40px; border-radius:14px; }
    .scan-row { flex-direction:column; }
    .scan-row .btn { width:100%; }
  }
</style>
@endsection

@section('content')

{{-- Hero --}}
<div class="verify-hero">
  <div class="wrap fade-up">
    <span class="badge" style="margin-bottom:16px;" data-i18n="v_hero_badge">MKULIMA VERIFY</span>
    <h1 class="verify-title" data-i18n="v_hero_title">
      Changanua. Thibitisha. Linda.
    </h1>
    <p class="verify-sub" data-i18n="v_hero_sub">
      Kinga shamba lako dhidi ya pembejeo feki. Kagua namba za usajili za mbegu (TOSCI), dawa za mimea (TPHPA), mbolea (TFRA), na mawakala waliothibitishwa.
    </p>
  </div>
</div>

{{-- Scan Input Box --}}
<div class="wrap">
  <div class="scan-box-wrap fade-up">
    <h3 class="scan-title" data-i18n="v_scan_box_title">Kagua Pembejeo Hapa</h3>
    <form class="scan-form" onsubmit="handleVerifyScan(event)">
      <div class="scan-row">
        <label for="scan_input" class="sr-only">Namba ya usajili, serial code au chapa</label>
        <input 
          id="scan_input"
          type="text" 
          required 
          placeholder="Ingiza Namba ya Usajili, Serial Code au Chapa..."
          data-i18n-ph="v_scan_ph"
          aria-describedby="scan_help scan_error"
        >
        <button type="submit" class="btn btn-primary btn-lg" id="scan_btn" data-i18n="v_scan_btn">
          Thibitisha
        </button>
      </div>
      <p id="scan_help" class="scan-help" data-i18n="v_scan_disclaimer">
        * Mathibitisho yote yanatolewa kulingana na data rasmi au za Mkulima Forum. Data za AI zinaonyeshwa kwa alama za wazi za ushuhuda (Rule 5).
      </p>
      <p id="scan_error" class="scan-error" role="alert" aria-live="polite"></p>
    </form>

    {{-- Scan Result Container --}}
    <div id="scan_result_box" class="scan-result">
      <div class="scan-result-head">
        <span id="res_status_badge" class="badge"></span>
        <span id="res_provenance_badge" class="provenance-tag"></span>
      </div>
      <h4 id="res_title"></h4>
      <div id="res_reasons" class="res-reasons"></div>
      <div id="res_action" class="res-action"></div>
    </div>
  </div>
</div>

{{-- Features Grid --}}
<section style="padding-top:0;">
  <div class="wrap">
    <div style="text-align:center; margin-bottom:32px;">
      <span class="eyebrow" data-i18n="v_feat_eyebrow">HUDUMA ZA MKULIMA VERIFY</span>
      <h2 class="section-title" data-i18n="v_feat_title">Kinga Shamba Lako Dhidi ya Hasara</h2>
    </div>

    <div class="verify-feature-grid">
      @foreach([
        ['seed','Ukaguzi wa Mbegu (TOSCI)','Seed Verification','Thibitisha aina za mbegu zilizosajiliwa na taasisi ya TOSCI kabla ya kupanda.','Verify certified seed varieties registered by TOSCI before planting.','vf0'],
        ['input','Ukaguzi wa Dawa (TPHPA)','Pesticide Verification','Kagua dawa za kuua wadudu na magugu zilizoidhinishwa na mamlaka ya TPHPA.','Check crop protection products approved by TPHPA regulatory agency.','vf1'],
        ['dealer','Mawakala Waliothibitishwa','Agrodealer KYC','Tafuta maduka ya pembejeo yenye leseni halali za kisheria (Mkulima Verified).','Locate agro-dealers with matched licences and Mkulima Verified trust badges.','vf2'],
      ] as $feat)
      <div class="card">
        <div class="card-icon" aria-hidden="true">
          @if($feat[0] === 'seed')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22V10"/><path d="M12 13C7 13 4 10 4 5c5 0 8 3 8 8Z"/><path d="M12 17c5 0 8-3 8-8-5 0-8 3-8 8Z"/></svg>
          @elseif($feat[0] === 'input')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 3h6"/><path d="M10 3v6l-5 9a2 2 0 0 0 2 3h10a2 2 0 0 0 2-3l-5-9V3"/><path d="M8 15h8"/></svg>
          @else
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 10h18"/><path d="M5 10v10h14V10"/><path d="m4 10 2-6h12l2 6"/><path d="M9 20v-6h6v6"/></svg>
          @endif
        </div>
        <h3 data-i18n="{{ $feat[5] }}_title">{{ $feat[1] }}</h3>
        <p data-i18n="{{ $feat[5] }}_desc">{{ $feat[3] }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

@endsection

@section('page_scripts')
<script nonce="{{ $cspNonce ?? '' }}">
async function handleVerifyScan(e) {
  e.preventDefault();
  const input = document.getElementById('scan_input').value;
  const btn = document.getElementById('scan_btn');
  const resBox = document.getElementById('scan_result_box');
  const errorBox = document.getElementById('scan_error');
  errorBox.style.display = 'none';
  errorBox.textContent = '';

  btn.disabled = true;
  btn.textContent = '⏳ Inathibitisha...';

  try {
    const res = await fetch('/api/v1/verify/scan', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ input, scan_method: 'manual' })
    });
    const json = await res.json();
    if (!res.ok || !json.data) throw new Error(json.message || 'Verification failed');
    const data = json.data;

    resBox.style.display = 'block';
    document.getElementById('res_title').textContent = data.product ? data.product.trade_name : input;
    
    const badge = document.getElementById('res_status_badge');
    badge.textContent = data.status.replace('_', ' ');
    badge.className = 'badge ' + (data.status === 'VERIFIED' || data.status === 'REGISTERED_SOURCE_CONFIRMED' ? 'green' : 'amber');

    const prov = document.getElementById('res_provenance_badge');
    prov.textContent = 'Source: ' + data.provenance;
    prov.className = 'provenance-tag provenance-' + data.provenance.toLowerCase();

    const reasons = document.getElementById('res_reasons');
    reasons.replaceChildren(...data.reasons.flatMap((reason, index) => {
      const nodes = [document.createTextNode(`• ${reason}`)];
      if (index < data.reasons.length - 1) nodes.push(document.createElement('br'));
      return nodes;
    }));
    document.getElementById('res_action').textContent = data.recommended_action[MK_LANG] || data.recommended_action['sw'];

  } catch (err) {
    errorBox.textContent = err.message || 'Imeshindikana kuthibitisha. Tafadhali jaribu tena.';
    errorBox.style.display = 'block';
  } finally {
    btn.disabled = false;
    btn.textContent = 'Thibitisha';
  }
}

mkPageTranslations = {
  sw: {
    v_hero_badge: 'MKULIMA VERIFY', v_hero_title: 'Changanua. Thibitisha. Linda.',
    v_hero_sub: 'Kinga shamba lako dhidi ya pembejeo feki. Kagua namba za usajili za mbegu (TOSCI), dawa za mimea (TPHPA), mbolea (TFRA), na mawakala waliothibitishwa.',
    v_scan_box_title: 'Kagua Pembejeo Hapa', v_scan_btn: 'Thibitisha', v_scan_ph: 'Ingiza Namba ya Usajili, Serial Code au Chapa...',
    v_scan_disclaimer: '* Mathibitisho yote yanatolewa kulingana na data rasmi au za Mkulima Forum.',
    v_feat_eyebrow: 'HUDUMA ZA MKULIMA VERIFY', v_feat_title: 'Kinga Shamba Lako Dhidi ya Hasara',
    vf0_title: 'Ukaguzi wa Mbegu (TOSCI)', vf0_desc: 'Thibitisha aina za mbegu zilizosajiliwa na taasisi ya TOSCI kabla ya kupanda.',
    vf1_title: 'Ukaguzi wa Dawa (TPHPA)', vf1_desc: 'Kagua dawa za kuua wadudu na magugu zilizoidhinishwa na mamlaka ya TPHPA.',
    vf2_title: 'Mawakala Waliothibitishwa', vf2_desc: 'Tafuta maduka ya pembejeo yenye leseni halali za kisheria (Mkulima Verified).',
  },
  en: {
    v_hero_badge: 'MKULIMA VERIFY', v_hero_title: 'Scan. Verify. Protect.',
    v_hero_sub: 'Protect your farm against fake inputs. Verify registration numbers for seeds (TOSCI), pesticides (TPHPA), fertilizers (TFRA), and trusted agrodealers.',
    v_scan_box_title: 'Verify Agricultural Input', v_scan_btn: 'Verify Now', v_scan_ph: 'Enter Registration Number, Serial Code or Brand...',
    v_scan_disclaimer: '* All verifications sourced from regulatory records or Mkulima Forum registry.',
    v_feat_eyebrow: 'MKULIMA VERIFY SERVICES', v_feat_title: 'Protect Your Farm From Crop Loss',
    vf0_title: 'Seed Certification (TOSCI)', vf0_desc: 'Verify certified seed varieties registered by TOSCI before planting.',
    vf1_title: 'Pesticide Approval (TPHPA)', vf1_desc: 'Check crop protection products approved by TPHPA regulatory agency.',
    vf2_title: 'Mkulima Verified Dealers', vf2_desc: 'Locate agro-dealers with matched licences and Mkulima Verified trust badges.',
  }
};
</script>
@endsection
