<?php

namespace Tests\Unit;

use App\Enums\PaymentMethod;
use App\Models\{Category, Product, Warehouse, Sale, ExchangeRate, User};
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

        Sale::create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price_per_unit' => 10,
            'payment_method' => PaymentMethod::CASH_USD,
            'currency' => 'USD',
            'exchange_rate_id' => $rate1->id,
            'user_id' => $user->id,
        ]);

        Sale::create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_per_unit' => 10,
            'payment_method' => PaymentMethod::CASH_USD,
            'currency' => 'USD',
            'exchange_rate_id' => $rate2->id,
            'user_id' => $user->id,
        ]);

        $report = new SalesReport();
        $this->assertEquals(800.0, $report->total('daily'));
    }
}
