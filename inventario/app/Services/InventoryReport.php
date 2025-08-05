<?php

namespace App\Services;

use App\Models\Stock;
use Illuminate\Support\Collection;

class InventoryReport
{
    public function valuationByWarehouse(): Collection
    {
        return Stock::with(['warehouse', 'product'])
            ->get()
            ->groupBy(fn($s) => $s->warehouse->name)
            ->map(function ($stocks) {
                $totalCost = $stocks->sum(fn($s) => $s->quantity * $s->average_cost);
                $totalPrice = $stocks->sum(fn($s) => $s->quantity * $s->product->price);
                $totalQty = $stocks->sum('quantity');
                $avgCost = $totalQty ? $totalCost / $totalQty : 0;
                $margin = $totalPrice > 0 ? ($totalPrice - $totalCost) / $totalPrice : 0;
                return [
                    'warehouse' => $stocks->first()->warehouse->name,
                    'inventory_value' => $totalCost,
                    'average_cost' => $avgCost,
                    'profit_margin' => $margin,
                ];
            })
            ->values();
    }
}
