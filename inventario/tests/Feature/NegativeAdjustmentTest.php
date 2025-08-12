<?php

namespace Tests\Feature;

use App\Models\{User, Category, Product, Warehouse, Stock, StockMovement};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Enums\MovementType;

class NegativeAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_negative_adjustment_decreases_stock_and_records_reason(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'General']);
        $product = Product::create([
            'name' => 'Test',
            'sku' => 'T1',
            'category_id' => $category->id,
        ]);
        $warehouse = Warehouse::create(['name' => 'Main']);

        $this->actingAs($user)->post('/entries', [
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_cost' => 5,
            'currency' => 'CUP',
            'exchange_rate_id' => null,
        ]);

        $response = $this->actingAs($user)->post('/adjustments', [
            'type' => 'neg',
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'reason' => 'Damaged stock',
        ]);

        $response->assertRedirect('/warehouses/' . $warehouse->id);

        $stock = Stock::where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->first();
        $this->assertEquals(7, $stock->quantity);

        $movement = StockMovement::orderByDesc('id')->first();
        $this->assertEquals(MovementType::ADJUSTMENT_NEG, $movement->type);
        $this->assertEquals('Damaged stock', $movement->reason);
    }

    public function test_reason_is_required_for_negative_adjustment(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'General']);
        $product = Product::create([
            'name' => 'Test',
            'sku' => 'T1',
            'category_id' => $category->id,
        ]);
        $warehouse = Warehouse::create(['name' => 'Main']);
        $stock = Stock::create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'average_cost' => 0,
        ]);

        $response = $this->actingAs($user)->post('/adjustments', [
            'type' => 'neg',
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertSessionHasErrors('reason');
        $this->assertEquals(5, $stock->fresh()->quantity);
    }
}
