<?php

namespace Tests\Unit;

use App\Models\{Category, Product, Warehouse, Stock};
use App\Services\InventoryReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_valuation_by_warehouse(): void
    {
        $category = Category::create(['name' => 'General']);
        $product1 = Product::create([
            'name' => 'Prod1',
            'sku' => 'P1',
            'category_id' => $category->id,
            'price' => 100,
        ]);
        $product2 = Product::create([
            'name' => 'Prod2',
            'sku' => 'P2',
            'category_id' => $category->id,
            'price' => 200,
        ]);
        $warehouse = Warehouse::create(['name' => 'Main']);

        Stock::create(['warehouse_id' => $warehouse->id, 'product_id' => $product1->id, 'quantity' => 10, 'average_cost' => 60]);
        Stock::create(['warehouse_id' => $warehouse->id, 'product_id' => $product2->id, 'quantity' => 5, 'average_cost' => 120]);

        $report = new InventoryReport();
        $data = $report->valuationByWarehouse();
        $main = $data->firstWhere('warehouse', 'Main');

        $this->assertEquals(1200.0, $main['inventory_value']);
        $this->assertEquals(80.0, $main['average_cost']);
        $this->assertEquals(0.4, $main['profit_margin']);
    }
}
