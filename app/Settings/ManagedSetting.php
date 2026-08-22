<?php

namespace App\Settings;

/**
 * One administrator-managed setting.
 *
 * Describes where the value lives (`key`), what Laravel config it drives
 * (`configPath`), how to validate it, and how sensitive it is. The catalogue in
 * SettingsSchema is the single source of truth: the API, the validation, the
 * masking and the runtime config overlay are all derived from these, so a new
 * setting is one entry rather than four edits in four places.
 */
final class ManagedSetting
{
    public function __construct(
        /** Storage key in config_settings, e.g. "mail.smtp_host". */
        public readonly string $key,
        public readonly string $group,
        public readonly string $label,
        /** string|integer|boolean|json|secret */
        public readonly string $type = 'string',
        /** Laravel config path this drives at runtime, e.g. "mail.mailers.smtp.host". */
        public readonly ?string $configPath = null,
        /** Env var read as the fallback when no database value exists. */
        public readonly ?string $envKey = null,
        /** Laravel validation rules applied on save. */
        public readonly array $rules = ['nullable', 'string', 'max:255'],
        public readonly ?string $help = null,
        /** Secrets are encrypted at rest and never returned to the client. */
        public readonly bool $secret = false,
        /** Only a superadmin may change this. */
        public readonly bool $superadminOnly = false,
        /**
         * Changing this reconfigures how the platform is reached, so the UI
         * demands an explicit confirmation before saving.
         */
        public readonly bool $highImpact = false,
        /** Fixed choices, rendered as a select. */
        public readonly array $options = [],
    ) {}

    /** What the client is allowed to see for this setting's current value. */
    public function present(mixed $value): mixed
    {
        if (! $this->secret) {
            return $value;
        }

        // Never return a secret, not even partially. The client is told only
        // whether one is set, so an administrator can tell "configured" from
        // "empty" without the value ever leaving the server.
        return null;
    }

    public function toArray(mixed $value, bool $canEdit): array
    {
        return [
            'key' => $this->key,
            'group' => $this->group,
            'label' => $this->label,
            'type' => $this->type,
            'help' => $this->help,
            'secret' => $this->secret,
            'is_set' => $this->secret ? ($value !== null && $value !== '') : null,
            'value' => $this->present($value),
            'options' => $this->options,
            'high_impact' => $this->highImpact,
            'superadmin_only' => $this->superadminOnly,
            'can_edit' => $canEdit,
        ];
    }
}
