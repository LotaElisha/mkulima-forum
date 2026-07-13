<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mkulima Forum — AI Plant Scanner kwa Wakulima wa Tanzania</title>
<meta name="description" content="Piga picha ya mmea wako — AI itambue magonjwa, wadudu na upungufu wa virutubisho papo hapo. Soko, jukwaa, bei za masoko na hali ya hewa — yote kwa Kiswahili.">
<style>
  :root{
    --green:#2E7D32; --green-dark:#1B5E20; --amber:#F9A825; --amber-dark:#C17900;
    --ink:#1a2b1d; --muted:#5c6f60; --bg:#F6F8F6; --card:#ffffff; --radius:16px;
  }
  *{margin:0;padding:0;box-sizing:border-box}
  html{scroll-behavior:smooth}
  body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Ubuntu,sans-serif;color:var(--ink);background:var(--bg);line-height:1.6}
  img,svg{max-width:100%}
  a{text-decoration:none;color:inherit}
  .wrap{max-width:1080px;margin:0 auto;padding:0 20px}

  /* Header */
  header{position:sticky;top:0;z-index:50;background:rgba(255,255,255,.92);backdrop-filter:blur(8px);border-bottom:1px solid #e3eae4}
  .nav{display:flex;align-items:center;justify-content:space-between;height:60px}
  .logo{display:flex;align-items:center;gap:8px;font-weight:800;font-size:1.1rem;color:var(--green-dark)}
  .logo .leaf{width:30px;height:30px;background:var(--green);border-radius:9px;display:grid;place-items:center;color:#fff;font-size:16px}
  .nav-links{display:flex;gap:22px;align-items:center;font-size:.95rem;color:var(--muted)}
  .nav-links a:hover{color:var(--green)}
  .btn{display:inline-flex;align-items:center;gap:8px;font-weight:700;border-radius:12px;padding:12px 22px;transition:transform .15s ease, box-shadow .15s ease;cursor:pointer;border:none;font-size:1rem}
  .btn:hover{transform:translateY(-1px)}
  .btn-amber{background:var(--amber);color:var(--green-dark);box-shadow:0 4px 14px rgba(249,168,37,.35)}
  .btn-ghost{background:transparent;color:var(--green);border:2px solid var(--green)}
  .btn-white{background:#fff;color:var(--green-dark)}
  .nav .btn{padding:9px 16px;font-size:.9rem}
  @media(max-width:640px){.nav-links a:not(.btn){display:none}}

  /* Hero */
  .hero{background:linear-gradient(135deg,var(--green) 0%,var(--green-dark) 100%);color:#fff;overflow:hidden}
  .hero-grid{display:grid;grid-template-columns:1.15fr .85fr;gap:32px;align-items:center;padding:64px 0 72px}
  @media(max-width:800px){.hero-grid{grid-template-columns:1fr;padding:44px 0 40px}}
  .badge{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.25);padding:6px 14px;border-radius:999px;font-size:.85rem;margin-bottom:18px}
  .badge .dot{width:8px;height:8px;border-radius:50%;background:var(--amber)}
  .hero h1{font-size:clamp(2rem,5vw,3.1rem);line-height:1.15;font-weight:800;margin-bottom:8px}
  .hero h1 .accent{color:var(--amber)}
  .hero .tagline{font-size:clamp(1.05rem,2.5vw,1.3rem);color:var(--amber);font-weight:700;letter-spacing:.06em;margin-bottom:16px}
  .hero p.lead{color:rgba(255,255,255,.85);font-size:1.05rem;max-width:32rem;margin-bottom:26px}
  .hero-ctas{display:flex;gap:14px;flex-wrap:wrap;margin-bottom:26px}
  .hero-ctas .btn{font-size:1.05rem}
  .hero-ctas .btn-ghost{color:#fff;border-color:rgba(255,255,255,.6)}
  .trust{display:flex;gap:18px;flex-wrap:wrap;font-size:.85rem;color:rgba(255,255,255,.75)}
  .trust span{display:inline-flex;align-items:center;gap:6px}
  .trust b{color:var(--amber)}

  /* Phone mockup (pure CSS) */
  .phone-wrap{display:flex;justify-content:center}
  .phone{width:250px;background:#0f1a10;border-radius:34px;padding:12px;box-shadow:0 30px 60px rgba(0,0,0,.35);transform:rotate(3deg)}
  .phone .screen{background:#111c13;border-radius:24px;overflow:hidden}
  .phone .bar{background:var(--green);color:#fff;text-align:center;padding:12px 8px 8px}
  .phone .bar b{display:block;font-size:.85rem}
  .phone .bar small{color:var(--amber);font-size:.65rem;font-weight:700;letter-spacing:.05em}
  .phone .cam{margin:12px;height:150px;border-radius:14px;background:linear-gradient(160deg,#e9f0e9,#cfe0cf);display:grid;place-items:center;position:relative}
  .phone .cam .frame{position:absolute;inset:14px;border:2px dashed rgba(46,125,50,.5);border-radius:10px}
  .phone .cam .leaf-ic{font-size:44px}
  .phone .scan-btn{margin:0 12px 10px;background:var(--amber);color:var(--green-dark);border-radius:12px;text-align:center;font-weight:800;font-size:.8rem;padding:11px}
  .phone .result{margin:0 12px 14px;background:#1c2b1e;border-radius:12px;padding:9px 11px;font-size:.62rem;color:#cfe0cf}
  .phone .result b{color:#fff;display:block;font-size:.7rem}
  .phone .result .ok{color:#7bd48a}
  @media(max-width:800px){.phone{transform:none;width:220px}}

  /* Sections */
  section{padding:64px 0}
  .kicker{color:var(--green);font-weight:800;text-transform:uppercase;font-size:.8rem;letter-spacing:.12em;margin-bottom:8px}
  h2{font-size:clamp(1.5rem,3.5vw,2.1rem);font-weight:800;margin-bottom:10px}
  .sub{color:var(--muted);max-width:38rem;margin-bottom:36px}

  /* Steps */
  .steps{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
  @media(max-width:760px){.steps{grid-template-columns:1fr}}
  .step{background:var(--card);border-radius:var(--radius);padding:26px;border:1px solid #e3eae4;position:relative}
  .step .num{position:absolute;top:-14px;left:22px;background:var(--amber);color:var(--green-dark);font-weight:800;width:32px;height:32px;border-radius:10px;display:grid;place-items:center}
  .step .ic{font-size:30px;margin-bottom:10px}
  .step h3{font-size:1.05rem;margin-bottom:6px}
  .step p{color:var(--muted);font-size:.92rem}

  /* Features */
  .features{background:#fff}
  .feat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
  @media(max-width:900px){.feat-grid{grid-template-columns:repeat(2,1fr)}}
  @media(max-width:560px){.feat-grid{grid-template-columns:1fr}}
  .feat{border:1px solid #e3eae4;border-radius:var(--radius);padding:22px;background:var(--bg);transition:box-shadow .2s ease}
  .feat:hover{box-shadow:0 10px 26px rgba(27,94,32,.10)}
  .feat.flag{background:linear-gradient(135deg,var(--green),var(--green-dark));color:#fff;border:none}
  .feat.flag p{color:rgba(255,255,255,.82)}
  .feat.flag .tag{background:var(--amber);color:var(--green-dark)}
  .feat.flag-red{background:linear-gradient(135deg,#C62828,#8e1b1b);color:#fff;border:none}
  .feat.flag-red p{color:rgba(255,255,255,.85)}
  .feat.flag-red .ico{background:rgba(255,255,255,.15)}
  .feat.flag-red .tag{background:#fff;color:#8e1b1b}
  .feat .ico{width:44px;height:44px;border-radius:12px;display:grid;place-items:center;font-size:22px;background:rgba(46,125,50,.1);margin-bottom:12px}
  .feat.flag .ico{background:rgba(255,255,255,.15)}
  .feat h3{font-size:1.02rem;margin-bottom:6px;display:flex;align-items:center;gap:8px;flex-wrap:wrap}
  .feat p{color:var(--muted);font-size:.9rem}
  .tag{font-size:.62rem;font-weight:800;padding:3px 8px;border-radius:999px;background:rgba(46,125,50,.12);color:var(--green-dark);letter-spacing:.04em}

  /* SMS / offline */
  .sms{background:var(--green-dark);color:#fff}
  .sms-grid{display:grid;grid-template-columns:1fr 1fr;gap:36px;align-items:center}
  @media(max-width:760px){.sms-grid{grid-template-columns:1fr}}
  .sms h2{color:#fff}
  .sms .sub{color:rgba(255,255,255,.75)}
  .sms ul{list-style:none;display:grid;gap:10px}
  .sms li{display:flex;gap:10px;align-items:flex-start;color:rgba(255,255,255,.88);font-size:.95rem}
  .sms li .chk{color:var(--amber);font-weight:800}
  .terminal{background:#0d150e;border-radius:var(--radius);padding:20px;font-family:ui-monospace,Menlo,monospace;font-size:.85rem;border:1px solid rgba(255,255,255,.12)}
  .terminal .t-head{color:#7d917f;font-size:.7rem;margin-bottom:12px;letter-spacing:.08em}
  .terminal .out{color:#a9c4ab}
  .terminal .in{color:var(--amber);font-weight:700}
  .terminal p{margin-bottom:10px;white-space:pre-line}

  /* Download CTA */
  .cta{background:linear-gradient(135deg,var(--amber),#f7b733);text-align:center}
  .cta h2{color:var(--green-dark)}
  .cta .sub{margin-left:auto;margin-right:auto;color:rgba(27,94,32,.75)}
  .cta .btn-dark{background:var(--green-dark);color:#fff;font-size:1.05rem}
  .cta small{display:block;margin-top:14px;color:rgba(27,94,32,.7)}

  footer{background:#12210f;color:#9db59f;padding:36px 0;font-size:.88rem}
  .foot{display:flex;justify-content:space-between;gap:20px;flex-wrap:wrap;align-items:center}
  .foot .logo{color:#fff}
  .foot .logo .leaf{background:var(--amber);color:var(--green-dark)}
</style>
</head>
<body>

<header>
  <div class="wrap nav">
    <a class="logo" href="#"><span class="leaf">🌱</span> Mkulima Forum</a>
    <nav class="nav-links">
      <a href="#jinsi">Jinsi Inavyofanya</a>
      <a href="#vipengele">Vipengele</a>
      <a href="#sms">Bila Intaneti</a>
      <a class="btn btn-amber" href="#pakua">Pakua App</a>
    </nav>
  </div>
</header>

<!-- HERO -->
<div class="hero">
  <div class="wrap hero-grid">
    <div>
      <span class="badge"><span class="dot"></span> AI kwa Wakulima wa Tanzania</span>
      <h1>Daktari wa Mimea<br>Mfukoni <span class="accent">Mwako</span></h1>
      <div class="tagline">SKANI &bull; TAMBUA &bull; TIBU</div>
      <p class="lead">
        Piga picha ya mmea wako — <b>AI Plant Scanner</b> itambue magonjwa, wadudu
        na upungufu wa virutubisho papo hapo, na kukupa ushauri wa tiba kwa Kiswahili.
      </p>
      <div class="hero-ctas">
        <a class="btn btn-amber" href="#pakua">📷 Kagua Mmea Sasa</a>
        <a class="btn btn-ghost" href="#vipengele">Angalia Vipengele</a>
      </div>
      <div class="trust">
        <span><b>✓</b> Bure kutumia</span>
        <span><b>✓</b> Kiswahili &amp; English</span>
        <span><b>✓</b> Inafaa simu za kawaida</span>
        <span><b>✓</b> SMS &amp; USSD bila intaneti</span>
        <span><b>✓</b> Tafuta kwa Kiswahili au English</span>
      </div>
    </div>

    <div class="phone-wrap">
      <div class="phone" aria-hidden="true">
        <div class="screen">
          <div class="bar"><b>AI Plant Scanner</b><small>SKANI • TAMBUA • TIBU</small></div>
          <div class="cam"><div class="frame"></div><span class="leaf-ic">🌿</span></div>
          <div class="scan-btn">📷 Kagua Mmea Sasa</div>
          <div class="result">
            <b>Matokeo ya Uchunguzi</b>
            Ugonjwa: Kutu ya Majani (Leaf Rust)<br>
            Tiba: <span class="ok">Dawa ya fungicide + ondoa majani yaliyoathirika</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- HOW IT WORKS -->
<section id="jinsi">
  <div class="wrap">
    <div class="kicker">Jinsi Inavyofanya Kazi</div>
    <h2>Hatua 3 tu — chini ya dakika moja</h2>
    <p class="sub">Huhitaji ujuzi wowote wa kiufundi. Kama unaweza kupiga picha, unaweza kutumia Mkulima Forum.</p>
    <div class="steps">
      <div class="step">
        <div class="num">1</div>
        <div class="ic">📷</div>
        <h3>Piga Picha</h3>
        <p>Fungua app, bonyeza kitufe cha Kagua, piga picha ya jani au mmea wenye tatizo.</p>
      </div>
      <div class="step">
        <div class="num">2</div>
        <div class="ic">🔬</div>
        <h3>AI Inatambua</h3>
        <p>Akili bandia inachambua picha na kutambua ugonjwa, wadudu au upungufu wa virutubisho.</p>
      </div>
      <div class="step">
        <div class="num">3</div>
        <div class="ic">💊</div>
        <h3>Pata Tiba</h3>
        <p>Unapokea maelezo ya tiba, kinga na dawa — na unaweza kuuliza wataalamu kwenye Jukwaa.</p>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section id="vipengele" class="features">
  <div class="wrap">
    <div class="kicker">Vipengele</div>
    <h2>Zaidi ya scanner — mfumo kamili wa kilimo</h2>
    <p class="sub">Kila kitu mkulima anachohitaji, mahali pamoja, kwa Kiswahili.</p>
    <div class="feat-grid">
      <div class="feat flag">
        <div class="ico">📷</div>
        <h3>AI Plant Scanner <span class="tag">KIPENGELE KIKUU</span></h3>
        <p>Tambua magonjwa ya mimea, wadudu na upungufu wa virutubisho kwa picha moja — papo hapo.</p>
      </div>
      <div class="feat">
        <div class="ico">🤖</div>
        <h3>Mkulima AI</h3>
        <p>Msaidizi wa mazungumzo saa 24/7 — uliza chochote kuhusu mazao, mbolea, wadudu na mifugo.</p>
      </div>
      <div class="feat flag-red">
        <div class="ico">🛡️</div>
        <h3>Kagua Dawa <span class="tag">MPYA</span></h3>
        <p>Gundua dawa na mbolea feki: kagua lebo kwa picha (AI), tafuta orodha ya usajili ya TPRI/TFRA, na uone tahadhari za dawa feki mkoani kwako.</p>
      </div>
      <div class="feat">
        <div class="ico">🛒</div>
        <h3>Soko la Mkulima</h3>
        <p>Nunua pembejeo au uze mazao bila madalali — malipo hulindwa na Escrow hadi bidhaa ikufikie.</p>
      </div>
      <div class="feat">
        <div class="ico">💬</div>
        <h3>Jukwaa la Wakulima</h3>
        <p>Uliza maswali, jadiliana na wakulima wenzako, na upate majibu ya wataalamu waliothibitishwa.</p>
      </div>
      <div class="feat">
        <div class="ico">📈</div>
        <h3>Bei za Masoko</h3>
        <p>Bei halisi za mazao kwenye masoko makuu — na tarehe ya bei ikionyeshwa wazi kila wakati.</p>
      </div>
    </div>
  </div>
</section>

<!-- SMS / OFFLINE -->
<section id="sms" class="sms">
  <div class="wrap sms-grid">
    <div>
      <div class="kicker" style="color:var(--amber)">Bila Intaneti? Hakuna Shida</div>
      <h2>Inafanya kazi kwenye simu ya kawaida</h2>
      <p class="sub">Huduma muhimu zinapatikana kwa SMS na USSD — hakuna smartphone wala bando linalohitajika.</p>
      <ul>
        <li><span class="chk">✓</span> Tuma <b>&nbsp;BEI mahindi&nbsp;</b> upate bei za soko za leo</li>
        <li><span class="chk">✓</span> Tuma <b>&nbsp;HALI Dodoma&nbsp;</b> upate hali ya hewa ya eneo lako</li>
        <li><span class="chk">✓</span> App inahifadhi taarifa — inaendelea kufanya kazi mtandao ukikatika</li>
      </ul>
    </div>
    <div class="terminal" aria-hidden="true">
      <div class="t-head">— SMS —</div>
      <p><span class="in">Wewe: BEI mahindi</span></p>
      <p class="out">Bei za mahindi:
Kariakoo: TZS 45,000/gunia (12/07)
App: mkulimaforum.app</p>
      <p><span class="in">Wewe: HALI Dodoma</span></p>
      <p class="out">Hali ya hewa Dodoma:
Joto: 22°C · Unyevu: 59%
Mawingu kiasi</p>
    </div>
  </div>
</section>

<!-- DOWNLOAD CTA -->
<section id="pakua" class="cta">
  <div class="wrap">
    <div class="kicker" style="color:var(--green-dark)">Anza Leo</div>
    <h2>Pakua Mkulima Forum — bure</h2>
    <p class="sub">Jiunge na wakulima wanaotumia AI kulinda mazao yao na kuuza kwa bei nzuri.</p>
    <a class="btn btn-dark" href="/app/mkulima-forum.apk">⬇️ Pakua APK (Android)</a>
    <small>Inafaa simu za Android zenye nafasi ndogo · Toleo la iOS linakuja</small>
  </div>
</section>

<footer>
  <div class="wrap foot">
    <a class="logo" href="#"><span class="leaf">🌱</span> Mkulima Forum</a>
    <div>Skani • Tambua • Tibu — AI kwa mkulima wa kawaida.</div>
    <div>&copy; {{ date('Y') }} Mkulima Forum · Tanzania</div>
  </div>
</footer>

</body>
</html>
