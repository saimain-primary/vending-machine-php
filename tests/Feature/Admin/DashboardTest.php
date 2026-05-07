<?php

use App\Models\Product;
use App\Models\User;

test('guests cannot access the admin dashboard', function () {
    $this->get('/admin/dashboard')->assertRedirect('/login');
});

test('customers cannot access the admin dashboard', function () {
    $user = User::factory()->customer()->create();

    $this->actingAs($user)->get('/admin/dashboard')->assertForbidden();
});

test('admin can access the dashboard', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/dashboard')->assertStatus(200);
});

test('dashboard renders with paginated products', function () {
    $admin = User::factory()->admin()->create();
    Product::factory()->count(15)->create();

    $this->actingAs($admin)->get('/admin/dashboard')
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Dashboard')
            ->has('products.data', 10)
            ->where('products.total', 15)
        );
});

test('dashboard filters products by search query', function () {
    $admin = User::factory()->admin()->create();
    Product::factory()->create(['name' => 'Cola Zero']);
    Product::factory()->create(['name' => 'Water']);

    $this->actingAs($admin)->get('/admin/dashboard?search=Cola')
        ->assertInertia(fn ($page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.name', 'Cola Zero')
        );
});

test('dashboard sorts products by name', function () {
    $admin = User::factory()->admin()->create();
    Product::factory()->create(['name' => 'Zebra Juice']);
    Product::factory()->create(['name' => 'Apple Cider']);

    $this->actingAs($admin)->get('/admin/dashboard?sort=name&direction=asc')
        ->assertInertia(fn ($page) => $page
            ->where('products.data.0.name', 'Apple Cider')
        );
});

test('dashboard sorts products by price descending', function () {
    $admin = User::factory()->admin()->create();
    Product::factory()->create(['name' => 'Cheap', 'price_in_mills' => 500]);
    Product::factory()->create(['name' => 'Expensive', 'price_in_mills' => 9000]);

    $this->actingAs($admin)->get('/admin/dashboard?sort=price_in_mills&direction=desc')
        ->assertInertia(fn ($page) => $page
            ->where('products.data.0.name', 'Expensive')
        );
});

test('dashboard sorts products by created_at', function () {
    $admin = User::factory()->admin()->create();
    $old = Product::factory()->create(['created_at' => now()->subDays(5)]);
    $new = Product::factory()->create(['created_at' => now()]);

    $this->actingAs($admin)->get('/admin/dashboard?sort=created_at&direction=asc')
        ->assertInertia(fn ($page) => $page
            ->where('products.data.0.id', $old->id)
        );
});

test('dashboard ignores invalid sort columns', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/dashboard?sort=password')->assertStatus(200);
});

test('dashboard product data includes created_at and updated_at', function () {
    $admin = User::factory()->admin()->create();
    Product::factory()->create();

    $this->actingAs($admin)->get('/admin/dashboard')
        ->assertInertia(fn ($page) => $page
            ->has('products.data.0.created_at')
            ->has('products.data.0.updated_at')
        );
});

test('dashboard passes back active filters', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/dashboard?search=foo&sort=name&direction=desc')
        ->assertInertia(fn ($page) => $page
            ->where('filters.search', 'foo')
            ->where('filters.sort', 'name')
            ->where('filters.direction', 'desc')
        );
});
