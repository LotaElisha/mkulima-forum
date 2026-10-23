<x-filament-panels::page>
    <form wire:submit="save" class="space-y-8">
        <p class="text-sm text-gray-600">
            Siri zinahifadhiwa zikiwa zimesimbwa. Sehemu tupu inaacha thamani ya sasa.
            Thamani ya siri haionyeshwi tena baada ya kuhifadhiwa.
        </p>

        @foreach (collect($fields)->groupBy('group') as $group => $items)
            <section class="space-y-4 rounded-xl border border-gray-200 bg-white p-4">
                <h2 class="text-base font-semibold">{{ $group }}</h2>
                @foreach ($items as $item)
                    <div>
                        <label class="mb-1 block text-sm font-medium" for="setting-{{ $item['field'] }}">
                            {{ $item['label'] }}
                            @if ($item['secret'] && $item['is_set'])
                                <span class="font-normal text-gray-500">— imewashwa, acha tupu usibadilishe</span>
                            @endif
                        </label>
                        @if ($item['options'])
                            <select id="setting-{{ $item['field'] }}" wire:model="values.{{ $item['field'] }}" class="w-full rounded-lg border-gray-300">
                                <option value="">—</option>
                                @foreach ($item['options'] as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        @elseif ($item['type'] === 'json')
                            <textarea id="setting-{{ $item['field'] }}" rows="3" wire:model="values.{{ $item['field'] }}" class="w-full rounded-lg border-gray-300 font-mono text-sm"></textarea>
                        @else
                            <input
                                id="setting-{{ $item['field'] }}"
                                type="{{ $item['secret'] ? 'password' : 'text' }}"
                                wire:model="values.{{ $item['field'] }}"
                                autocomplete="{{ $item['secret'] ? 'new-password' : 'off' }}"
                                class="w-full rounded-lg border-gray-300"
                            >
                        @endif
                        @if ($item['help'])
                            <p class="mt-1 text-xs text-gray-500">{{ $item['help'] }}</p>
                        @endif
                        @error('values.'.$item['field'])
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </section>
        @endforeach

        <label class="flex items-start gap-2 text-sm">
            <input type="checkbox" wire:model="confirmHighImpact" class="mt-1">
            <span>Ninathibitisha mabadiliko ya anwani ya tovuti na mipangilio mingine yenye athari kubwa.</span>
        </label>
        @error('confirmHighImpact')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror

        <x-filament::button type="submit">
            Hifadhi
        </x-filament::button>
    </form>
</x-filament-panels::page>
