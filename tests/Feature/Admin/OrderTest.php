<?php

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;

test('guests cannot access admin orders', function () {
    $this->get('/admin/orders')->assertRedirect('/login');
});

test('customers cannot access admin orders', function () {
    $user = User::factory()->customer()->create();

    $this->actingAs($user)->get('/admin/orders')->assertForbidden();
});

test('admin can view all orders', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/orders')->assertStatus(200);
});

test('admin orders page renders paginated transactions', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer()->create();
    $product = Product::factory()->create();

    Transaction::factory()->count(5)->create([
        'user_id' => $customer->id,
        'product_id' => $product->id,
    ]);

    $this->actingAs($admin)->get('/admin/orders')
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Orders/Index')
            ->has('orders.data', 5)
        );
});

test('admin orders shows customer name and email', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer()->create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);
    $product = Product::factory()->create();

    Transaction::factory()->create(['user_id' => $customer->id, 'product_id' => $product->id]);

    $this->actingAs($admin)->get('/admin/orders')
        ->assertInertia(fn ($page) => $page
            ->where('orders.data.0.customer_name', 'Jane Doe')
            ->where('orders.data.0.customer_email', 'jane@example.com')
        );
});

test('admin orders shows product name', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer()->create();
    $product = Product::factory()->create(['name' => 'Orange Juice']);

    Transaction::factory()->create(['user_id' => $customer->id, 'product_id' => $product->id]);

    $this->actingAs($admin)->get('/admin/orders')
        ->assertInertia(fn ($page) => $page
            ->where('orders.data.0.product_name', 'Orange Juice')
        );
});


test('admin orders can be filtered by customer name', function () {
    $admin = User::factory()->admin()->create();
    $alice = User::factory()->customer()->create(['name' => 'Alice Smith']);
    $bob = User::factory()->customer()->create(['name' => 'Bob Jones']);
    $product = Product::factory()->create();

    Transaction::factory()->create(['user_id' => $alice->id, 'product_id' => $product->id]);
    Transaction::factory()->create(['user_id' => $bob->id, 'product_id' => $product->id]);

    $this->actingAs($admin)->get('/admin/orders?search=Alice')
        ->assertInertia(fn ($page) => $page
            ->has('orders.data', 1)
            ->where('orders.data.0.customer_name', 'Alice Smith')
        );
});

test('admin orders can be filtered by product name', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer()->create();
    $cola = Product::factory()->create(['name' => 'Cola']);
    $water = Product::factory()->create(['name' => 'Water']);

    Transaction::factory()->create(['user_id' => $customer->id, 'product_id' => $cola->id]);
    Transaction::factory()->create(['user_id' => $customer->id, 'product_id' => $water->id]);

    $this->actingAs($admin)->get('/admin/orders?search=Cola')
        ->assertInertia(fn ($page) => $page
            ->has('orders.data', 1)
            ->where('orders.data.0.product_name', 'Cola')
        );
});
