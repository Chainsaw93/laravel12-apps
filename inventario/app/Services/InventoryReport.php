<?php

namespace App\Services;

use App\Models\Stock;
use Illuminate\Support\Collection;

class InventoryReport
{
    public function valuationByWarehouse(): Collection
    {
        return Stock::query()
            ->selectRaw(
                'warehouses.name as warehouse, '
                . 'sum(stocks.quantity * stocks.average_cost) as total_cost, '
                . 'sum(stocks.quantity * products.price) as total_price, '
                . 'sum(stocks.quantity) as total_qty'
            )
            ->join('warehouses', 'warehouses.id', '=', 'stocks.warehouse_id')
            ->join('products', 'products.id', '=', 'stocks.product_id')
            ->groupBy('warehouses.id', 'warehouses.name')
            ->get()
            ->map(function ($row) {
                $totalCost = (float) $row->total_cost;
                $totalPrice = (float) $row->total_price;
                $totalQty = (float) $row->total_qty;
                $avgCost = $totalQty ? $totalCost / $totalQty : 0;
                $margin = $totalPrice > 0 ? ($totalPrice - $totalCost) / $totalPrice : 0;
                return [
                    'warehouse' => $row->warehouse,
                    'inventory_value' => $totalCost,
                    'average_cost' => $avgCost,
                    'profit_margin' => $margin,
                ];
            })
            ->values();
    }
}
