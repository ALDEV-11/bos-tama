<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Location;
use App\Models\LocationStock;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LocationStock> */
class LocationStockFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => null,
            'location_id' => Location::factory(),
            'qty' => fake()->numberBetween(0, 50),
        ];
    }

    public function for(Category $category): static
    {
        return $this->state(fn (array $attributes) => []);
    }
}
