<?php

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;

test('guests cannot access order history', function () {
    $this->get('/orders')->assertRedirect('/login');
});

test('customer can view their order history', function () {
    $user = User::factory()->customer()->create();

    $this->actingAs($user)->get('/orders')->assertStatus(200);
});

test('order history shows only the current user\'s transactions', function () {
    $user = User::factory()->customer()->create();
    $otherUser = User::factory()->customer()->create();
    $product = Product::factory()->create();

    Transaction::create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price_in_mills' => $product->price_in_mills,
        'total_amount_in_mills' => $product->price_in_mills,
        'paid_amount_in_mills' => $product->price_in_mills,
        'change_amount_in_mills' => 0,
    ]);
    Transaction::create([
        'user_id' => $otherUser->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price_in_mills' => $product->price_in_mills,
        'total_amount_in_mills' => $product->price_in_mills,
        'paid_amount_in_mills' => $product->price_in_mills,
        'change_amount_in_mills' => 0,
    ]);

    $this->actingAs($user)->get('/orders')
        ->assertInertia(fn ($page) => $page
            ->component('Orders/Index')
            ->has('orders.data', 1)
        );
});

test('order history shows product name in transaction', function () {
    $user = User::factory()->customer()->create();
    $product = Product::factory()->create(['name' => 'Test Soda']);

    Transaction::create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price_in_mills' => $product->price_in_mills,
        'total_amount_in_mills' => $product->price_in_mills,
        'paid_amount_in_mills' => $product->price_in_mills,
        'change_amount_in_mills' => 0,
    ]);

    $this->actingAs($user)->get('/orders')
        ->assertInertia(fn ($page) => $page
            ->where('orders.data.0.product_name', 'Test Soda')
        );
});


test('admin is redirected from order history to admin dashboard', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/orders')
        ->assertRedirect(route('admin.dashboard'));
});
