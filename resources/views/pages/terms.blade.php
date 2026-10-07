@extends('layouts.public')
@section('title', 'Terms of Service | MkulimaForum')
@section('meta_description', 'Terms governing use of MkulimaForum services.')
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
<section class="page-hero"><div class="wrap legal-wrap"><span class="eyebrow">MASHARTI / TERMS</span><h1 class="page-title">Masharti ya Matumizi</h1><p class="section-lead">Ilisasishwa: 11 Agosti 2026. Kwa kutumia MkulimaForum, unakubali masharti haya.</p></div></section>
<section class="legal-body"><div class="wrap legal-wrap legal">
<div><h2>Matumizi yanayokubalika</h2><p>Usitumie huduma kwa udanganyifu, unyanyasaji, uvunjaji wa sheria, kueneza taarifa hatari, au kuingilia akaunti na mifumo ya wengine.</p></div>
<div><h2>Ushauri wa kilimo na AI</h2><p>Majibu ya AI na uthibitishaji wa pembejeo ni msaada wa maamuzi, si dhamana. Thibitisha maamuzi yenye hatari kubwa na mtaalamu au mdhibiti husika.</p></div>
<div><h2>Soko na malipo</h2><p>Watumiaji wanawajibika kwa usahihi wa orodha na taarifa zao. Malipo na escrow hufuata hali iliyoonyeshwa kwenye muamala; usithibitishe kupokea bidhaa kabla ya kuikagua.</p></div>
<div><h2>Kusimamisha akaunti</h2><p>Tunaweza kuzuia akaunti inayohatarisha watumiaji, fedha, au uadilifu wa jukwaa, kwa kuzingatia mchakato wa mapitio unaofaa.</p></div>
<div><h2>Mawasiliano</h2><p>Maswali kuhusu masharti haya yanaweza kutumwa kupitia ukurasa wa <a href="/contact">mawasiliano</a>.</p></div>
</div></section>
@endsection
