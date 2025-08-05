<?php

namespace Tests\Feature;

use App\Models\{User, Category, Product, Warehouse, Stock};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_delete_product_with_stock(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'General']);
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TP1',
            'category_id' => $category->id,
        ]);
        $warehouse = Warehouse::create(['name' => 'Main']);
        Stock::create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'average_cost' => 0,
        ]);

        $response = $this->actingAs($user)->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHasErrors('product');
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_can_delete_product_without_relations(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'General']);
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TP2',
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
