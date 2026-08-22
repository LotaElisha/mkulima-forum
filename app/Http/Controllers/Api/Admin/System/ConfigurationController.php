<?php

namespace App\Http\Controllers\Api\Admin\System;

use App\Http\Controllers\Controller;
use App\Settings\SettingsManager;
use App\Settings\SettingsSchema;
use App\Support\Roles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Admin → System → Configuration.
 *
 * Reads and writes only what SettingsSchema whitelists. Secrets never travel
 * back to the client: the response says whether one is set, never what it is,
 * so an administrator can tell "configured" from "empty" without the value
 * leaving the server even once.
 */
class ConfigurationController extends Controller
{
    public function __construct(private readonly SettingsManager $settings) {}

    /** Current configuration, grouped, with secrets withheld. */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $groups = [];

        foreach (SettingsSchema::groups() as $group) {
            $items = [];
            foreach (SettingsSchema::inGroup($group) as $definition) {
                $items[] = $definition->toArray(
                    $this->settings->get($definition->key),
                    $this->canEdit($user, $definition->superadminOnly),
                );
            }

            $groups[] = [
                'key' => $group,
                'label' => SettingsSchema::groupLabel($group),
                'settings' => $items,
            ];
        }

        return response()->json([
            'groups' => $groups,
            'can_manage_secrets' => $this->canEdit($user, true),
            'environment' => config('app.env'),
        ]);
    }

    /**
     * Save one or more settings.
     *
     * Validated per-setting against the schema's own rules, so a bad SMTP port
     * or a malformed URL is refused before it can be written and break the
     * platform's own outbound mail.
     */
    public function update(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'settings' => ['required', 'array', 'min:1'],
            'confirm_high_impact' => ['nullable', 'boolean'],
        ])['settings'];

        $user = $request->user();
        $applied = [];

        foreach ($payload as $key => $value) {
            $definition = SettingsSchema::find((string) $key);

            if (! $definition) {
                throw ValidationException::withMessages([
                    "settings.{$key}" => ["Unknown setting [{$key}]."],
                ]);
            }

            if ($definition->superadminOnly && ! $this->canEdit($user, true)) {
                return response()->json([
                    'message' => "Changing {$definition->label} requires a superadmin.",
                ], 403);
            }

            // A high-impact setting reconfigures how the platform is reached.
            // The client must say explicitly that the operator meant it.
            if ($definition->highImpact && ! $request->boolean('confirm_high_impact')) {
                return response()->json([
                    'message' => "{$definition->label} is a high-impact setting. Re-send with confirm_high_impact to apply it.",
                    'requires_confirmation' => $definition->key,
                ], 409);
            }

            // An empty non-secret value clears the override and falls back to
            // .env; an empty secret is ignored so "leave blank to keep" works.
            if ($value === null || $value === '') {
                if ($definition->secret) {
                    continue;
                }
                $this->settings->forget($definition->key, $user);
                $applied[] = ['setting' => $definition->key, 'change' => 'cleared, using .env fallback'];

                continue;
            }

            // Validated under a FLAT field name, not the setting key.
            // Setting keys contain dots ("mail.smtp_port"), and Laravel reads a
            // dotted rule key as a nested path — so validating under the key
            // itself made the validator look for $data['mail']['smtp_port'],
            // find nothing, and pass everything. Every rule in the schema was
            // silently inert until this was fixed.
            $validator = Validator::make(
                ['value' => $value],
                ['value' => $definition->rules],
                [],
                ['value' => $definition->label],
            );

            if ($validator->fails()) {
                throw ValidationException::withMessages([
                    "settings.{$definition->key}" => $validator->errors()->all(),
                ]);
            }

            $applied[] = $this->settings->set($definition->key, $value, $user);
        }

        return response()->json([
            'message' => count($applied) === 1 ? 'Setting saved.' : count($applied).' settings saved.',
            'applied' => $applied,
        ]);
    }

    /**
     * Superadmin for sensitive settings, admin for the rest.
     *
     * Deliberately does NOT use $user->can(): User::can() is overridden in this
     * codebase to return true for anyone whose role is admin OR superadmin, so
     * it cannot distinguish the two — using it here would hand every ordinary
     * admin the ability to rotate SMTP passwords and webhook secrets, which is
     * exactly what this gate exists to prevent.
     *
     * hasPermissionTo() throws when a permission has never been seeded, so an
     * installation that has not re-run RolesAndPermissionsSeeder degrades to
     * the role check rather than erroring.
     */
    private function canEdit($user, bool $sensitive): bool
    {
        if (! $user) {
            return false;
        }

        if ($sensitive) {
            return $user->role === Roles::SUPERADMIN
                || $this->hasPermission($user, 'system.secrets.manage');
        }

        return in_array($user->role, [Roles::ADMIN, Roles::SUPERADMIN], true)
            || $this->hasPermission($user, 'system.settings.manage');
    }

    private function hasPermission($user, string $permission): bool
    {
        try {
            return $user->hasPermissionTo($permission);
        } catch (\Throwable) {
            return false;
        }
    }
}
