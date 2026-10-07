@extends('layouts.public')

@section('title', 'Pakua App — MkulimaForum')

@section('head_extra')
<style>
  .dl-section { min-height: 68vh; display: grid; place-items: center; padding: 72px 0 80px; }
  .dl-inner { max-width: 760px; text-align: center; }
  .dl-mark {
    width: 72px; height: 72px; margin: 0 auto 22px; border-radius: 20px;
    display: grid; place-items: center;
    background: var(--leaf-pale); color: var(--forest-mid);
  }
  .dl-lead { font-size: 17px; color: var(--ink-muted); max-width: 600px; margin: 0 auto 28px; line-height: 1.7; }
  .dl-pending { color: var(--ink-muted); margin-bottom: 18px; }
  .dl-actions { display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; margin-bottom: 18px; }
  .dl-tags { display: flex; justify-content: center; gap: 8px; flex-wrap: wrap; margin-bottom: 32px; }
  .dl-note {
    background: var(--surface-soft); border: 1px solid var(--border-light);
    border-radius: var(--radius-xl); padding: 20px 22px;
    text-align: left; max-width: 620px; margin: 0 auto;
    display: flex; gap: 12px; align-items: flex-start;
  }
  .dl-note svg { color: var(--sun-gold); flex: none; margin-top: 1px; }
  .dl-note strong { display: block; color: var(--ink-dark); margin-bottom: 4px; font-size: 16px; }
  .dl-note p { color: var(--ink-muted); font-size: 15px; line-height: 1.65; margin: 0; }
  @media (max-width: 700px) {
    .dl-section { min-height: 0; padding: 32px 0 48px; }
    .dl-mark { width: 60px; height: 60px; border-radius: 16px; margin-bottom: 16px; }
    .dl-lead { font-size: 16px; margin-bottom: 22px; }
    .dl-note { padding: 16px; border-radius: 14px; }
  }
</style>
@endsection

@section('content')
<section class="dl-section">
  <div class="wrap dl-inner">
    <div class="dl-mark">
      <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="2" width="14" height="20" rx="2.5"/><path d="M11 18h2"/></svg>
    </div>
    <span class="eyebrow">TOLEO LA MAJARIBIO</span>
    <h1 class="page-title">Jaribu MkulimaForum kwenye Android</h1>
    <p class="dl-lead">
      Pakua APK ya majaribio yenye muonekano mpya. Toleo hili limeunganishwa na huduma za MkulimaForum na linakusudiwa kwa upimaji kabla ya uzinduzi rasmi.
    </p>

    @php($androidBuild = \App\Support\AppDownload::android())

    <div class="dl-actions">
    @if($androidBuild)
      {{-- Filename and size are read from the build actually on disk. The page
           used to hardcode both, so it kept advertising a stale APK. --}}
      <a href="{{ $androidBuild['url'] }}" download="MkulimaForum.apk" class="btn btn-primary btn-lg">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Pakua APK ya Android
      </a>
    @else
      <p class="dl-pending">Toleo la Android bado halijachapishwa. Rudi hapa hivi karibuni.</p>
    @endif

    @if(\App\Support\AppDownload::hasWebBuild())
      {{-- Only rendered when public/app/web/index.html exists. This button used
           to be unconditional and always resolved to a 404. --}}
      <a href="{{ \App\Support\AppDownload::webUrl() }}" class="btn btn-outline btn-lg">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
        Fungua Web App
      </a>
    @endif
    </div>

    <div class="dl-tags">
      <span class="tag">Android</span>
      <span class="tag">Toleo {{ config('app.version', '1.0.0') }}</span>
      @if($androidBuild)<span class="tag">Takriban {{ $androidBuild['human'] }}</span>@endif
    </div>

    <div class="dl-note">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
        <div>
          <strong>Kumbuka: hili ni toleo la majaribio</strong>
          <p>
            Android inaweza kukuomba uruhusu usakinishaji kutoka kwenye kivinjari. APK hii imesainiwa kwa ufunguo wa majaribio; usiitumie kama toleo la uzalishaji au kuisambaza kwenye Play Store.
          </p>
        </div>
    </div>
  </div>
</section>
@endsection
