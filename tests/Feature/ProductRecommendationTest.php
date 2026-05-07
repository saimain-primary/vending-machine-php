<?php

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;

test('recommendations endpoint returns json', function () {
    $product = Product::factory()->create(['slug' => 'main-product']);

    $this->getJson("/api/v1/products/{$product->slug}/recommendations")
        ->assertStatus(200)
        ->assertJsonIsArray();
});

test('recommendations exclude the current product', function () {
    $product = Product::factory()->create(['quantity_available' => 10]);
    Product::factory()->count(3)->create(['quantity_available' => 10]);

    $response = $this->getJson("/api/v1/products/{$product->slug}/recommendations");

    $ids = collect($response->json())->pluck('id');
    expect($ids)->not->toContain($product->id);
});

test('recommendations exclude out-of-stock products', function () {
    $product = Product::factory()->create(['quantity_available' => 10]);
    $outOfStock = Product::factory()->create(['quantity_available' => 0]);
    Product::factory()->count(2)->create(['quantity_available' => 5]);

    $response = $this->getJson("/api/v1/products/{$product->slug}/recommendations");

    $ids = collect($response->json())->pluck('id');
    expect($ids)->not->toContain($outOfStock->id);
});

test('recommendations return at most four products', function () {
    $product = Product::factory()->create(['quantity_available' => 10]);
    Product::factory()->count(10)->create(['quantity_available' => 5]);

    $response = $this->getJson("/api/v1/products/{$product->slug}/recommendations");

    expect($response->json())->toHaveCount(4);
});

test('recommendations return fewer than four when not enough products exist', function () {
    $product = Product::factory()->create(['quantity_available' => 10]);
    Product::factory()->count(2)->create(['quantity_available' => 5]);

    $response = $this->getJson("/api/v1/products/{$product->slug}/recommendations");

    expect($response->json())->toHaveCount(2);
});

test('recommendations include required fields', function () {
    $product = Product::factory()->create(['quantity_available' => 10]);
    Product::factory()->create(['quantity_available' => 5]);

    $response = $this->getJson("/api/v1/products/{$product->slug}/recommendations");

    $recommendation = $response->json(0);
    expect($recommendation)->toHaveKeys(['id', 'name', 'slug', 'price_in_mills', 'quantity_available', 'stock_status']);
});

test('more popular products appear first in recommendations', function () {
    $product = Product::factory()->create(['quantity_available' => 10, 'price_in_mills' => 2000]);
    $popular = Product::factory()->create(['quantity_available' => 10, 'price_in_mills' => 2000]);
    $unpopular = Product::factory()->create(['quantity_available' => 10, 'price_in_mills' => 2000]);
    $buyer = User::factory()->create();

    // Give popular product 3 transactions, unpopular product 0
    Transaction::create([
        'user_id' => $buyer->id, 'product_id' => $popular->id, 'quantity' => 1,
        'unit_price_in_mills' => 2000, 'total_amount_in_mills' => 2000,
        'paid_amount_in_mills' => 2000, 'change_amount_in_mills' => 0,
    ]);
    Transaction::create([
        'user_id' => $buyer->id, 'product_id' => $popular->id, 'quantity' => 1,
        'unit_price_in_mills' => 2000, 'total_amount_in_mills' => 2000,
        'paid_amount_in_mills' => 2000, 'change_amount_in_mills' => 0,
    ]);

    $response = $this->getJson("/api/v1/products/{$product->slug}/recommendations");

    expect($response->json(0)['id'])->toBe($popular->id);
});

test('recommendations return empty array when no other products exist', function () {
    $product = Product::factory()->create(['quantity_available' => 10]);

    $this->getJson("/api/v1/products/{$product->slug}/recommendations")
        ->assertJson([]);
});
