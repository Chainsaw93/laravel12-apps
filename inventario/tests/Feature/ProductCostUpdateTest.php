<?php

namespace Tests\Feature;

use App\Models\{User, Category, Product, Warehouse, Supplier};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCostUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_cost_and_currency_updated_on_purchase(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'General']);
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TP1',
            'category_id' => $category->id,
        ]);
        $warehouse = Warehouse::create(['name' => 'Main']);
        $supplier = Supplier::create(['name' => 'Acme']);

        $this->actingAs($user)->post('/purchases', [
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'currency' => 'CUP',
            'exchange_rate_id' => null,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2, 'cost' => 25],
            ],
        ])->assertRedirect('/purchases');

        $product->refresh();
        $this->assertEquals(25.0, $product->cost);
        $this->assertEquals('CUP', $product->currency);
    }
}
