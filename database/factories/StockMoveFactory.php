<?php

namespace Database\Factories;

use App\Enums\StockMoveType;
use App\Models\Product;
use App\Models\StockMove;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<StockMove> */
class StockMoveFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'type' => fake()->randomElement(StockMoveType::cases()),
            'qty' => fake()->numberBetween(1, 20),
            'stock_before' => fake()->numberBetween(10, 100),
            'stock_after' => fake()->numberBetween(0, 100),
            'from_location_id' => null,
            'to_location_id' => null,
            'reference' => 'MKM-'.Str::upper(fake()->unique()->bothify('######')),
            'note' => fake()->optional()->sentence(),
            'user_id' => User::factory(),
            'related_move_id' => null,
        ];
    }

    public function for(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
