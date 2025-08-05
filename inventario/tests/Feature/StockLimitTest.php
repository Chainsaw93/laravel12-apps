<?php

namespace Tests\Feature;

use App\Models\{User, Category, Product, Warehouse, Stock, Client};
use App\Enums\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_sell_more_than_available_stock(): void
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
        $stock = Stock::create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'average_cost' => 0,
        ]);

        $response = $this->actingAs($user)->post('/sales', [
            'client_id' => $client->id,
            'warehouse_id' => $warehouse->id,
            'currency' => 'CUP',
            'payment_method' => PaymentMethod::CASH_CUP->value,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 10, 'price' => 10],
            ],
        ]);

        $response->assertSessionHasErrors('items');
        $this->assertEquals(5, $stock->fresh()->quantity);
    }

    public function test_cannot_transfer_more_than_available_stock(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'General']);
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TP1',
            'category_id' => $category->id,
        ]);
        $from = Warehouse::create(['name' => 'Origin']);
        $to = Warehouse::create(['name' => 'Destination']);
        $fromStock = Stock::create([
            'warehouse_id' => $from->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'average_cost' => 0,
        ]);
        $toStock = Stock::create([
            'warehouse_id' => $to->id,
            'product_id' => $product->id,
            'quantity' => 0,
            'average_cost' => 0,
        ]);

        $response = $this->actingAs($user)->post('/transfers', [
            'from_warehouse_id' => $from->id,
            'to_warehouse_id' => $to->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertEquals(5, $fromStock->fresh()->quantity);
        $this->assertEquals(0, $toStock->fresh()->quantity);
    }
}
