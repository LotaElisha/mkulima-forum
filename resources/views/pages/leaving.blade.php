@extends('layouts.auth')

@section('title', 'Unaondoka MkulimaForum')

@section('content')
  {{-- Shown when a short link points somewhere outside the allowlist.

       The alternative was redirecting silently, which turns mkulimaforum.com
       into a redirector: a phishing link wearing the platform's own domain.
       Naming the destination and making the visitor choose costs one tap and
       removes that entirely. --}}
  <div class="leaving-mark" aria-hidden="true">
    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M12 9v4"/><path d="M12 17h.01"/>
      <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/>
    </svg>
  </div>

  <h1>Unaondoka MkulimaForum</h1>
  <p class="lead">Kiungo hiki kinakupeleka kwenye tovuti isiyo yetu. Hakikisha unaitambua kabla ya kuendelea.</p>

  <p class="destination" role="note">
    <span class="destination-label">Unakwenda</span>
    <span class="destination-host">{{ $host ?: 'tovuti isiyojulikana' }}</span>
  </p>

  {{-- Safe action is the primary one. Painting "Endelea" green would make the
       risky choice the obvious one on a security interstitial, which defeats
       the point of showing it. --}}
  <a class="btn btn-primary" href="/">Rudi MkulimaForum</a>
  <a class="btn btn-quiet" href="{{ $target }}" rel="noopener noreferrer nofollow">Endelea kwenye tovuti hii</a>
@endsection

@section('head_extra')
<style>
  .leaving-mark{
    width:52px; height:52px; margin:0 auto 18px; border-radius:15px;
    display:grid; place-items:center;
    background:#FCF1E4; color:#A85B06;
  }
  .auth-card h1, .auth-card .lead{ text-align:center; }
  .destination{
    display:flex; flex-direction:column; gap:4px; align-items:center;
    background:var(--surface-sunken); border:1px solid var(--line);
    border-radius:var(--r-md); padding:14px 16px; margin-bottom:22px;
  }
  .destination-label{
    font-size:.75rem; font-weight:700; letter-spacing:.1em;
    text-transform:uppercase; color:var(--ink-muted);
  }
  .destination-host{
    font-family:ui-monospace,SFMono-Regular,Menlo,monospace;
    font-size:1rem; font-weight:600; color:var(--ink);
    word-break:break-all; text-align:center;
  }
  .auth-card .btn{ margin-bottom:10px; }
  .auth-card .btn:last-child{ margin-bottom:0; }
</style>
@endsection
