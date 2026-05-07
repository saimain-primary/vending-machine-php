<?php

namespace App\Enums;

enum StockMovementType: string
{
    case InitialStock = 'initial_stock';
    case ManualAdjustment = 'manual_adjustment';
    case Purchase = 'purchase';

    public function label(): string
    {
        return match ($this) {
            self::InitialStock => 'Initial stock',
            self::ManualAdjustment => 'Manual adjustment',
            self::Purchase => 'Purchase',
        };
    }
}
