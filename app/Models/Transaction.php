<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'product_id',
    'quantity',
    'unit_price_in_mills',
    'total_amount_in_mills',
    'paid_amount_in_mills',
    'change_amount_in_mills',
    'status',
])]
class Transaction extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'product_id' => 'integer',
            'quantity' => 'integer',
            'unit_price_in_mills' => 'integer',
            'total_amount_in_mills' => 'integer',
            'paid_amount_in_mills' => 'integer',
            'change_amount_in_mills' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function formattedTotal(): Attribute
    {
        return Attribute::make(
            get: fn () => number_format($this->total_amount_in_mills / 1000, 3),
        );
    }

    protected function formattedChange(): Attribute
    {
        return Attribute::make(
            get: fn () => number_format($this->change_amount_in_mills / 1000, 3),
        );
    }
}
