@extends('layouts.public')

@section('title', 'MkulimaForum | Bei, hali ya hewa, na jamii')
@section('meta_description', 'Bei za masoko zilizoandikwa, hali ya hewa, na mijadala ya wakulima kwa Kiswahili. Soko, malipo, na utambuzi wa magonjwa bado havijawashwa.')

@section('content')
<div class="wrap mk-home">
  <section class="mk-hero" aria-labelledby="home-title">
    <x-mk.badge live>Kinachofanya kazi sasa</x-mk.badge>
    <h1 id="home-title">{{ $settings['hero_title'] ?? 'Bei, hali ya hewa, na jamii.' }}</h1>
    <p class="lead">{{ $settings['hero_lead'] ?? 'Anza na taarifa unazoweza kutumia leo.' }}</p>
    <div class="mk-actions">
      <a class="mk-btn" href="{{ route('register') }}">Jisajili</a>
      <a class="mk-btn mk-btn-quiet" href="{{ route('login') }}">Ingia</a>
    </div>
  </section>

  <div class="mk-grid mk-grid-3">
    <section class="mk-card" id="hewa" aria-labelledby="hewa-title">
      <x-mk.badge live>Hali ya hewa</x-mk.badge>
      <h2 id="hewa-title">{{ $brief['weather']['location'] }}</h2>
      @if ($brief['weather']['available'])
        <p>{{ $brief['weather']['summary'] }}</p>
      @else
        <x-mk.empty title="Hakuna taarifa sasa">Hali ya hewa itaonekana hapa ikiwa seva ina kumbukumbu. Hatutungi namba.</x-mk.empty>
      @endif
    </section>

    <section class="mk-card" id="bei" aria-labelledby="bei-title">
      <x-mk.badge live>Bei zilizoandikwa</x-mk.badge>
      <h2 id="bei-title">Bei za soko</h2>
      @forelse ($brief['prices'] as $price)
        <div class="mk-row">
          <div>
            <strong>{{ $price['commodity'] }}</strong>
            <span>{{ $price['market'] }} · {{ $price['date'] }} · {{ $price['price'] }}</span>
          </div>
        </div>
      @empty
        <x-mk.empty title="Bado hakuna bei">Bei zinaonekana baada ya kuandikwa, pamoja na tarehe.</x-mk.empty>
      @endforelse
    </section>

    <section class="mk-card" id="jukwaa" aria-labelledby="jukwaa-title">
      <x-mk.badge live>Jukwaa</x-mk.badge>
      <h2 id="jukwaa-title">Mijadala</h2>
      @forelse ($brief['threads'] as $thread)
        <a class="mk-row" href="{{ route('forum.show', $thread['uuid']) }}">
          <div>
            <strong>{{ $thread['title'] }}</strong>
            <span>{{ $thread['replies'] }} majibu</span>
          </div>
        </a>
      @empty
        <x-mk.empty title="Bado hakuna mijadala">Kusoma mijadala kumewashwa. Kuandika swali bado hakijawashwa kwenye toleo hili.</x-mk.empty>
      @endforelse
      <p style="margin-top:12px"><a href="{{ route('forum.index') }}">Fungua jukwaa</a></p>
    </section>
  </div>

  <section aria-labelledby="soon-title" style="margin-top:28px">
    <h2 id="soon-title" style="font-size:20px;margin-bottom:8px">Bado hazijawashwa</h2>
    <div class="mk-soon">
      <article class="mk-card"><x-mk.badge>Inakuja hivi karibuni</x-mk.badge><h3>Soko na malipo</h3><p>Kununua, kuuza, na kulipa bado hakufanyi kazi.</p></article>
      <article class="mk-card"><x-mk.badge>Inakuja hivi karibuni</x-mk.badge><h3>Utambuzi wa magonjwa</h3><p>Kupiga picha ya jani na kupata utambuzi bado hakufanyi kazi.</p></article>
      <article class="mk-card"><x-mk.badge>Inakuja hivi karibuni</x-mk.badge><h3>SMS na USSD</h3><p>Huduma hizi hazitumwi. Unahitaji intaneti.</p></article>
      <article class="mk-card"><x-mk.badge>Inakuja hivi karibuni</x-mk.badge><h3>Kagua pembejeo</h3><p>Ukaguzi wa mbegu na dawa bado haujawashwa.</p></article>
    </div>
  </section>
</div>
@endsection
