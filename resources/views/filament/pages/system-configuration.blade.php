{{--
    Built from Filament's own components and fi-* classes: the panel ships
    Filament's prebuilt CSS, so plain Tailwind utilities (bg-white, border-gray-200)
    either do nothing or stay white in dark mode.
--}}
<x-filament-panels::page>
    <form wire:submit="save" style="display:flex;flex-direction:column;gap:1.5rem">
        <x-filament::callout
            icon="heroicon-o-lock-closed"
            color="info"
            heading="Siri zinahifadhiwa zikiwa zimesimbwa"
            description="Sehemu ya siri iliyoachwa tupu inabaki na thamani yake ya sasa. Thamani ya siri haionyeshwi tena baada ya kuhifadhiwa."
        />

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,34rem),1fr));gap:1.5rem;align-items:start">
            @foreach (collect($fields)->groupBy('group') as $group => $items)
                <x-filament::section :heading="$group">
                    <div style="display:flex;flex-direction:column;gap:1.25rem">
                        @foreach ($items as $item)
                            @php($id = 'setting-'.$item['field'])
                            <div class="fi-fo-field">
                                <div class="fi-fo-field-label-col">
                                    <div class="fi-fo-field-label-ctn">
                                        <label for="{{ $id }}" class="fi-fo-field-label">
                                            <span class="fi-fo-field-label-content">{{ $item['label'] }}</span>
                                        </label>
                                        @if ($item['secret'] && $item['is_set'])
                                            <x-filament::badge color="success" size="sm">Imewekwa</x-filament::badge>
                                        @elseif ($item['secret'])
                                            <x-filament::badge color="gray" size="sm">Haijawekwa</x-filament::badge>
                                        @endif
                                    </div>
                                </div>

                                <div class="fi-fo-field-content-col">
                                    @if ($item['options'])
                                        <x-filament::input.wrapper :valid="! $errors->has('values.'.$item['field'])">
                                            <x-filament::input.select id="{{ $id }}" wire:model="values.{{ $item['field'] }}">
                                                <option value="">—</option>
                                                @foreach ($item['options'] as $option)
                                                    <option value="{{ $option }}">{{ $option }}</option>
                                                @endforeach
                                            </x-filament::input.select>
                                        </x-filament::input.wrapper>
                                    @elseif ($item['type'] === 'json')
                                        <x-filament::input.wrapper :valid="! $errors->has('values.'.$item['field'])">
                                            <textarea
                                                id="{{ $id }}"
                                                rows="3"
                                                wire:model="values.{{ $item['field'] }}"
                                                class="fi-input"
                                                style="font-family:ui-monospace,monospace;font-size:.875rem;resize:vertical"
                                            ></textarea>
                                        </x-filament::input.wrapper>
                                    @else
                                        <x-filament::input.wrapper :valid="! $errors->has('values.'.$item['field'])">
                                            <x-filament::input
                                                id="{{ $id }}"
                                                type="{{ $item['secret'] ? 'password' : 'text' }}"
                                                wire:model="values.{{ $item['field'] }}"
                                                autocomplete="{{ $item['secret'] ? 'new-password' : 'off' }}"
                                                :placeholder="$item['secret'] && $item['is_set'] ? 'Acha tupu usibadilishe' : null"
                                            />
                                        </x-filament::input.wrapper>
                                    @endif

                                    @if ($item['help'])
                                        <p style="font-size:.8125rem;line-height:1.4;opacity:.7">{{ $item['help'] }}</p>
                                    @endif

                                    @error('values.'.$item['field'])
                                        <p class="fi-fo-field-wrp-error-message">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-filament::section>
            @endforeach
        </div>

        <x-filament::section>
            <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:1rem">
                <label style="display:flex;align-items:flex-start;gap:.625rem;font-size:.875rem;max-width:44rem">
                    <x-filament::input.checkbox wire:model="confirmHighImpact" style="margin-top:.15rem" />
                    <span>Ninathibitisha mabadiliko ya anwani ya tovuti na mipangilio mingine yenye athari kubwa.</span>
                </label>

                <x-filament::button type="submit" icon="heroicon-o-check">
                    Hifadhi
                </x-filament::button>
            </div>
            @error('confirmHighImpact')
                <p class="fi-fo-field-wrp-error-message" style="margin-top:.5rem">{{ $message }}</p>
            @enderror
        </x-filament::section>
    </form>
</x-filament-panels::page>
