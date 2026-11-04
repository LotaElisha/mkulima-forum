<?php

namespace App\Filament\Pages;

use App\Models\ConfigSetting;
use App\Models\User;
use App\Settings\ManagedSetting;
use App\Settings\SettingsManager;
use App\Settings\SettingsSchema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SystemConfiguration extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Configuration';

    protected static ?string $title = 'Configuration';

    protected static ?string $slug = 'configuration';

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 90;

    protected string $view = 'filament.pages.system-configuration';

    /** @var array<int, array<string, mixed>> */
    public array $fields = [];

    /** @var array<string, string> */
    public array $values = [];

    public bool $confirmHighImpact = false;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->can('viewAny', ConfigSetting::class);
    }

    public function mount(SettingsManager $settings): void
    {
        foreach (SettingsSchema::all() as $definition) {
            $field = $this->fieldName($definition->key);
            $current = $settings->get($definition->key);
            $this->fields[] = [
                'key' => $definition->key,
                'field' => $field,
                'label' => $definition->label,
                'help' => $definition->help,
                'group' => SettingsSchema::groupLabel($definition->group),
                'secret' => $definition->secret,
                'is_set' => $definition->secret ? ($current !== null && $current !== '') : null,
                'options' => $definition->options,
                'high_impact' => $definition->highImpact,
                'type' => $definition->type,
            ];
            $this->values[$field] = $definition->secret ? '' : $this->display($definition, $current);
        }
    }

    public function getSubheading(): ?string
    {
        return 'Siri zinahifadhiwa zikiwa zimesimbwa. Sehemu ya siri ikiachwa tupu, '
            .'thamani ya sasa inabaki. Thamani ya siri haionyeshwi tena baada ya kuhifadhiwa.';
    }

    /**
     * Built from Filament components rather than hand-written HTML: Filament
     * ships precompiled CSS, so ad-hoc Tailwind classes in a page view are
     * simply absent. The old view rendered bare labels with invisible inputs.
     *
     * State lives in $values (statePath), so save() and its validation are
     * unchanged; errors added as "values.<field>" show under each field.
     */
    public function form(Schema $schema): Schema
    {
        $sections = [];
        foreach (collect($this->fields)->groupBy('group') as $group => $items) {
            $sections[] = Section::make($group)
                ->columns(['md' => 2])
                ->schema($items->map(fn (array $item) => $this->component($item))->all());
        }

        return $schema->components($sections)->statePath('values');
    }

    /** @param  array<string, mixed>  $item */
    private function component(array $item): TextInput|Select|Textarea
    {
        if ($item['options']) {
            $component = Select::make($item['field'])
                ->options(array_combine($item['options'], $item['options']))
                ->placeholder('—')
                ->native(true);
        } elseif ($item['type'] === 'json') {
            $component = Textarea::make($item['field'])->rows(3)->columnSpanFull();
        } elseif ($item['secret']) {
            $component = TextInput::make($item['field'])
                ->password()
                ->revealable()
                ->autocomplete('new-password')
                ->placeholder($item['is_set'] ? 'Acha tupu usibadilishe' : 'Weka thamani')
                ->hint($item['is_set'] ? 'Imewekwa' : 'Haijawekwa')
                ->hintColor($item['is_set'] ? 'success' : 'danger')
                ->hintIcon($item['is_set'] ? 'heroicon-m-lock-closed' : 'heroicon-m-exclamation-triangle');
        } else {
            $component = TextInput::make($item['field'])
                ->autocomplete('off')
                ->numeric($item['type'] === 'integer');
        }

        $component->label($item['label'])->helperText($item['help']);

        if ($item['high_impact'] && ! $item['secret']) {
            $component
                ->hint('Athari kubwa')
                ->hintColor('warning')
                ->hintIcon('heroicon-m-exclamation-triangle');
        }

        return $component;
    }

    public function save(SettingsManager $settings): void
    {
        $actor = auth()->user();
        abort_unless($actor instanceof User && $actor->can('update', new ConfigSetting), 403);

        $pending = [];
        $highImpactChanged = false;

        foreach (SettingsSchema::all() as $definition) {
            $field = $this->fieldName($definition->key);
            $raw = $this->values[$field] ?? '';

            if ($definition->secret && trim((string) $raw) === '') {
                continue;
            }

            $current = $settings->get($definition->key);
            if (! $definition->secret && $this->display($definition, $current) === (string) $raw) {
                continue;
            }

            $value = $this->cast($definition, (string) $raw);
            if ($value instanceof \Throwable) {
                $this->addError('values.'.$field, $value->getMessage());

                return;
            }

            $validator = Validator::make(
                ['value' => $value],
                ['value' => $definition->rules]
            );
            if ($validator->fails()) {
                $this->addError('values.'.$field, $validator->errors()->first('value'));

                return;
            }

            if ($definition->highImpact) {
                $highImpactChanged = true;
            }

            $pending[] = [$definition, $value];
        }

        if ($highImpactChanged && ! $this->confirmHighImpact) {
            $this->addError('confirmHighImpact', 'Thibitisha mabadiliko yanayoathiri jinsi watu wanavyofika kwenye tovuti.');

            return;
        }

        foreach ($pending as [$definition, $value]) {
            $change = $settings->set($definition->key, $value, $actor);
            if ($definition->secret) {
                Log::info($change['change']);
                $this->values[$this->fieldName($definition->key)] = '';
                $this->markSecretSet($definition->key);
            }
        }

        // Rebuild the form so a just-saved secret shows "Imewekwa" at once.
        $this->cacheSchema('form', null);

        Notification::make()->title('Mipangilio imehifadhiwa')->success()->send();
    }

    private function markSecretSet(string $key): void
    {
        foreach ($this->fields as $i => $field) {
            if ($field['key'] === $key) {
                $this->fields[$i]['is_set'] = true;
            }
        }
    }

    private function fieldName(string $key): string
    {
        return str_replace('.', '__', $key);
    }

    private function display(ManagedSetting $definition, mixed $current): string
    {
        if ($current === null || $current === '') {
            return '';
        }

        if ($definition->type === 'json') {
            return json_encode($current, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '';
        }

        return (string) $current;
    }

    private function cast(ManagedSetting $definition, string $raw): mixed
    {
        if ($definition->type === 'json') {
            $decoded = json_decode($raw, true);
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                return new \InvalidArgumentException('Weka orodha ya JSON, kwa mfano ["mkulimaforum.com"].');
            }

            return $decoded;
        }

        if ($definition->type === 'integer') {
            if ($raw === '') {
                return null;
            }

            return (int) $raw;
        }

        return $raw === '' ? null : $raw;
    }
}
