<?php

use App\Enums\StockStatus;

test('quantity of zero returns out of stock', function () {
    expect(StockStatus::fromQuantity(0))->toBe(StockStatus::OutOfStock);
});

test('quantity of one returns low stock', function () {
    expect(StockStatus::fromQuantity(1))->toBe(StockStatus::LowStock);
});

test('quantity of five returns low stock at upper boundary', function () {
    expect(StockStatus::fromQuantity(5))->toBe(StockStatus::LowStock);
});

test('quantity of six returns in stock just above low stock boundary', function () {
    expect(StockStatus::fromQuantity(6))->toBe(StockStatus::InStock);
});

test('large quantity returns in stock', function () {
    expect(StockStatus::fromQuantity(100))->toBe(StockStatus::InStock);
});

test('enum string values match expected labels', function () {
    expect(StockStatus::InStock->value)->toBe('In Stock')
        ->and(StockStatus::LowStock->value)->toBe('Low Stock')
        ->and(StockStatus::OutOfStock->value)->toBe('Out of Stock');
});
