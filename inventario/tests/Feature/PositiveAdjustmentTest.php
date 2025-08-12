<?php

namespace Tests\Feature;

use App\Models\{User, Category, Product, Warehouse, StockMovement, Stock};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Enums\MovementType;

class PositiveAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_positive_adjustment_increases_stock_and_records_reason(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'General']);
        $product = Product::create([
            'name' => 'Test',
            'sku' => 'T1',
            'category_id' => $category->id,
        ]);
        $warehouse = Warehouse::create(['name' => 'Main']);

        $response = $this->actingAs($user)->post('/adjustments', [
            'type' => 'pos',
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'purchase_price' => 3,
            'currency' => 'CUP',
            'exchange_rate_id' => null,
            'reason' => 'Found stock',
        ]);

        $response->assertRedirect('/warehouses/' . $warehouse->id);

        $stock = Stock::where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->first();
        $this->assertEquals(5, $stock->quantity);
        $this->assertEquals(3, $stock->average_cost);

        $movement = StockMovement::orderByDesc('id')->first();
        $this->assertEquals(MovementType::ADJUSTMENT_POS, $movement->type);
        $this->assertEquals('Found stock', $movement->reason);
    }

    public function test_reason_is_required_for_positive_adjustment(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'General']);
        $product = Product::create([
            'name' => 'Test',
            'sku' => 'T1',
            'category_id' => $category->id,
        ]);
        $warehouse = Warehouse::create(['name' => 'Main']);

        $response = $this->actingAs($user)->post('/adjustments', [
            'type' => 'pos',
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'purchase_price' => 2,
            'currency' => 'CUP',
            'exchange_rate_id' => null,
        ]);

        $response->assertSessionHasErrors('reason');
        $stock = Stock::where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->first();
        $this->assertNull($stock);
    }
}
