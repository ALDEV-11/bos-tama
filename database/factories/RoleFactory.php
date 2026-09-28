<?php

namespace Database\Factories;

use App\Enums\RoleName;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Role> */
class RoleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(RoleName::values()),
            'label' => fake()->word(),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => RoleName::Admin->value,
            'label' => 'Administrator',
        ]);
    }

    public function petugas(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => RoleName::Petugas->value,
            'label' => 'Petugas',
        ]);
    }
}
