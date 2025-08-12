<?php

namespace Tests\Feature;

use App\Models\{User, Category, Product, Warehouse, Client, Stock, InvoiceItem};
use App\Enums\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockCostTest extends TestCase
{
    use RefreshDatabase;

    public function test_average_cost_used_when_selling(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'General']);
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TP1',
            'category_id' => $category->id,
        ]);
        $warehouse = Warehouse::create(['name' => 'Main']);
        $client = Client::create(['name' => 'Acme']);

        $this->actingAs($user)->post('/entries', [
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_cost' => 10,
            'currency' => 'CUP',
            'exchange_rate_id' => null,
        ]);

        $this->actingAs($user)->post('/entries', [
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_cost' => 20,
            'currency' => 'CUP',
            'exchange_rate_id' => null,
        ]);

        $stock = Stock::where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->first();
        $this->assertEquals(20, $stock->quantity);
        $this->assertEquals(15.0, $stock->average_cost);

        $this->actingAs($user)->post('/sales', [
            'client_id' => $client->id,
            'warehouse_id' => $warehouse->id,
            'currency' => 'CUP',
            'payment_method' => PaymentMethod::CASH_CUP->value,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5, 'price' => 25],
            ],
        ])->assertRedirect('/sales');

        $stock->refresh();
        $this->assertEquals(15, $stock->quantity);

        $item = InvoiceItem::first();
        $this->assertEquals(15.0, $item->cost);
        $this->assertEquals(75.0, $item->total_cost);
    }
}
