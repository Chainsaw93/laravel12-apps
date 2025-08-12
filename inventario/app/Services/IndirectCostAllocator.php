<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;

class IndirectCostAllocator
{
    /**
     * Allocate indirect costs of a purchase across its batches.
     *
     * @param  Purchase  $purchase
     * @param  string  $method  'quantity' or 'value'
     */
    public function allocate(Purchase $purchase, string $method = 'quantity'): void
    {
        $costs = $purchase->indirectCosts()->where('allocated', false)->get();
        if ($costs->isEmpty()) {
            return;
        }

        $totalIndirect = $costs->sum('amount_cup');
        $items = $purchase->items()->get();

        $totalBasis = $method === 'value'
            ? $items->sum(fn ($item) => $item->quantity * $item->cost_cup)
            : $items->sum('quantity');

        DB::transaction(function () use ($method, $purchase, $items, $totalIndirect, $totalBasis, $costs) {
            foreach ($items as $item) {
                $weight = $method === 'value'
                    ? ($item->quantity * $item->cost_cup) / $totalBasis
                    : $item->quantity / $totalBasis;

                $allocatedTotal = $totalIndirect * $weight;
                $perUnitIndirect = $allocatedTotal / $item->quantity;

                $batch = Batch::find($item->batch_id);

                if ($batch) {
                    $batch->indirect_cost = $perUnitIndirect;
                    $batch->total_cost_cup = ($batch->unit_cost_cup + $perUnitIndirect) * $batch->quantity_remaining;
                    $batch->save();
                }
            }

            $costs->each->update(['allocated' => true]);
        });
    }
}
