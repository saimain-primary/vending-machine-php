<?php

use App\Enums\StockStatus;
use App\Models\Product;

test('price attribute divides price_in_mills by 1000', function () {
    $product = new Product(['price_in_mills' => 1500]);

    expect($product->price)->toBe(1.5);
});

test('price attribute handles zero', function () {
    $product = new Product(['price_in_mills' => 0]);

    expect($product->price)->toBe(0);
});

test('formatted price returns three decimal places', function () {
    $product = new Product(['price_in_mills' => 1500]);

    expect($product->formatted_price)->toBe('1.500');
});

test('formatted price rounds to three decimal places', function () {
    $product = new Product(['price_in_mills' => 1999]);

    expect($product->formatted_price)->toBe('1.999');
});

test('stock status returns in stock for quantity above five', function () {
    $product = new Product(['quantity_available' => 10]);

    expect($product->stock_status)->toBe(StockStatus::InStock);
});

test('stock status returns low stock for quantity of five or below', function () {
    $product = new Product(['quantity_available' => 3]);

    expect($product->stock_status)->toBe(StockStatus::LowStock);
});

test('stock status returns out of stock for zero quantity', function () {
    $product = new Product(['quantity_available' => 0]);

    expect($product->stock_status)->toBe(StockStatus::OutOfStock);
});
