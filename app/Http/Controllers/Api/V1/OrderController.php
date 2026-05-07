<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()
            ->transactions()
            ->with('product')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return $this->respondWithCollection(OrderResource::collection($orders), 'Orders retrieved.');
    }
}
