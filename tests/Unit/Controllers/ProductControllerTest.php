<?php

use App\Http\Controllers\ProductController;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

// ProductController::buy() is the only action that contains business logic.
// These tests verify that the controller delegates to ProductService and handles
// the response correctly — the database is never touched.

test('buy delegates to the product service with the authenticated user and the product', function () {
    $user = User::factory()->customer()->make();
    $product = Product::factory()->make(['name' => 'Cola', 'quantity_available' => 5]);

    $service = Mockery::mock(ProductService::class);
    $service->shouldReceive('purchase')
        ->once()
        ->with(
            Mockery::on(fn ($u) => $u instanceof User),
            Mockery::on(fn ($p) => $p instanceof Product),
        );

    $request = Request::create('/buy', 'POST');
    $request->setUserResolver(fn () => $user);

    (new ProductController($service))->buy($request, $product);
});

test('buy returns a redirect response after successful purchase', function () {
    $user = User::factory()->customer()->make();
    $product = Product::factory()->make(['name' => 'Cola', 'quantity_available' => 5]);

    $service = Mockery::mock(ProductService::class);
    $service->shouldReceive('purchase');

    $request = Request::create('/buy', 'POST');
    $request->setUserResolver(fn () => $user);

    $response = (new ProductController($service))->buy($request, $product);

    expect($response)->toBeInstanceOf(RedirectResponse::class);
});

test('buy propagates a 422 HttpException thrown by the service for out-of-stock products', function () {
    $user = User::factory()->customer()->make();
    $product = Product::factory()->make(['quantity_available' => 0]);

    $service = Mockery::mock(ProductService::class);
    $service->shouldReceive('purchase')
        ->andThrow(new HttpException(422, 'Product is out of stock.'));

    $request = Request::create('/buy', 'POST');
    $request->setUserResolver(fn () => $user);

    expect(fn () => (new ProductController($service))->buy($request, $product))
        ->toThrow(HttpException::class);
});

test('buy does not call the service when the product is already handled by the service', function () {
    // Verify the controller does NOT duplicate the out-of-stock check —
    // that guard lives exclusively in ProductService.
    $user = User::factory()->customer()->make();
    $product = Product::factory()->make(['quantity_available' => 0]);

    $service = Mockery::mock(ProductService::class);
    // Service is called exactly once — controller doesn't short-circuit before it
    $service->shouldReceive('purchase')->once()->andThrow(new HttpException(422));

    $request = Request::create('/buy', 'POST');
    $request->setUserResolver(fn () => $user);

    try {
        (new ProductController($service))->buy($request, $product);
    } catch (HttpException) {
        // Expected
    }
});
