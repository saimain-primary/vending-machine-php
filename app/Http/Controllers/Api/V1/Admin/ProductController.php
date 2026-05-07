<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Http\Traits\ApiResponse;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ProductService $productService) {}

    public function index(Request $request): JsonResponse
    {
        $allowedSorts = ['name', 'price_in_mills', 'quantity_available', 'created_at', 'updated_at'];

        $sort = in_array($request->sort, $allowedSorts) ? $request->sort : null;
        $direction = $request->direction === 'desc' ? 'desc' : 'asc';
        $perPage = min((int) $request->input('per_page', 15), 100);

        $products = Product::query()
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when(
                $sort,
                fn ($q) => $q->orderBy($sort, $direction),
                fn ($q) => $q->orderBy('name'),
            )
            ->paginate($perPage)
            ->withQueryString();

        return $this->respondWithCollection(ProductResource::collection($products), 'Products retrieved.');
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->create($request->validated());

        return $this->respondWithResource(new ProductResource($product), 'Product created.', 201);
    }

    public function show(Product $product): JsonResponse
    {
        return $this->respondWithResource(new ProductResource($product), 'Product retrieved.');
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $updated = $this->productService->update($product, $request->validated());

        return $this->respondWithResource(new ProductResource($updated), 'Product updated.');
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->productService->delete($product);

        return $this->respondOk('Product deleted.');
    }
}
