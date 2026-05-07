<?php

namespace Database\Factories;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockMovementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'type' => fake()->randomElement(StockMovementType::cases()),
            'quantity_change' => fake()->numberBetween(-5, 20),
            'quantity_after' => fake()->numberBetween(0, 50),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
