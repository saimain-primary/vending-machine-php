<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $allowedSorts = ['name', 'price_in_mills', 'quantity_available', 'updated_at'];
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
                'price_in_mills' => $product->price_in_mills,
                'quantity_available' => $product->quantity_available,
                'stock_status' => $product->stock_status->value,
                'updated_at' => $product->updated_at->toISOString(),
            ]);

        $inventorySummary = [
            'total_units' => Product::query()->sum('quantity_available'),
            'low_stock_count' => Product::query()
                ->where('quantity_available', '>', 0)
                ->where('quantity_available', '<=', 5)
                ->count(),
            'out_of_stock_count' => Product::query()
                ->where('quantity_available', 0)
                ->count(),
        ];

        return Inertia::render('Admin/Dashboard', [
            'products' => $products,
            'inventorySummary' => $inventorySummary,
            'filters' => $request->only('search', 'sort', 'direction'),
        ]);
    }
}
