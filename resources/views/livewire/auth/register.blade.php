<div>
  <h1>Fungua akaunti</h1>
  <p class="lead">Akaunti ya mkulima. Tutakutumia barua pepe ili kuthibitisha anwani yako. Nenosiri liwe na herufi 12 au zaidi.</p>

  <form wire:submit="register">
    <div class="field">
      <label for="name">Jina lako</label>
      <div class="control">
        <input id="name" type="text" wire:model="name" autocomplete="name" required>
      </div>
      @error('name') <p class="mk-error">{{ $message }}</p> @enderror
    </div>

    <div class="field">
      <label for="email">Barua pepe</label>
      <div class="control">
        <input id="email" type="email" wire:model="email" autocomplete="email" inputmode="email" autocapitalize="none" spellcheck="false" required>
      </div>
      @error('email') <p class="mk-error">{{ $message }}</p> @enderror
    </div>

    <div class="field">
      <label for="password">Nenosiri</label>
      <div class="control">
        <input id="password" type="password" wire:model="password" autocomplete="new-password" required>
      </div>
      @error('password') <p class="mk-error">{{ $message }}</p> @enderror
    </div>

    <div class="field">
      <label for="password_confirmation">Rudia nenosiri</label>
      <div class="control">
        <input id="password_confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password" required>
      </div>
    </div>

    <button class="mk-btn mk-btn-block" type="submit" wire:loading.attr="disabled">
      <span class="label">Jisajili</span>
    </button>
  </form>
</div>
