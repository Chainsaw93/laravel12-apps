<?php

namespace Tests\Feature;

use App\Enums\PaymentMethod;
use App\Models\{User, Category, Product, Warehouse, Client, Stock, ExchangeRate, Invoice, InvoiceItem};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyInventoryFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_in_foreign_currency_records_precise_cost(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'General']);
        $product = Product::create([
            'name' => 'Test',
            'sku' => 'T1',
            'category_id' => $category->id,
        ]);
        $warehouse = Warehouse::create(['name' => 'Main']);
        $rate = ExchangeRate::create([
            'currency' => 'USD',
            'rate_to_cup' => 120.5,
            'effective_date' => now(),
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)->post('/entries', [
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_cost' => 10.1234,
            'currency' => 'USD',
            'exchange_rate_id' => $rate->id,
        ]);

        $stock = Stock::first();
        $this->assertEquals(10, $stock->quantity);
        $this->assertEquals(1219.8697, $stock->average_cost);
    }

    public function test_sale_in_foreign_currency_reduces_stock(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'General']);
        $product = Product::create([
            'name' => 'Test',
            'sku' => 'T1',
            'category_id' => $category->id,
        ]);
        $warehouse = Warehouse::create(['name' => 'Main']);
        $client = Client::create(['name' => 'Acme']);
        $rate = ExchangeRate::create([
            'currency' => 'USD',
            'rate_to_cup' => 120.5,
            'effective_date' => now(),
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)->post('/entries', [
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_cost' => 10.1234,
            'currency' => 'USD',
            'exchange_rate_id' => $rate->id,
        ]);

        $this->actingAs($user)->post('/sales', [
            'client_id' => $client->id,
            'warehouse_id' => $warehouse->id,
            'currency' => 'USD',
            'exchange_rate_id' => $rate->id,
            'payment_method' => PaymentMethod::CASH_USD->value,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 4, 'price' => 20.5],
            ],
        ])->assertRedirect('/sales');

        $stock = Stock::first();
        $this->assertEquals(6, $stock->quantity);

        $invoice = Invoice::first();
        $this->assertEquals('USD', $invoice->currency);
        $this->assertEquals($rate->id, $invoice->exchange_rate_id);
    }

    public function test_return_in_foreign_currency_restores_stock(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'General']);
        $product = Product::create([
            'name' => 'Test',
            'sku' => 'T1',
            'category_id' => $category->id,
        ]);
        $warehouse = Warehouse::create(['name' => 'Main']);
        $client = Client::create(['name' => 'Acme']);
        $rate = ExchangeRate::create([
            'currency' => 'USD',
            'rate_to_cup' => 120.5,
            'effective_date' => now(),
            'user_id' => $user->id,
        ]);

        // Entry
        $this->actingAs($user)->post('/entries', [
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'unit_cost' => 10.1234,
            'currency' => 'USD',
            'exchange_rate_id' => $rate->id,
        ]);

        // Sale
        $this->actingAs($user)->post('/sales', [
            'client_id' => $client->id,
            'warehouse_id' => $warehouse->id,
            'currency' => 'USD',
            'exchange_rate_id' => $rate->id,
            'payment_method' => PaymentMethod::CASH_USD->value,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2, 'price' => 20.5],
            ],
        ])->assertRedirect('/sales');

        $invoice = Invoice::first();
        $item = InvoiceItem::first();

        // Return
        $this->actingAs($user)->post("/sales/{$invoice->id}/returns", [
            'items' => [
                ['invoice_item_id' => $item->id, 'quantity' => 1],
            ],
        ])->assertStatus(302);

        $stock = Stock::first();
        $this->assertEquals(4, $stock->quantity);
    }
}

