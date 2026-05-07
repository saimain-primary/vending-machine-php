<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $orders = Transaction::query()
            ->with(['user', 'product'])
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$request->search}%")
                        ->orWhere('email', 'like', "%{$request->search}%"))
                        ->orWhereHas('product', fn ($q) => $q->where('name', 'like', "%{$request->search}%"));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Transaction $transaction) => [
                'id' => $transaction->id,
                'customer_name' => $transaction->user?->name ?? 'Deleted user',
                'customer_email' => $transaction->user?->email ?? '',
                'product_name' => $transaction->product?->name ?? 'Deleted product',
                'quantity' => $transaction->quantity,
                'unit_price_in_mills' => $transaction->unit_price_in_mills,
                'total_amount_in_mills' => $transaction->total_amount_in_mills,
                'status' => $transaction->status,
                'created_at' => $transaction->created_at->format('M j, Y · g:i A'),
            ]);

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'filters' => $request->only('search'),
        ]);
    }
}
