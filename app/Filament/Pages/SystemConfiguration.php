<?php

namespace App\Filament\Pages;

use App\Models\ConfigSetting;
use App\Models\User;
use App\Settings\ManagedSetting;
use App\Settings\SettingsManager;
use App\Settings\SettingsSchema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
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
            }
        }

        Notification::make()->title('Mipangilio imehifadhiwa')->success()->send();
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
