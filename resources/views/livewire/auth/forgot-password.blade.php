<div>
  <h1>Umesahau nenosiri?</h1>
  <p class="lead">Weka barua pepe uliyotumia kujisajili. Kama akaunti ipo, tutakutumia kiungo cha kuweka nenosiri jipya.</p>

  @if ($sent)
    <x-mk.alert>{{ __('auth_flows.reset_link_sent') }}</x-mk.alert>
  @endif

  <form wire:submit="send">
    <div class="field">
      <label for="email">Barua pepe</label>
      <div class="control">
        <input id="email" type="email" wire:model="email" autocomplete="email" inputmode="email" autocapitalize="none" spellcheck="false" required>
      </div>
      @error('email') <p class="mk-error">{{ $message }}</p> @enderror
    </div>

    <button class="mk-btn mk-btn-block" type="submit" wire:loading.attr="disabled">
      <span class="label">Tuma kiungo</span>
    </button>
  </form>
</div>
