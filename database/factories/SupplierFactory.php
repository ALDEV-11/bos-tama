<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Supplier> */
class SupplierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company().' '.fake()->randomElement(['Industri', 'Distribusi', 'Niaga', 'Sentosa']),
            'contact_name' => fake()->name(),
            'phone' => fake()->numerify('08##########'),
            'email' => fake()->unique()->companyEmail(),
            'address' => fake()->address(),
            'is_active' => true,
        ];
    }
}
