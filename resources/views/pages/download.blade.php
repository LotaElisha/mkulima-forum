@extends('layouts.public')

@section('title', 'Pakua App — MkulimaForum')

@section('content')
<section class="section" style="min-height:68vh; display:grid; place-items:center;">
  <div class="container" style="max-width:760px; text-align:center;">
    <div style="width:72px; height:72px; margin:0 auto 22px; border-radius:22px; display:grid; place-items:center; background:var(--sun-gold); color:var(--ink-dark);">
      <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="2" width="14" height="20" rx="2.5"/><path d="M11 18h2"/></svg>
    </div>
    <span class="eyebrow">TOLEO LA MAJARIBIO</span>
    <h1 class="page-title" style="font-size:clamp(2.3rem,6vw,4rem); margin:12px 0 18px;">Jaribu MkulimaForum kwenye Android</h1>
    <p style="font-size:1.05rem; color:var(--ink-muted); max-width:600px; margin:0 auto 28px; line-height:1.75;">
      Pakua APK ya majaribio yenye muonekano mpya. Toleo hili limeunganishwa na huduma za MkulimaForum na linakusudiwa kwa upimaji kabla ya uzinduzi rasmi.
    </p>

    @php($androidBuild = \App\Support\AppDownload::android())

    @if($androidBuild)
      {{-- Filename and size are read from the build actually on disk. The page
           used to hardcode both, so it kept advertising a stale APK. --}}
      <a href="{{ $androidBuild['url'] }}" download="MkulimaForum.apk" class="btn btn-gold btn-lg" style="margin-bottom:18px;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Pakua APK ya Android
      </a>
    @else
      <p style="color:var(--ink-muted); margin-bottom:18px;">Toleo la Android bado halijachapishwa. Rudi hapa hivi karibuni.</p>
    @endif

    @if(\App\Support\AppDownload::hasWebBuild())
      {{-- Only rendered when public/app/web/index.html exists. This button used
           to be unconditional and always resolved to a 404. --}}
      <a href="{{ \App\Support\AppDownload::webUrl() }}" class="btn btn-outline btn-lg" style="margin:0 0 18px 10px;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
        Fungua Web App
      </a>
    @endif

    <div style="display:flex; justify-content:center; gap:10px; flex-wrap:wrap; margin-bottom:30px; color:var(--ink-muted); font-size:.86rem;">
      <span class="tag">Android</span>
      <span class="tag">Toleo {{ config('app.version', '1.0.0') }}</span>
      @if($androidBuild)<span class="tag">Takriban {{ $androidBuild['human'] }}</span>@endif
    </div>

    <div style="background:var(--surface-soft); border:1px solid var(--border-light); border-radius:18px; padding:20px 22px; text-align:left; max-width:620px; margin:0 auto;">
      <div style="display:flex; gap:12px; align-items:flex-start;">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--sun-amber); flex:none;" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
        <div>
          <strong style="display:block; color:var(--ink-dark); margin-bottom:5px;">Kumbuka: hili ni toleo la majaribio</strong>
          <p style="color:var(--ink-muted); font-size:.88rem; line-height:1.65; margin:0;">
            Android inaweza kukuomba uruhusu usakinishaji kutoka kwenye kivinjari. APK hii imesainiwa kwa ufunguo wa majaribio; usiitumie kama toleo la uzalishaji au kuisambaza kwenye Play Store.
          </p>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
