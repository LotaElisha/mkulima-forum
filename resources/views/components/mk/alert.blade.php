@props(['tone' => 'ok'])
<div {{ $attributes->merge(['class' => 'mk-alert mk-alert-'.$tone, 'role' => 'status']) }}>{{ $slot }}</div>
