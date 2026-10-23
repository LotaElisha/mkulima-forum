<?php

namespace App\Policies;

use App\Models\ConfigSetting;
use App\Models\User;

/**
 * Secrets and operational settings. Role is read directly: User::can() used
 * to return true for every admin, so it could not tell admin from superadmin.
 */
class ConfigSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'superadmin';
    }

    public function view(User $user, ConfigSetting $setting): bool
    {
        return $user->role === 'superadmin';
    }

    public function update(User $user, ConfigSetting $setting): bool
    {
        return $user->role === 'superadmin';
    }
}
