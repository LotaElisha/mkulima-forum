<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MkulimaForum — Shiriki. Jifunze. Endelea.</title>
<meta name="description" content="MkulimaForum: Jukwaa la Kidigitali la Wakulima wa Afrika Mashariki. AI Plant Scanner, Soko la Kilimo, Mkulima Bot, Masoko ya Mazao na Hali ya Hewa.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root {
    --forest-dark: #0C3619;
    --forest-mid: #165A2A;
    --leaf-green: #4C9B27;
    --leaf-bright: #6BB535;
    --sun-gold: #F5A623;
    --sun-amber: #FFBA36;
    --cream-bg: #FAF8F2;
    --cream-card: #FFFFFF;
    --ink-dark: #0C2314;
    --ink-muted: #4A6351;
    --border-light: #E2EADF;
    --radius-xl: 24px;
    --radius-lg: 16px;
    --shadow-soft: 0 12px 36px rgba(12, 54, 25, 0.08);
    --shadow-hover: 0 20px 48px rgba(12, 54, 25, 0.14);
  }

  * { margin: 0; padding: 0; box-sizing: border-box; }
  html { scroll-behavior: smooth; }
  body {
    font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
    color: var(--ink-dark);
    background: var(--cream-bg);
    line-height: 1.6;
    overflow-x: hidden;
  }

  h1, h2, h3, h4, .brand-font {
    font-family: 'Outfit', sans-serif;
  }

  img, svg { max-width: 100%; display: block; }
  a { text-decoration: none; color: inherit; }
  .wrap { max-width: 1180px; margin: 0 auto; padding: 0 24px; }

  /* ---------------- Navigation ---------------- */
  header {
    position: sticky; top: 0; z-index: 100;
    background: rgba(250, 248, 242, 0.94);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid var(--border-light);
  }
  .nav {
    display: flex; align-items: center; justify-content: space-between;
    height: 76px;
  }
  .brand-logo-container {
    display: flex; align-items: center; gap: 12px;
  }
  .brand-logo-container img {
    height: 48px; width: auto; object-fit: contain;
  }
  .nav-links {
    display: flex; gap: 24px; align-items: center;
    font-weight: 600; font-size: 0.92rem; color: var(--ink-muted);
  }
  .nav-links a { transition: color 0.2s ease; cursor: pointer; }
  .nav-links a:hover { color: var(--forest-mid); }

  /* Language Switcher Pill */
  .lang-switcher {
    display: inline-flex; align-items: center; background: rgba(22, 90, 42, 0.08);
    border: 1px solid rgba(22, 90, 42, 0.2); border-radius: 999px; padding: 3px;
    font-size: 0.82rem; font-weight: 700; margin-left: 4px;
  }
  .lang-btn {
    border: none; background: transparent; padding: 5px 12px; border-radius: 999px;
    cursor: pointer; color: var(--ink-muted); transition: all 0.2s ease;
  }
  .lang-btn.active {
    background: var(--forest-dark); color: #ffffff;
    box-shadow: 0 2px 8px rgba(12, 54, 25, 0.2);
  }

  /* Buttons */
  .btn {
    display: inline-flex; align-items: center; gap: 10px;
    font-family: 'Outfit', sans-serif; font-weight: 700;
    border-radius: 999px; padding: 14px 28px;
    font-size: 1rem; cursor: pointer; border: none;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 4px 14px rgba(12, 54, 25, 0.08);
  }
  .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(12, 54, 25, 0.16);
  }
  .btn-gold {
    background: linear-gradient(135deg, var(--sun-gold), var(--sun-amber));
    color: var(--forest-dark);
  }
  .btn-forest {
    background: linear-gradient(135deg, var(--forest-mid), var(--forest-dark));
    color: #ffffff;
  }
  .btn-outline {
    background: transparent; color: var(--forest-dark);
    border: 2px solid var(--forest-mid);
  }
  .btn-outline:hover {
    background: var(--forest-mid); color: #ffffff;
  }
  .btn-glass {
    background: rgba(255, 255, 255, 0.15); color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.3); backdrop-filter: blur(8px);
  }
  .btn-glass:hover {
    background: rgba(255, 255, 255, 0.28); color: #ffffff;
  }
  .nav .btn { padding: 10px 20px; font-size: 0.9rem; }

  /* ---------------- Hero Section ---------------- */
  .hero {
    background: radial-gradient(circle at 80% 20%, #1a6b33 0%, var(--forest-dark) 70%);
    color: #ffffff; position: relative; overflow: hidden;
    padding: 60px 0 90px;
  }
  .hero::after {
    content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 40px;
    background: var(--cream-bg);
    clip-path: ellipse(60% 100% at 50% 100%);
  }

  .hero-grid {
    display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 40px;
    align-items: center; position: relative; z-index: 2;
  }

  .motto-badge {
    display: inline-flex; align-items: center; gap: 10px;
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.25);
    padding: 8px 18px; border-radius: 999px; font-weight: 700;
    font-size: 0.88rem; color: var(--sun-amber); letter-spacing: 0.05em;
    margin-bottom: 24px; backdrop-filter: blur(6px);
  }
  .motto-badge .pulse-dot {
    width: 9px; height: 9px; border-radius: 50%;
    background: var(--sun-amber); box-shadow: 0 0 10px var(--sun-amber);
    animation: pulse 2s infinite;
  }
  @keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.4); opacity: 0.6; }
  }

  .hero h1 {
    font-size: clamp(2.2rem, 5.5vw, 3.6rem);
    line-height: 1.12; font-weight: 900; margin-bottom: 16px;
    letter-spacing: -0.02em;
  }
  .hero h1 .gold { color: var(--sun-gold); }
  .hero p.lead {
    font-size: 1.15rem; color: rgba(255, 255, 255, 0.9);
    max-width: 36rem; margin-bottom: 32px; font-weight: 400;
  }

  /* Pillars Bar */
  .pillars-bar {
    display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 36px;
  }
  .pillar-item {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.18);
    padding: 8px 16px; border-radius: 12px; font-size: 0.88rem;
    font-weight: 600; color: #ffffff;
  }

  .hero-ctas { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 32px; }

  .trust-strip {
    display: flex; gap: 20px; flex-wrap: wrap;
    font-size: 0.85rem; color: rgba(255, 255, 255, 0.8);
    border-top: 1px solid rgba(255, 255, 255, 0.15); padding-top: 20px;
  }
  .trust-strip b { color: var(--sun-gold); }

  /* ---------------- Phone Mockup ---------------- */
  .phone-container {
    display: flex; justify-content: center; position: relative;
  }
  .phone-mockup {
    width: 290px; background: #07190C; border-radius: 44px;
    padding: 14px; box-shadow: 0 30px 70px rgba(0,0,0,0.5);
    border: 3px solid rgba(255,255,255,0.15);
    transform: perspective(1000px) rotateY(-8deg) rotateX(4deg);
    transition: transform 0.5s ease;
  }
  .phone-mockup:hover { transform: perspective(1000px) rotateY(0deg) rotateX(0deg); }
  .screen {
    background: #0B1E10; border-radius: 32px; overflow: hidden;
    color: #ffffff; border: 1px solid rgba(255,255,255,0.1);
  }
  .screen-header {
    background: var(--forest-mid); padding: 16px 14px 12px; text-align: center;
  }
  .screen-header h4 { font-size: 0.95rem; font-weight: 800; }
  .screen-header p { font-size: 0.65rem; color: var(--sun-amber); font-weight: 700; letter-spacing: 0.08em; }

  .scanner-view {
    margin: 14px; height: 170px; border-radius: 20px;
    background: linear-gradient(160deg, #1C4223, #0F2D15);
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    position: relative; overflow: hidden; border: 1px solid rgba(107, 181, 53, 0.3);
  }
  .scan-line {
    position: absolute; width: 100%; height: 3px;
    background: linear-gradient(90deg, transparent, var(--leaf-bright), transparent);
    box-shadow: 0 0 12px var(--leaf-bright);
    animation: scanAnimation 2.5s infinite ease-in-out;
  }
  @keyframes scanAnimation {
    0% { top: 10%; }
    50% { top: 85%; }
    100% { top: 10%; }
  }
  .scanner-icon { font-size: 52px; margin-bottom: 4px; }
  .scanner-tag {
    font-size: 0.68rem; font-weight: 700; background: rgba(76, 155, 39, 0.3);
    padding: 3px 10px; border-radius: 999px; color: #9FE870;
  }

  .scan-result-card {
    margin: 0 14px 16px; background: rgba(255, 255, 255, 0.08);
    border-radius: 16px; padding: 12px; font-size: 0.72rem;
    border: 1px solid rgba(255, 255, 255, 0.12);
  }
  .scan-result-card b { color: var(--sun-gold); font-size: 0.78rem; display: block; margin-bottom: 2px; }
  .status-badge-ok { color: #7BD48A; font-weight: 700; }

  /* ---------------- Sections ---------------- */
  section { padding: 90px 0; }
  .section-tag {
    color: var(--leaf-green); font-weight: 800; text-transform: uppercase;
    font-size: 0.85rem; letter-spacing: 0.14em; margin-bottom: 10px; display: block;
  }
  .section-title {
    font-size: clamp(1.8rem, 4vw, 2.6rem); font-weight: 800;
    color: var(--forest-dark); margin-bottom: 14px; line-height: 1.2;
  }
  .section-sub {
    color: var(--ink-muted); font-size: 1.05rem; max-width: 42rem; margin-bottom: 48px;
  }

  /* Core Features Grid */
  .grid-3 {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px;
  }
  @media(max-width: 960px) { .grid-3 { grid-template-columns: repeat(2, 1fr); } }
  @media(max-width: 640px) { .grid-3 { grid-template-columns: 1fr; } }

  .feature-card {
    background: var(--cream-card); border-radius: var(--radius-xl);
    padding: 32px; border: 1px solid var(--border-light);
    box-shadow: var(--shadow-soft); transition: all 0.3s ease;
    display: flex; flex-direction: column; justify-content: space-between;
  }
  .feature-card:hover {
    transform: translateY(-6px); box-shadow: var(--shadow-hover);
  }

  .feat-icon {
    width: 60px; height: 60px; border-radius: 18px;
    background: rgba(76, 155, 39, 0.12); color: var(--forest-mid);
    display: flex; align-items: center; justify-content: center;
    font-size: 28px; margin-bottom: 20px;
  }
  .feature-card h3 {
    font-size: 1.3rem; font-weight: 800; color: var(--forest-dark); margin-bottom: 10px;
  }
  .feature-card p {
    color: var(--ink-muted); font-size: 0.95rem; margin-bottom: 24px;
  }
  .pill-badge {
    display: inline-block; font-size: 0.75rem; font-weight: 700;
    background: var(--cream-bg); color: var(--forest-mid);
    border: 1px solid var(--border-light); padding: 4px 12px; border-radius: 999px;
  }

  /* Special Gemini Card */
  .card-gemini {
    background: linear-gradient(135deg, var(--forest-mid), var(--forest-dark));
    color: #ffffff; border: none;
  }
  .card-gemini h3 { color: #ffffff; }
  .card-gemini p { color: rgba(255, 255, 255, 0.85); }
  .card-gemini .pill-badge {
    background: rgba(255, 255, 255, 0.15); color: var(--sun-gold);
    border: 1px solid rgba(255, 255, 255, 0.25);
  }

  /* ---------------- Steps ---------------- */
  .steps-grid {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px;
  }
  @media(max-width: 840px) { .steps-grid { grid-template-columns: 1fr; } }
  .step-card {
    background: var(--cream-card); border-radius: var(--radius-xl); padding: 36px;
    border: 1px solid var(--border-light); position: relative;
  }
  .step-num {
    font-size: 3.5rem; font-weight: 900; color: rgba(76, 155, 39, 0.15);
    position: absolute; top: 16px; right: 24px; font-family: 'Outfit', sans-serif;
  }
  .step-card h3 { font-size: 1.25rem; font-weight: 800; color: var(--forest-dark); margin: 16px 0 10px; }
  .step-card p { color: var(--ink-muted); font-size: 0.95rem; }

  /* ---------------- Offline SMS Banner ---------------- */
  .offline-banner {
    background: linear-gradient(135deg, #10331A, #081D0E);
    border-radius: var(--radius-xl); color: #ffffff; padding: 48px;
    display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 40px; align-items: center;
  }
  @media(max-width: 840px) { .offline-banner { grid-template-columns: 1fr; padding: 32px; } }
  .offline-banner h2 { font-size: 2rem; font-weight: 800; margin-bottom: 12px; }
  .offline-banner p { color: rgba(255, 255, 255, 0.85); margin-bottom: 24px; }
  .sms-box {
    background: rgba(0,0,0,0.35); border-radius: 20px; padding: 24px;
    border: 1px solid rgba(255,255,255,0.12); font-family: monospace; font-size: 0.9rem;
  }

  /* ---------------- Try Interactive Form ---------------- */
  .try-section {
    background: linear-gradient(135deg, var(--forest-dark), #175429);
    border-radius: var(--radius-xl); padding: 48px; color: #ffffff;
  }
  .try-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: center; }
  @media(max-width: 840px) { .try-grid { grid-template-columns: 1fr; } }
  .try-box textarea {
    width: 100%; padding: 16px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.2);
    background: rgba(255,255,255,0.08); color: #ffffff; font-size: 0.95rem;
    outline: none; margin-bottom: 14px; resize: none; font-family: inherit;
  }
  .try-box textarea::placeholder { color: rgba(255,255,255,0.5); }

  /* ---------------- Pitch Deck Banner & Modal ---------------- */
  .pitch-banner {
    background: linear-gradient(135deg, #1C4E28, var(--forest-dark));
    border-radius: var(--radius-xl); padding: 40px 48px; color: #ffffff;
    display: flex; justify-content: space-between; align-items: center; gap: 24px;
    box-shadow: var(--shadow-soft); border: 1px solid rgba(245, 166, 35, 0.3);
  }
  @media(max-width: 840px) { .pitch-banner { flex-direction: column; text-align: center; padding: 32px; } }

  .modal-overlay {
    position: fixed; inset: 0; z-index: 999;
    background: rgba(12, 35, 20, 0.8); backdrop-filter: blur(8px);
    display: none; align-items: center; justify-content: center; padding: 24px;
  }
  .modal-overlay.active { display: flex; }
  .modal-card {
    background: #ffffff; border-radius: 24px; width: 100%; max-width: 900px;
    height: 85vh; display: flex; flex-direction: column; overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  }
  .modal-header {
    background: var(--forest-dark); color: #ffffff; padding: 20px 28px;
    display: flex; justify-content: space-between; align-items: center;
  }
  .modal-header h3 { font-size: 1.25rem; font-weight: 800; color: var(--sun-gold); }
  .close-btn {
    background: rgba(255,255,255,0.15); border: none; color: #ffffff;
    width: 36px; height: 36px; border-radius: 50%; font-size: 20px;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: background 0.2s ease;
  }
  .close-btn:hover { background: rgba(255,255,255,0.3); }

  /* ---------------- Footer ---------------- */
  footer {
    background: var(--forest-dark); color: rgba(255, 255, 255, 0.8);
    padding: 70px 0 30px; border-top: 1px solid rgba(255, 255, 255, 0.1);
  }
  .foot-grid {
    display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 40px; margin-bottom: 50px;
  }
  @media(max-width: 960px) { .foot-grid { grid-template-columns: 1fr 1fr; } }
  @media(max-width: 540px) { .foot-grid { grid-template-columns: 1fr; } }
  .foot-brand p { font-size: 0.9rem; margin-top: 16px; color: rgba(255, 255, 255, 0.7); max-width: 22rem; }
  .foot-title { color: #ffffff; font-weight: 800; font-size: 1.05rem; margin-bottom: 20px; }
  .foot-links { list-style: none; display: flex; flex-direction: column; gap: 12px; font-size: 0.9rem; }
  .foot-links a { color: rgba(255, 255, 255, 0.75); transition: color 0.2s ease; }
  .foot-links a:hover { color: var(--sun-gold); }
  .copyright {
    border-top: 1px solid rgba(255, 255, 255, 0.1); padding-top: 24px; text-align: center;
    font-size: 0.85rem; color: rgba(255, 255, 255, 0.5);
  }
</style>
</head>
<body>

<!-- NAVIGATION HEADER -->
<header>
  <div class="wrap nav">
    <a href="#" class="brand-logo-container">
      <img src="{{ $settings['logo_url'] }}" alt="MkulimaForum Logo">
    </a>

    <nav class="nav-links">
      <a href="#jinsi" data-i18n="nav_how">Jinsi Inavyofanya</a>
      <a href="#vipengele" data-i18n="nav_features">Vipengele</a>
      <a href="#pitchdeck" onclick="openPitchModal(event)" data-i18n="nav_pitch">📊 Pitch Deck</a>
      <a href="#sms" data-i18n="nav_offline">Bila Intaneti</a>
      <a href="/docs/Mkulima_Forum_Pitch_Deck.pdf" target="_blank" class="btn btn-outline nav-btn" data-i18n="btn_pitch_doc">PDF Deck</a>
      <a href="/app/mkulima-forum.apk" class="btn btn-gold nav-btn" data-i18n="nav_download">Pakua App</a>

      <!-- Language Toggle Switcher -->
      <div class="lang-switcher">
        <button type="button" class="lang-btn active" id="btnSw" onclick="switchLanguage('sw')">🇹🇿 SW</button>
        <button type="button" class="lang-btn" id="btnEn" onclick="switchLanguage('en')">🇬🇧 EN</button>
      </div>
    </nav>
  </div>
</header>

<!-- HERO SECTION -->
<div class="hero">
  <div class="wrap hero-grid">
    <div>
      <div class="motto-badge">
        <span class="pulse-dot"></span>
        <span id="mottoText">{{ $settings['brand_motto'] }}</span>
      </div>

      <h1 id="heroTitle">{!! $settings['hero_title'] !!}</h1>
      <div style="font-size: 1.1rem; font-weight: 800; color: var(--sun-amber); margin-bottom: 12px; letter-spacing: 0.05em;" id="heroTagline">
        {!! $settings['hero_tagline'] !!}
      </div>

      <p class="lead" id="heroLead">{!! $settings['hero_lead'] !!}</p>

      <div class="pillars-bar">
        <span class="pillar-item" id="pillar1">{{ $settings['pillar_1'] }}</span>
        <span class="pillar-item" id="pillar2">{{ $settings['pillar_2'] }}</span>
        <span class="pillar-item" id="pillar3">{{ $settings['pillar_3'] }}</span>
        <span class="pillar-item" id="pillar4">{{ $settings['pillar_4'] }}</span>
      </div>

      <div class="hero-ctas">
        <a href="/app/mkulima-forum.apk" class="btn btn-gold" data-i18n="hero_download_btn">
          ⬇️ Pakua App ya Mkulima (APK)
        </a>
        <button type="button" onclick="openPitchModal(event)" class="btn btn-glass">
          📊 <span data-i18n="hero_pitch_btn">Tazama Pitch Deck</span>
        </button>
      </div>

      <div class="trust-strip">
        <span><b>✓</b> <span data-i18n="trust_free">Bure kutumia</span></span>
        <span><b>✓</b> <span data-i18n="trust_lang">Kiswahili &amp; English</span></span>
        <span><b>✓</b> <span data-i18n="trust_offline">SMS &amp; USSD Offline</span></span>
        <span><b>✓</b> <span data-i18n="trust_ai">Gemini 3 Powered</span></span>
      </div>
    </div>

    <!-- Phone Mockup Display -->
    <div class="phone-container">
      <div class="phone-mockup">
        <div class="screen">
          <div class="screen-header">
            <h4>Mkulima Forum AI</h4>
            <p data-i18n="mockup_tagline">SKANI • TAMBUA • TIBU</p>
          </div>

          <div class="scanner-view">
            <div class="scan-line"></div>
            <div class="scanner-icon">🌿</div>
            <div class="scanner-tag">Gemini 3 Flash Active</div>
          </div>

          <div class="scan-result-card">
            <b data-i18n="mockup_result_title">Matokeo ya Uchunguzi</b>
            <span data-i18n="mockup_disease">Ugonjwa: Kutu ya Majani (Leaf Rust)</span><br>
            <span class="status-badge-ok" data-i18n="mockup_treatment">Tiba: Fungicide + Ondoa majani yaliyoathirika</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- HOW IT WORKS SECTION -->
<section id="jinsi">
  <div class="wrap">
    <span class="section-tag" data-i18n="how_tag">{{ $settings['kicker_jinsi'] }}</span>
    <h2 class="section-title" data-i18n="how_title">{{ $settings['title_jinsi'] }}</h2>
    <p class="section-sub" data-i18n="how_sub">{{ $settings['sub_jinsi'] }}</p>

    <div class="steps-grid">
      <div class="step-card">
        <span class="step-num">01</span>
        <div class="feat-icon">📷</div>
        <h3 data-i18n="step1_title">1. Piga Picha ya Mmea</h3>
        <p data-i18n="step1_desc">Fungua app, bonyeza kitufe cha Kagua Mmea, na upige picha ya jani lililoathirika shambani.</p>
      </div>

      <div class="step-card">
        <span class="step-num">02</span>
        <div class="feat-icon">⚡</div>
        <h3 data-i18n="step2_title">2. AI Inatambua Papo Hapo</h3>
        <p data-i18n="step2_desc">Gemini 3 Flash inachambua picha sekunde chache na kutambua ugonjwa au wadudu.</p>
      </div>

      <div class="step-card">
        <span class="step-num">03</span>
        <div class="feat-icon">💊</div>
        <h3 data-i18n="step3_title">3. Pata Tiba &amp; Ushauri</h3>
        <p data-i18n="step3_desc">Pata maelekezo ya dawa zilizosajiliwa na TFRA, hatua za kinga, na usaidizi wa mtalamu.</p>
      </div>
    </div>
  </div>
</section>

<!-- PITCH DECK BANNER SECTION -->
<section id="pitchdeck" style="padding: 30px 0;">
  <div class="wrap">
    <div class="pitch-banner">
      <div>
        <span class="motto-badge" style="background:rgba(255,255,255,0.15); margin-bottom:12px;" data-i18n="pitch_badge">
          INVESTOR PRESENTATION
        </span>
        <h2 style="font-size:1.8rem; font-weight:800; margin-bottom:8px;" data-i18n="pitch_heading">
          Google Africa Applied AI Lab Pitch Deck
        </h2>
        <p style="color:rgba(255,255,255,0.9); font-size:0.98rem; max-width:38rem;" data-i18n="pitch_sub">
          Jifunze zaidi kuhusu mradi wa Mkulima Forum, muundo wa biashara, teknolojia ya Gemini 3 AI, na athari zetu kwa wakulima wadogo wadogo.
        </p>
      </div>
      <div style="display:flex; gap:12px; flex-wrap:wrap; shrink:0;">
        <button type="button" onclick="openPitchModal(event)" class="btn btn-gold" data-i18n="btn_open_deck">
          👁️ Tazama Online
        </button>
        <a href="{{ $settings['pitch_deck_url'] ?? '/docs/Mkulima_Forum_Pitch_Deck.pdf' }}" target="_blank" class="btn btn-glass" data-i18n="btn_download_deck">
          ⬇️ Pakua PDF
        </a>
      </div>
    </div>
  </div>
</section>

<!-- CORE FEATURES SECTION -->
<section id="vipengele">
  <div class="wrap">
    <span class="section-tag" data-i18n="feat_tag">{{ $settings['kicker_vipengele'] }}</span>
    <h2 class="section-title" data-i18n="feat_title">{{ $settings['title_vipengele'] }}</h2>
    <p class="section-sub" data-i18n="feat_sub">{{ $settings['sub_vipengele'] }}</p>

    <div class="grid-3">
      <!-- Feature 1: Gemini 3 Plant Scanner -->
      <div class="feature-card card-gemini">
        <div>
          <div class="feat-icon" style="background:rgba(255,255,255,0.15); color:#fff;">📷</div>
          <h3 data-i18n="f1_title">AI Plant Scanner</h3>
          <p data-i18n="f1_desc">Piga picha jani lililoathirika. Gemini 3 Flash inatambua ugonjwa au wadudu papo hapo na kutoa tiba sahihi kwa Kiswahili.</p>
        </div>
        <div>
          <span class="pill-badge">Gemini 3 Flash AI</span>
        </div>
      </div>

      <!-- Feature 2: Mkulima Bot -->
      <div class="feature-card">
        <div>
          <div class="feat-icon">🤖</div>
          <h3 data-i18n="f2_title">Mkulima Bot AI</h3>
          <p data-i18n="f2_desc">Msaidizi wako wa kilimo 24/7 — uliza maswali ya mazao, pembejeo, na tiba kupitia sauti au maandishi.</p>
        </div>
        <div>
          <span class="pill-badge">Virtual Agronomist</span>
        </div>
      </div>

      <!-- Feature 3: Kagua Dawa Counterfeit Verification -->
      <div class="feature-card">
        <div>
          <div class="feat-icon">🛡️</div>
          <h3 data-i18n="f3_title">Kagua Dawa &amp; Pembejeo</h3>
          <p data-i18n="f3_desc">Gundua pembejeo feki kwa kagua lebo za dawa na mbolea, kutafuta usajili wa TPHPA/TFRA, na tahadhari za jamii.</p>
        </div>
        <div>
          <span class="pill-badge">Counterfeit Protection</span>
        </div>
      </div>

      <!-- Feature 4: Soko & Escrow -->
      <div class="feature-card">
        <div>
          <div class="feat-icon">🛒</div>
          <h3 data-i18n="f4_title">Soko la Pembejeo &amp; Mazao</h3>
          <p data-i18n="f4_desc">Nunua dawa, mbegu na mbolea kutoka kwa wauzaji walioidhinishwa. Malipo yote yanalindwa na Mkulima Escrow.</p>
        </div>
        <div>
          <span class="pill-badge">Escrow Secured</span>
        </div>
      </div>

      <!-- Feature 5: Grounded Weather -->
      <div class="feature-card">
        <div>
          <div class="feat-icon">⛅</div>
          <h3 data-i18n="f5_title">Hali ya Hewa &amp; Ushauri</h3>
          <p data-i18n="f5_desc">Gemini 3 Pro na Google Search Grounding inakupa utabiri sahihi wa hewa na ushauri wa msimu kulingana na mkoa wako.</p>
        </div>
        <div>
          <span class="pill-badge">Gemini 3 Pro Grounded</span>
        </div>
      </div>

      <!-- Feature 6: Offline Gemma 2B -->
      <div class="feature-card">
        <div>
          <div class="feat-icon">📱</div>
          <h3 data-i18n="f6_title">Offline Mode (Gemma 2B)</h3>
          <p data-i18n="f6_desc">Huna mtandao shambani? Gemma 2B INT4 quantized inafanya kazi moja kwa moja kwenye simu yako bila intaneti.</p>
        </div>
        <div>
          <span class="pill-badge">Google AI Edge SDK</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- OFFLINE SMS SECTION -->
<section id="sms" style="padding-top:0;">
  <div class="wrap">
    <div class="offline-banner">
      <div>
        <span class="motto-badge" style="background:rgba(255,255,255,0.15); margin-bottom:14px;" data-i18n="sms_badge">
          HUDUMA BILA INTANETI
        </span>
        <h2 data-i18n="sms_title">Inafanya kazi kwenye simu yoyote</h2>
        <p data-i18n="sms_desc">
          Hata bila smartphone au mtandao wa intaneti shambani, unaweza kupata bei za masoko, hali ya hewa, na ushauri wa kilimo kwa njia ya SMS na USSD.
        </p>
        <div style="display:flex; gap:12px; font-weight:700; font-size:0.95rem; color:var(--sun-gold);">
          <span>✓ SMS Gateway</span> &bull; <span>✓ USSD Code</span> &bull; <span>✓ Gemma 2B Offline</span>
        </div>
      </div>

      <div class="sms-box">
        <div style="color:var(--sun-amber); font-weight:700; margin-bottom:8px;" data-i18n="sms_demo_head">
          📲 Mfano wa Kutuma SMS (Bure):
        </div>
        <div style="margin-bottom:6px;">Tuma: <b style="color:#ffffff;">BEI MAHINDI DODOMA</b> -> kwenda 15500</div>
        <div style="color:rgba(255,255,255,0.7); font-size:0.8rem; border-top:1px solid rgba(255,255,255,0.15); padding-top:8px;" data-i18n="sms_demo_resp">
          Jibu: "Bei ya Mahindi Dodoma leo ni TZS 52,000/Gunia (TFRA Certified Market)."
        </div>
      </div>
    </div>
  </div>
</section>

<!-- INTERACTIVE TRY SECTION -->
<section id="jaribu" style="padding-top: 0;">
  <div class="wrap">
    <div class="try-section">
      <div class="try-grid">
        <div>
          <span class="motto-badge" style="background:rgba(255,255,255,0.15);" data-i18n="try_badge">JARIBU MTAALAMU WA AI</span>
          <h2 style="font-size:2.2rem; font-weight:800; margin-bottom:14px;" data-i18n="try_heading">Uliza Swali la Kilimo Hapa</h2>
          <p style="color:rgba(255,255,255,0.85); font-size:1rem; margin-bottom:20px;" data-i18n="try_desc">
            Jaribu uwezo wa <b>Mkulima Bot</b> kwa kuuliza swali kuhusu kilimo cha mahindi, nyanya, mpunga au mbolea.
          </p>
          <div style="font-size:0.88rem; color:var(--sun-gold);" data-i18n="try_sub">
            ✓ Inatumia Gemini 3 Flash &bull; Lugha ya Kiswahili &amp; English
          </div>
        </div>

        <div class="try-box">
          <form id="agriAiForm" onsubmit="handleDemoQuestion(event)">
            <textarea id="userQuestion" rows="3" placeholder="Mfano: Jinsi ya kuzuia funza wa mahindi..." required></textarea>
            <button type="submit" class="btn btn-gold" style="width:100%; justify-content:center;" data-i18n="btn_ask_ai">
              🚀 Tuma Swali kwa AI
            </button>
          </form>
          <div id="aiAnswerOutput" style="display:none; margin-top:16px; padding:16px; background:rgba(0,0,0,0.4); border-radius:12px; font-size:0.9rem; border:1px solid rgba(255,255,255,0.15);">
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- SERVER STATUS & API SECTION -->
<section id="status">
  <div class="wrap">
    <div style="background: var(--cream-card); border-radius: var(--radius-xl); padding: 48px; border: 1px solid var(--border-light); text-align: center; box-shadow: var(--shadow-soft);">
      <div style="display:inline-flex; align-items:center; gap:8px; background:rgba(76, 155, 39, 0.12); color:var(--forest-mid); padding:6px 16px; border-radius:999px; font-weight:700; font-size:0.85rem; margin-bottom:16px;">
        <span style="width:8px; height:8px; background:var(--leaf-green); border-radius:50%;"></span> MFUMO UPO HEWANI (ONLINE)
      </div>
      <h3 style="font-size:1.6rem; font-weight:800; color:var(--forest-dark); margin-bottom:10px;">
        MkulimaForum API v1.0.0
      </h3>
      <p style="color:var(--ink-muted); max-width:32rem; margin:0 auto 24px; font-size:0.95rem;">
        PostgreSQL (pgvector), Redis Cache, Laravel Sanctum Auth, M-Pesa &amp; Tigo Pesa Gateway, Gemini 3 AI Engine.
      </p>

      <div style="display:flex; justify-content:center; gap:16px; flex-wrap:wrap;">
        <a class="btn btn-forest" href="/api/health" target="_blank" data-i18n="btn_api_health">
          🔍 Kagua Status ya API (/api/health)
        </a>
        <a class="btn btn-outline" href="/admin/login" data-i18n="btn_admin_portal">
          🔐 Admin Dashboard Portal
        </a>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="wrap">
    <div class="foot-grid">
      <div class="foot-brand">
        <img src="{{ $settings['logo_url'] }}" alt="MkulimaForum" style="height:44px; width:auto;">
        <p data-i18n="foot_desc">Jukwaa kuu la kidigitali linalowaunganisha wakulima, wataalamu, masoko, na teknolojia ya AI nchini Tanzania.</p>
      </div>

      <div>
        <h4 class="foot-title" data-i18n="foot_f_title">Vipengele</h4>
        <ul class="foot-links">
          <li><a href="#vipengele">AI Plant Scanner</a></li>
          <li><a href="#vipengele">Mkulima Bot Chat</a></li>
          <li><a href="#vipengele">Soko la Pembejeo</a></li>
          <li><a href="#status">API Endpoints</a></li>
        </ul>
      </div>

      <div>
        <h4 class="foot-title" data-i18n="foot_q_title">Viungo vya Haraka</h4>
        <ul class="foot-links">
          <li><a href="{{ $settings['pitch_deck_url'] ?? '/docs/Mkulima_Forum_Pitch_Deck.pdf' }}" target="_blank" data-i18n="foot_link_pitch">📊 Pitch Deck (PDF)</a></li>
          <li><a href="/app/mkulima-forum.apk">Pakua App (APK)</a></li>
          <li><a href="/admin/login">Admin Dashboard</a></li>
          <li><a href="/api/health">API Health</a></li>
        </ul>
      </div>
    </div>

    <div class="copyright">
      MkulimaForum &copy; {{ date('Y') }} | Shiriki. Jifunze. Endelea. &bull; Built for East African Farmers
    </div>
  </div>
</footer>

<!-- PITCH DECK MODAL VIEWER -->
<div id="pitchModal" class="modal-overlay" onclick="closePitchModalOnOverlay(event)">
  <div class="modal-card">
    <div class="modal-header">
      <h3>📊 Mkulima Forum — Google Africa Applied AI Lab Pitch Deck</h3>
      <div style="display:flex; gap:10px; align-items:center;">
        <a href="{{ $settings['pitch_deck_url'] ?? '/docs/Mkulima_Forum_Pitch_Deck.pdf' }}" target="_blank" class="btn btn-gold" style="padding:6px 14px; font-size:0.8rem;">
          ⬇️ Download PDF
        </a>
        <button type="button" class="close-btn" onclick="closePitchModal()">×</button>
      </div>
    </div>
    <iframe src="{{ $settings['pitch_deck_url'] ?? '/docs/Mkulima_Forum_Pitch_Deck.pdf' }}#toolbar=1" style="width:100%; height:100%; border:none;" title="Pitch Deck PDF"></iframe>
  </div>
</div>

<!-- MULTILINGUAL I18N ENGINE -->
<script>
const translations = {
  sw: {
    nav_how: "Jinsi Inavyofanya",
    nav_features: "Vipengele",
    nav_pitch: "📊 Pitch Deck",
    nav_offline: "Bila Intaneti",
    btn_pitch_doc: "PDF Deck",
    nav_download: "Pakua App",
    hero_download_btn: "⬇️ Pakua App ya Mkulima (APK)",
    hero_pitch_btn: "Tazama Pitch Deck",
    trust_free: "Bure kutumia",
    trust_lang: "Kiswahili & English",
    trust_offline: "SMS & USSD Offline",
    trust_ai: "Gemini 3 Powered",
    mockup_tagline: "SKANI • TAMBUA • TIBU",
    mockup_result_title: "Matokeo ya Uchunguzi",
    mockup_disease: "Ugonjwa: Kutu ya Majani (Leaf Rust)",
    mockup_treatment: "Tiba: Fungicide + Ondoa majani yaliyoathirika",
    how_tag: "{{ $settings['kicker_jinsi'] }}",
    how_title: "{{ $settings['title_jinsi'] }}",
    how_sub: "{{ $settings['sub_jinsi'] }}",
    step1_title: "1. Piga Picha ya Mmea",
    step1_desc: "Fungua app, bonyeza kitufe cha Kagua Mmea, na upige picha ya jani lililoathirika shambani.",
    step2_title: "2. AI Inatambua Papo Hapo",
    step2_desc: "Gemini 3 Flash inachambua picha sekunde chache na kutambua ugonjwa au wadudu.",
    step3_title: "3. Pata Tiba & Ushauri",
    step3_desc: "Pata maelekezo ya dawa zilizosajiliwa na TFRA, hatua za kinga, na usaidizi wa mtalamu.",
    pitch_badge: "INVESTOR PRESENTATION",
    pitch_heading: "Google Africa Applied AI Lab Pitch Deck",
    pitch_sub: "Jifunze zaidi kuhusu mradi wa Mkulima Forum, muundo wa biashara, teknolojia ya Gemini 3 AI, na athari zetu kwa wakulima wadogo wadogo.",
    btn_open_deck: "👁️ Tazama Online",
    btn_download_deck: "⬇️ Pakua PDF",
    feat_tag: "{{ $settings['kicker_vipengele'] }}",
    feat_title: "{{ $settings['title_vipengele'] }}",
    feat_sub: "{{ $settings['sub_vipengele'] }}",
    f1_title: "AI Plant Scanner",
    f1_desc: "Piga picha jani lililoathirika. Gemini 3 Flash inatambua ugonjwa au wadudu papo hapo na kutoa tiba sahihi kwa Kiswahili.",
    f2_title: "Mkulima Bot AI",
    f2_desc: "Msaidizi wako wa kilimo 24/7 — uliza maswali ya mazao, pembejeo, na tiba kupitia sauti au maandishi.",
    f3_title: "Kagua Dawa & Pembejeo",
    f3_desc: "Gundua pembejeo feki kwa kagua lebo za dawa na mbolea, kutafuta usajili wa TPHPA/TFRA, na tahadhari za jamii.",
    f4_title: "Soko la Pembejeo & Mazao",
    f4_desc: "Nunua dawa, mbegu na mbolea kutoka kwa wauzaji walioidhinishwa. Malipo yote yanalindwa na Mkulima Escrow.",
    f5_title: "Hali ya Hewa & Ushauri",
    f5_desc: "Gemini 3 Pro na Google Search Grounding inakupa utabiri sahihi wa hewa na ushauri wa msimu kulingana na mkoa wako.",
    f6_title: "Offline Mode (Gemma 2B)",
    f6_desc: "Huna mtandao shambani? Gemma 2B INT4 quantized inafanya kazi moja kwa moja kwenye simu yako bila intaneti.",
    sms_badge: "HUDUMA BILA INTANETI",
    sms_title: "Inafanya kazi kwenye simu yoyote",
    sms_desc: "Hata bila smartphone au mtandao wa intaneti shambani, unaweza kupata bei za masoko, hali ya hewa, na ushauri wa kilimo kwa njia ya SMS na USSD.",
    sms_demo_head: "📲 Mfano wa Kutuma SMS (Bure):",
    sms_demo_resp: "Jibu: \"Bei ya Mahindi Dodoma leo ni TZS 52,000/Gunia (TFRA Certified Market).\"",
    try_badge: "JARIBU MTAALAMU WA AI",
    try_heading: "Uliza Swali la Kilimo Hapa",
    try_desc: "Jaribu uwezo wa Mkulima Bot kwa kuuliza swali kuhusu kilimo cha mahindi, nyanya, mpunga au mbolea.",
    try_sub: "✓ Inatumia Gemini 3 Flash • Lugha ya Kiswahili & English",
    btn_ask_ai: "🚀 Tuma Swali kwa AI",
    btn_api_health: "🔍 Kagua Status ya API (/api/health)",
    btn_admin_portal: "🔐 Admin Dashboard Portal",
    foot_desc: "Jukwaa kuu la kidigitali linalowaunganisha wakulima, wataalamu, masoko, na teknolojia ya AI nchini Tanzania.",
    foot_f_title: "Vipengele",
    foot_q_title: "Viungo vya Haraka",
    foot_link_pitch: "📊 Pitch Deck (PDF)",
    placeholder_q: "Mfano: Jinsi ya kuzuia funza wa mahindi..."
  },
  en: {
    nav_how: "How It Works",
    nav_features: "Features",
    nav_pitch: "📊 Pitch Deck",
    nav_offline: "Offline SMS",
    btn_pitch_doc: "PDF Deck",
    nav_download: "Download App",
    hero_download_btn: "⬇️ Download Farmer App (APK)",
    hero_pitch_btn: "View Pitch Deck",
    trust_free: "Free to Use",
    trust_lang: "Swahili & English",
    trust_offline: "SMS & USSD Offline",
    trust_ai: "Gemini 3 Powered",
    mockup_tagline: "SCAN • DIAGNOSE • TREAT",
    mockup_result_title: "Diagnosis Result",
    mockup_disease: "Disease: Leaf Rust",
    mockup_treatment: "Treatment: Fungicide + Remove infected leaves",
    how_tag: "HOW IT WORKS",
    how_title: "3 Simple Steps — Under One Minute",
    how_sub: "No technical knowledge required. If you can take a photo, you can use Mkulima Forum.",
    step1_title: "1. Snap a Plant Photo",
    step1_desc: "Open the app, tap Scan Plant, and snap a picture of an affected crop leaf in your field.",
    step2_title: "2. Instant AI Diagnosis",
    step2_desc: "Gemini 3 Flash analyzes the photo in seconds to identify crop diseases or pest infestations.",
    step3_title: "3. Get Treatments & Advice",
    step3_desc: "Receive actionable treatment steps, TFRA-certified remedies, and expert agronomist guidance.",
    pitch_badge: "INVESTOR PRESENTATION",
    pitch_heading: "Google Africa Applied AI Lab Pitch Deck",
    pitch_sub: "Learn more about the Mkulima Forum project, business model, Gemini 3 AI technology integration, and impact on East African smallholder farmers.",
    btn_open_deck: "👁️ View Online",
    btn_download_deck: "⬇️ Download PDF",
    feat_tag: "FEATURES",
    feat_title: "More Than a Scanner — Complete Agri Ecosystem",
    feat_sub: "Everything a farmer needs, in one unified platform, available in Swahili & English.",
    f1_title: "AI Plant Scanner",
    f1_desc: "Snap a photo of an affected leaf. Gemini 3 Flash instantly identifies diseases or pests and recommends accurate treatments.",
    f2_title: "Mkulima Bot AI",
    f2_desc: "Your 24/7 AI agronomist — ask questions about crops, inputs, and soil treatments via text or voice.",
    f3_title: "Agri-Input Verification",
    f3_desc: "Verify genuine pesticides & fertilizers by scanning labels, cross-referencing TPHPA/TFRA registries, and community alerts.",
    f4_title: "Marketplace & Escrow",
    f4_desc: "Buy inputs or sell harvests directly without middlemen. All payments secured by Mkulima Escrow.",
    f5_title: "Weather & Crop Advisory",
    f5_desc: "Gemini 3 Pro with Google Search Grounding provides real-time regional micro-climate forecasts and crop planning.",
    f6_title: "Offline Mode (Gemma 2B)",
    f6_desc: "No connectivity in remote fields? Quantized Gemma 2B INT4 runs lightweight inference directly on your phone NPU/GPU.",
    sms_badge: "OFFLINE CAPABILITY",
    sms_title: "Works on Any Feature Phone",
    sms_desc: "Even without a smartphone or internet bundle, access market prices, weather alerts, and farming advice via SMS and USSD.",
    sms_demo_head: "📲 Sample SMS Query (Free):",
    sms_demo_resp: "Reply: \"Maize market price in Dodoma today: TZS 52,000/Bag (TFRA Certified Market).\"",
    try_badge: "TRY AI AGRONOMIST",
    try_heading: "Ask an Agriculture Question",
    try_desc: "Test Mkulima Bot by asking a question about maize, tomato, rice farming, or fertilizer application.",
    try_sub: "✓ Powered by Gemini 3 Flash • Swahili & English Support",
    btn_ask_ai: "🚀 Send Question to AI",
    btn_api_health: "🔍 Check API Status (/api/health)",
    btn_admin_portal: "🔐 Admin Dashboard Portal",
    foot_desc: "The primary digital platform connecting smallholder farmers, certified agronomists, markets, and AI technology across Tanzania.",
    foot_f_title: "Features",
    foot_q_title: "Quick Links",
    foot_link_pitch: "📊 Pitch Deck (PDF)",
    placeholder_q: "e.g. How to prevent fall armyworms in maize..."
  }
};

let currentLang = localStorage.getItem('preferred_lang') || 'sw';

function applyLanguage(lang) {
  currentLang = lang;
  localStorage.setItem('preferred_lang', lang);
  document.documentElement.lang = lang;

  // Toggle active button UI
  document.getElementById('btnSw').classList.toggle('active', lang === 'sw');
  document.getElementById('btnEn').classList.toggle('active', lang === 'en');

  const dict = translations[lang] || translations.sw;

  // Update text nodes by data-i18n key
  document.querySelectorAll('[data-i18n]').forEach(el => {
    const key = el.getAttribute('data-i18n');
    if (dict[key]) {
      el.textContent = dict[key];
    }
  });

  // Update textarea placeholder
  const textarea = document.getElementById('userQuestion');
  if (textarea && dict.placeholder_q) {
    textarea.placeholder = dict.placeholder_q;
  }
}

function switchLanguage(lang) {
  applyLanguage(lang);
}

// Modal functions for Pitch Deck
function openPitchModal(e) {
  if (e) e.preventDefault();
  document.getElementById('pitchModal').classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closePitchModal() {
  document.getElementById('pitchModal').classList.remove('active');
  document.body.style.overflow = '';
}

function closePitchModalOnOverlay(e) {
  if (e.target.id === 'pitchModal') {
    closePitchModal();
  }
}

// Initialize language on DOM ready
document.addEventListener('DOMContentLoaded', () => {
  applyLanguage(currentLang);
});

async function handleDemoQuestion(e) {
  e.preventDefault();
  const q = document.getElementById('userQuestion').value;
  const out = document.getElementById('aiAnswerOutput');
  out.style.display = 'block';
  const isSw = currentLang === 'sw';
  out.innerHTML = `<div style="color:var(--sun-gold)">⏳ ${isSw ? 'Mkulima Bot AI inaandaa majibu...' : 'Mkulima Bot AI is generating response...'}</div>`;

  try {
    const res = await fetch('/api/agronomist/ask', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ question: q, language: currentLang })
    });
    const data = await res.json();

    if (data.text || data.answer) {
      out.innerHTML = `<b>🤖 ${isSw ? 'Majibu ya Mkulima Bot AI' : 'Mkulima Bot AI Answer'}:</b><br><br>` + (data.text || data.answer);
    } else {
      out.innerHTML = `<b>🤖 ${isSw ? 'Ushauri wa Kawaida' : 'Agronomy Recommendation'}:</b><br>${isSw ? 'Kuhusu ' + q + ', hakikisha unatumia mbolea iliyosajiliwa na TFRA na kunyunyizia dawa mara tu dalili zinapojitokeza.' : 'Regarding ' + q + ', ensure you apply TFRA-certified fertilizers and spray approved pesticides at first symptom appearance.'}`;
    }
  } catch (err) {
    out.innerHTML = `<b>🤖 ${isSw ? 'Ushauri wa Mkulima Bot' : 'Mkulima Bot Advice'}:</b><br>${isSw ? 'Kuhusu ' + q + ', hakikisha unakagua majani asubuhi na mapema na kutumia pembejeo sahihi zilizothibitishwa.' : 'Regarding ' + q + ', inspect leaf surfaces early morning and use certified agricultural inputs.'}`;
  }
}
</script>
</body>
</html>
