@extends('layouts.public')
@section('title', 'Ombi la Kufuta Akaunti na Data | Account Deletion — Mkulima Forum')
@section('meta_description', 'Jinsi ya kuomba kufuta akaunti yako na data zako zote kwenye Mkulima Forum.')

@section('content')
<section class="page-hero">
  <div class="wrap" style="max-width:800px;">
    <span class="eyebrow">USALAMA NA DATA / DATA SAFETY</span>
    <h1 class="section-title">Kufuta Akaunti na Data Zako</h1>
    <p class="section-lead">Mkulima Forum inaheshimu haki yako ya faragha. Ukurasa huu unaeleza jinsi ya kuomba kufutwa kwa akaunti na taarifa zako zote.</p>
  </div>
</section>

<section>
  <div class="wrap" style="max-width:800px; display:grid; gap:32px;">
    <div style="background:var(--surface-card, #fff); border:1px solid var(--border-light, #e2e8f0); border-radius:16px; padding:28px;">
      <h2 style="font-size:1.3rem; font-weight:800; color:var(--forest-dark, #166534); margin-bottom:12px;">1. Jinsi ya Kuomba Kufuta Akaunti (How to Request Deletion)</h2>
      <p style="margin-bottom:14px; line-height:1.6;">Unaweza kuomba kufuta akaunti yako ya Mkulima Forum kwa njia hii:</p>
      <ul style="padding-left:20px; line-height:1.8;">
        <li><strong>Kupitia Barua Pepe:</strong> Tuma barua pepe kwenda <a href="mailto:support@mkulimaforum.com" style="color:var(--forest-mid, #166534); font-weight:700;">support@mkulimaforum.com</a> ukitumia barua pepe au namba ya simu uliyojisajili nayo, ukiomba kufuta akaunti yako. Ombi lako litashughulikiwa ndani ya siku 7 za kazi.</li>
      </ul>
    </div>

    <div style="background:var(--surface-card, #fff); border:1px solid var(--border-light, #e2e8f0); border-radius:16px; padding:28px;">
      <h2 style="font-size:1.3rem; font-weight:800; color:var(--forest-dark, #166534); margin-bottom:12px;">2. Taarifa Zinazofutwa (Data That Will Be Deleted)</h2>
      <p style="margin-bottom:14px; line-height:1.6;">Akaunti yako ikifutwa, data zifuatazo hufutwa moja kwa moja kutoka kwenye seva zetu:</p>
      <ul style="padding-left:20px; line-height:1.8;">
        <li>Taarifa binafsi: Jina, namba ya simu, barua pepe, na picha ya profaili.</li>
        <li>Taarifa za mashamba (farm records) na mipangilio ya mazao.</li>
        <li>Picha za majani ya mimea ulizopakia kwenye utambuzi wa magonjwa (AI disease scanner).</li>
        <li>Tokeni za uthibitishaji na vipindi vya kuingia kwenye mfumo (sessions).</li>
      </ul>
    </div>

    <div style="background:var(--surface-card, #fff); border:1px solid var(--border-light, #e2e8f0); border-radius:16px; padding:28px;">
      <h2 style="font-size:1.3rem; font-weight:800; color:var(--forest-dark, #166534); margin-bottom:12px;">3. Taarifa Zinazobaki Kisheria (Retained Data)</h2>
      <p style="line-height:1.6;">
        Kumbukumbu za miamala ya kifedha iliyokamilika kwenye soko (risiti, namba za miamala ya malipo ya simu) zinaweza kubakizwa kwa kipindi kinachotakiwa na sheria za kodi na fedha za Jamhuri ya Muungano wa Tanzania, baada ya hapo hufutwa kabisa.
      </p>
    </div>

    <div style="background:var(--surface-soft, #F7F9F7); border:1px solid var(--border-light, #E5EAE6); border-radius:16px; padding:24px;">
      <h3 style="font-weight:700; margin-bottom:8px;">Maswali au Msaada?</h3>
      <p>Ikiwa una maswali yoyote kuhusu data zako, tafadhali wasiliana na timu yetu kupitia <a href="/contact">ukurasa wa mawasiliano</a> au barua pepe <a href="mailto:support@mkulimaforum.com">support@mkulimaforum.com</a>.</p>
    </div>
  </div>
</section>
@endsection
