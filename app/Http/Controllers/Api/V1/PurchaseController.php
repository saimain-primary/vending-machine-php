<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ProductService $productService) {}

    public function store(Request $request, Product $product): JsonResponse
    {
        $this->productService->purchase($request->user(), $product);

        return $this->respondOk("You purchased {$product->name}.", [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'quantity_available' => $product->fresh()->quantity_available,
            ],
        ]);
    }
}
