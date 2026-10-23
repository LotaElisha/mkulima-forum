<div>
  <h1>Weka nenosiri jipya</h1>
  <p class="lead">Nenosiri liwe na herufi 12 au zaidi.</p>

  <form wire:submit="resetPassword">
    <div class="field">
      <label for="email">Barua pepe</label>
      <div class="control">
        <input id="email" type="email" wire:model="email" autocomplete="email" inputmode="email" autocapitalize="none" required>
      </div>
      @error('email') <p class="field-error show">{{ $message }}</p> @enderror
    </div>

    <div class="field">
      <label for="password">Nenosiri jipya</label>
      <div class="control">
        <input id="password" type="password" wire:model="password" autocomplete="new-password" required>
      </div>
      @error('password') <p class="field-error show">{{ $message }}</p> @enderror
    </div>

    <div class="field">
      <label for="password_confirmation">Rudia nenosiri</label>
      <div class="control">
        <input id="password_confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password" required>
      </div>
    </div>

    <button class="btn btn-primary" type="submit" wire:loading.attr="disabled">
      <span class="label">Hifadhi nenosiri</span>
    </button>
  </form>
</div>
