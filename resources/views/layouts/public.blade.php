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
       MKULIMA DESIGN SYSTEM — public site
       ---------------------------------------------
       The same tokens as the farmer app (mkulima_app/lib/core/theme.dart),
       so the website and the app read as one product: about 90% white,
       green reserved for actions, navigation, badges and icons, nothing
       below 13px, 44px touch targets and 48px primary actions.
       Page templates should use these tokens rather than raw colours.
       ============================================= */
    :root {
      /* Green: actions and identity only */
      --forest-dark:   #14532D;   /* MkColors.primaryDark */
      --forest-mid:    #1B7A3E;   /* MkColors.primary */
      --forest-light:  #1B7A3E;
      --leaf-green:    #1B7A3E;
      --leaf-bright:   #3FA463;
      --leaf-pale:     #EEF7F0;   /* selected states, icon chips */
      /* Accent: small highlights only, never a fill behind body text */
      --sun-gold:      #9A5B00;   /* text-safe amber (MkColors.warning) */
      --sun-amber:     #E0A008;
      --accent-soft:   #FDF1D6;
      /* Surfaces */
      --cream-bg:      #FFFFFF;   /* legacy name kept for page templates */
      --cream-card:    #FFFFFF;
      --surface:       #FFFFFF;
      --surface-card:  #FFFFFF;
      --surface-soft:  #F4F7F4;   /* the only tinted surface (MkColors.surfaceMuted) */
      --surface-sunken:#F4F7F4;
      /* Ink */
      --ink-dark:      #0F1511;
      --ink-body:      #2E3631;
      --ink-muted:     #5A645E;   /* 5.9:1 on white */
      --ink-faint:     #5A645E;   /* was #8A938C (3.2:1); faint text must still be read */
      /* Lines */
      --border-light:  #E5EAE6;
      --border-mid:    #CDD6CF;
      --line:          #E5EAE6;
      /* Shape */
      --radius-2xl:    24px;
      --radius-xl:     16px;      /* MkRadii.card */
      --radius-lg:     14px;      /* MkRadii.button */
      --radius-md:     10px;
      --shadow-xs:     0 1px 2px rgba(15,21,17,.05);
      --shadow-sm:     0 1px 3px rgba(15,21,17,.06), 0 1px 2px rgba(15,21,17,.04);
      --shadow-md:     0 8px 24px rgba(15,21,17,.08);
      --shadow-lg:     0 16px 40px rgba(15,21,17,.12);
      --nav-h:         68px;
    }

    /* =============================================
       RESET & BASE
       ============================================= */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; font-size: 16px; }
    body {
      font-family: Roboto, -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
      color: var(--ink-body);
      background: #fff;
      font-size: 16px;
      line-height: 1.6;
      -webkit-font-smoothing: antialiased;
      overflow-x: hidden;
    }
    h1,h2,h3,h4,h5,h6,.brand-font {
      font-family: inherit; color: var(--ink-dark);
      letter-spacing: -0.015em; font-weight: 700; line-height: 1.2;
    }
    a { color: inherit; text-decoration: none; }
    img,svg { display: block; max-width: 100%; }
    button { font-family: inherit; cursor: pointer; border: none; outline: none; }
    input, select, textarea { font: inherit; font-size: 16px; } /* 16px stops iOS zoom-on-focus */

    /* =============================================
       LAYOUT
       ============================================= */
    .wrap    { max-width: 1160px; margin: 0 auto; padding: 0 24px; }
    .wrap-lg { max-width: 1280px; margin: 0 auto; padding: 0 32px; }
    .wrap-sm { max-width: 720px;  margin: 0 auto; padding: 0 24px; }
    section { padding: 80px 0; }
    section.tight { padding: 56px 0; }
    section.hero-section { padding: 0; }
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    .grid-3 { display: grid; grid-template-columns: repeat(3,1fr); gap: 20px; }
    .grid-4 { display: grid; grid-template-columns: repeat(4,1fr); gap: 16px; }
    @media(max-width: 1060px) { .grid-4 { grid-template-columns: repeat(2,1fr); } }
    @media(max-width: 860px)  { .grid-3, .grid-2 { grid-template-columns: 1fr; } }
    @media(max-width: 640px)  { .grid-4 { grid-template-columns: 1fr; } }

    /* =============================================
       TYPE SCALE (mirrors MkText)
       ============================================= */
    .eyebrow {
      display: inline-block;
      font-size: 13px; font-weight: 700;
      letter-spacing: .08em; text-transform: uppercase;
      color: var(--forest-mid); margin-bottom: 12px;
    }
    .page-title {
      font-size: clamp(30px, 4.6vw, 48px);
      font-weight: 800; line-height: 1.1; letter-spacing: -0.025em;
      color: var(--ink-dark); margin-bottom: 18px;
    }
    .section-title {
      font-size: clamp(24px, 3.2vw, 34px);
      font-weight: 700; line-height: 1.2;
      color: var(--ink-dark); margin-bottom: 12px;
    }
    .section-lead {
      font-size: 17px; color: var(--ink-muted);
      max-width: 42rem; line-height: 1.65;
    }
    .gold  { color: var(--sun-gold); }
    .green { color: var(--forest-mid); }

    /* =============================================
       BUTTONS — one green primary, one quiet secondary
       ============================================= */
    .btn {
      display: inline-flex; align-items: center; justify-content: center; gap: 8px;
      min-height: 48px; padding: 12px 22px;
      font-weight: 600; font-size: 15px; line-height: 1.2;
      border-radius: var(--radius-lg); border: 1.5px solid transparent;
      transition: background .15s ease, border-color .15s ease, color .15s ease;
      white-space: nowrap;
    }
    .btn-primary, .btn-gold { background: var(--forest-mid); color: #fff; }
    .btn-primary:hover, .btn-gold:hover { background: var(--forest-dark); }
    .btn-outline, .btn-quiet { background: #fff; color: var(--forest-dark); border-color: var(--border-mid); }
    .btn-outline:hover, .btn-quiet:hover { background: var(--leaf-pale); border-color: var(--forest-mid); }
    /* Ghost buttons were for dark heroes; there are none left. */
    .btn-ghost { background: #fff; color: var(--forest-dark); border-color: var(--border-mid); }
    .btn-ghost:hover { background: var(--leaf-pale); }
    .btn-sm { min-height: 44px; padding: 10px 16px; font-size: 14px; }
    .btn-lg { min-height: 52px; padding: 14px 28px; font-size: 16px; }
    .text-link {
      display: inline-flex; align-items: center; gap: 6px; min-height: 44px;
      font-weight: 600; color: var(--forest-dark);
    }
    .text-link:hover { text-decoration: underline; text-underline-offset: 3px; }

    /* =============================================
       CARDS, CHIPS, BADGES
       ============================================= */
    .card {
      background: #fff; border: 1px solid var(--border-light);
      border-radius: var(--radius-xl); padding: 28px;
      transition: border-color .15s ease, box-shadow .15s ease;
    }
    .card:hover { border-color: var(--border-mid); box-shadow: var(--shadow-md); }
    .card-icon {
      width: 48px; height: 48px; border-radius: 14px;
      background: var(--leaf-pale); color: var(--forest-mid);
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 16px; flex-shrink: 0;
    }
    .card h3 { font-size: 18px; font-weight: 700; color: var(--ink-dark); margin-bottom: 8px; }
    .card p  { color: var(--ink-muted); font-size: 15px; line-height: 1.6; }
    .tag {
      display: inline-flex; align-items: center; font-size: 13px; font-weight: 600;
      background: var(--surface-soft); color: var(--ink-body);
      border: 1px solid var(--border-light); padding: 4px 12px; border-radius: 999px;
    }
    .badge {
      display: inline-flex; align-items: center; gap: 8px;
      background: var(--leaf-pale); border: 0; color: var(--forest-dark);
      padding: 6px 14px; border-radius: 999px;
      font-size: 13px; font-weight: 700; letter-spacing: .04em;
    }
    .badge.dark { background: var(--leaf-pale); color: var(--forest-dark); }
    .pulse { width: 8px; height: 8px; border-radius: 50%; background: var(--leaf-bright); animation: mk-pulse 2s infinite; }
    @keyframes mk-pulse { 0%,100%{opacity:1;} 50%{opacity:.4;} }
    .divider { height: 1px; background: var(--border-light); margin: 48px 0; }
    .sr-only { position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0; }

    /* =============================================
       NAVIGATION
       ============================================= */
    #site-header {
      position: sticky; top: 0; z-index: 200; height: var(--nav-h);
      background: rgba(255,255,255,.97); backdrop-filter: blur(12px);
      border-bottom: 1px solid var(--border-light);
    }
    .nav-wrap { height: 100%; display: flex; align-items: center; justify-content: space-between; gap: 24px; }
    .nav-logo { display: flex; align-items: center; gap: 10px; flex-shrink: 0; min-height: 44px; }
    .nav-logo img { height: 40px; width: auto; object-fit: contain; }
    .nav-links { display: flex; align-items: center; gap: 2px; list-style: none; flex: 1; justify-content: center; }
    .nav-links a, .nav-dropdown-trigger {
      display: flex; align-items: center; gap: 5px; min-height: 44px; padding: 0 14px;
      font-size: 15px; font-weight: 500; color: var(--ink-body);
      border-radius: 10px; background: transparent; white-space: nowrap;
      transition: background .15s ease, color .15s ease;
    }
    .nav-links a:hover, .nav-dropdown-trigger:hover { background: var(--surface-soft); color: var(--ink-dark); }
    .nav-links a.active, .nav-dropdown-item.active .nav-dropdown-trigger { color: var(--forest-dark); font-weight: 600; }
    .nav-dropdown-item { position: relative; }
    .nav-dropdown-trigger svg { width: 14px; height: 14px; transition: transform .2s ease; }
    .nav-dropdown-item:hover .nav-dropdown-trigger svg { transform: rotate(180deg); }
    .nav-dropdown-menu {
      position: absolute; top: calc(100% + 6px); left: 50%; transform: translateX(-50%) translateY(-4px);
      background: #fff; border: 1px solid var(--border-light); border-radius: var(--radius-lg);
      padding: 6px; min-width: 220px; box-shadow: var(--shadow-lg);
      opacity: 0; pointer-events: none; transition: opacity .15s ease, transform .15s ease; z-index: 210;
    }
    .nav-dropdown-item:hover .nav-dropdown-menu,
    .nav-dropdown-item:focus-within .nav-dropdown-menu,
    .nav-dropdown-item.keyboard-open .nav-dropdown-menu { opacity: 1; pointer-events: all; transform: translateX(-50%) translateY(0); }
    .nav-dropdown-menu a {
      display: flex; align-items: center; gap: 10px; min-height: 44px; padding: 0 14px;
      border-radius: 10px; font-size: 15px; font-weight: 500; color: var(--ink-body); white-space: nowrap;
    }
    .nav-dropdown-menu a:hover { background: var(--surface-soft); }
    .nav-dropdown-menu a.active { background: var(--leaf-pale); color: var(--forest-dark); font-weight: 600; }
    .nav-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
    .lang-pill { display: flex; align-items: center; background: var(--surface-soft); border-radius: 999px; padding: 3px; }
    .lang-btn {
      background: transparent; border: none; min-height: 32px; padding: 0 12px; border-radius: 999px;
      font-size: 13px; font-weight: 700; color: var(--ink-muted);
    }
    .lang-btn.active { background: #fff; color: var(--forest-dark); box-shadow: var(--shadow-xs); }
    .hamburger {
      display: none; width: 48px; height: 48px; flex-direction: column;
      align-items: center; justify-content: center; gap: 5px; background: transparent; border-radius: 12px;
    }
    .hamburger:hover { background: var(--surface-soft); }
    .hamburger span { display: block; width: 22px; height: 2px; background: var(--ink-dark); border-radius: 2px; transition: all .25s ease; }
    .hamburger.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
    .hamburger.open span:nth-child(2) { opacity: 0; }
    .hamburger.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }
    #nav-drawer {
      position: fixed; inset: 0; z-index: 190; display: flex; flex-direction: column; background: #fff;
      opacity: 0; visibility: hidden; pointer-events: none; transition: opacity .2s ease, visibility .2s ease;
    }
    #nav-drawer.open { opacity: 1; visibility: visible; pointer-events: auto; }
    .drawer-header {
      height: var(--nav-h); display: flex; align-items: center; justify-content: space-between;
      padding: 0 16px 0 18px; border-bottom: 1px solid var(--border-light);
    }
    .drawer-close {
      width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;
      background: transparent; color: var(--ink-dark); border-radius: 12px;
    }
    .drawer-close:hover { background: var(--surface-soft); }
    .drawer-links { flex: 1; overflow-y: auto; padding: 8px 12px 16px; display: flex; flex-direction: column; }
    .drawer-links a {
      display: flex; align-items: center; min-height: 52px; padding: 0 12px; border-radius: 12px;
      font-size: 16px; font-weight: 500; color: var(--ink-dark);
    }
    .drawer-links a:hover { background: var(--surface-soft); }
    .drawer-group {
      font-size: 13px; font-weight: 700; color: var(--ink-muted);
      padding: 18px 12px 6px; text-transform: uppercase; letter-spacing: .08em;
    }
    .drawer-divider { height: 1px; background: var(--border-light); margin: 8px 12px; }
    .drawer-lang { display: flex; gap: 8px; padding: 8px 12px; }
    .drawer-lang button {
      flex: 1; min-height: 48px; border-radius: 12px; font-size: 15px; font-weight: 600;
      background: #fff; color: var(--ink-body); border: 1.5px solid var(--border-light);
    }
    .drawer-lang button.active { background: var(--leaf-pale); color: var(--forest-dark); border-color: var(--forest-mid); }
    .drawer-footer { padding: 16px 18px calc(16px + env(safe-area-inset-bottom)); border-top: 1px solid var(--border-light); display: flex; gap: 10px; }
    .drawer-footer .btn { flex: 1; }

    @media(max-width: 1000px) {
      .nav-links, .nav-actions .lang-pill, .nav-actions > .btn { display: none; }
      .hamburger { display: flex; }
    }

    /* =============================================
       FOOTER — white, like the rest of the page
       ============================================= */
    #site-footer { background: var(--surface-soft); color: var(--ink-body); padding: 64px 0 0; border-top: 1px solid var(--border-light); }
    .foot-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 40px; padding-bottom: 48px; }
    @media(max-width: 920px) { .foot-grid { grid-template-columns: 1fr 1fr; } }
    .foot-brand img { height: 40px; width: auto; }
    .foot-brand p { font-size: 15px; margin-top: 14px; color: var(--ink-muted); line-height: 1.6; max-width: 26rem; }
    .foot-contact { display: inline-flex; align-items: center; min-height: 44px; margin-top: 8px; font-weight: 600; color: var(--forest-dark); }
    .foot-col-title { font-weight: 700; font-size: 13px; color: var(--ink-dark); margin-bottom: 10px; letter-spacing: .08em; text-transform: uppercase; }
    .foot-links { list-style: none; display: flex; flex-direction: column; }
    .foot-links a { display: inline-flex; align-items: center; min-height: 40px; font-size: 15px; color: var(--ink-muted); }
    .foot-links a:hover { color: var(--forest-dark); }
    .foot-bottom {
      padding: 20px 0; display: flex; align-items: center; justify-content: space-between; gap: 12px;
      font-size: 13px; color: var(--ink-muted); flex-wrap: wrap; border-top: 1px solid var(--border-light);
    }

    /* =============================================
       SHARED PAGE BLOCKS
       ============================================= */
    .page-hero { background: #fff; padding: 72px 0 56px; border-bottom: 1px solid var(--border-light); }
    /* Highlight panels: a soft grey card, not a coloured band */
    .panel-dark {
      background: var(--surface-soft); color: var(--ink-body);
      border: 1px solid var(--border-light); border-radius: var(--radius-2xl); padding: 48px;
    }
    .panel-dark h2, .panel-dark h3 { color: var(--ink-dark); }
    .panel-dark p { color: var(--ink-muted); }
    .comm-hero, .verify-hero, .pitch-hero {
      background: #fff !important; color: var(--ink-body) !important;
      border-bottom: 1px solid var(--border-light);
    }
    .comm-hero h1, .verify-hero h1, .pitch-hero h1 { color: var(--ink-dark) !important; font-weight: 800 !important; letter-spacing: -.025em; }
    .comm-hero p, .verify-hero p, .pitch-hero p { color: var(--ink-muted) !important; }
    .contact-info-card { background: var(--surface-soft) !important; color: var(--ink-body) !important; border: 1px solid var(--border-light); }
    .contact-info-card h3 { color: var(--ink-dark) !important; font-weight: 700 !important; }
    .contact-info-card p, .contact-info-card h4 { color: var(--ink-muted) !important; }
    .contact-info-card a { color: var(--forest-dark) !important; font-weight: 600; }
    .contact-info-card .c-info-icon { background: #fff; border: 1px solid var(--border-light); color: var(--forest-mid); }
    .contact-info-card .c-info-label { color: var(--ink-muted); }
    .contact-info-card .c-info-value { color: var(--ink-body); }
    .contact-info-card .c-divider { background: var(--border-light); }
    /* Legacy dark-green gradient bands in page markup render as soft panels. */
    section[style*="linear-gradient(135deg,#0E4220"],
    section[style*="linear-gradient(135deg, #0E4220"],
    div[style*="linear-gradient(145deg,#0C3619"] {
      background: var(--surface-soft) !important; color: var(--ink-body) !important;
      border-top: 1px solid var(--border-light); border-bottom: 1px solid var(--border-light);
    }
    section[style*="linear-gradient(135deg,#0E4220"] :is(h2,h3),
    section[style*="linear-gradient(135deg, #0E4220"] :is(h2,h3),
    div[style*="linear-gradient(145deg,#0C3619"] :is(h2,h3) { color: var(--ink-dark) !important; }
    section[style*="linear-gradient(135deg,#0E4220"] p,
    section[style*="linear-gradient(135deg, #0E4220"] p,
    div[style*="linear-gradient(145deg,#0C3619"] p { color: var(--ink-muted) !important; }

    /* Forms */
    .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
    .form-group label { font-size: 14px; font-weight: 600; color: var(--ink-dark); }
    .form-group input, .form-group select, .form-group textarea {
      width: 100%; min-height: 48px; padding: 12px 14px; border-radius: 12px;
      border: 1.5px solid var(--border-mid); background: #fff; color: var(--ink-dark);
    }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
      outline: none; border-color: var(--forest-mid); box-shadow: 0 0 0 3px var(--leaf-pale);
    }

    /* Fade-up on scroll */
    .fade-up { opacity: 1; transform: none; }
    .js .fade-up { opacity: 0; transform: translateY(16px); transition: opacity .45s ease, transform .45s ease; }
    .js .fade-up.visible { opacity: 1; transform: none; }
    @media (prefers-reduced-motion: reduce) { .js .fade-up { opacity: 1; transform: none; transition: none; } }

    /* Focus visibility */
    a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible {
      outline: 3px solid var(--leaf-bright); outline-offset: 2px; border-radius: 6px;
    }

    /* =============================================
       PHONES (design floor 360px)
       ============================================= */
    @media (max-width: 700px) {
      section            { padding: 44px 0; }
      section.tight      { padding: 32px 0; }
      .wrap, .wrap-lg, .wrap-sm { padding-left: 16px; padding-right: 16px; }
      .page-title        { font-size: 28px; line-height: 1.18; margin-bottom: 14px; }
      .section-title     { font-size: 23px; line-height: 1.25; margin-bottom: 10px; }
      .section-lead      { font-size: 16px; }
      p, li, td, label   { font-size: max(1em, 14px); }
      .card              { padding: 18px; border-radius: 14px; }
      .card h3           { font-size: 17px; }
      .card-icon         { width: 44px; height: 44px; border-radius: 12px; margin-bottom: 12px; }
      .grid-2, .grid-3, .grid-4 { gap: 12px; }
      .btn               { width: 100%; }
      .btn-sm            { width: auto; }
      .card:hover        { box-shadow: none; }
      .cat-btn, .chip, .filter-btn { min-height: 44px !important; padding: 10px 16px !important; }
      .store-pill        { display: inline-flex; align-items: center; min-height: 44px; padding: 6px 12px; font-size: 13px; }

      /* Page-level blocks declare desktop padding in each page's <style>. */
      .solution-row, .journey, .light-capabilities, .final-cta,
      .sol-band, .submit-story-panel, .no-pitch, .story-band,
      .contact-form-card, .partner-form, .deck-inner, .pdf-fallback {
        padding-top: 32px !important; padding-bottom: 32px !important;
      }
      .cap-item, .highlight-card, .principle-card, .team-card,
      .story-card-body, .contact-info-card { padding: 18px !important; }
      .contact-form-card, .partner-form, .submit-story-panel { padding-left: 16px !important; padding-right: 16px !important; }
      .hero-copy { padding: 24px 0 !important; }
      .editorial-hero, .editorial-hero .wrap { min-height: 0 !important; }
      .panel-dark { padding: 24px 18px !important; border-radius: 16px; }
      .page-hero, .editorial-hero, .hero-section { padding-top: 28px !important; padding-bottom: 32px !important; }
      .solution-row { gap: 20px !important; }
      .sol-tile { padding: 20px !important; min-height: 0 !important; }

      .foot-grid { grid-template-columns: 1fr 1fr; gap: 8px 16px; padding-bottom: 24px; }
      .foot-brand { grid-column: 1 / -1; margin-bottom: 12px; }
      #site-footer { padding-top: 36px; }
    }

    /* Sticky mobile action bar: the primary action stays in reach. */
    .mobile-action-bar { display: none; }
    @media (max-width: 700px) {
      .mobile-action-bar {
        position: fixed; left: 0; right: 0; bottom: 0; z-index: 180;
        display: flex; gap: 10px; align-items: center;
        padding: 10px 16px calc(10px + env(safe-area-inset-bottom));
        background: rgba(255,255,255,.98); border-top: 1px solid var(--border-light);
      }
      .mobile-action-bar .btn { flex: 1; }
      .mobile-action-bar .btn-quiet { flex: 0 0 auto; width: auto; padding: 12px 20px; }
      #site-footer { padding-bottom: 84px; }
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
      <a href="/download" class="btn btn-primary btn-sm" data-i18n="nav_download">Pakua App</a>

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
    <button class="drawer-close" onclick="toggleDrawer()" aria-label="Funga menyu"><x-icon name="close" :size="24" /></button>
  </div>
  <div class="drawer-links">
    <a href="/" onclick="toggleDrawer()"><span data-i18n="nav_home">Home</span></a>
    <a href="/verify" onclick="toggleDrawer()"><span data-i18n="nav_verify">Mkulima Verify</span></a>
    
    <div class="drawer-group" data-i18n="nav_solutions_group">SOLUTIONS</div>
    <a href="/solutions" onclick="toggleDrawer()"><span data-i18n="nav_solutions">All Solutions</span></a>
    <a href="/technology" onclick="toggleDrawer()"><span data-i18n="nav_tech">Technology & AI</span></a>

    <div class="drawer-group" data-i18n="nav_community_group">COMMUNITY</div>
    <a href="/community" onclick="toggleDrawer()"><span data-i18n="nav_community">Community Hub</span></a>
    <a href="/stories" onclick="toggleDrawer()"><span data-i18n="nav_stories">Farmer Stories</span></a>

    <div class="drawer-group" data-i18n="nav_company_group">COMPANY</div>
    <a href="/about" onclick="toggleDrawer()"><span data-i18n="nav_about">About Us</span></a>
    <a href="/impact" onclick="toggleDrawer()"><span data-i18n="nav_impact">Impact</span></a>
    <a href="/partners" onclick="toggleDrawer()"><span data-i18n="nav_partners">Partners</span></a>
    <a href="/pitch-deck" onclick="toggleDrawer()"><span data-i18n="nav_pitchdeck">Pitch Deck</span></a>
    <a href="/contact" onclick="toggleDrawer()"><span data-i18n="nav_contact">Contact</span></a>

    <div class="drawer-divider"></div>
    <!-- Lang switcher in drawer -->
    <div class="drawer-lang">
      <button onclick="mkSwitchLang('sw'); toggleDrawer()" id="drawerBtnSw">Kiswahili</button>
      <button onclick="mkSwitchLang('en'); toggleDrawer()" id="drawerBtnEn">English</button>
    </div>
  </div>
  <div class="drawer-footer">
    <a href="/login" class="btn btn-outline" data-i18n="nav_login">Ingia</a>
    <a href="/download" class="btn btn-primary" data-i18n="nav_download">Pakua App</a>
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
        <a href="/contact" class="foot-contact" data-i18n="foot_contact_link">Wasiliana Nasi →</a>
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
      <span>Tanzania &bull; Built for East African Farmers &bull; Powered by Mkulima AI</span>
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
