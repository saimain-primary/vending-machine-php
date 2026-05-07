<?php

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;

// --- Index ---

test('product listing page is publicly accessible', function () {
    $this->get('/')->assertStatus(200);
});

test('product listing returns paginated products', function () {
    Product::factory()->count(15)->create();

    $this->get('/')
        ->assertInertia(fn ($page) => $page
            ->component('Products/Index')
            ->has('products.data', 10)
        );
});

test('product listing filters by search query', function () {
    Product::factory()->create(['name' => 'Cola Zero']);
    Product::factory()->create(['name' => 'Water']);

    $this->get('/?search=Cola')
        ->assertInertia(fn ($page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.name', 'Cola Zero')
        );
});

test('product listing ignores invalid sort columns', function () {
    Product::factory()->count(3)->create();

    $this->get('/?sort=password')->assertStatus(200);
});

test('product listing sorts by price ascending', function () {
    Product::factory()->create(['name' => 'Expensive', 'price_in_mills' => 9000]);
    Product::factory()->create(['name' => 'Cheap', 'price_in_mills' => 1000]);

    $this->get('/?sort=price_in_mills&direction=asc')
        ->assertInertia(fn ($page) => $page
            ->where('products.data.0.name', 'Cheap')
        );
});

test('product listing sorts by price descending', function () {
    Product::factory()->create(['name' => 'Expensive', 'price_in_mills' => 9000]);
    Product::factory()->create(['name' => 'Cheap', 'price_in_mills' => 1000]);

    $this->get('/?sort=price_in_mills&direction=desc')
        ->assertInertia(fn ($page) => $page
            ->where('products.data.0.name', 'Expensive')
        );
});

test('product listing includes isAdmin flag for guest', function () {
    $this->get('/')
        ->assertInertia(fn ($page) => $page->where('isAdmin', false));
});

test('product listing includes isAdmin flag for logged-in admin', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/')
        ->assertInertia(fn ($page) => $page->where('isAdmin', true));
});

// --- Show ---

test('product detail page is publicly accessible', function () {
    $product = Product::factory()->create(['slug' => 'test-product']);

    $this->get("/products/{$product->slug}")->assertStatus(200);
});

test('product detail page renders with correct product data', function () {
    $product = Product::factory()->create(['name' => 'Test Cola', 'slug' => 'test-cola']);

    $this->get("/products/{$product->slug}")
        ->assertInertia(fn ($page) => $page
            ->component('Products/Show')
            ->where('product.name', 'Test Cola')
            ->where('product.slug', 'test-cola')
        );
});

test('product detail page returns 404 for unknown slug', function () {
    $this->get('/products/nonexistent-slug')->assertStatus(404);
});

// --- Buy ---

test('guests cannot purchase products', function () {
    $product = Product::factory()->create(['quantity_available' => 5, 'slug' => 'buyable']);

    $this->post("/products/{$product->slug}/buy")->assertRedirect('/login');
});

test('customer can purchase an in-stock product', function () {
    $user = User::factory()->customer()->create();
    $product = Product::factory()->create(['quantity_available' => 5]);

    $this->actingAs($user)
        ->post("/products/{$product->slug}/buy")
        ->assertRedirect();

    expect($product->fresh()->quantity_available)->toBe(4);
    $this->assertDatabaseHas('transactions', [
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price_in_mills' => $product->price_in_mills,
    ]);
});

test('buying a product decrements quantity by one', function () {
    $user = User::factory()->customer()->create();
    $product = Product::factory()->create(['quantity_available' => 10]);

    $this->actingAs($user)->post("/products/{$product->slug}/buy");

    expect($product->fresh()->quantity_available)->toBe(9);
});

test('buying an out-of-stock product returns 422', function () {
    $user = User::factory()->customer()->create();
    $product = Product::factory()->create(['quantity_available' => 0]);

    $this->actingAs($user)
        ->post("/products/{$product->slug}/buy")
        ->assertStatus(422);

    $this->assertDatabaseCount('transactions', 0);
});

test('buying the last unit leaves quantity at zero', function () {
    $user = User::factory()->customer()->create();
    $product = Product::factory()->create(['quantity_available' => 1]);

    $this->actingAs($user)->post("/products/{$product->slug}/buy");

    expect($product->fresh()->quantity_available)->toBe(0);
});

test('buy stores transaction with correct amounts', function () {
    $user = User::factory()->customer()->create();
    $product = Product::factory()->create(['quantity_available' => 5, 'price_in_mills' => 2000]);

    $this->actingAs($user)->post("/products/{$product->slug}/buy");

    $this->assertDatabaseHas('transactions', [
        'user_id' => $user->id,
        'product_id' => $product->id,
        'unit_price_in_mills' => 2000,
        'total_amount_in_mills' => 2000,
        'paid_amount_in_mills' => 2000,
        'change_amount_in_mills' => 0,
    ]);
});
