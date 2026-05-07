<?php

namespace App\Models;

use App\Enums\StockStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'slug',
    'price_in_mills',
    'quantity_available',
])]
class Product extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }
    
    protected function casts(): array
    {
        return [
            'price_in_mills' => 'integer',
            'quantity_available' => 'integer',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->price_in_mills / 1000,
        );
    }

    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => number_format($this->price_in_mills / 1000, 3),
        );
    }

    protected function stockStatus(): Attribute
    {
        return Attribute::make(
            get: fn () => StockStatus::fromQuantity($this->quantity_available),
        );
    }
}
