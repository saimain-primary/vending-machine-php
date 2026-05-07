<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class ProductRecommendationService
{
    public function recommendationsFor(Product $product): Collection
    {
        return Product::withCount('transactions')
            ->where('id', '!=', $product->id)
            ->where('quantity_available', '>', 0)
            ->orderByDesc('transactions_count')
            ->orderByRaw('ABS(price_in_mills - ?)', [$product->price_in_mills])
            ->limit(4)
            ->get();
    }
}
