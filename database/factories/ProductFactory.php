<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sku' => 'VSC-'.Str::upper(fake()->unique()->bothify('####')),
            'name' => fake()->randomElement(['Toples Bowl', 'Botol Dispensing', 'Kotak Kosmetik', 'Tas Makeup', 'Kuas Makeup', 'Jars Kaca']).' '.Str::upper(fake()->unique()->lexify('???')),
            'description' => fake()->optional()->sentence(),
            'category_id' => Category::factory(),
            'supplier_id' => Supplier::factory(),
            'unit' => fake()->randomElement(['pcs', 'box', 'lusin', 'set']),
            'price' => fake()->randomFloat(2, 5_000, 250_000),
            'min_stock' => fake()->numberBetween(3, 15),
            'stock' => fake()->numberBetween(0, 120),
            'is_active' => true,
        ];
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => max(1, (int) ($attributes['min_stock'] ?? 5)),
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }
}
