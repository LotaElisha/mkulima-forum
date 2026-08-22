<!DOCTYPE html>
<html lang="{{ $lang ?? 'sw' }}" class="scroll-smooth">
<head>
  <script nonce="{{ $cspNonce ?? '' }}">document.documentElement.classList.add('js');</script>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'MkulimaForum | AI-Powered Agriculture Platform for Tanzania')</title>
  <meta name="description" content="@yield('meta_description', 'MkulimaForum is an AI-powered digital agriculture ecosystem connecting Tanzania farmers with knowledge, markets, trusted inputs, weather intelligence, and practical farming support.')">
  <meta name="theme-color" content="#FFFFFF">
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

  {{-- No remote fonts.

       This used to pull four families and thirteen weights from
       fonts.googleapis.com — including Material Symbols, on which every icon
       on the site depended. On a slow or filtered Tanzanian mobile connection
       that request blocked first paint and, when it failed, printed the icon
       names as literal text. Typography now uses the device's own UI font,
       which paints instantly, costs nothing in data, and looks native on the
       low-cost Androids most farmers are using. --}}

  @yield('head_extra')

  <style>
    /* =============================================
       DESIGN TOKENS
       ============================================= */
    /* Brand direction: a white canvas carrying agricultural green.
       White is the page; green marks what you can act on — buttons, active
       navigation, verification state, progress. Gold survives only as a small
       highlight, never as a primary action, so "the green button" is always
       the thing to press. */
    :root {
      --forest-dark:   #14532D;
      --forest-mid:    #1B7A3E;
      --forest-light:  #2E9150;
      --leaf-green:    #1B7A3E;
      --leaf-bright:   #3FA463;
      --leaf-pale:     #EEF7F0;   /* subtle green tint for section separation */
      --sun-gold:      #E0A008;   /* accent only — badges, small highlights */
      --sun-amber:     #F0B429;
      --cream-bg:      #FFFFFF;   /* was #FFFDF8 cream; the brief asks for white */
      --surface-card:  #FFFFFF;
      --surface-soft:  #F7F9F7;   /* the only non-white background in use */
      --ink-dark:      #0F1511;
      --ink-body:      #3B443E;
      --ink-muted:     #626D66;   /* 5.3:1 on white — was #6F6B61 at 4.6:1 */
      --ink-faint:     #8A938C;
      --border-light:  #E5EAE6;
      --border-mid:    #CDD6CF;
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
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      color: var(--ink-body);
      background: var(--cream-bg);
      line-height: 1.65;
      -webkit-font-smoothing: antialiased;
      overflow-x: hidden;
    }
    h1,h2,h3,h4,h5,h6,.brand-font { font-family: inherit; letter-spacing: -0.022em; font-weight: 800; }
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
            font-size: .82rem; font-weight: 800;
      letter-spacing: .14em; text-transform: uppercase;
      color: var(--forest-light); margin-bottom: 14px;
    }
    .page-title {
      font-family: inherit;
      font-size: clamp(2.2rem, 5vw, 3.6rem);
      font-weight: 800; line-height: 1.08;
      color: var(--ink-dark); margin-bottom: 20px;
    }
    .section-title {
      font-family: inherit;
      font-size: clamp(1.7rem, 3.5vw, 2.5rem);
      font-weight: 800; line-height: 1.16;
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
       font-weight: 700; font-size: .95rem;
      padding: 13px 26px; border-radius: 999px; border: none;
      transition: all .22s cubic-bezier(.16,1,.3,1); white-space: nowrap;
    }
    .btn:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
    .btn-primary   { background: var(--ink-dark); color: #fff; }
    .btn-primary:hover { background: #35322A; }
    .btn-gold      { background: var(--sun-gold); color: var(--ink-dark); }
    .btn-outline   { background: transparent; color: var(--ink-dark); border: 1.5px solid var(--ink-dark); }
    .btn-outline:hover { background: var(--ink-dark); color: #fff; }
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
      background: rgba(255,253,248,.96); backdrop-filter: blur(14px) saturate(1.4);
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
       font-size: 1.18rem; font-weight: 900;
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
    /* Dropdown Navigation */
    .nav-dropdown-item { position: relative; }
    .nav-dropdown-trigger {
      display: flex; align-items: center; gap: 5px; padding: 8px 14px;
      font-size: .9rem; font-weight: 600; color: var(--ink-muted);
      border-radius: 8px; cursor: pointer; background: transparent;
      transition: all .15s ease; border: none;
    }
    .nav-dropdown-trigger:hover, .nav-dropdown-item.active .nav-dropdown-trigger { background: var(--leaf-pale); color: var(--forest-dark); }
    .nav-dropdown-trigger svg { width:14px; height:14px; transition: transform .2s ease; }
    .nav-dropdown-item:hover .nav-dropdown-trigger svg { transform: rotate(180deg); }
    .nav-dropdown-menu {
      position: absolute; top: calc(100% + 8px); left: 50%; transform: translateX(-50%) translateY(-6px);
      background: var(--surface-card); border: 1px solid var(--border-light);
      border-radius: var(--radius-lg); padding: 8px; min-width: 220px;
      box-shadow: var(--shadow-lg); opacity: 0; pointer-events: none;
      transition: all .2s ease; z-index: 210;
    }
    .nav-dropdown-item:hover .nav-dropdown-menu,
    .nav-dropdown-item:focus-within .nav-dropdown-menu,
    .nav-dropdown-item.keyboard-open .nav-dropdown-menu { opacity: 1; pointer-events: all; transform: translateX(-50%) translateY(0); }
    .nav-dropdown-menu a {
      display: flex; align-items: center; gap: 10px; padding: 10px 14px;
      border-radius: 8px; font-size: .88rem; font-weight: 600; color: var(--ink-body);
      transition: all .15s ease; white-space: nowrap;
    }
    .nav-dropdown-menu a:hover, .nav-dropdown-menu a.active { background: var(--leaf-pale); color: var(--forest-dark); }
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
      transform: translateX(0); opacity:0; visibility:hidden; pointer-events:none;
      transition: opacity .24s ease, visibility .24s ease;
    }
    #nav-drawer.open { transform: translateX(0); opacity:1; visibility:visible; pointer-events:auto; }
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
      background: #24221D; color: rgba(255,255,255,.8);
      padding: 72px 0 0; border-top: 3px solid var(--sun-gold);
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
    .foot-col-title {  font-weight: 800; font-size: .92rem; color: #fff; margin-bottom: 18px; letter-spacing: .04em; }
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
    .sr-only { position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0; }

    /* Page hero (light) */
    .page-hero {
      background: #FCF8EF;
      padding: 100px 0 80px; border-bottom: 1px solid var(--border-light);
    }
    /* Dark accent panels */
    .panel-dark {
      background: #F7F0E3;
      color: var(--ink-body); border: 1px solid var(--border-light); border-radius: var(--radius-2xl); padding: 56px;
    }
    @media(max-width: 640px) { .panel-dark { padding: 36px 24px; } }
    .panel-dark h2 { color: var(--ink-dark); font-family:inherit; font-weight:800; }
    .panel-dark p  { color: var(--ink-muted); }
    .panel-dark .badge.dark { background:transparent; border-color:#87A57A; color:var(--forest-mid); }
    .panel-dark .btn-ghost { background:transparent; color:var(--ink-dark); border:1.5px solid var(--ink-dark); backdrop-filter:none; }

    /* Shared Option 3 editorial theme for legacy public-page sections. */
    .comm-hero, .verify-hero, .pitch-hero {
      background: #FCF8EF !important; color: var(--ink-body) !important;
      border-bottom: 1px solid var(--border-light);
    }
    .comm-hero h1, .verify-hero h1, .pitch-hero h1 {
      color: var(--ink-dark) !important; font-family:inherit !important;
      font-weight:400 !important; letter-spacing:-.025em;
    }
    .comm-hero p, .verify-hero p, .pitch-hero p { color: var(--ink-muted) !important; }
    .comm-hero .badge.dark, .verify-hero .badge.dark, .pitch-hero .badge.dark {
      background:transparent; border-color:#87A57A; color:var(--forest-mid);
    }
    .contact-info-card { background:#F7F0E3 !important; color:var(--ink-body) !important; border:1px solid var(--border-light); }
    .contact-info-card h3 { color:var(--ink-dark) !important; font-family:inherit; font-weight:800 !important; }
    .contact-info-card a, .contact-info-card p { color:var(--ink-muted) !important; }
    .contact-info-card .c-info-icon { background:#FFFDF8; border:1px solid var(--border-light); color:var(--forest-mid); }
    .contact-info-card .c-info-label { color:var(--forest-mid); }
    .contact-info-card .c-info-value { color:var(--ink-body); }
    .contact-info-card .c-divider { background:var(--border-light); }
    .contact-info-card h4 { color:var(--ink-muted) !important; }
    .contact-info-card [style*="color:rgba(255,255,255"] { color:var(--ink-muted) !important; }
    .contact-info-card a { color:#9A6A0C !important; }
    .pitch-hero .btn-ghost {
      background:transparent; color:var(--ink-dark); border:1.5px solid var(--ink-dark); backdrop-filter:none;
    }
    section[style*="linear-gradient(135deg,#0E4220"],
    section[style*="linear-gradient(135deg, #0E4220"],
    div[style*="linear-gradient(145deg,#0C3619"] {
      background:#F7F0E3 !important; color:var(--ink-body) !important; border-top:1px solid var(--border-light); border-bottom:1px solid var(--border-light);
    }
    section[style*="linear-gradient(135deg,#0E4220"] h2,
    section[style*="linear-gradient(135deg, #0E4220"] h2,
    div[style*="linear-gradient(145deg,#0C3619"] h3 { color:var(--ink-dark) !important; font-family:inherit; font-weight:800 !important; }
    section[style*="linear-gradient(135deg,#0E4220"] p,
    section[style*="linear-gradient(135deg, #0E4220"] p,
    div[style*="linear-gradient(145deg,#0C3619"] p { color:var(--ink-muted) !important; }
    section[style*="linear-gradient(135deg,#0E4220"] .btn-ghost,
    section[style*="linear-gradient(135deg, #0E4220"] .btn-ghost {
      background:transparent; color:var(--ink-dark); border:1.5px solid var(--ink-dark); backdrop-filter:none;
    }

    /* Fade-up on scroll */
    .fade-up { opacity: 1; transform: none; }
    .js .fade-up { opacity: 0; transform: translateY(24px); transition: opacity .55s ease, transform .55s ease; }
    .js .fade-up.visible { opacity: 1; transform: translateY(0); }
    @media (prefers-reduced-motion: reduce) { .js .fade-up { opacity: 1; transform: none; transition: none; } }

    /* =============================================
       MOBILE-FIRST CORRECTIONS
       ---------------------------------------------
       The desktop layout above was only ever adapted for phones by collapsing
       grids to one column, so every section kept its 96px desktop padding and
       every heading kept its desktop size. At 390px that produced a home page
       6,312px tall — roughly seven and a half screens of scrolling — and a
       solutions page of 11,173px. The rules below re-proportion the page for
       the screen most farmers actually hold.
       ============================================= */
    @media (max-width: 700px) {

      /* Vertical rhythm: ~40% of the desktop value. Sections still read as
         separate, without a screen of empty space between each one. */
      section            { padding: 44px 0; }
      section.tight      { padding: 32px 0; }
      .wrap, .wrap-lg, .wrap-sm { padding-left: 18px; padding-right: 18px; }

      /* Type scale. clamp() was already shrinking headings by viewport, but
         from a desktop baseline, so h1 still landed near 35px on a 390px
         screen and wrapped to four lines. */
      /* Sizes in px, not rem: shrinking the root font size scaled the whole
         page down and pushed eyebrows and captions to 11px, which is not
         readable in daylight on a phone held at arm's length. 13px is the
         floor for any text a farmer has to read. */
      .page-title        { font-size: 28px; line-height: 1.18; margin-bottom: 14px; }
      .section-title     { font-size: 23px; line-height: 1.22; margin-bottom: 10px; }
      .section-lead      { font-size: 16px; line-height: 1.62; }
      .eyebrow           { font-size: 13px; letter-spacing: .09em; margin-bottom: 10px; }
      body               { font-size: 16px; }
      p, li, td, label, .card p, .tag { font-size: max(1em, 14px); }

      /* Cards: compact, so more than one is visible at a time. Desktop used
         32px padding and a 20px radius, which at phone width leaves a card
         that is mostly margin. */
      .card              { padding: 18px; border-radius: 14px; }
      .card h3           { font-size: 1.05rem; margin-bottom: 6px; }
      .card p            { font-size: .9rem; line-height: 1.55; }
      .card-icon         { width: 42px; height: 42px; border-radius: 12px; margin-bottom: 12px; }
      .grid-2, .grid-3, .grid-4 { gap: 12px; }

      /* Touch targets. The audit found 19-25 controls per page under the 40px
         minimum — mostly 16px text links in the footer and drawer. */
      .btn               { min-height: 48px; padding: 13px 22px; width: 100%; }
      .btn-sm            { min-height: 44px; width: auto; }
      .btn-lg            { min-height: 52px; font-size: 1rem; }
      .drawer-links a,
      .drawer-links button { min-height: 52px; }
      #site-footer a     { display: inline-block; padding: 7px 0; min-height: 40px; }

      /* Nothing below 13px: 12px eyebrows and captions are unreadable in
         daylight on a small screen. */
      .tag               { font-size: .8rem; padding: 4px 11px; }

      /* Inline text links ("Tazama Pitch Deck →", "Angalia suluhisho zote →")
         were 22-29px tall, well under any touch guideline. */
      .text-link, .store-pill {
        display: inline-flex; align-items: center;
        min-height: 44px; padding: 6px 2px;
      }
      .store-pill { padding: 6px 12px; font-size: 13px; }
      .hero-kicker { font-size: 13px !important; }

      /* Filter chips and any other bare <button> in page content. */
      .cat-btn, .chip, .filter-btn { min-height: 44px !important; padding: 10px 16px !important; }

      /* The drawer close control was a 20x29 glyph. */
      .drawer-header button, .hamburger { min-width: 48px; min-height: 48px; }
      .availability small { font-size: 13px; }

      /* Hover lifts are meaningless on touch and cause a sticky pressed state. */
      .card:hover, .btn:hover { transform: none; box-shadow: var(--shadow-sm); }

      /* Page-level blocks keep their own desktop padding, declared inside each
         page's <style>. Rather than edit ten files, cap the recurring ones
         here; this is what turns an 11,000px page into a scrollable one. */
      .solution-row, .journey, .light-capabilities, .final-cta,
      .sol-band, .submit-story-panel, .no-pitch, .story-band,
      .contact-form-card, .partner-form, .deck-inner, .pdf-fallback {
        padding-top: 36px !important;
        padding-bottom: 36px !important;
      }
      .cap-item, .highlight-card, .principle-card, .team-card,
      .story-card-body, .contact-info-card {
        padding: 18px !important;
      }
      .contact-form-card, .partner-form, .submit-story-panel {
        padding-left: 18px !important;
        padding-right: 18px !important;
      }
      .hero-copy { padding: 28px 0 !important; }
      .editorial-hero, .editorial-hero .wrap { min-height: 0 !important; }

      /* Section separation without a colour wash: the brief asks for white to
         dominate, so alternating bands are a hairline rule, not a fill. */
      .panel-dark { padding: 28px 18px !important; border-radius: 16px; }

      /* Hero blocks. The home hero carried 315px of top padding on a 390px
         screen — most of the first screenful was empty space above the
         headline. */
      .page-hero, .editorial-hero, .hero-section {
        padding-top: 28px !important;
        padding-bottom: 32px !important;
      }
      .editorial-title { margin-bottom: 16px; }

      /* Long-form marketing rows. Eight of these stacked is what made
         /solutions 11,000px tall. */
      .solution-row { gap: 20px !important; }
      .sol-tile { padding: 20px !important; min-height: 0 !important; }

      /* Footer. At 1,571px it was nearly two full screens of links below every
         page. Two columns halves that without shrinking any tap target, and
         the link rows tighten from 40px to a still-comfortable 36px because
         they sit in a list where a mis-tap is cheap. */
      .foot-grid {
        grid-template-columns: 1fr 1fr !important;
        gap: 20px !important;
        padding-bottom: 28px !important;
      }
      .foot-brand { grid-column: 1 / -1; }
      #site-footer { padding-top: 36px !important; }
      #site-footer a { padding: 9px 0; min-height: 42px; font-size: 14px; }
      .foot-col-title { margin-bottom: 8px !important; font-size: 13px !important; }
    }

    /* =============================================
       STICKY MOBILE ACTION BAR
       ---------------------------------------------
       The primary conversion (get the app / sign in) used to live only in the
       hamburger drawer once the page scrolled past the hero, so on a phone the
       site had no visible call to action for thousands of pixels at a time.
       ============================================= */
    .mobile-action-bar { display: none; }
    @media (max-width: 700px) {
      .mobile-action-bar {
        position: fixed; left: 0; right: 0; bottom: 0; z-index: 180;
        display: flex; gap: 10px; align-items: center;
        padding: 10px 16px calc(10px + env(safe-area-inset-bottom));
        background: rgba(255,255,255,.97);
        backdrop-filter: blur(12px);
        border-top: 1px solid var(--border-light);
      }
      .mobile-action-bar .btn { margin: 0; flex: 1; min-height: 48px; font-size: .95rem; }
      .mobile-action-bar .btn-quiet {
        flex: 0 0 auto; width: auto; padding: 13px 18px;
        background: #fff; color: var(--forest-dark);
        border: 1.5px solid var(--border-mid);
      }
      /* Clear the bar so it never covers the last line of the footer. */
      #site-footer { padding-bottom: 84px; }
    }

    /* =============================================
       BUTTON ROLES
       ---------------------------------------------
       Primary action is green. It was near-black with a gold secondary, which
       read as a generic corporate site rather than an agricultural one and
       gave the eye no single place to land.
       ============================================= */
    .btn-primary   { background: var(--forest-mid); color: #fff; }
    .btn-primary:hover { background: var(--forest-dark); }
    .btn-gold      { background: var(--forest-mid); color: #fff; }
    .btn-gold:hover { background: var(--forest-dark); }
    .btn-outline   { background: #fff; color: var(--forest-dark); border: 1.5px solid var(--border-mid); }
    .btn-outline:hover { background: var(--leaf-pale); color: var(--forest-dark); border-color: var(--forest-light); }

    /* Focus visibility: keyboard and switch-access users had no focus ring at
       all, because `button { outline: none }` sits in the reset above. */
    a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible {
      outline: 3px solid var(--leaf-bright);
      outline-offset: 2px;
      border-radius: 6px;
    }
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

    <!-- Primary nav links (Grouped & Uncongested) -->
    <ul class="nav-links" role="list">
      <li><a href="/" class="{{ request()->is('/') ? 'active' : '' }}" data-i18n="nav_home">Home</a></li>
      <li><a href="/verify" class="{{ request()->is('verify') ? 'active' : '' }}"><span data-i18n="nav_verify">Verify</span></a></li>

      <!-- Solutions Dropdown -->
      <li class="nav-dropdown-item {{ request()->is('solutions', 'technology') ? 'active' : '' }}">
        <button class="nav-dropdown-trigger" aria-haspopup="true" aria-expanded="false">
          <span data-i18n="nav_solutions_group">Solutions</span>
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div class="nav-dropdown-menu" role="menu">
          <a href="/solutions" class="{{ request()->is('solutions') ? 'active' : '' }}" role="menuitem"><span data-i18n="nav_solutions">All Solutions</span></a>
          <a href="/technology" class="{{ request()->is('technology') ? 'active' : '' }}" role="menuitem"><span data-i18n="nav_tech">Technology & AI</span></a>
          <a href="/api/health" target="_blank" rel="noopener" role="menuitem"><span data-i18n="nav_api">API Status</span></a>
        </div>
      </li>

      <!-- Community Dropdown -->
      <li class="nav-dropdown-item {{ request()->is('community', 'stories') ? 'active' : '' }}">
        <button class="nav-dropdown-trigger" aria-haspopup="true" aria-expanded="false">
          <span data-i18n="nav_community_group">Community</span>
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div class="nav-dropdown-menu" role="menu">
          <a href="/community" class="{{ request()->is('community') ? 'active' : '' }}" role="menuitem"><span data-i18n="nav_community">Community Hub</span></a>
          <a href="/stories" class="{{ request()->is('stories') ? 'active' : '' }}" role="menuitem"><span data-i18n="nav_stories">Farmer Stories</span></a>
        </div>
      </li>

      <!-- Company Dropdown -->
      <li class="nav-dropdown-item {{ request()->is('about', 'impact', 'partners', 'pitch-deck', 'contact') ? 'active' : '' }}">
        <button class="nav-dropdown-trigger" aria-haspopup="true" aria-expanded="false">
          <span data-i18n="nav_company_group">Company</span>
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div class="nav-dropdown-menu" role="menu">
          <a href="/about" class="{{ request()->is('about') ? 'active' : '' }}" role="menuitem"><span data-i18n="nav_about">About Us</span></a>
          <a href="/impact" class="{{ request()->is('impact') ? 'active' : '' }}" role="menuitem"><span data-i18n="nav_impact">Impact & Reach</span></a>
          <a href="/partners" class="{{ request()->is('partners') ? 'active' : '' }}" role="menuitem"><span data-i18n="nav_partners">Partners</span></a>
          <a href="/pitch-deck" class="{{ request()->is('pitch-deck') ? 'active' : '' }}" role="menuitem"><span data-i18n="nav_pitchdeck">Pitch Deck</span></a>
          <a href="/contact" class="{{ request()->is('contact') ? 'active' : '' }}" role="menuitem"><span data-i18n="nav_contact">Contact</span></a>
        </div>
      </li>
    </ul>

    <!-- Right side actions -->
    <div class="nav-actions">
      <!-- Language toggle -->
      <div class="lang-pill" role="group" aria-label="Language">
        <button class="lang-btn active" id="btnSw" onclick="mkSwitchLang('sw')">SW</button>
        <button class="lang-btn" id="btnEn" onclick="mkSwitchLang('en')">EN</button>
      </div>
      <a href="/login" class="btn btn-outline btn-sm" data-i18n="nav_login">Ingia</a>
      <a href="/download" class="btn btn-gold btn-sm" data-i18n="nav_download">Pakua App</a>

      <!-- Hamburger -->
      <button class="hamburger" id="hamburger-btn" aria-label="Open navigation menu" aria-expanded="false" onclick="toggleDrawer()">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</header>

<!-- Mobile Drawer (Clean Grouped Mobile Menu) -->
<nav id="nav-drawer" aria-label="Mobile navigation" aria-hidden="true" inert>
  <div class="drawer-header">
    <a href="/" class="nav-logo"><img src="{{ $settings['logo_url'] ?? '/images/brand-banner.png' }}" alt="MkulimaForum"></a>
    <button onclick="toggleDrawer()" aria-label="Close menu" style="background:transparent; font-size:24px; color:var(--ink-dark);">✕</button>
  </div>
  <div class="drawer-links">
    <a href="/" onclick="toggleDrawer()"><span data-i18n="nav_home">Home</span></a>
    <a href="/verify" onclick="toggleDrawer()"><span data-i18n="nav_verify">Mkulima Verify</span></a>
    
    <div class="drawer-divider"></div>
    <div style="font-size:.72rem; font-weight:800; color:var(--leaf-green); padding:4px 16px; text-transform:uppercase; letter-spacing:.08em;" data-i18n="nav_solutions_group">SOLUTIONS</div>
    <a href="/solutions" onclick="toggleDrawer()"><span data-i18n="nav_solutions">All Solutions</span></a>
    <a href="/technology" onclick="toggleDrawer()"><span data-i18n="nav_tech">Technology & AI</span></a>

    <div class="drawer-divider"></div>
    <div style="font-size:.72rem; font-weight:800; color:var(--leaf-green); padding:4px 16px; text-transform:uppercase; letter-spacing:.08em;" data-i18n="nav_community_group">COMMUNITY</div>
    <a href="/community" onclick="toggleDrawer()"><span data-i18n="nav_community">Community Hub</span></a>
    <a href="/stories" onclick="toggleDrawer()"><span data-i18n="nav_stories">Farmer Stories</span></a>

    <div class="drawer-divider"></div>
    <div style="font-size:.72rem; font-weight:800; color:var(--leaf-green); padding:4px 16px; text-transform:uppercase; letter-spacing:.08em;" data-i18n="nav_company_group">COMPANY</div>
    <a href="/about" onclick="toggleDrawer()"><span data-i18n="nav_about">About Us</span></a>
    <a href="/impact" onclick="toggleDrawer()"><span data-i18n="nav_impact">Impact</span></a>
    <a href="/partners" onclick="toggleDrawer()"><span data-i18n="nav_partners">Partners</span></a>
    <a href="/pitch-deck" onclick="toggleDrawer()"><span data-i18n="nav_pitchdeck">Pitch Deck</span></a>
    <a href="/contact" onclick="toggleDrawer()"><span data-i18n="nav_contact">Contact</span></a>

    <div class="drawer-divider"></div>
    <!-- Lang switcher in drawer -->
    <div style="display:flex; gap:8px; padding: 8px 16px;">
      <button onclick="mkSwitchLang('sw'); toggleDrawer()" style="flex:1; padding:12px; border-radius:12px; font-weight:800; background:var(--leaf-pale); color:var(--forest-dark); border:2px solid var(--border-mid);" id="drawerBtnSw">Kiswahili</button>
      <button onclick="mkSwitchLang('en'); toggleDrawer()" style="flex:1; padding:12px; border-radius:12px; font-weight:800; background:var(--leaf-pale); color:var(--forest-dark); border:2px solid var(--border-mid);" id="drawerBtnEn">English</button>
    </div>
  </div>
  <div class="drawer-footer">
    <a href="/login" class="btn btn-outline" style="flex:1; justify-content:center;" data-i18n="nav_login">Ingia</a>
    <a href="/download" class="btn btn-gold" style="flex:1; justify-content:center;" data-i18n="nav_download">Pakua App</a>
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
          <li><a href="/solutions#mkulima-bot" data-i18n="foot_bot">Mkulima AI</a></li>
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
          <li><a href="/pitch-deck" data-i18n="foot_pitch">Pitch Deck</a></li>
          <li><a href="/technology" data-i18n="foot_tech">Teknolojia</a></li>
          <li><a href="/download" data-i18n="foot_app">Pakua App</a></li>
          <li><a href="/api/health" target="_blank" data-i18n="foot_api">API Status</a></li>
          <li><a href="/privacy" data-i18n="foot_privacy">Faragha</a></li>
          <li><a href="/terms" data-i18n="foot_terms">Masharti</a></li>
        </ul>
      </div>
    </div>

    <div class="foot-bottom">
      <span>MkulimaForum &copy; {{ date('Y') }} &bull; <span data-i18n="foot_motto">Shiriki. Jifunze. Endelea.</span></span>
      <span>Tanzania &bull; Built for East African Farmers &bull; Powered by <span style="color:var(--sun-gold);">Mkulima AI</span></span>
    </div>
  </div>
</footer>

{{-- Sticky mobile action bar.

     On a phone the only route to the app or to sign-in was buried in the
     hamburger drawer, so once a visitor scrolled past the hero — which on the
     home page meant the next 5,000+ pixels — there was no call to action on
     screen at all. Hidden above 700px, where the header nav already carries
     these actions. --}}
<div class="mobile-action-bar" role="navigation" aria-label="Vitendo vikuu">
  <a href="/download" class="btn btn-primary">
    <x-icon name="download" :size="18" />
    <span data-i18n="nav_download">Pakua App</span>
  </a>
  <a href="/login" class="btn btn-quiet" aria-label="Ingia kwenye akaunti yako">
    <span data-i18n="nav_login">Ingia</span>
  </a>
</div>

<!-- ============================================================
     GLOBAL SCRIPTS: i18n + Nav
     ============================================================ -->
<script nonce="{{ $cspNonce ?? '' }}">
// ---- Translation dictionary ----
const MK_TRANSLATIONS = {
  sw: {
    nav_home:'Nyumbani', nav_verify:'Thibitisha', nav_community:'Jamii', nav_about:'Kuhusu', nav_solutions:'Suluhisho Zote',
    nav_solutions_group:'Suluhisho', nav_community_group:'Jamii', nav_company_group:'Taasisi',
    nav_impact:'Athari', nav_partners:'Washirika', nav_pitchdeck:'Pitch Deck',
    nav_more:'Zaidi', nav_stories:'Hadithi za Wakulima', nav_tech:'Teknolojia na AI',
    nav_contact:'Wasiliana', nav_api:'Hali ya API', nav_download:'Pakua App', nav_login:'Ingia', nav_webapp:'Web App',
    foot_tagline:'Jukwaa la kidigitali linalowaunganisha wakulima, wataalamu, masoko, na teknolojia ya AI nchini Tanzania.',
    foot_contact_link:'Wasiliana Nasi →',
    foot_company:'KAMPUNI', foot_about:'Kuhusu Sisi', foot_impact:'Athari Zetu',
    foot_partners:'Washirika', foot_stories:'Hadithi za Wakulima', foot_contact:'Wasiliana',
    foot_solutions:'SULUHISHO', foot_scanner:'AI Plant Scanner', foot_bot:'Mkulima AI',
    foot_market:'Soko la Kilimo', foot_verify:'Kagua Pembejeo', foot_weather:'Hali ya Hewa', foot_offline:'Offline AI',
    foot_resources:'RASILIMALI', foot_pitch:'Pitch Deck', foot_tech:'Teknolojia',
    foot_app:'Pakua App', foot_api:'Hali ya API', foot_privacy:'Faragha', foot_terms:'Masharti',
    foot_motto:'Shiriki. Jifunze. Endelea.',
  },
  en: {
    nav_home:'Home', nav_verify:'Verify', nav_community:'Community', nav_about:'About', nav_solutions:'All Solutions',
    nav_solutions_group:'Solutions', nav_community_group:'Community', nav_company_group:'Company',
    nav_impact:'Impact', nav_partners:'Partners', nav_pitchdeck:'Pitch Deck',
    nav_more:'More', nav_stories:'Farmer Stories', nav_tech:'Technology & AI',
    nav_contact:'Contact', nav_api:'API Status', nav_download:'Download App', nav_login:'Login', nav_webapp:'Web App',
    foot_tagline:'A digital platform connecting farmers, agronomists, markets, and AI technology across Tanzania and East Africa.',
    foot_contact_link:'Contact Us →',
    foot_company:'COMPANY', foot_about:'About Us', foot_impact:'Our Impact',
    foot_partners:'Partners', foot_stories:'Farmer Stories', foot_contact:'Contact',
    foot_solutions:'SOLUTIONS', foot_scanner:'AI Plant Scanner', foot_bot:'Mkulima AI',
    foot_market:'Agri Marketplace', foot_verify:'Input Verification', foot_weather:'Weather Intelligence', foot_offline:'Offline AI',
    foot_resources:'RESOURCES', foot_pitch:'Pitch Deck', foot_tech:'Technology',
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
  drawer.setAttribute('aria-hidden', String(!isOpen));
  drawer.toggleAttribute('inert', !isOpen);
  document.body.style.overflow = isOpen ? 'hidden' : '';
}

// Close drawer on ESC
document.addEventListener('keydown', e => { if(e.key==='Escape') { const d=document.getElementById('nav-drawer'); if(d.classList.contains('open')) toggleDrawer(); } });

document.querySelectorAll('.nav-dropdown-trigger').forEach(trigger => {
  const item = trigger.closest('.nav-dropdown-item');
  const setOpen = open => {
    trigger.setAttribute('aria-expanded', String(open));
    item.classList.toggle('keyboard-open', open);
  };
  trigger.addEventListener('click', () => setOpen(trigger.getAttribute('aria-expanded') !== 'true'));
  item.addEventListener('focusout', event => { if (!item.contains(event.relatedTarget)) setOpen(false); });
  trigger.addEventListener('keydown', event => { if (event.key === 'Escape') { setOpen(false); trigger.focus(); } });
});

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
