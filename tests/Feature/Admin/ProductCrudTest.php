<?php

use App\Models\Product;
use App\Models\User;

// --- Store ---

test('admin can create a product', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/admin/products', [
        'name' => 'New Cola',
        'price' => '1.50',
        'quantity_available' => 20,
    ])->assertRedirect(route('admin.dashboard'));

    $this->assertDatabaseHas('products', [
        'name' => 'New Cola',
        'price_in_mills' => 1500,
        'quantity_available' => 20,
    ]);
});

test('creating a product auto-generates a slug from the name', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/admin/products', [
        'name' => 'Sparkling Water',
        'price' => '1.00',
        'quantity_available' => 10,
    ]);

    $this->assertDatabaseHas('products', ['slug' => 'sparkling-water']);
});

test('creating a product converts price to mills correctly', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/admin/products', [
        'name' => 'Precise Price',
        'price' => '2.999',
        'quantity_available' => 5,
    ]);

    $this->assertDatabaseHas('products', ['price_in_mills' => 2999]);
});

test('guest cannot create a product', function () {
    $this->post('/admin/products', [
        'name' => 'Ghost Cola',
        'price' => '1.00',
        'quantity_available' => 10,
    ])->assertRedirect('/login');

    $this->assertDatabaseEmpty('products');
});

test('customer cannot create a product', function () {
    $user = User::factory()->customer()->create();

    $this->actingAs($user)->post('/admin/products', [
        'name' => 'Unauthorized Cola',
        'price' => '1.00',
        'quantity_available' => 10,
    ])->assertForbidden();
});

test('creating a product fails without a name', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/admin/products', [
        'price' => '1.00',
        'quantity_available' => 10,
    ])->assertSessionHasErrors('name');
});

test('creating a product fails with a name shorter than 2 characters', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/admin/products', [
        'name' => 'A',
        'price' => '1.00',
        'quantity_available' => 10,
    ])->assertSessionHasErrors('name');
});

test('creating a product fails with a duplicate name', function () {
    $admin = User::factory()->admin()->create();
    Product::factory()->create(['name' => 'Existing Cola']);

    $this->actingAs($admin)->post('/admin/products', [
        'name' => 'Existing Cola',
        'price' => '1.00',
        'quantity_available' => 10,
    ])->assertSessionHasErrors('name');
});

test('creating a product fails with a price below the minimum', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/admin/products', [
        'name' => 'Too Cheap',
        'price' => '0.00',
        'quantity_available' => 10,
    ])->assertSessionHasErrors('price');
});

test('creating a product fails with a negative quantity', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/admin/products', [
        'name' => 'Negative Stock',
        'price' => '1.00',
        'quantity_available' => -1,
    ])->assertSessionHasErrors('quantity_available');
});

test('creating a product with zero quantity is allowed', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/admin/products', [
        'name' => 'Out of Stock Item',
        'price' => '1.00',
        'quantity_available' => 0,
    ])->assertRedirect(route('admin.dashboard'));

    $this->assertDatabaseHas('products', ['name' => 'Out of Stock Item', 'quantity_available' => 0]);
});

// --- Update ---

test('admin can update a product name', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create(['name' => 'Old Name']);

    $this->actingAs($admin)->put("/admin/products/{$product->id}", [
        'name' => 'New Name',
    ])->assertRedirect(route('admin.dashboard'));

    expect($product->fresh()->name)->toBe('New Name');
});

test('admin can update a product price', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create(['price_in_mills' => 1000]);

    $this->actingAs($admin)->put("/admin/products/{$product->id}", [
        'price' => '3.50',
    ]);

    expect($product->fresh()->price_in_mills)->toBe(3500);
});

test('admin can update product quantity', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create(['quantity_available' => 5]);

    $this->actingAs($admin)->put("/admin/products/{$product->id}", [
        'quantity_available' => 25,
    ]);

    expect($product->fresh()->quantity_available)->toBe(25);
});

test('updating a product name allows keeping the same name', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create(['name' => 'Unchanged Cola']);

    $this->actingAs($admin)->put("/admin/products/{$product->id}", [
        'name' => 'Unchanged Cola',
    ])->assertRedirect(route('admin.dashboard'));
});

test('updating a product name fails if name is taken by another product', function () {
    $admin = User::factory()->admin()->create();
    Product::factory()->create(['name' => 'Taken Name']);
    $product = Product::factory()->create(['name' => 'My Product']);

    $this->actingAs($admin)->put("/admin/products/{$product->id}", [
        'name' => 'Taken Name',
    ])->assertSessionHasErrors('name');
});

test('guest cannot update a product', function () {
    $product = Product::factory()->create();

    $this->put("/admin/products/{$product->id}", ['name' => 'Hacked'])->assertRedirect('/login');
});

test('customer cannot update a product', function () {
    $user = User::factory()->customer()->create();
    $product = Product::factory()->create();

    $this->actingAs($user)->put("/admin/products/{$product->id}", ['name' => 'Hacked'])->assertForbidden();
});

// --- Destroy ---

test('admin can delete a product', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create();

    $this->actingAs($admin)->delete("/admin/products/{$product->id}")
        ->assertRedirect(route('admin.dashboard'));

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});

test('guest cannot delete a product', function () {
    $product = Product::factory()->create();

    $this->delete("/admin/products/{$product->id}")->assertRedirect('/login');

    $this->assertDatabaseHas('products', ['id' => $product->id]);
});

test('customer cannot delete a product', function () {
    $user = User::factory()->customer()->create();
    $product = Product::factory()->create();

    $this->actingAs($user)->delete("/admin/products/{$product->id}")->assertForbidden();

    $this->assertDatabaseHas('products', ['id' => $product->id]);
});
