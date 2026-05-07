<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function purchase(User $user, Product $product): void
    {
        DB::transaction(function () use ($user, $product): void {
            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($product->id);

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

            $this->recordStockMovement(
                product: $product->refresh(),
                user: $user,
                type: StockMovementType::Purchase,
                quantityChange: -1,
                note: 'Customer purchase',
            );
        });
    }

    public function create(array $validated, ?User $user = null): Product
    {
        return DB::transaction(function () use ($validated, $user): Product {
            $product = Product::create([
                'name' => $validated['name'],
                'price_in_mills' => (int) round($validated['price'] * 1000),
                'quantity_available' => $validated['quantity_available'],
            ]);

            $this->recordStockMovement(
                product: $product,
                user: $user,
                type: StockMovementType::InitialStock,
                quantityChange: $product->quantity_available,
                note: 'Product created',
            );

            return $product;
        });
    }

    public function update(Product $product, array $validated, ?User $user = null): Product
    {
        $previousQuantity = $product->quantity_available;

        if (isset($validated['price'])) {
            $validated['price_in_mills'] = (int) round($validated['price'] * 1000);
            unset($validated['price']);
        }

        DB::transaction(function () use ($product, $validated, $user, $previousQuantity): void {
            $product->update($validated);

            if (! array_key_exists('quantity_available', $validated)) {
                return;
            }

            $quantityChange = $product->quantity_available - $previousQuantity;

            if ($quantityChange === 0) {
                return;
            }

            $this->recordStockMovement(
                product: $product,
                user: $user,
                type: StockMovementType::ManualAdjustment,
                quantityChange: $quantityChange,
                note: 'Admin quantity update',
            );
        });

        return $product;
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    private function recordStockMovement(
        Product $product,
        ?User $user,
        StockMovementType $type,
        int $quantityChange,
        string $note,
    ): void {
        StockMovement::create([
            'product_id' => $product->id,
            'user_id' => $user?->id,
            'type' => $type,
            'quantity_change' => $quantityChange,
            'quantity_after' => $product->quantity_available,
            'note' => $note,
        ]);
    }
}
