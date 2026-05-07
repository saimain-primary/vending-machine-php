<?php

namespace Tests\Feature\PhpUnit;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductsControllerPhpUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_listing_returns_paginated_products(): void
    {
        Product::factory()->count(15)->create();

        $this->get('/')
            ->assertInertia(fn ($page) => $page
                ->component('Products/Index')
                ->has('products.data', 10)
            );
    }

    public function test_product_listing_filters_by_search_query(): void
    {
        Product::factory()->create(['name' => 'Cola Zero']);
        Product::factory()->create(['name' => 'Water']);

        $this->get('/?search=Cola')
            ->assertInertia(fn ($page) => $page
                ->has('products.data', 1)
                ->where('products.data.0.name', 'Cola Zero')
            );
    }

    public function test_product_listing_sorts_by_price_descending(): void
    {
        Product::factory()->create(['name' => 'Expensive', 'price_in_mills' => 9000]);
        Product::factory()->create(['name' => 'Cheap', 'price_in_mills' => 1000]);

        $this->get('/?sort=price_in_mills&direction=desc')
            ->assertInertia(fn ($page) => $page
                ->where('products.data.0.name', 'Expensive')
            );
    }

    public function test_product_detail_page_renders_product_data(): void
    {
        Product::factory()->create(['name' => 'Test Cola', 'slug' => 'test-cola']);

        $this->get('/products/test-cola')
            ->assertInertia(fn ($page) => $page
                ->component('Products/Show')
                ->where('product.name', 'Test Cola')
                ->where('product.slug', 'test-cola')
            );
    }

    public function test_guest_cannot_purchase_product(): void
    {
        $product = Product::factory()->create(['quantity_available' => 5]);

        $this->post("/products/{$product->slug}/buy")
            ->assertRedirect('/login');
    }

    public function test_customer_can_purchase_in_stock_product(): void
    {
        $user = User::factory()->customer()->create();
        $product = Product::factory()->create(['quantity_available' => 5, 'price_in_mills' => 2000]);

        $this->actingAs($user)
            ->post("/products/{$product->slug}/buy")
            ->assertRedirect();

        $this->assertSame(4, $product->fresh()->quantity_available);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price_in_mills' => 2000,
            'total_amount_in_mills' => 2000,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'type' => StockMovementType::Purchase->value,
            'quantity_change' => -1,
            'quantity_after' => 4,
        ]);
    }

    public function test_buying_out_of_stock_product_returns_unprocessable_response(): void
    {
        $user = User::factory()->customer()->create();
        $product = Product::factory()->create(['quantity_available' => 0]);

        $this->actingAs($user)
            ->post("/products/{$product->slug}/buy")
            ->assertUnprocessable();

        $this->assertDatabaseCount('transactions', 0);
        $this->assertDatabaseCount('stock_movements', 0);
    }
}
