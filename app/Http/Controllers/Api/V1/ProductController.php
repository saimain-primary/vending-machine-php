<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductRecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function __construct(private readonly ProductRecommendationService $recommendationService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $allowedSorts = ['name', 'price_in_mills', 'quantity_available'];

        $sort = in_array($request->sort, $allowedSorts) ? $request->sort : null;
        $direction = $request->direction === 'desc' ? 'desc' : 'asc';
        $perPage = min((int) $request->input('per_page', 15), 50);

        $products = Product::query()
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when(
                $sort,
                fn ($q) => $q->orderBy($sort, $direction),
                fn ($q) => $q->orderBy('name'),
            )
            ->paginate($perPage)
            ->withQueryString();

        return ProductResource::collection($products);
    }

    public function show(Product $product): ProductResource
    {
        return new ProductResource($product);
    }

    public function recommendations(Product $product): JsonResponse
    {
        $recommendations = $this->recommendationService->recommendationsFor($product);

        return response()->json(
            $recommendations->map(fn (Product $p) => (new ProductResource($p))->toArray(request()))
        );
    }
}
