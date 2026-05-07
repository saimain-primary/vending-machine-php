<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;

class ProductService
{
    public function purchase(User $user, Product $product): void
    {
        abort_if($product->quantity_available === 0, 422, 'Product is out of stock.');

        Transaction::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price_in_mills' => $product->price_in_mills,
            'total_amount_in_mills' => $product->price_in_mills,
            'paid_amount_in_mills' => $product->price_in_mills,
            'change_amount_in_mills' => 0,
        ]);

        $product->decrement('quantity_available');
    }

    public function create(array $validated): Product
    {
        return Product::create([
            'name' => $validated['name'],
            'price_in_mills' => (int) round($validated['price'] * 1000),
            'quantity_available' => $validated['quantity_available'],
        ]);
    }

    public function update(Product $product, array $validated): Product
    {
        if (isset($validated['price'])) {
            $validated['price_in_mills'] = (int) round($validated['price'] * 1000);
            unset($validated['price']);
        }

        $product->update($validated);

        return $product;
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }
}
