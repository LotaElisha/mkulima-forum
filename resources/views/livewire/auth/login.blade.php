<div>
  <h1>Ingia</h1>
  <p class="lead">Tumia barua pepe na nenosiri. Kuingia kwa SMS bado hakijawashwa.</p>

  @if ($notice)
    <div class="alert alert-ok show" role="status">{{ $notice }}</div>
  @endif

  @if (session('status'))
    <div class="alert alert-ok show" role="status">{{ session('status') }}</div>
  @endif

  <form wire:submit="login">
    <div class="field">
      <label for="email">Barua pepe</label>
      <div class="control">
        <input id="email" type="email" wire:model="email" autocomplete="username" inputmode="email" autocapitalize="none" spellcheck="false" required>
      </div>
      @error('email') <p class="field-error show">{{ $message }}</p> @enderror
    </div>

    <div class="field">
      <label for="password">Nenosiri</label>
      <div class="control">
        <input id="password" type="password" wire:model="password" autocomplete="current-password" required>
      </div>
      @error('password') <p class="field-error show">{{ $message }}</p> @enderror
    </div>

    <div class="field">
      <label>
        <input type="checkbox" wire:model="remember">
        Nikumbuke kwenye kifaa hiki
      </label>
    </div>

    <button class="btn btn-primary" type="submit" wire:loading.attr="disabled">
      <span class="label">Ingia</span>
    </button>
  </form>

  <p class="lead" style="margin-top:18px">
    <a href="{{ route('password.request') }}">Umesahau nenosiri?</a>
  </p>
</div>
