<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            ['name' => 'Coke', 'price_in_mills' => 3990, 'quantity_available' => 10],
            ['name' => 'Pepsi', 'price_in_mills' => 6885, 'quantity_available' => 10],
            ['name' => 'Water', 'price_in_mills' => 500, 'quantity_available' => 10],
        ])->each(fn (array $product) => Product::query()->updateOrCreate(
            ['name' => $product['name']],
            $product,
        ));
    }
}
