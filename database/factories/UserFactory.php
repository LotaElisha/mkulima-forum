<?php

namespace Database\Factories;

use App\Support\Roles;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * Matches the custom users schema (phone-first, tenant-scoped).
     */
    public function definition(): array
    {
        return [
            'tenant_id' => 1,
            'name' => fake()->name(),
            'phone' => '2557' . fake()->unique()->numerify('########'),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => Roles::FARMER,
            'kyc_status' => 'pending',
            'status' => 'active',
            'phone_verified_at' => now(),
            'preferred_language' => 'sw',
        ];
    }

    public function role(string $role): static
    {
        return $this->state(fn () => ['role' => $role]);
    }
}
