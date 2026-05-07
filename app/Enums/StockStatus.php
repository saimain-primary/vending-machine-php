<?php

namespace App\Enums;

enum StockStatus: string
{
    case InStock = 'In Stock';
    case LowStock = 'Low Stock';
    case OutOfStock = 'Out of Stock';

    public static function fromQuantity(int $quantity): self
    {
        return match (true) {
            $quantity === 0 => self::OutOfStock,
            $quantity <= 5  => self::LowStock,
            default         => self::InStock,
        };
    }
}
