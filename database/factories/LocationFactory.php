<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Location> */
class LocationFactory extends Factory
{
    public function definition(): array
    {
        $code = Str::upper(fake()->unique()->bothify('LOC-##'));

        return [
            'code' => $code,
            'name' => fake()->randomElement(['Toko Pusat', 'Gudang Utama', 'Etalase Depan', 'Gudang Cadangan']).' '.Str::upper(fake()->unique()->lexify('??')),
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
