<?php

namespace Tests\Feature;

use App\Models\{User, Category, Product, Warehouse, Stock};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_delete_warehouse_with_stock(): void
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

        $response = $this->actingAs($user)->delete(route('warehouses.destroy', $warehouse));

        $response->assertRedirect(route('warehouses.index'));
        $response->assertSessionHasErrors('warehouse');
        $this->assertDatabaseHas('warehouses', ['id' => $warehouse->id]);
    }

    public function test_can_delete_warehouse_without_stock(): void
    {
        $user = User::factory()->create();
        $warehouse = Warehouse::create(['name' => 'Main']);

        $response = $this->actingAs($user)->delete(route('warehouses.destroy', $warehouse));

        $response->assertRedirect(route('warehouses.index'));
        $this->assertDatabaseMissing('warehouses', ['id' => $warehouse->id]);
    }
}
