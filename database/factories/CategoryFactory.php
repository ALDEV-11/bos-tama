<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Category> */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Wadah', 'Instrument', 'Kosmetik', 'Aksesori', 'Perawatan']).' '.Str::upper(fake()->unique()->lexify('???')),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
