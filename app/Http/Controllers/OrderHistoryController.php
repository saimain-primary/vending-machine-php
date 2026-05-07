<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderHistoryController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        if ($request->user()->can('admin')) {
            return redirect()->route('admin.dashboard');
        }

        $orders = $request->user()
            ->transactions()
            ->with('product')
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($transaction) => [
                'id' => $transaction->id,
                'product_name' => $transaction->product?->name ?? 'Deleted product',
                'quantity' => $transaction->quantity,
                'unit_price_in_mills' => $transaction->unit_price_in_mills,
                'total_amount_in_mills' => $transaction->total_amount_in_mills,
                'status' => $transaction->status,
                'created_at' => $transaction->created_at->format('M j, Y · g:i A'),
            ]);

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
        ]);
    }
}
