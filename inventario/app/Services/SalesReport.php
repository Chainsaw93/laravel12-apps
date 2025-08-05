<?php

namespace App\Services;

use App\Models\Invoice;
use Carbon\Carbon;

class SalesReport
{
    public function total(string $period): float
    {
        $now = Carbon::now();

        return Invoice::query()
            ->when($period === 'daily', fn($q) => $q->whereDate('created_at', $now->toDateString()))
            ->when($period === 'weekly', fn($q) => $q->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]))
            ->when($period === 'monthly', fn($q) => $q->whereYear('created_at', $now->year)->whereMonth('created_at', $now->month))
            ->sum('total_amount');
    }
}
