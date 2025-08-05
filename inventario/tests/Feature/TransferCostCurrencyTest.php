<?php

namespace Tests\Feature;

use App\Enums\MovementType;
use App\Models\{User, Category, Product, Warehouse, Stock, StockMovement, ExchangeRate};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransferCostCurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_transfer_records_cost_and_currency(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'General']);
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TP1',
            'category_id' => $category->id,
        ]);
        $fromWarehouse = Warehouse::create(['name' => 'Main']);
        $toWarehouse = Warehouse::create(['name' => 'Branch']);
        $rate = ExchangeRate::create([
            'currency' => 'USD',
            'rate_to_cup' => 120,
            'effective_date' => now(),
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)->post('/entries', [
            'warehouse_id' => $fromWarehouse->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'purchase_price' => 10,
            'currency' => 'USD',
            'exchange_rate_id' => $rate->id,
        ]);

        $fromStock = Stock::where('warehouse_id', $fromWarehouse->id)
            ->where('product_id', $product->id)
            ->first();

        $this->actingAs($user)->post('/transfers', [
            'from_warehouse_id' => $fromWarehouse->id,
            'to_warehouse_id' => $toWarehouse->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ])->assertRedirect(route('warehouses.show', $fromWarehouse->id));

        $outMovement = StockMovement::where('stock_id', $fromStock->id)
            ->where('type', MovementType::TRANSFER_OUT)
            ->latest()
            ->first();

        $toStock = Stock::where('warehouse_id', $toWarehouse->id)
            ->where('product_id', $product->id)
            ->first();

        $inMovement = StockMovement::where('stock_id', $toStock->id)
            ->where('type', MovementType::TRANSFER_IN)
            ->latest()
            ->first();

        $this->assertEquals(10.0, $outMovement->purchase_price);
        $this->assertEquals('USD', $outMovement->currency);
        $this->assertEquals($rate->id, $outMovement->exchange_rate_id);

        $this->assertEquals(10.0, $inMovement->purchase_price);
        $this->assertEquals('USD', $inMovement->currency);
        $this->assertEquals($rate->id, $inMovement->exchange_rate_id);
    }
}

