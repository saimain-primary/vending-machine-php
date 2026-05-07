<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductRecommendationService;
use Illuminate\Http\JsonResponse;

class ProductRecommendationController extends Controller
{
    public function __construct(private readonly ProductRecommendationService $recommendationService) {}

    public function index(Product $product): JsonResponse
    {
        $recommendations = $this->recommendationService->recommendationsFor($product);

        return response()->json(
            $recommendations->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price_in_mills' => $p->price_in_mills,
                'quantity_available' => $p->quantity_available,
                'stock_status' => $p->stock_status->value,
            ])
        );
    }
}
