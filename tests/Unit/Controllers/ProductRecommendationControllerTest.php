<?php

use App\Http\Controllers\ProductRecommendationController;
use App\Models\Product;
use App\Services\ProductRecommendationService;
use Illuminate\Http\JsonResponse;

// ProductRecommendationController maps a Collection returned by the service into
// a JSON response. These tests verify the delegation and the shape of that mapping.

test('index delegates to the recommendation service with the given product', function () {
    $product = Product::factory()->make();

    $service = Mockery::mock(ProductRecommendationService::class);
    $service->shouldReceive('recommendationsFor')
        ->once()
        ->with(Mockery::on(fn ($p) => $p instanceof Product))
        ->andReturn(collect([]));

    (new ProductRecommendationController($service))->index($product);
});

test('index returns a JsonResponse', function () {
    $service = Mockery::mock(ProductRecommendationService::class);
    $service->shouldReceive('recommendationsFor')->andReturn(collect([]));

    $response = (new ProductRecommendationController($service))->index(Product::factory()->make());

    expect($response)->toBeInstanceOf(JsonResponse::class);
});

test('index returns an empty array when the service returns no recommendations', function () {
    $service = Mockery::mock(ProductRecommendationService::class);
    $service->shouldReceive('recommendationsFor')->andReturn(collect([]));

    $data = (new ProductRecommendationController($service))
        ->index(Product::factory()->make())
        ->getData(true);

    expect($data)->toBeEmpty();
});

test('index maps each recommendation into the expected fields', function () {
    $recommendation = tap(new Product(), fn ($p) => $p->forceFill([
        'id' => 42,
        'name' => 'Sparkling Water',
        'slug' => 'sparkling-water',
        'price_in_mills' => 1200,
        'quantity_available' => 10,
    ]));

    $service = Mockery::mock(ProductRecommendationService::class);
    $service->shouldReceive('recommendationsFor')->andReturn(collect([$recommendation]));

    $data = (new ProductRecommendationController($service))
        ->index(Product::factory()->make())
        ->getData(true);

    expect($data)->toHaveCount(1)
        ->and($data[0])->toHaveKeys(['id', 'name', 'slug', 'price_in_mills', 'quantity_available', 'stock_status'])
        ->and($data[0]['id'])->toBe(42)
        ->and($data[0]['name'])->toBe('Sparkling Water')
        ->and($data[0]['slug'])->toBe('sparkling-water')
        ->and($data[0]['price_in_mills'])->toBe(1200)
        ->and($data[0]['stock_status'])->toBe('In Stock');
});

test('index maps stock status correctly for low-stock products', function () {
    $recommendation = tap(new Product(), fn ($p) => $p->forceFill([
        'id' => 1, 'name' => 'Rare Item', 'slug' => 'rare-item',
        'price_in_mills' => 500, 'quantity_available' => 3,
    ]));

    $service = Mockery::mock(ProductRecommendationService::class);
    $service->shouldReceive('recommendationsFor')->andReturn(collect([$recommendation]));

    $data = (new ProductRecommendationController($service))
        ->index(Product::factory()->make())
        ->getData(true);

    expect($data[0]['stock_status'])->toBe('Low Stock');
});

test('index returns all recommendations provided by the service', function () {
    $recommendations = collect(range(1, 4))->map(fn ($i) => tap(new Product(), fn ($p) => $p->forceFill([
        'id' => $i, 'name' => "Product $i", 'slug' => "product-$i",
        'price_in_mills' => 1000, 'quantity_available' => 10,
    ])));

    $service = Mockery::mock(ProductRecommendationService::class);
    $service->shouldReceive('recommendationsFor')->andReturn($recommendations);

    $data = (new ProductRecommendationController($service))
        ->index(Product::factory()->make())
        ->getData(true);

    expect($data)->toHaveCount(4);
});
