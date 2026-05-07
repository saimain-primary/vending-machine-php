<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminOrderResource;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
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
            ->withQueryString();

        return AdminOrderResource::collection($orders);
    }
}
