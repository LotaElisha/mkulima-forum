@extends('layouts.public')

@section('title', 'Mkulima Trace · '.$summary['code'])
@section('meta_description', 'Mkulima Trace — Shamba hadi Soko. Historia fupi ya mzigo huu wa mazao.')
@section('og_title', 'Mkulima Trace · Shamba hadi Soko')
@section('og_description', 'Historia fupi ya mzigo huu wa mazao, kutoka shambani hadi sokoni.')

@section('head_extra')
<meta name="robots" content="noindex">
<style>
  .trace-wrap { max-width:640px; margin:0 auto; padding:40px 16px 64px; }
  .trace-badge { display:inline-block; padding:4px 10px; border-radius:999px; font-size:13px; font-weight:700;
    letter-spacing:.04em; background:var(--leaf-pale); color:var(--forest-dark); }
  .trace-title { font-size:28px; font-weight:800; color:var(--ink-dark); margin:12px 0 4px; line-height:1.2; }
  .trace-tag { font-size:15px; color:var(--ink-muted); margin-bottom:24px; }
  .trace-card { background:#fff; border:1px solid var(--border-light); border-radius:14px; padding:20px; }
  .trace-row { display:flex; justify-content:space-between; gap:12px; padding:12px 0; border-bottom:1px solid var(--border-light); font-size:15px; }
  .trace-row:last-child { border-bottom:none; }
  .trace-row span:first-child { color:var(--ink-muted); }
  .trace-row span:last-child { color:var(--ink-dark); font-weight:600; text-align:right; }
  .trace-en { display:block; font-size:13px; font-weight:400; color:var(--ink-muted); }
  .trace-notice { margin-top:20px; font-size:14px; line-height:1.6; color:var(--ink-muted); }
  .trace-app { margin-top:20px; font-size:15px; line-height:1.6; color:var(--ink-body); }
</style>
@endsection

@section('content')
@php
  $statuses = [
    'active' => ['Inaendelea', 'Active'],
    'in_transfer' => ['Inakabidhiwa', 'Being handed over'],
    'split' => ['Imegawanywa', 'Split'],
    'merged' => ['Imeunganishwa', 'Merged'],
    'processed' => ['Imesindikwa', 'Processed'],
    'delivered' => ['Imefikishwa', 'Delivered'],
    'closed' => ['Imefungwa', 'Closed'],
  ];
  $status = $statuses[$summary['status']] ?? [$summary['status'], $summary['status']];
  $month = $summary['harvest_month'] ? \Illuminate\Support\Carbon::createFromFormat('Y-m', $summary['harvest_month'])->format('m/Y') : '—';
@endphp
<div class="trace-wrap">
  <span class="trace-badge">MKULIMA TRACE</span>
  <h1 class="trace-title">{{ $summary['product'] ?: ucfirst($summary['crop']) }}</h1>
  <p class="trace-tag">Shamba hadi Soko · From farm to market</p>

  <div class="trace-card">
    <div class="trace-row"><span>Msimbo <span class="trace-en">Code</span></span><span>{{ $summary['code'] }}</span></div>
    <div class="trace-row"><span>Zao <span class="trace-en">Crop</span></span><span>{{ $summary['crop'] }}</span></div>
    @if($summary['grade'])
    <div class="trace-row"><span>Daraja <span class="trace-en">Grade</span></span><span>{{ $summary['grade'] }}</span></div>
    @endif
    <div class="trace-row"><span>Mkoa <span class="trace-en">Region</span></span><span>{{ $summary['region'] ?: '—' }}</span></div>
    <div class="trace-row"><span>Mwezi wa mavuno <span class="trace-en">Harvest month</span></span><span>{{ $month }}</span></div>
    <div class="trace-row"><span>Hatua zilizorekodiwa <span class="trace-en">Recorded steps</span></span><span>{{ $summary['steps'] }}</span></div>
    <div class="trace-row"><span>Makabidhiano yaliyothibitishwa na pande zote <span class="trace-en">Hand-offs confirmed by both sides</span></span><span>{{ $summary['handoffs_confirmed'] }} / {{ $summary['handoffs'] }}</span></div>
    <div class="trace-row"><span>Hali <span class="trace-en">Status</span></span><span>{{ $status[0] }} <span class="trace-en">{{ $status[1] }}</span></span></div>
  </div>

  <p class="trace-app">
    Washiriki wa mnyororo huu wanaona historia kamili kwenye programu ya MkulimaForum.
    <span class="trace-en">People in this lot's chain see the full history in the MkulimaForum app.</span>
  </p>
  <p class="trace-notice">
    {{ __('trace.record_notice', [], 'sw') }}
    <span class="trace-en">{{ __('trace.record_notice', [], 'en') }}</span>
  </p>
</div>
@endsection
