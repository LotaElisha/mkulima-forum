<x-filament-panels::page>
    <form wire:submit="save" class="fi-sc-form">
        {{ $this->form }}

        <x-filament::section>
            <label class="fi-fo-field-label">
                <x-filament::input.checkbox wire:model="confirmHighImpact" />
                <span class="fi-fo-field-label-content">
                    Ninathibitisha mabadiliko ya anwani ya tovuti na mipangilio mingine yenye athari kubwa.
                </span>
            </label>
            @error('confirmHighImpact')
                <p class="fi-fo-field-wrp-error-message">{{ $message }}</p>
            @enderror

            <x-slot name="footer">
                <x-filament::button type="submit" icon="heroicon-m-check">
                    Hifadhi
                </x-filament::button>
            </x-slot>
        </x-filament::section>
    </form>
</x-filament-panels::page>
