<?php

namespace Tests\Feature\PhpUnit;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductsControllerPhpUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_product(): void
    {
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
        $this->assertDatabaseHas('stock_movements', [
            'user_id' => $admin->id,
            'type' => StockMovementType::InitialStock->value,
            'quantity_change' => 20,
            'quantity_after' => 20,
        ]);
    }

    public function test_customer_cannot_create_product(): void
    {
        $user = User::factory()->customer()->create();

        $this->actingAs($user)->post('/admin/products', [
            'name' => 'Unauthorized Cola',
            'price' => '1.00',
            'quantity_available' => 10,
        ])->assertForbidden();

        $this->assertDatabaseEmpty('products');
    }

    public function test_create_product_validates_required_fields(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/products', [])
            ->assertSessionHasErrors(['name', 'price', 'quantity_available']);
    }

    public function test_create_product_validates_positive_price(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/products', [
            'name' => 'Too Cheap',
            'price' => '0.00',
            'quantity_available' => 10,
        ])->assertSessionHasErrors('price');
    }

    public function test_create_product_validates_non_negative_quantity(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/products', [
            'name' => 'Negative Stock',
            'price' => '1.00',
            'quantity_available' => -1,
        ])->assertSessionHasErrors('quantity_available');
    }

    public function test_admin_can_update_product_quantity_and_record_stock_movement(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['quantity_available' => 5]);

        $this->actingAs($admin)->put("/admin/products/{$product->id}", [
            'quantity_available' => 25,
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertSame(25, $product->fresh()->quantity_available);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'type' => StockMovementType::ManualAdjustment->value,
            'quantity_change' => 20,
            'quantity_after' => 25,
        ]);
    }

    public function test_admin_can_delete_product(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();

        $this->actingAs($admin)->delete("/admin/products/{$product->id}")
            ->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
