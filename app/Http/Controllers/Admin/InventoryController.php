<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    public function index(Request $request): Response
    {
        $productId = $request->integer('product_id') ?: null;

        $stockMovements = StockMovement::query()
            ->with(['product:id,name', 'user:id,name'])
            ->when($productId, fn ($query) => $query->where('product_id', $productId))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (StockMovement $movement) => [
                'id' => $movement->id,
                'product_name' => $movement->product?->name ?? 'Deleted product',
                'user_name' => $movement->user?->name,
                'type' => $movement->type->value,
                'type_label' => $movement->type->label(),
                'quantity_change' => $movement->quantity_change,
                'quantity_after' => $movement->quantity_after,
                'note' => $movement->note,
                'created_at' => $movement->created_at->toISOString(),
            ]);

        $products = Product::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Inventory/Index', [
            'stockMovements' => $stockMovements,
            'products' => $products,
            'filters' => [
                'product_id' => $productId,
            ],
        ]);
    }
}
