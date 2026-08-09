<?php

namespace Database\Factories;

use App\Models\User;
use App\Support\Roles;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * Matches the custom users schema (phone-first, tenant-scoped).
     */
    public function definition(): array
    {
        $faker = \Faker\Factory::create();

        return [
            'tenant_id' => 1,
            'name' => $faker->name(),
            'phone' => '2557'.$faker->unique()->numerify('########'),
            'email' => $faker->unique()->safeEmail(),
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
