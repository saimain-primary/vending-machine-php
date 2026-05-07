<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService) {}

    public function index(Request $request): Response
    {
        $allowedSorts = ['name', 'price_in_mills', 'quantity_available'];
        $allowedDirections = ['asc', 'desc'];

        $sort = in_array($request->sort, $allowedSorts) ? $request->sort : null;
        $direction = in_array($request->direction, $allowedDirections) ? $request->direction : 'asc';

        $products = Product::query()
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when(
                $sort,
                fn ($q) => $q->orderBy($sort, $direction),
                fn ($q) => $q->orderBy('name'),
            )
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price_in_mills' => $product->price_in_mills,
                'quantity_available' => $product->quantity_available,
                'stock_status' => $product->stock_status->value,
            ]);

        return Inertia::render('Products/Index', [
            'products' => $products,
            'filters' => $request->only('search', 'sort', 'direction'),
            'isAdmin' => $request->user()?->isAdmin() ?? false,
        ]);
    }

    public function show(Product $product): Response
    {
        return Inertia::render('Products/Show', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price_in_mills' => $product->price_in_mills,
                'quantity_available' => $product->quantity_available,
                'stock_status' => $product->stock_status->value,
            ],
        ]);
    }

    public function buy(Request $request, Product $product): RedirectResponse
    {
        $this->productService->purchase($request->user(), $product);

        return back()->with('success', "You purchased {$product->name}.");
    }
}
