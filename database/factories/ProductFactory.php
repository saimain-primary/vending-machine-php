<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(fake()->numberBetween(1, 3), true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'price_in_mills' => fake()->numberBetween(500, 10000),
            'quantity_available' => fake()->numberBetween(0, 50),
        ];
    }
}
