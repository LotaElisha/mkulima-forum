@props(['size' => 40, 'settings' => []])

{{--
  The MkulimaForum mark: the original farmer emblem with the wordmark set in
  the brand banner's two greens. The header used to shrink the whole banner
  image (cream backdrop, motto, four pillars) to 42px tall, where none of it
  was legible. An emblem uploaded in Admin → Settings still wins.
--}}
<span {{ $attributes->merge(['class' => 'brand']) }}>
  <img src="{{ $settings['emblem_url'] ?? '/images/logo-icon.jpg' }}"
       alt="" width="{{ $size }}" height="{{ $size }}"
       style="width:{{ $size }}px;height:{{ $size }}px;border-radius:50%;object-fit:cover;flex:none">
  <span class="brand-word" style="font-size:{{ round($size * 0.5) }}px">
    <span style="color:#14532D">Mkulima</span><span style="color:#4E9A2B">Forum</span>
  </span>
</span>
