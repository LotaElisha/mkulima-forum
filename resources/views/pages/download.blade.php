@extends('layouts.public')

@section('title', 'Pakua App — MkulimaForum')

@section('content')
<section class="section" style="min-height:68vh; display:grid; place-items:center;">
  <div class="container" style="max-width:760px; text-align:center;">
    <div style="width:72px; height:72px; margin:0 auto 22px; border-radius:22px; display:grid; place-items:center; background:var(--sun-gold); color:var(--ink-dark);">
      <span class="material-symbols-outlined" style="font-size:36px;">android</span>
    </div>
    <span class="eyebrow">TOLEO LA MAJARIBIO</span>
    <h1 class="page-title" style="font-size:clamp(2.3rem,6vw,4rem); margin:12px 0 18px;">Jaribu MkulimaForum kwenye Android</h1>
    <p style="font-size:1.05rem; color:var(--ink-muted); max-width:600px; margin:0 auto 28px; line-height:1.75;">
      Pakua APK ya majaribio yenye muonekano mpya. Toleo hili limeunganishwa na huduma za MkulimaForum na linakusudiwa kwa upimaji kabla ya uzinduzi rasmi.
    </p>

    <a href="/app/mkulima-forum-test.apk" download="MkulimaForum-Test.apk" class="btn btn-gold btn-lg" style="margin-bottom:18px;">
      <span class="material-symbols-outlined" aria-hidden="true">download</span>
      Pakua APK ya Android
    </a>

    <div style="display:flex; justify-content:center; gap:10px; flex-wrap:wrap; margin-bottom:30px; color:var(--ink-muted); font-size:.86rem;">
      <span class="tag">Android</span>
      <span class="tag">Toleo 1.0.0</span>
      <span class="tag">Takriban MB 171</span>
    </div>

    <div style="background:var(--surface-soft); border:1px solid var(--border-light); border-radius:18px; padding:20px 22px; text-align:left; max-width:620px; margin:0 auto;">
      <div style="display:flex; gap:12px; align-items:flex-start;">
        <span class="material-symbols-outlined" style="color:var(--sun-amber);">info</span>
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
