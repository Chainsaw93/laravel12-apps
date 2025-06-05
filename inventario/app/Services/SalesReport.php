<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Models\Sale;
use Carbon\Carbon;

class SalesReport
{
    public function total(string $period, float $usdToCup, float $mlcToCup): float
    {
        $now = Carbon::now();
        return Sale::all()->filter(function (Sale $sale) use ($period, $now) {
            return match ($period) {
                'daily' => $sale->created_at->isSameDay($now),
                'weekly' => $sale->created_at->isSameWeek($now),
                'monthly' => $sale->created_at->isSameMonth($now),
                default => false,
            };
        })->reduce(function (float $carry, Sale $sale) use ($usdToCup, $mlcToCup) {
            $total = $sale->quantity * $sale->price_per_unit;
            return $carry + match ($sale->payment_method) {
                PaymentMethod::CASH_USD, PaymentMethod::TRANSFER_USD => $total * $usdToCup,
                PaymentMethod::TRANSFER_MLC => $total * $mlcToCup,
                default => $total,
            };
        }, 0.0);
    }
}
