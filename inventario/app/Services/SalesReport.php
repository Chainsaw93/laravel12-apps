<?php

namespace App\Services;

use App\Models\Sale;
use Carbon\Carbon;

class SalesReport
{
    public function total(string $period): float
    {
        $now = Carbon::now();
        return Sale::with('exchangeRate')->get()
            ->filter(function (Sale $sale) use ($period, $now) {
                return match ($period) {
                    'daily' => $sale->created_at->isSameDay($now),
                    'weekly' => $sale->created_at->isSameWeek($now),
                    'monthly' => $sale->created_at->isSameMonth($now),
                    default => false,
                };
            })
            ->reduce(function (float $carry, Sale $sale) {
                $rate = $sale->exchangeRate->rate_to_cup ?? 1;
                $total = $sale->quantity * $sale->price_per_unit * $rate;
                return $carry + $total;
            }, 0.0);
    }
}
