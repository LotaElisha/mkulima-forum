<?php

namespace App\Actions\Auth;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

/**
 * Self-serve registration creates a farmer and nothing else.
 *
 * Staff accounts are provisioned by an administrator. The legacy /api/auth
 * register endpoint still accepts Roles::SELF_REGISTERABLE so existing
 * clients keep working; /api/v1 does not.
 */
class RegisterFarmer
{
    public function __invoke(array $input): User
    {
        $user = User::provision([
            'tenant_id' => $this->tenantId($input['country_code'] ?? 'tz'),
            'name' => $input['name'],
            'email' => mb_strtolower($input['email']),
            'phone' => $input['phone'] ?? null,
            'password' => Hash::make($input['password']),
            'role' => 'farmer',
            'status' => 'active',
            'kyc_status' => 'not_submitted',
            'preferred_language' => $input['preferred_language'] ?? 'sw',
        ]);

        $user->assignRole(Role::firstOrCreate([
            'name' => 'farmer',
            'guard_name' => 'web',
        ]));

        return $user;
    }

    private function tenantId(string $countryCode): int
    {
        $tenant = Tenant::query()->where('country_code', strtolower($countryCode))->first()
            ?? Tenant::query()->where('country_code', 'tz')->first()
            ?? Tenant::query()->orderBy('id')->first();

        if (! $tenant) {
            throw ValidationException::withMessages([
                'country_code' => ['Hatukuweza kufungua akaunti kwa nchi hiyo bado.'],
            ]);
        }

        return $tenant->id;
    }
}
