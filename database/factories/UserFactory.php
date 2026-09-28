<?php

namespace Database\Factories;

use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('08##########'),
            'role_id' => Role::factory()->state(fn (array $attributes) => [
                'name' => RoleName::Petugas->value,
                'label' => 'Petugas',
            ]),
            'is_active' => true,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => Role::factory()->state(fn (array $state) => [
                'name' => RoleName::Admin->value,
                'label' => 'Administrator',
            ])->create()->id,
        ]);
    }

    public function petugas(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => Role::factory()->state(fn (array $state) => [
                'name' => RoleName::Petugas->value,
                'label' => 'Petugas',
            ])->create()->id,
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
