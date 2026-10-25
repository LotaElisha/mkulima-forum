@extends('layouts.public')

@section('title', $thread['title'].' | Jukwaa')

@section('content')
<div class="wrap mk-home" style="max-width:720px">
  <p><a href="{{ route('forum.index') }}">Rudi kwenye jukwaa</a></p>
  <h1 style="font-size:28px;margin:12px 0 8px">{{ $thread['title'] }}</h1>
  <p style="color:var(--mk-color-muted);margin-bottom:16px">{{ $thread['user']['name'] ?? 'Mkulima' }} · {{ $thread['region'] }}</p>
  <div class="mk-card"><p style="color:var(--mk-color-ink-body);white-space:pre-wrap">{{ $thread['body'] }}</p></div>

  <h2 style="font-size:20px;margin:24px 0 8px">Majibu</h2>
  @forelse ($thread['replies'] ?? [] as $reply)
    <article class="mk-card" style="margin-bottom:10px">
      <strong>{{ $reply['user']['name'] ?? 'Mkulima' }}</strong>
      <p style="margin-top:6px;color:var(--mk-color-ink-body);white-space:pre-wrap">{{ $reply['body'] }}</p>
    </article>
  @empty
    <x-mk.empty title="Hakuna majibu bado">Majibu yataonekana hapa.</x-mk.empty>
  @endforelse

  <x-mk.alert tone="warn">Kuandika jibu bado hakijawashwa kwenye toleo hili.</x-mk.alert>
</div>
@endsection
