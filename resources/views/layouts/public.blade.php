<!DOCTYPE html>
<html lang="{{ $lang ?? 'sw' }}" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'MkulimaForum | AI-Powered Agriculture Platform for Tanzania')</title>
  <meta name="description" content="@yield('meta_description', 'MkulimaForum is an AI-powered digital agriculture ecosystem connecting Tanzania farmers with knowledge, markets, trusted inputs, weather intelligence, and practical farming support.')">
  <meta name="theme-color" content="#0C3619">
  <link rel="canonical" href="{{ url()->current() }}">

  {{-- Open Graph --}}
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="MkulimaForum">
  <meta property="og:title" content="@yield('og_title', 'MkulimaForum | AI Agriculture Platform')">
  <meta property="og:description" content="@yield('og_description', 'Connecting East African farmers with AI, markets, and agricultural knowledge.')">
  <meta property="og:image" content="{{ url($settings['logo_url'] ?? '/images/brand-banner.png') }}">
  <meta property="og:url" content="{{ url()->current() }}">

  {{-- Twitter Card --}}
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="@yield('og_title', 'MkulimaForum')">
  <meta name="twitter:description" content="@yield('og_description', 'AI Agriculture Platform for Tanzania')">
  <meta name="twitter:image" content="{{ url($settings['logo_url'] ?? '/images/brand-banner.png') }}">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

  @yield('head_extra')

  <style>
    /* =============================================
       DESIGN TOKENS
       ============================================= */
    :root {
      --forest-dark:   #0C3619;
      --forest-mid:    #165A2A;
      --forest-light:  #237A3B;
      --leaf-green:    #4C9B27;
      --leaf-bright:   #6BB535;
      --leaf-pale:     #EFF7E9;
      --sun-gold:      #F5A623;
      --sun-amber:     #FFBA36;
      --cream-bg:      #FAFAF7;
      --surface-card:  #FFFFFF;
      --ink-dark:      #0C2314;
      --ink-body:      #2D3B35;
      --ink-muted:     #5A7060;
      --ink-faint:     #8A9E91;
      --border-light:  #E4EDE0;
      --border-mid:    #CDD9C9;
      --radius-2xl:    28px;
      --radius-xl:     20px;
      --radius-lg:     14px;
      --radius-md:     10px;
      --shadow-xs:     0 1px 4px rgba(12,54,25,.06);
      --shadow-sm:     0 4px 14px rgba(12,54,25,.08);
      --shadow-md:     0 10px 30px rgba(12,54,25,.10);
      --shadow-lg:     0 20px 50px rgba(12,54,25,.14);
      --nav-h:         72px;
    }

    /* =============================================
       RESET & BASE
       ============================================= */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; font-size: 16px; }
    body {
      font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
      color: var(--ink-body);
      background: var(--cream-bg);
      line-height: 1.65;
      -webkit-font-smoothing: antialiased;
      overflow-x: hidden;
    }
    h1,h2,h3,h4,h5,h6,.brand-font { font-family: 'Outfit', sans-serif; letter-spacing: -0.02em; }
    a { color: inherit; text-decoration: none; }
    img,svg { display: block; max-width: 100%; }
    button { font-family: inherit; cursor: pointer; border: none; outline: none; }

    /* =============================================
       LAYOUT UTILITIES
       ============================================= */
    .wrap { max-width: 1180px; margin: 0 auto; padding: 0 24px; }
    .wrap-lg { max-width: 1320px; margin: 0 auto; padding: 0 32px; }
    .wrap-sm { max-width: 720px; margin: 0 auto; padding: 0 24px; }
    section { padding: 96px 0; }
    section.tight { padding: 64px 0; }
    section.hero-section { padding: 0; }
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; }
    .grid-3 { display: grid; grid-template-columns: repeat(3,1fr); gap: 28px; }
    .grid-4 { display: grid; grid-template-columns: repeat(4,1fr); gap: 24px; }
    @media(max-width: 1060px) { .grid-4 { grid-template-columns: repeat(2,1fr); } }
    @media(max-width: 860px)  { .grid-3, .grid-2 { grid-template-columns: 1fr; } }
    @media(max-width: 640px)  { .grid-4 { grid-template-columns: 1fr; } }

    /* =============================================
       TYPOGRAPHY
       ============================================= */
    .eyebrow {
      display: inline-block;
      font-family: 'Outfit', sans-serif;
      font-size: .78rem; font-weight: 800;
      letter-spacing: .14em; text-transform: uppercase;
      color: var(--leaf-green); margin-bottom: 14px;
    }
    .page-title {
      font-size: clamp(2.2rem, 5vw, 3.6rem);
      font-weight: 900; line-height: 1.1;
      color: var(--ink-dark); margin-bottom: 20px;
    }
    .section-title {
      font-size: clamp(1.7rem, 3.5vw, 2.5rem);
      font-weight: 800; line-height: 1.18;
      color: var(--ink-dark); margin-bottom: 14px;
    }
    .section-lead {
      font-size: 1.05rem; color: var(--ink-muted);
      max-width: 44rem; line-height: 1.7;
    }
    .gold { color: var(--sun-gold); }
    .green { color: var(--leaf-green); }

    /* =============================================
       BUTTONS
       ============================================= */
    .btn {
      display: inline-flex; align-items: center; justify-content: center; gap: 9px;
      font-family: 'Outfit', sans-serif; font-weight: 700; font-size: .95rem;
      padding: 13px 26px; border-radius: 999px; border: none;
      transition: all .22s cubic-bezier(.16,1,.3,1); white-space: nowrap;
    }
    .btn:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
    .btn-primary   { background: var(--forest-dark); color: #fff; }
    .btn-primary:hover { background: var(--forest-mid); }
    .btn-gold      { background: linear-gradient(135deg,var(--sun-gold),var(--sun-amber)); color: var(--forest-dark); }
    .btn-outline   { background: transparent; color: var(--forest-dark); border: 2px solid var(--forest-mid); }
    .btn-outline:hover { background: var(--forest-dark); color: #fff; }
    .btn-ghost     { background: rgba(255,255,255,.14); color: #fff; border: 1px solid rgba(255,255,255,.25); backdrop-filter: blur(6px); }
    .btn-ghost:hover { background: rgba(255,255,255,.26); }
    .btn-sm        { padding: 9px 18px; font-size: .85rem; }
    .btn-lg        { padding: 16px 34px; font-size: 1.05rem; }

    /* =============================================
       CARDS
       ============================================= */
    .card {
      background: var(--surface-card); border: 1px solid var(--border-light);
      border-radius: var(--radius-xl); padding: 32px;
      box-shadow: var(--shadow-sm); transition: all .25s ease;
    }
    .card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
    .card-icon {
      width: 56px; height: 56px; border-radius: 16px;
      background: var(--leaf-pale); color: var(--forest-mid);
      display: flex; align-items: center; justify-content: center;
      font-size: 26px; margin-bottom: 20px; flex-shrink: 0;
    }
    .card h3 { font-size: 1.2rem; font-weight: 800; color: var(--ink-dark); margin-bottom: 10px; }
    .card p  { color: var(--ink-muted); font-size: .92rem; line-height: 1.65; }
    .tag {
      display: inline-block; font-size: .72rem; font-weight: 700;
      background: var(--leaf-pale); color: var(--forest-mid);
      border: 1px solid var(--border-light); padding: 3px 10px; border-radius: 999px;
    }

    /* =============================================
       NAVIGATION
       ============================================= */
    #site-header {
      position: sticky; top: 0; z-index: 200;
      height: var(--nav-h);
      background: rgba(250,250,247,.96); backdrop-filter: blur(14px) saturate(1.6);
      border-bottom: 1px solid var(--border-light);
    }
    .nav-wrap {
      height: 100%; display: flex; align-items: center;
      justify-content: space-between; gap: 24px;
    }
    /* Logo */
    .nav-logo { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
    .nav-logo img { height: 44px; width: auto; object-fit: contain; }
    .nav-logo-text {
      font-family: 'Outfit', sans-serif; font-size: 1.18rem; font-weight: 900;
      color: var(--forest-dark); display: none;
    }
    /* Primary links */
    .nav-links {
      display: flex; align-items: center; gap: 2px;
      list-style: none; flex: 1; justify-content: center;
    }
    .nav-links a {
      display: block; padding: 8px 14px; font-size: .9rem; font-weight: 600;
      color: var(--ink-muted); border-radius: 8px;
      transition: all .15s ease; white-space: nowrap;
    }
    .nav-links a:hover, .nav-links a.active { background: var(--leaf-pale); color: var(--forest-dark); }
    /* More dropdown */
    .nav-more { position: relative; }
    .nav-more-trigger {
      display: flex; align-items: center; gap: 5px; padding: 8px 14px;
      font-size: .9rem; font-weight: 600; color: var(--ink-muted);
      border-radius: 8px; cursor: pointer; background: transparent;
      transition: all .15s ease;
    }
    .nav-more-trigger:hover { background: var(--leaf-pale); color: var(--forest-dark); }
    .nav-more-trigger svg { width:14px; height:14px; transition: transform .2s ease; }
    .nav-more:hover .nav-more-trigger svg { transform: rotate(180deg); }
    .nav-dropdown {
      position: absolute; top: calc(100% + 8px); right: 0;
      background: var(--surface-card); border: 1px solid var(--border-light);
      border-radius: var(--radius-lg); padding: 8px; min-width: 200px;
      box-shadow: var(--shadow-lg); opacity: 0; pointer-events: none;
      transform: translateY(-6px); transition: all .2s ease;
    }
    .nav-more:hover .nav-dropdown { opacity: 1; pointer-events: all; transform: translateY(0); }
    .nav-dropdown a {
      display: flex; align-items: center; gap: 10px; padding: 10px 14px;
      border-radius: 8px; font-size: .88rem; font-weight: 600; color: var(--ink-body);
      transition: all .15s ease;
    }
    .nav-dropdown a:hover { background: var(--leaf-pale); color: var(--forest-dark); }
    /* Right actions */
    .nav-actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
    /* Lang switcher */
    .lang-pill {
      display: flex; align-items: center; background: var(--leaf-pale);
      border: 1px solid var(--border-mid); border-radius: 999px; padding: 3px; gap: 1px;
    }
    .lang-btn {
      background: transparent; border: none; padding: 5px 11px; border-radius: 999px;
      font-size: .78rem; font-weight: 800; color: var(--ink-muted);
      cursor: pointer; transition: all .18s ease;
    }
    .lang-btn.active { background: var(--forest-dark); color: #fff; box-shadow: var(--shadow-xs); }
    /* Hamburger */
    .hamburger {
      display: none; width: 40px; height: 40px; flex-direction: column;
      align-items: center; justify-content: center; gap: 5px;
      background: transparent; border-radius: 10px; transition: background .15s;
    }
    .hamburger:hover { background: var(--leaf-pale); }
    .hamburger span { display: block; width: 22px; height: 2px; background: var(--ink-dark); border-radius: 2px; transition: all .25s ease; }
    .hamburger.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
    .hamburger.open span:nth-child(2) { opacity: 0; transform: scaleX(0); }
    .hamburger.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }
    /* Mobile drawer */
    #nav-drawer {
      position: fixed; inset: 0; z-index: 190; display: flex; flex-direction: column;
      background: var(--surface-card); padding: 0;
      transform: translateX(100%); transition: transform .3s cubic-bezier(.16,1,.3,1);
    }
    #nav-drawer.open { transform: translateX(0); }
    .drawer-header {
      height: var(--nav-h); display: flex; align-items: center; justify-content: space-between;
      padding: 0 24px; border-bottom: 1px solid var(--border-light);
    }
    .drawer-links {
      flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 4px;
    }
    .drawer-links a, .drawer-links button.drawer-lang-btn {
      display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: 12px;
      font-size: 1rem; font-weight: 700; color: var(--ink-dark); transition: background .15s;
    }
    .drawer-links a:hover { background: var(--leaf-pale); }
    .drawer-links .drawer-divider { height: 1px; background: var(--border-light); margin: 8px 0; }
    .drawer-footer {
      padding: 20px 24px; border-top: 1px solid var(--border-light); display: flex; gap: 10px;
    }
    .drawer-footer .btn { flex: 1; justify-content: center; }

    @media(max-width: 1000px) {
      .nav-links, .nav-actions .lang-pill, .nav-more { display: none; }
      .nav-actions .btn { display: none; }
      .hamburger { display: flex; }
    }

    /* =============================================
       FOOTER
       ============================================= */
    #site-footer {
      background: var(--forest-dark); color: rgba(255,255,255,.8);
      padding: 72px 0 0; border-top: 3px solid var(--forest-mid);
    }
    .foot-grid {
      display: grid; grid-template-columns: 2fr 1fr 1fr 1fr;
      gap: 40px; padding-bottom: 56px;
      border-bottom: 1px solid rgba(255,255,255,.1);
    }
    @media(max-width: 920px) { .foot-grid { grid-template-columns: 1fr 1fr; } }
    @media(max-width: 540px) { .foot-grid { grid-template-columns: 1fr; } }
    .foot-brand img { height: 42px; width: auto; }
    .foot-brand p { font-size: .88rem; margin-top: 14px; color: rgba(255,255,255,.65); line-height: 1.6; }
    .foot-col-title { font-family: 'Outfit', sans-serif; font-weight: 800; font-size: .92rem; color: #fff; margin-bottom: 18px; letter-spacing: .04em; }
    .foot-links { list-style: none; display: flex; flex-direction: column; gap: 10px; }
    .foot-links a { font-size: .88rem; color: rgba(255,255,255,.65); transition: color .18s; }
    .foot-links a:hover { color: var(--sun-gold); }
    .foot-bottom {
      padding: 24px 0; display: flex; align-items: center; justify-content: space-between; gap: 16px;
      font-size: .82rem; color: rgba(255,255,255,.4); flex-wrap: wrap;
    }
    .foot-bottom a { color: rgba(255,255,255,.5); transition: color .18s; }
    .foot-bottom a:hover { color: var(--sun-gold); }

    /* =============================================
       MISC HELPERS
       ============================================= */
    .badge {
      display: inline-flex; align-items: center; gap: 8px;
      background: var(--leaf-pale); border: 1px solid var(--border-mid);
      color: var(--forest-mid); padding: 7px 16px; border-radius: 999px;
      font-size: .82rem; font-weight: 700; letter-spacing: .04em;
    }
    .badge.dark {
      background: rgba(255,255,255,.12); border-color: rgba(255,255,255,.22); color: #fff;
    }
    .pulse { width: 8px; height: 8px; border-radius: 50%; background: var(--leaf-bright); box-shadow: 0 0 8px var(--leaf-bright); animation: mk-pulse 2s infinite; }
    @keyframes mk-pulse { 0%,100%{transform:scale(1);opacity:1;} 50%{transform:scale(1.5);opacity:.5;} }
    .divider { height: 1px; background: var(--border-light); margin: 48px 0; }

    /* Page hero (light) */
    .page-hero {
      background: linear-gradient(160deg, #F4FAF0 0%, var(--cream-bg) 100%);
      padding: 100px 0 80px; border-bottom: 1px solid var(--border-light);
    }
    /* Dark accent panels */
    .panel-dark {
      background: linear-gradient(135deg, var(--forest-dark), #0A2A10);
      color: #fff; border-radius: var(--radius-2xl); padding: 56px;
    }
    @media(max-width: 640px) { .panel-dark { padding: 36px 24px; } }
    .panel-dark h2 { color: #fff; }
    .panel-dark p  { color: rgba(255,255,255,.82); }

    /* Fade-up on scroll */
    .fade-up { opacity: 0; transform: translateY(24px); transition: opacity .55s ease, transform .55s ease; }
    .fade-up.visible { opacity: 1; transform: translateY(0); }
  </style>
</head>
<body>

<!-- ============================================================
     NAVIGATION
     ============================================================ -->
<header id="site-header">
  <div class="wrap nav-wrap">
    <!-- Logo -->
    <a href="/" class="nav-logo">
      <img src="{{ $settings['logo_url'] ?? '/images/brand-banner.png' }}" alt="MkulimaForum">
    </a>

    <!-- Primary nav links -->
    <ul class="nav-links" role="list">
      <li><a href="/" class="{{ request()->is('/') ? 'active' : '' }}" data-i18n="nav_home">Home</a></li>
      <li><a href="/verify" class="{{ request()->is('verify') ? 'active' : '' }}">🔍 <span data-i18n="nav_verify">Verify</span></a></li>
      <li><a href="/community" class="{{ request()->is('community') ? 'active' : '' }}">💬 <span data-i18n="nav_community">Community</span></a></li>
      <li><a href="/about" class="{{ request()->is('about') ? 'active' : '' }}" data-i18n="nav_about">About</a></li>
      <li><a href="/solutions" class="{{ request()->is('solutions') ? 'active' : '' }}" data-i18n="nav_solutions">Solutions</a></li>
      <li><a href="/impact" class="{{ request()->is('impact') ? 'active' : '' }}" data-i18n="nav_impact">Impact</a></li>
      <li><a href="/partners" class="{{ request()->is('partners') ? 'active' : '' }}" data-i18n="nav_partners">Partners</a></li>
      <li><a href="/pitch-deck" class="{{ request()->is('pitch-deck') ? 'active' : '' }}" data-i18n="nav_pitchdeck">Pitch Deck</a></li>

      <!-- More dropdown -->
      <li class="nav-more">
        <button class="nav-more-trigger" aria-haspopup="true" data-i18n="nav_more">
          More
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div class="nav-dropdown" role="menu">
          <a href="/stories" role="menuitem">🌾 <span data-i18n="nav_stories">Farmer Stories</span></a>
          <a href="/technology" role="menuitem">⚙️ <span data-i18n="nav_tech">Technology</span></a>
          <a href="/contact" role="menuitem">✉️ <span data-i18n="nav_contact">Contact</span></a>
          <a href="/api/health" target="_blank" rel="noopener" role="menuitem">🔍 <span data-i18n="nav_api">API Status</span></a>
        </div>
      </li>
    </ul>

    <!-- Right side actions -->
    <div class="nav-actions">
      <!-- Language toggle -->
      <div class="lang-pill" role="group" aria-label="Language">
        <button class="lang-btn active" id="btnSw" onclick="mkSwitchLang('sw')">🇹🇿 SW</button>
        <button class="lang-btn" id="btnEn" onclick="mkSwitchLang('en')">🇬🇧 EN</button>
      </div>
      <a href="/app/mkulima-forum.apk" class="btn btn-gold btn-sm" data-i18n="nav_download">⬇️ Pakua App</a>

      <!-- Hamburger -->
      <button class="hamburger" id="hamburger-btn" aria-label="Open navigation menu" aria-expanded="false" onclick="toggleDrawer()">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</header>

<!-- Mobile Drawer -->
<nav id="nav-drawer" aria-label="Mobile navigation">
  <div class="drawer-header">
    <a href="/" class="nav-logo"><img src="{{ $settings['logo_url'] ?? '/images/brand-banner.png' }}" alt="MkulimaForum"></a>
    <button onclick="toggleDrawer()" aria-label="Close menu" style="background:transparent; font-size:24px; color:var(--ink-dark);">✕</button>
  </div>
  <div class="drawer-links">
    <a href="/" onclick="toggleDrawer()">🏠 <span data-i18n="nav_home">Home</span></a>
    <a href="/about" onclick="toggleDrawer()">🌿 <span data-i18n="nav_about">About</span></a>
    <a href="/solutions" onclick="toggleDrawer()">⚡ <span data-i18n="nav_solutions">Solutions</span></a>
    <a href="/impact" onclick="toggleDrawer()">📊 <span data-i18n="nav_impact">Impact</span></a>
    <a href="/partners" onclick="toggleDrawer()">🤝 <span data-i18n="nav_partners">Partners</span></a>
    <a href="/pitch-deck" onclick="toggleDrawer()">📄 <span data-i18n="nav_pitchdeck">Pitch Deck</span></a>
    <div class="drawer-divider"></div>
    <a href="/stories" onclick="toggleDrawer()">🌾 <span data-i18n="nav_stories">Farmer Stories</span></a>
    <a href="/technology" onclick="toggleDrawer()">⚙️ <span data-i18n="nav_tech">Technology</span></a>
    <a href="/contact" onclick="toggleDrawer()">✉️ <span data-i18n="nav_contact">Contact</span></a>
    <div class="drawer-divider"></div>
    <!-- Lang switcher in drawer -->
    <div style="display:flex; gap:8px; padding: 8px 16px;">
      <button onclick="mkSwitchLang('sw'); toggleDrawer()" style="flex:1; padding:12px; border-radius:12px; font-weight:800; background:var(--leaf-pale); color:var(--forest-dark); border:2px solid var(--border-mid);" id="drawerBtnSw">🇹🇿 Kiswahili</button>
      <button onclick="mkSwitchLang('en'); toggleDrawer()" style="flex:1; padding:12px; border-radius:12px; font-weight:800; background:var(--leaf-pale); color:var(--forest-dark); border:2px solid var(--border-mid);" id="drawerBtnEn">🇬🇧 English</button>
    </div>
  </div>
  <div class="drawer-footer">
    <a href="/app/mkulima-forum.apk" class="btn btn-gold" style="flex:1; justify-content:center;" data-i18n="nav_download">⬇️ Pakua App</a>
  </div>
</nav>

<!-- ============================================================
     PAGE CONTENT
     ============================================================ -->
<main id="main-content">
  @yield('content')
</main>

<!-- ============================================================
     FOOTER
     ============================================================ -->
<footer id="site-footer">
  <div class="wrap">
    <div class="foot-grid">
      <!-- Brand -->
      <div class="foot-brand">
        <img src="{{ $settings['logo_url'] ?? '/images/brand-banner.png' }}" alt="MkulimaForum">
        <p data-i18n="foot_tagline">Jukwaa la kidigitali linalowaunganisha wakulima, wataalamu, masoko, na teknolojia ya AI nchini Tanzania na Afrika Mashariki.</p>
        <div style="margin-top:18px; display:flex; gap:12px;">
          <a href="/contact" style="color:var(--sun-gold); font-size:.82rem; font-weight:700;" data-i18n="foot_contact_link">Wasiliana Nasi →</a>
        </div>
      </div>

      <!-- Company -->
      <div>
        <h4 class="foot-col-title" data-i18n="foot_company">KAMPUNI</h4>
        <ul class="foot-links">
          <li><a href="/about" data-i18n="foot_about">Kuhusu Sisi</a></li>
          <li><a href="/impact" data-i18n="foot_impact">Athari Zetu</a></li>
          <li><a href="/partners" data-i18n="foot_partners">Washirika</a></li>
          <li><a href="/stories" data-i18n="foot_stories">Hadithi za Wakulima</a></li>
          <li><a href="/contact" data-i18n="foot_contact">Wasiliana</a></li>
        </ul>
      </div>

      <!-- Solutions -->
      <div>
        <h4 class="foot-col-title" data-i18n="foot_solutions">SULUHISHO</h4>
        <ul class="foot-links">
          <li><a href="/solutions#plant-scanner" data-i18n="foot_scanner">AI Plant Scanner</a></li>
          <li><a href="/solutions#mkulima-bot" data-i18n="foot_bot">Mkulima Bot</a></li>
          <li><a href="/solutions#marketplace" data-i18n="foot_market">Soko la Kilimo</a></li>
          <li><a href="/solutions#input-verify" data-i18n="foot_verify">Kagua Pembejeo</a></li>
          <li><a href="/solutions#weather" data-i18n="foot_weather">Hali ya Hewa</a></li>
          <li><a href="/solutions#offline" data-i18n="foot_offline">Offline AI</a></li>
        </ul>
      </div>

      <!-- Resources -->
      <div>
        <h4 class="foot-col-title" data-i18n="foot_resources">RASILIMALI</h4>
        <ul class="foot-links">
          <li><a href="/pitch-deck" data-i18n="foot_pitch">📊 Pitch Deck</a></li>
          <li><a href="/technology" data-i18n="foot_tech">Teknolojia</a></li>
          <li><a href="/app/mkulima-forum.apk" data-i18n="foot_app">Pakua App</a></li>
          <li><a href="/api/health" target="_blank" data-i18n="foot_api">API Status</a></li>
          <li><a href="/privacy" data-i18n="foot_privacy">Faragha</a></li>
          <li><a href="/terms" data-i18n="foot_terms">Masharti</a></li>
        </ul>
      </div>
    </div>

    <div class="foot-bottom">
      <span>MkulimaForum &copy; {{ date('Y') }} &bull; <span data-i18n="foot_motto">Shiriki. Jifunze. Endelea.</span></span>
      <span>Tanzania 🇹🇿 &bull; Built for East African Farmers &bull; Powered by <span style="color:var(--sun-gold);">Google Gemini 3 AI</span></span>
    </div>
  </div>
</footer>

<!-- ============================================================
     GLOBAL SCRIPTS: i18n + Nav
     ============================================================ -->
<script>
// ---- Translation dictionary ----
const MK_TRANSLATIONS = {
  sw: {
    nav_home:'Nyumbani', nav_about:'Kuhusu', nav_solutions:'Suluhisho',
    nav_impact:'Athari', nav_partners:'Washirika', nav_pitchdeck:'Pitch Deck',
    nav_more:'Zaidi', nav_stories:'Hadithi za Wakulima', nav_tech:'Teknolojia',
    nav_contact:'Wasiliana', nav_api:'Hali ya API', nav_download:'⬇️ Pakua App',
    foot_tagline:'Jukwaa la kidigitali linalowaunganisha wakulima, wataalamu, masoko, na teknolojia ya AI nchini Tanzania.',
    foot_contact_link:'Wasiliana Nasi →',
    foot_company:'KAMPUNI', foot_about:'Kuhusu Sisi', foot_impact:'Athari Zetu',
    foot_partners:'Washirika', foot_stories:'Hadithi za Wakulima', foot_contact:'Wasiliana',
    foot_solutions:'SULUHISHO', foot_scanner:'AI Plant Scanner', foot_bot:'Mkulima Bot',
    foot_market:'Soko la Kilimo', foot_verify:'Kagua Pembejeo', foot_weather:'Hali ya Hewa', foot_offline:'Offline AI',
    foot_resources:'RASILIMALI', foot_pitch:'📊 Pitch Deck', foot_tech:'Teknolojia',
    foot_app:'Pakua App', foot_api:'Hali ya API', foot_privacy:'Faragha', foot_terms:'Masharti',
    foot_motto:'Shiriki. Jifunze. Endelea.',
  },
  en: {
    nav_home:'Home', nav_about:'About', nav_solutions:'Solutions',
    nav_impact:'Impact', nav_partners:'Partners', nav_pitchdeck:'Pitch Deck',
    nav_more:'More', nav_stories:'Farmer Stories', nav_tech:'Technology',
    nav_contact:'Contact', nav_api:'API Status', nav_download:'⬇️ Download App',
    foot_tagline:'A digital platform connecting farmers, agronomists, markets, and AI technology across Tanzania and East Africa.',
    foot_contact_link:'Contact Us →',
    foot_company:'COMPANY', foot_about:'About Us', foot_impact:'Our Impact',
    foot_partners:'Partners', foot_stories:'Farmer Stories', foot_contact:'Contact',
    foot_solutions:'SOLUTIONS', foot_scanner:'AI Plant Scanner', foot_bot:'Mkulima Bot',
    foot_market:'Agri Marketplace', foot_verify:'Input Verification', foot_weather:'Weather Intelligence', foot_offline:'Offline AI',
    foot_resources:'RESOURCES', foot_pitch:'📊 Pitch Deck', foot_tech:'Technology',
    foot_app:'Download App', foot_api:'API Status', foot_privacy:'Privacy', foot_terms:'Terms',
    foot_motto:'Share. Learn. Grow.',
  }
};

// Per-page translations are merged in via mkPageTranslations (defined in each page)
if(typeof mkPageTranslations === 'undefined') { var mkPageTranslations = {sw:{},en:{}}; }

let MK_LANG = localStorage.getItem('mk_lang') || 'sw';

function mkApplyLang(lang) {
  MK_LANG = lang;
  localStorage.setItem('mk_lang', lang);
  document.documentElement.lang = lang;

  // Toggle nav buttons
  document.querySelectorAll('#btnSw, #drawerBtnSw').forEach(b => b.classList.toggle('active', lang==='sw'));
  document.querySelectorAll('#btnEn, #drawerBtnEn').forEach(b => b.classList.toggle('active', lang==='en'));

  // Merge global + page dicts
  const dict = Object.assign({}, MK_TRANSLATIONS[lang] || {}, (mkPageTranslations[lang] || {}));

  document.querySelectorAll('[data-i18n]').forEach(el => {
    const k = el.getAttribute('data-i18n');
    if(dict[k] !== undefined) el.textContent = dict[k];
  });

  document.querySelectorAll('[data-i18n-html]').forEach(el => {
    const k = el.getAttribute('data-i18n-html');
    if(dict[k] !== undefined) el.innerHTML = dict[k];
  });

  document.querySelectorAll('[data-i18n-ph]').forEach(el => {
    const k = el.getAttribute('data-i18n-ph');
    if(dict[k] !== undefined) el.placeholder = dict[k];
  });
}

function mkSwitchLang(lang) { mkApplyLang(lang); }

// ---- Mobile Drawer ----
function toggleDrawer() {
  const drawer = document.getElementById('nav-drawer');
  const btn    = document.getElementById('hamburger-btn');
  const isOpen = drawer.classList.toggle('open');
  btn.classList.toggle('open', isOpen);
  btn.setAttribute('aria-expanded', isOpen);
  document.body.style.overflow = isOpen ? 'hidden' : '';
}

// Close drawer on ESC
document.addEventListener('keydown', e => { if(e.key==='Escape') { const d=document.getElementById('nav-drawer'); if(d.classList.contains('open')) toggleDrawer(); } });

// ---- Scroll fade-up ----
const mkObserver = new IntersectionObserver(entries => {
  entries.forEach(e => { if(e.isIntersecting) { e.target.classList.add('visible'); mkObserver.unobserve(e.target); } });
}, { threshold: .12 });
function mkObserveAll() { document.querySelectorAll('.fade-up').forEach(el => mkObserver.observe(el)); }

// ---- Init ----
document.addEventListener('DOMContentLoaded', () => {
  mkApplyLang(MK_LANG);
  mkObserveAll();
});
</script>

@yield('page_scripts')

</body>
</html>
