<?php

use App\Http\Controllers\Admin\ProductController;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;

test('store delegates creation to the product service with the validated request data', function () {
    $validated = ['name' => 'New Cola', 'price' => '1.50', 'quantity_available' => 20];

    $service = Mockery::mock(ProductService::class);
    $service->shouldReceive('create')->once()->with($validated, null)->andReturn(new Product);

    $request = Mockery::mock(StoreProductRequest::class);
    $request->shouldReceive('validated')->andReturn($validated);
    $request->shouldReceive('user')->andReturn(null);

    (new ProductController($service))->store($request);
});

test('store redirects to the admin dashboard after creation', function () {
    $service = Mockery::mock(ProductService::class);
    $service->shouldReceive('create')->andReturn(new Product);

    $request = Mockery::mock(StoreProductRequest::class);
    $request->shouldReceive('validated')->andReturn(['name' => 'Cola', 'price' => '1.00', 'quantity_available' => 5]);
    $request->shouldReceive('user')->andReturn(null);

    $response = (new ProductController($service))->store($request);

    expect($response)->toBeInstanceOf(RedirectResponse::class)
        ->and($response->getTargetUrl())->toBe(route('admin.dashboard'));
});

test('update delegates to the product service with the product and validated data', function () {
    $product = Product::factory()->make();
    $validated = ['name' => 'Updated Cola'];

    $service = Mockery::mock(ProductService::class);
    $service->shouldReceive('update')
        ->once()
        ->with(
            Mockery::on(fn ($p) => $p instanceof Product),
            $validated,
            null,
        )
        ->andReturn($product);

    $request = Mockery::mock(UpdateProductRequest::class);
    $request->shouldReceive('validated')->andReturn($validated);
    $request->shouldReceive('user')->andReturn(null);

    (new ProductController($service))->update($request, $product);
});

test('update redirects to the admin dashboard after saving', function () {
    $product = Product::factory()->make();

    $service = Mockery::mock(ProductService::class);
    $service->shouldReceive('update')->andReturn($product);

    $request = Mockery::mock(UpdateProductRequest::class);
    $request->shouldReceive('validated')->andReturn(['price' => '2.00']);
    $request->shouldReceive('user')->andReturn(null);

    $response = (new ProductController($service))->update($request, $product);

    expect($response)->toBeInstanceOf(RedirectResponse::class)
        ->and($response->getTargetUrl())->toBe(route('admin.dashboard'));
});

test('update passes all validated fields including partial updates', function () {
    $product = Product::factory()->make();
    $validated = ['quantity_available' => 99];

    $service = Mockery::mock(ProductService::class);
    $service->shouldReceive('update')->once()->with(Mockery::any(), $validated, null)->andReturn($product);

    $request = Mockery::mock(UpdateProductRequest::class);
    $request->shouldReceive('validated')->andReturn($validated);
    $request->shouldReceive('user')->andReturn(null);

    (new ProductController($service))->update($request, $product);
});

test('destroy delegates deletion to the product service', function () {
    $product = Product::factory()->make();

    $service = Mockery::mock(ProductService::class);
    $service->shouldReceive('delete')->once()->with(Mockery::on(fn ($p) => $p instanceof Product));

    (new ProductController($service))->destroy($product);
});

test('destroy redirects to the admin dashboard after deletion', function () {
    $product = Product::factory()->make();

    $service = Mockery::mock(ProductService::class);
    $service->shouldReceive('delete');

    $response = (new ProductController($service))->destroy($product);

    expect($response)->toBeInstanceOf(RedirectResponse::class)
        ->and($response->getTargetUrl())->toBe(route('admin.dashboard'));
});
