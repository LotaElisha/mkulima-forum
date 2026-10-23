<div>
  @if ($sent)
    <x-mk.alert>Asante. Tumepokea ujumbe wako na tutaujibu.</x-mk.alert>
  @endif

  <form wire:submit="submit">
    <div class="form-grid-2">
      <div class="mk-field">
        <label for="cf_name">Jina lako kamili</label>
        <input id="cf_name" type="text" wire:model="name" autocomplete="name" required>
        @error('name') <p class="mk-error">{{ $message }}</p> @enderror
      </div>
      <div class="mk-field">
        <label for="cf_email">Barua pepe</label>
        <input id="cf_email" type="email" wire:model="email" autocomplete="email" inputmode="email" autocapitalize="none" required>
        @error('email') <p class="mk-error">{{ $message }}</p> @enderror
      </div>
    </div>

    <div class="mk-field">
      <label for="cf_phone">Simu <span class="optional">(si lazima)</span></label>
      <input id="cf_phone" type="tel" wire:model="phone" autocomplete="tel">
      @error('phone') <p class="mk-error">{{ $message }}</p> @enderror
    </div>

    <div class="mk-field">
      <label for="cf_type">Aina ya ujumbe</label>
      <select id="cf_type" wire:model="subject" required>
        <option value="">-- Chagua aina --</option>
        <option value="partnership">Ushirikiano</option>
        <option value="investor">Uwekezaji</option>
        <option value="press">Vyombo vya habari</option>
        <option value="farmer">Msaada kwa mkulima</option>
        <option value="technical">Msaada wa kiufundi</option>
        <option value="general">Swali la jumla</option>
      </select>
      @error('subject') <p class="mk-error">{{ $message }}</p> @enderror
    </div>

    <div class="mk-field">
      <label for="cf_org">Shirika <span class="optional">(si lazima)</span></label>
      <input id="cf_org" type="text" wire:model="organization">
    </div>

    <div class="mk-field">
      <label for="cf_message">Ujumbe wako</label>
      <textarea id="cf_message" rows="5" wire:model="message" required></textarea>
      @error('message') <p class="mk-error">{{ $message }}</p> @enderror
    </div>

    <button type="submit" class="mk-btn" wire:loading.attr="disabled">
      Tuma ujumbe
    </button>
  </form>
</div>
