<?php

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;

test('guests cannot access the admin inventory page', function () {
    $this->get('/admin/inventory')->assertRedirect('/login');
});

test('customers cannot access the admin inventory page', function () {
    $user = User::factory()->customer()->create();

    $this->actingAs($user)->get('/admin/inventory')->assertForbidden();
});

test('admin can access the inventory page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/inventory')->assertSuccessful();
});

test('inventory page renders paginated stock movements', function () {
    $admin = User::factory()->admin()->create();
    StockMovement::factory()->count(18)->create();

    $this->actingAs($admin)->get('/admin/inventory')
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Inventory/Index')
            ->has('stockMovements.data', 15)
            ->where('stockMovements.total', 18)
        );
});

test('inventory page includes movement details', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create(['name' => 'Tracked Cola']);

    StockMovement::factory()->create([
        'product_id' => $product->id,
        'user_id' => $admin->id,
        'type' => StockMovementType::ManualAdjustment,
        'quantity_change' => 7,
        'quantity_after' => 12,
        'created_at' => now(),
    ]);

    $this->actingAs($admin)->get('/admin/inventory')
        ->assertInertia(fn ($page) => $page
            ->where('stockMovements.data.0.product_name', 'Tracked Cola')
            ->where('stockMovements.data.0.user_name', $admin->name)
            ->where('stockMovements.data.0.type', StockMovementType::ManualAdjustment->value)
            ->where('stockMovements.data.0.type_label', 'Manual adjustment')
            ->where('stockMovements.data.0.quantity_change', 7)
            ->where('stockMovements.data.0.quantity_after', 12)
        );
});

test('inventory page can be filtered by product', function () {
    $admin = User::factory()->admin()->create();
    $cola = Product::factory()->create(['name' => 'Tracked Cola']);
    $water = Product::factory()->create(['name' => 'Water']);

    StockMovement::factory()->create([
        'product_id' => $cola->id,
        'type' => StockMovementType::ManualAdjustment,
    ]);
    StockMovement::factory()->create([
        'product_id' => $water->id,
        'type' => StockMovementType::Purchase,
    ]);

    $this->actingAs($admin)->get("/admin/inventory?product_id={$cola->id}")
        ->assertInertia(fn ($page) => $page
            ->has('stockMovements.data', 1)
            ->where('stockMovements.data.0.product_name', 'Tracked Cola')
            ->where('filters.product_id', $cola->id)
        );
});

test('inventory page includes products for the filter', function () {
    $admin = User::factory()->admin()->create();
    Product::factory()->create(['name' => 'Water']);
    Product::factory()->create(['name' => 'Cola']);

    $this->actingAs($admin)->get('/admin/inventory')
        ->assertInertia(fn ($page) => $page
            ->has('products', 2)
            ->where('products.0.name', 'Cola')
            ->where('products.1.name', 'Water')
        );
});
