<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $priceInMills = fake()->numberBetween(500, 10000);

        return [
            'user_id' => \App\Models\User::factory(),
            'product_id' => \App\Models\Product::factory(),
            'quantity' => 1,
            'unit_price_in_mills' => $priceInMills,
            'total_amount_in_mills' => $priceInMills,
            'paid_amount_in_mills' => $priceInMills,
            'change_amount_in_mills' => 0,
        ];
    }
}
