@extends('layouts.public')

@section('title', 'Mkulima Verify | Scan. Verify. Protect. — Agricultural Input Anti-Counterfeit Tanzania')
@section('meta_description', 'Mkulima Verify protects Tanzanian farmers against fake seeds, pesticides, and fertilizers. Scan registration numbers, verify agrodealers, and report suspicious inputs.')
@section('og_title', 'Mkulima Verify | Scan. Verify. Protect. — AgriTech Tanzania')
@section('og_description', 'Changanua. Thibitisha. Linda. Protect your farm against counterfeit inputs with Mkulima Verify.')

@section('head_extra')
<style>
  .verify-hero {
    background: radial-gradient(circle at 60% 30%, #1F6B38 0%, var(--forest-dark) 70%);
    color:#fff; padding:90px 0 100px; text-align:center; position:relative; overflow:hidden;
  }
  .scan-box-wrap {
    background:var(--surface-card); border:1px solid var(--border-light); border-radius:var(--radius-2xl);
    padding:40px; box-shadow:var(--shadow-md); max-width:640px; margin:-50px auto 60px; position:relative; z-index:10;
  }
  .provenance-tag {
    display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:6px; font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em;
  }
  .provenance-regulatory { background:#E6F4EA; color:#137333; }
  .provenance-platform { background:#E8F0FE; color:#1A73E8; }
  .provenance-ai { background:#FFEFE7; color:#D93025; }
  .provenance-community { background:#FEF7E0; color:#B06000; }
</style>
@endsection

@section('content')

{{-- Hero --}}
<div class="verify-hero">
  <div class="wrap fade-up" style="max-width:720px;">
    <span class="badge dark" style="margin-bottom:16px;" data-i18n="v_hero_badge">MKULIMA VERIFY</span>
    <h1 style="font-size:clamp(2.4rem,6vw,3.6rem); font-weight:900; color:#fff; line-height:1.1; margin-bottom:16px;" data-i18n="v_hero_title">
      Changanua. Thibitisha. Linda.
    </h1>
    <p style="font-size:1.1rem; color:rgba(255,255,255,.9); line-height:1.7; margin-bottom:24px;" data-i18n="v_hero_sub">
      Kinga shamba lako dhidi ya pembejeo feki. Kagua namba za usajili za mbegu (TOSCI), dawa za mimea (TPHPA), mbolea (TFRA), na mawakala waliothibitishwa.
    </p>
  </div>
</div>

{{-- Scan Input Box --}}
<div class="wrap">
  <div class="scan-box-wrap fade-up">
    <h3 style="font-size:1.2rem; font-weight:800; color:var(--ink-dark); margin-bottom:16px;" data-i18n="v_scan_box_title">🔍 Kagua Pembejeo Hapa</h3>
    <form onsubmit="handleVerifyScan(event)" style="display:flex; flex-direction:column; gap:14px;">
      <div style="display:flex; gap:10px;">
        <input 
          id="scan_input"
          type="text" 
          required 
          placeholder="Ingiza Namba ya Usajili, Serial Code au Chapa..."
          data-i18n-ph="v_scan_ph"
          style="flex:1; padding:14px 18px; border:1.5px solid var(--border-light); border-radius:12px; font-size:1rem; outline:none;"
        >
        <button type="submit" class="btn btn-primary btn-lg" id="scan_btn" data-i18n="v_scan_btn">
          Thibitisha
        </button>
      </div>
      <p style="font-size:.78rem; color:var(--ink-muted); text-align:left;" data-i18n="v_scan_disclaimer">
        * Mathibitisho yote yanatolewa kulingana na data rasmi au za Mkulima Forum. Data za AI zinaonyeshwa kwa alama za wazi za ushuhuda (Rule 5).
      </p>
    </form>

    {{-- Scan Result Container --}}
    <div id="scan_result_box" style="display:none; margin-top:24px; text-align:left; padding:20px; border-radius:14px; border:1px solid var(--border-light);">
      <div style="display:flex; items-center; justify-content:space-between; margin-bottom:12px;">
        <span id="res_status_badge" class="badge" style="font-weight:800; font-size:.85rem;"></span>
        <span id="res_provenance_badge" class="provenance-tag"></span>
      </div>
      <h4 id="res_title" style="font-size:1.1rem; font-weight:800; color:var(--ink-dark); margin-bottom:8px;"></h4>
      <div id="res_reasons" style="font-size:.9rem; color:var(--ink-muted); line-height:1.6; margin-bottom:14px;"></div>
      <div id="res_action" style="padding:12px 16px; border-radius:10px; font-size:.88rem; font-weight:700; background:var(--leaf-pale); color:var(--forest-dark);"></div>
    </div>
  </div>
</div>

{{-- Features Grid --}}
<section style="padding-top:0;">
  <div class="wrap">
    <div style="text-align:center; margin-bottom:44px;">
      <span class="eyebrow" data-i18n="v_feat_eyebrow">HUDUMA ZA MKULIMA VERIFY</span>
      <h2 class="section-title" data-i18n="v_feat_title">Kinga Shamba Lako Dhidi ya Hasara</h2>
    </div>

    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:28px;">
      @foreach([
        ['🌱','Ukaguzi wa Mbegu (TOSCI)','Seed Verification','Thibitisha aina za mbegu zilizosajiliwa na taasisi ya TOSCI kabla ya kupanda.','Verify certified seed varieties registered by TOSCI before planting.','vf0'],
        ['🧪','Ukaguzi wa Dawa (TPHPA)','Pesticide Verification','Kagua dawa za kuua wadudu na magugu zilizoidhinishwa na mamlaka ya TPHPA.','Check crop protection products approved by TPHPA regulatory agency.','vf1'],
        ['🏪','Mawakala Waliothibitishwa','Agrodealer KYC','Tafuta maduka ya pembejeo yenye leseni halali za kisheria (Mkulima Verified).','Locate agro-dealers with matched licences and Mkulima Verified trust badges.','vf2'],
      ] as $feat)
      <div style="background:var(--surface-card); border:1px solid var(--border-light); border-radius:var(--radius-xl); padding:32px;">
        <div style="font-size:2.8rem; margin-bottom:16px;">{{ $feat[0] }}</div>
        <h3 style="font-size:1.2rem; font-weight:800; color:var(--ink-dark); margin-bottom:8px;" data-i18n="{{ $feat[5] }}_title">{{ $feat[1] }}</h3>
        <p style="font-size:.9rem; color:var(--ink-muted); line-height:1.7;" data-i18n="{{ $feat[5] }}_desc">{{ $feat[3] }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

@endsection

@section('page_scripts')
<script>
async function handleVerifyScan(e) {
  e.preventDefault();
  const input = document.getElementById('scan_input').value;
  const btn = document.getElementById('scan_btn');
  const resBox = document.getElementById('scan_result_box');

  btn.disabled = true;
  btn.textContent = '⏳ Inathibitisha...';

  try {
    const res = await fetch('/api/v1/verify/scan', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ input, scan_method: 'manual' })
    });
    const json = await res.json();
    const data = json.data;

    resBox.style.display = 'block';
    document.getElementById('res_title').textContent = data.product ? data.product.trade_name : input;
    
    const badge = document.getElementById('res_status_badge');
    badge.textContent = data.status.replace('_', ' ');
    badge.className = 'badge ' + (data.status === 'VERIFIED' || data.status === 'REGISTERED_SOURCE_CONFIRMED' ? 'green' : 'amber');

    const prov = document.getElementById('res_provenance_badge');
    prov.textContent = 'Source: ' + data.provenance;
    prov.className = 'provenance-tag provenance-' + data.provenance.toLowerCase();

    document.getElementById('res_reasons').innerHTML = data.reasons.map(r => `• ${r}`).join('<br>');
    document.getElementById('res_action').textContent = data.recommended_action[MK_LANG] || data.recommended_action['sw'];

  } catch (err) {
    alert('Error performing verification scan.');
  } finally {
    btn.disabled = false;
    btn.textContent = 'Thibitisha';
  }
}

const mkPageTranslations = {
  sw: {
    v_hero_badge: 'MKULIMA VERIFY', v_hero_title: 'Changanua. Thibitisha. Linda.',
    v_hero_sub: 'Kinga shamba lako dhidi ya pembejeo feki. Kagua namba za usajili za mbegu (TOSCI), dawa za mimea (TPHPA), mbolea (TFRA), na mawakala waliothibitishwa.',
    v_scan_box_title: '🔍 Kagua Pembejeo Hapa', v_scan_btn: 'Thibitisha', v_scan_ph: 'Ingiza Namba ya Usajili, Serial Code au Chapa...',
    v_scan_disclaimer: '* Mathibitisho yote yanatolewa kulingana na data rasmi au za Mkulima Forum.',
    v_feat_eyebrow: 'HUDUMA ZA MKULIMA VERIFY', v_feat_title: 'Kinga Shamba Lako Dhidi ya Hasara',
    vf0_title: 'Ukaguzi wa Mbegu (TOSCI)', vf0_desc: 'Thibitisha aina za mbegu zilizosajiliwa na taasisi ya TOSCI kabla ya kupanda.',
    vf1_title: 'Ukaguzi wa Dawa (TPHPA)', vf1_desc: 'Kagua dawa za kuua wadudu na magugu zilizoidhinishwa na mamlaka ya TPHPA.',
    vf2_title: 'Mawakala Waliothibitishwa', vf2_desc: 'Tafuta maduka ya pembejeo yenye leseni halali za kisheria (Mkulima Verified).',
  },
  en: {
    v_hero_badge: 'MKULIMA VERIFY', v_hero_title: 'Scan. Verify. Protect.',
    v_hero_sub: 'Protect your farm against fake inputs. Verify registration numbers for seeds (TOSCI), pesticides (TPHPA), fertilizers (TFRA), and trusted agrodealers.',
    v_scan_box_title: '🔍 Verify Agricultural Input', v_scan_btn: 'Verify Now', v_scan_ph: 'Enter Registration Number, Serial Code or Brand...',
    v_scan_disclaimer: '* All verifications sourced from regulatory records or Mkulima Forum registry.',
    v_feat_eyebrow: 'MKULIMA VERIFY SERVICES', v_feat_title: 'Protect Your Farm From Crop Loss',
    vf0_title: 'Seed Certification (TOSCI)', vf0_desc: 'Verify certified seed varieties registered by TOSCI before planting.',
    vf1_title: 'Pesticide Approval (TPHPA)', vf1_desc: 'Check crop protection products approved by TPHPA regulatory agency.',
    vf2_title: 'Mkulima Verified Dealers', vf2_desc: 'Locate agro-dealers with matched licences and Mkulima Verified trust badges.',
  }
};
</script>
@endsection
