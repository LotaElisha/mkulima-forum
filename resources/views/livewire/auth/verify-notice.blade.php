<div>
  <h1>Thibitisha barua pepe</h1>
  <p class="lead">Tumetuma kiungo kwenye barua pepe yako. Fungua kiungo hicho ili kuthibitisha akaunti. Ukikosa ujumbe, angalia folda ya taka.</p>

  @if ($sent)
    <x-mk.alert>{{ __('auth_flows.verification_sent') }}</x-mk.alert>
  @endif

  @error('email') <p class="mk-error">{{ $message }}</p> @enderror

  @auth
    <button class="mk-btn mk-btn-block" type="button" wire:click="resend" wire:loading.attr="disabled">
      <span class="label">Tuma kiungo tena</span>
    </button>
  @else
    <p class="lead"><a href="{{ route('login') }}">Ingia</a> ili kuomba kiungo kipya.</p>
  @endauth
</div>
