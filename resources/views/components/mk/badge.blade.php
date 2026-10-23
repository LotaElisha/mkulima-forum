@props(['live' => false])
<span {{ $attributes->merge(['class' => $live ? 'mk-badge mk-badge-live' : 'mk-badge']) }}>{{ $slot }}</span>
