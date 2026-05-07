<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminOrderResource;
use App\Http\Traits\ApiResponse;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
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

        return $this->respondWithCollection(AdminOrderResource::collection($orders), 'Orders retrieved.');
    }
}
