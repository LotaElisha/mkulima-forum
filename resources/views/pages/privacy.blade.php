@extends('layouts.public')
@section('title', 'Privacy Policy | MkulimaForum')
@section('meta_description', 'How MkulimaForum collects, uses, protects, and retains farmer data.')
@section('head_extra')
<style>
  .legal-wrap { max-width: 800px; }
  .legal-body { padding: 56px 0 80px; }
  .legal { display: grid; gap: 16px; }
  .legal > div {
    background: #fff; border: 1px solid var(--border-light);
    border-radius: var(--radius-xl); padding: 24px 28px;
  }
  .legal h2 { font-size: 20px; font-weight: 700; color: var(--ink-dark); margin-bottom: 8px; }
  .legal p { font-size: 16px; line-height: 1.7; color: var(--ink-body); }
  .legal a { color: var(--forest-dark); font-weight: 600; text-decoration: underline; text-underline-offset: 3px; }
  @media (max-width: 700px) {
    .legal-body { padding: 24px 0 48px; }
    .legal { gap: 12px; }
    .legal > div { padding: 18px; border-radius: 14px; }
    .legal h2 { font-size: 18px; }
  }
</style>
@endsection
@section('content')
<section class="page-hero"><div class="wrap legal-wrap"><span class="eyebrow">FARAGHA / PRIVACY</span><h1 class="page-title">Sera ya Faragha</h1><p class="section-lead">Ilisasishwa: 11 Agosti 2026. Sera hii inaeleza data tunazokusanya na jinsi tunavyoilinda.</p></div></section>
<section class="legal-body"><div class="wrap legal-wrap legal">
<div><h2>Data tunayokusanya</h2><p>Tunakusanya maelezo ya akaunti, shughuli za soko na jamii, taarifa za kifaa, na picha unazowasilisha kwa uchambuzi. Taarifa za malipo hushughulikiwa pamoja na watoa huduma wa malipo.</p></div>
<div><h2>Jinsi tunavyotumia data</h2><p>Tunatumia data kutoa huduma, kulinda akaunti na miamala, kuboresha usahihi wa huduma, kuzuia udanganyifu, na kutimiza wajibu wa kisheria.</p></div>
<div><h2>Uhifadhi na usalama</h2><p>Picha za uchunguzi huhifadhiwa kwa faragha na hupatikana kwa mwenye akaunti pekee. Tunatumia udhibiti wa ufikiaji, usimbaji unaofaa, kumbukumbu za ukaguzi, na muda mdogo wa kuhifadhi data.</p></div>
<div><h2>Haki zako</h2><p>Unaweza kuomba nakala, marekebisho, au kufutwa kwa data yako, kwa kiwango kinachoruhusiwa na sheria na mahitaji ya kumbukumbu za kifedha.</p></div>
<div><h2>Wasiliana nasi</h2><p>Kwa maswali ya faragha, tumia ukurasa wa <a href="/contact">mawasiliano</a>.</p></div>
</div></section>
@endsection
