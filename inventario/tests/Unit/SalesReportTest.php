<?php

namespace Tests\Unit;

use App\Models\{Category, Product, Warehouse, Invoice, InvoiceItem, ExchangeRate, User, Client};
use App\Enums\PaymentMethod;
use App\Services\SalesReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_totals_convert_using_exchange_rate(): void
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

        $rate1 = ExchangeRate::create([
            'currency' => 'USD',
            'rate_to_cup' => 25,
            'effective_date' => now()->toDateString(),
            'user_id' => $user->id,
        ]);
        $rate2 = ExchangeRate::create([
            'currency' => 'USD',
            'rate_to_cup' => 30,
            'effective_date' => now()->subDay()->toDateString(),
            'user_id' => $user->id,
        ]);

        $invoice1 = Invoice::create([
            'client_id' => $client->id,
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'currency' => 'USD',
            'exchange_rate_id' => $rate1->id,
            'total_amount' => 0,
            'status' => 'issued',
            'payment_method' => PaymentMethod::CASH_USD,
        ]);
        InvoiceItem::create([
            'invoice_id' => $invoice1->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 250,
            'currency_price' => 10,
            'total' => 500,
        ]);
        $invoice1->update(['total_amount' => 500]);

        $invoice2 = Invoice::create([
            'client_id' => $client->id,
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'currency' => 'USD',
            'exchange_rate_id' => $rate2->id,
            'total_amount' => 0,
            'status' => 'issued',
            'payment_method' => PaymentMethod::CASH_USD,
        ]);
        InvoiceItem::create([
            'invoice_id' => $invoice2->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 300,
            'currency_price' => 10,
            'total' => 300,
        ]);
        $invoice2->update(['total_amount' => 300]);

        $report = new SalesReport();
        $this->assertEquals(800.0, $report->total('daily'));
    }
}
