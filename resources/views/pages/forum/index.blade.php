@extends('layouts.public')

@section('title', 'Jukwaa | MkulimaForum')
@section('meta_description', 'Soma mijadala ya wakulima. Kuandika swali bado hakijawashwa.')

@section('content')
<div class="wrap mk-home">
  <x-mk.badge live>Kusoma</x-mk.badge>
  <h1 style="font-size:28px;margin:8px 0">Jukwaa</h1>
  <p class="lead" style="color:var(--mk-color-muted);max-width:36rem">Unaweza kusoma mijadala. Kuandika swali kitapatiwa baadaye.</p>

  @forelse ($threads as $thread)
    <a class="mk-row" href="{{ route('forum.show', $thread['uuid']) }}">
      <div>
        <strong>{{ $thread['title'] }}</strong>
        <span>{{ $thread['region'] ?: 'Tanzania' }} · {{ $thread['reply_count'] }} majibu</span>
      </div>
    </a>
  @empty
    <x-mk.empty title="Bado hakuna mijadala">Mijadala itaonekana hapa ikiandikwa.</x-mk.empty>
  @endforelse
</div>
@endsection
