{{--
  Shared shell for every authentication screen.

  Design rules this file enforces, so no auth page can drift again:
   • White canvas. Green is an accent on actions and state, never a backdrop.
   • System fonts only. The public site pulls four Google font families over
     the network before it can paint; on a Tanzanian mobile connection that is
     the difference between an instant sign-in and a blank screen.
   • Icons are inline SVG. The rest of the site renders Material Symbols via a
     remote ligature font, so a failed font load prints the literal word
     "check_circle" on screen. Nothing here can fail that way.
   • 16px inputs (below that iOS auto-zooms on focus) and 48px touch targets.
--}}
<!DOCTYPE html>
<html lang="{{ $lang ?? 'sw' }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#FFFFFF">
  <meta name="robots" content="noindex, nofollow">
  <title>@yield('title', 'MkulimaForum')</title>
  <style>
    :root{
      --green-700:#1B5E20; --green-600:#2E7D32; --green-500:#3D8B41;
      --green-50:#F1F8F2;  --green-100:#DCEDDF;
      --ink:#101613; --ink-body:#3C4640; --ink-muted:#66716A;
      --line:#E3E8E4; --line-strong:#CBD4CD;
      --surface:#FFFFFF; --surface-sunken:#F7F9F7;
      --danger:#B3261E; --danger-bg:#FDECEA;
      --ok:#1B5E20; --ok-bg:#E8F5E9;
      --r-sm:10px; --r-md:14px; --r-lg:20px;
    }
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    html{-webkit-text-size-adjust:100%}
    body{
      font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;
      background:var(--surface); color:var(--ink-body);
      line-height:1.55; -webkit-font-smoothing:antialiased;
      min-height:100dvh; display:flex; flex-direction:column;
    }
    a{color:var(--green-700); text-decoration:none; font-weight:600}
    a:hover{text-decoration:underline}
    svg{display:block; flex:none}

    /* ── Shell ─────────────────────────────────────────────── */
    .auth-shell{
      flex:1; width:100%; max-width:440px; margin:0 auto;
      padding:28px 20px calc(32px + env(safe-area-inset-bottom));
      display:flex; flex-direction:column;
    }
    .auth-brand{
      display:inline-flex; align-items:center; gap:10px;
      align-self:center; margin-bottom:26px;
      font-weight:800; font-size:1.0625rem; letter-spacing:-.01em;
      color:var(--ink); text-decoration:none;
    }
    .auth-brand:hover{text-decoration:none}
    .auth-brand .mark{
      width:34px; height:34px; border-radius:10px; flex:none;
      background:var(--green-600); color:#fff;
      display:grid; place-items:center;
    }

    .auth-card{background:var(--surface)}
    @media(min-width:520px){
      .auth-card{
        border:1px solid var(--line); border-radius:var(--r-lg);
        padding:32px; box-shadow:0 1px 2px rgba(16,22,19,.04),0 8px 28px rgba(16,22,19,.06);
      }
    }

    h1{
      font-size:1.5rem; line-height:1.25; font-weight:800;
      letter-spacing:-.02em; color:var(--ink); margin-bottom:8px;
    }
    .lead{color:var(--ink-muted); font-size:.9375rem; margin-bottom:24px}

    /* ── Fields ────────────────────────────────────────────── */
    .field{margin-bottom:18px}
    label{
      display:block; font-size:.875rem; font-weight:600;
      color:var(--ink); margin-bottom:7px;
    }
    .control{position:relative; display:flex; align-items:center}
    input[type=email],input[type=password],input[type=text],input[type=tel],select{
      width:100%; min-height:52px;
      padding:14px 16px; font-size:16px; font-family:inherit;
      color:var(--ink); background:var(--surface);
      border:1.5px solid var(--line-strong); border-radius:var(--r-md);
      transition:border-color .15s, box-shadow .15s;
    }
    input:focus,select:focus{
      outline:none; border-color:var(--green-600);
      box-shadow:0 0 0 3px var(--green-100);
    }
    input[aria-invalid=true]{border-color:var(--danger)}
    input::placeholder{color:#9AA39C}
    .has-toggle input{padding-right:52px}
    .toggle-visibility{
      position:absolute; right:4px; width:48px; height:48px;
      display:grid; place-items:center; border:0; background:none;
      color:var(--ink-muted); border-radius:var(--r-sm); cursor:pointer;
    }
    .toggle-visibility:hover{color:var(--green-700); background:var(--green-50)}
    .hint{font-size:.8125rem; color:var(--ink-muted); margin-top:6px}
    .field-error{
      display:none; font-size:.8125rem; color:var(--danger);
      margin-top:6px; font-weight:600;
    }
    .field-error.show{display:block}

    /* ── Buttons ───────────────────────────────────────────── */
    .btn{
      width:100%; min-height:52px;
      display:inline-flex; align-items:center; justify-content:center; gap:8px;
      font-family:inherit; font-size:1rem; font-weight:700;
      border-radius:var(--r-md); border:1.5px solid transparent;
      cursor:pointer; transition:background .15s, opacity .15s;
    }
    .btn-primary{background:var(--green-600); color:#fff}
    .btn-primary:hover{background:var(--green-700)}
    .btn-primary:disabled{opacity:.6; cursor:not-allowed}
    .btn-quiet{background:var(--surface); color:var(--ink); border-color:var(--line-strong)}
    .btn-quiet:hover{background:var(--surface-sunken)}
    .spinner{
      width:18px; height:18px; border-radius:50%;
      border:2px solid rgba(255,255,255,.45); border-top-color:#fff;
      animation:spin .7s linear infinite;
    }
    @keyframes spin{to{transform:rotate(360deg)}}
    @media(prefers-reduced-motion:reduce){.spinner{animation-duration:2s}}

    /* ── Feedback ──────────────────────────────────────────── */
    .alert{
      display:none; gap:10px; align-items:flex-start;
      padding:13px 15px; border-radius:var(--r-md);
      font-size:.9375rem; margin-bottom:20px; font-weight:500;
    }
    .alert.show{display:flex}
    .alert-error{background:var(--danger-bg); color:var(--danger)}
    .alert-ok{background:var(--ok-bg); color:var(--ok)}

    .foot{
      text-align:center; margin-top:24px;
      font-size:.9375rem; color:var(--ink-muted);
    }
    .back{
      display:inline-flex; align-items:center; gap:6px;
      min-height:44px; font-size:.9375rem;
    }
  </style>
  @yield('head_extra')
</head>
<body>
  <main class="auth-shell">
    <a class="auth-brand" href="/">
      <span class="mark" aria-hidden="true">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/>
          <path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/>
        </svg>
      </span>
      MkulimaForum
    </a>

    <section class="auth-card">
      @yield('content')
    </section>

    <p class="foot">@yield('foot')</p>
  </main>
</body>
</html>
