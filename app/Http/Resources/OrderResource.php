<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_name' => $this->product?->name ?? 'Deleted product',
            'product_slug' => $this->product?->slug,
            'quantity' => $this->quantity,
            'unit_price_in_mills' => $this->unit_price_in_mills,
            'total_amount_in_mills' => $this->total_amount_in_mills,
            'status' => $this->status,
            'purchased_at' => $this->created_at->toISOString(),
        ];
    }
}
