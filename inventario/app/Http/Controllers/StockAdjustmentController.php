<?php

namespace App\Http\Controllers;

use App\Enums\MovementType;
use App\Models\Batch;
use App\Models\ExchangeRate;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class StockAdjustmentController extends Controller
{
    public function create()
    {
        return view('adjustments.create', [
            'warehouses' => Warehouse::all(),
            'products' => Product::all(),
            'rates' => ExchangeRate::orderByDesc('effective_date')->get()->keyBy('currency'),
        ]);
    }

    public function store(Request $request)
    {
        $type = $request->input('type', 'neg');
        $product_id = $request->product_id;

        if ($type === 'pos') {
            $data = $request->validate([
                'type' => 'required|in:neg,pos',
                'warehouse_id' => 'required|exists:warehouses,id',
                'product_id' => 'required|exists:products,id',
                'unit_id' => ['nullable', Rule::exists('product_units', 'unit_id')
                    ->where(fn ($q) => $q->where('product_id', $product_id))],
                'quantity' => 'required|integer|min:1',
                'unit_cost' => 'required|numeric|min:0',
                'currency' => 'required|in:CUP,USD,MLC',
                'exchange_rate_id' => 'required_if:currency,USD,MLC|nullable|exists:exchange_rates,id',
                'reason' => 'required|string',
                'description' => 'nullable|string',
            ], [
                'unit_id.exists' => __('messages.unit_mismatch'),
            ]);

            DB::transaction(function () use ($data) {
                $product = Product::find($data['product_id']);
                $unitId = $data['unit_id'] ?? $product->unit_id;
                $factor = $product->getConversionFactor($unitId);
                $baseQty = $data['quantity'] * $factor;

                $stock = Stock::firstOrCreate(
                    ['warehouse_id' => $data['warehouse_id'], 'product_id' => $data['product_id']],
                    ['quantity' => 0, 'average_cost' => 0]
                );

                $rate = null;
                if ($data['currency'] !== 'CUP') {
                    $rate = ExchangeRate::find($data['exchange_rate_id']);
                } else {
                    $data['exchange_rate_id'] = null;
                }

                $currencyCost = $data['unit_cost'] / $factor;
                $costCup = $rate ? $currencyCost * $rate->rate_to_cup : $currencyCost;

                $oldQuantity = $stock->quantity;
                $oldCost = $stock->average_cost;

                $stock->increment('quantity', $baseQty);

                $newAvg = (($oldQuantity * $oldCost) + ($baseQty * $costCup)) / ($oldQuantity + $baseQty);
                $stock->update(['average_cost' => $newAvg]);

                StockMovement::create([
                    'stock_id' => $stock->id,
                    'type' => MovementType::ADJUSTMENT_POS,
                    'quantity' => $baseQty,
                    'unit_cost' => $currencyCost,
                    'currency' => $data['currency'],
                    'exchange_rate_id' => $rate?->id,
                    'reason' => $data['reason'],
                    'description' => $data['description'] ?? null,
                    'user_id' => Auth::id(),
                ]);

                $batch = Batch::create([
                    'product_id' => $data['product_id'],
                    'warehouse_id' => $data['warehouse_id'],
                    'quantity_remaining' => $baseQty,
                    'unit_cost_cup' => $costCup,
                    'currency' => $data['currency'],
                    'indirect_cost' => 0,
                    'total_cost_cup' => $costCup * $baseQty,
                    'received_at' => now(),
                ]);

                InventoryMovement::create([
                    'batch_id' => $batch->id,
                    'product_id' => $data['product_id'],
                    'warehouse_id' => $data['warehouse_id'],
                    'movement_type' => MovementType::ADJUSTMENT_POS,
                    'quantity' => $baseQty,
                    'unit_cost_cup' => $costCup,
                    'indirect_cost_unit' => 0,
                    'currency' => $data['currency'],
                    'exchange_rate_id' => $rate?->id,
                    'total_cost_cup' => $costCup * $baseQty,
                    'user_id' => Auth::id(),
                ]);
            });
        } else {
            $data = $request->validate([
                'type' => 'required|in:neg,pos',
                'warehouse_id' => 'required|exists:warehouses,id',
                'product_id' => 'required|exists:products,id',
                'unit_id' => ['nullable', Rule::exists('product_units', 'unit_id')
                    ->where(fn ($q) => $q->where('product_id', $product_id))],
                'quantity' => 'required|integer|min:1',
                'reason' => 'required|string',
                'description' => 'nullable|string',
            ], [
                'unit_id.exists' => __('messages.unit_mismatch'),
            ]);

            DB::transaction(function () use ($data) {
                $product = Product::find($data['product_id']);
                $unitId = $data['unit_id'] ?? $product->unit_id;
                $factor = $product->getConversionFactor($unitId);
                $baseQty = $data['quantity'] * $factor;

                $warehouse = Warehouse::find($data['warehouse_id']);
                $stock = Stock::where('warehouse_id', $warehouse->id)
                    ->where('product_id', $data['product_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $stock || $stock->quantity < $baseQty) {
                    throw ValidationException::withMessages([
                        'quantity' => __('messages.insufficient_stock'),
                    ]);
                }

                $method = $warehouse->valuation_method ?? 'average';
                $remaining = $baseQty;
                $costAccum = 0;

                if ($method === 'average') {
                    $order = $warehouse->valuation_method === 'lifo' ? 'desc' : 'asc';
                    $batches = Batch::where('warehouse_id', $warehouse->id)
                        ->where('product_id', $data['product_id'])
                        ->where('quantity_remaining', '>', 0)
                        ->orderBy('received_at', $order)
                        ->lockForUpdate()
                        ->get();
                    foreach ($batches as $batch) {
                        if ($remaining <= 0) {
                            break;
                        }
                        $qtyToRemove = min($remaining, $batch->quantity_remaining);
                        $batch->quantity_remaining -= $qtyToRemove;
                        $batchCost = ($batch->unit_cost_cup + $batch->indirect_cost) * $qtyToRemove;
                        $batch->total_cost_cup -= $batchCost;
                        $batch->save();
                        InventoryMovement::create([
                            'batch_id' => $batch->id,
                            'product_id' => $data['product_id'],
                            'warehouse_id' => $warehouse->id,
                            'movement_type' => MovementType::ADJUSTMENT_NEG,
                            'quantity' => $qtyToRemove,
                            'unit_cost_cup' => $batch->unit_cost_cup,
                            'indirect_cost_unit' => $batch->indirect_cost,
                            'currency' => 'CUP',
                            'exchange_rate_id' => null,
                            'total_cost_cup' => $batchCost,
                            'user_id' => Auth::id(),
                        ]);
                        $costAccum += $batchCost;
                        $remaining -= $qtyToRemove;
                    }
                    if ($remaining > 0) {
                        throw ValidationException::withMessages([
                            'quantity' => __('messages.insufficient_stock'),
                        ]);
                    }
                } else {
                    $order = $method === 'fifo' ? 'asc' : 'desc';
                    $batches = Batch::where('warehouse_id', $warehouse->id)
                        ->where('product_id', $data['product_id'])
                        ->where('quantity_remaining', '>', 0)
                        ->orderBy('received_at', $order)
                        ->lockForUpdate()
                        ->get();
                    foreach ($batches as $batch) {
                        if ($remaining <= 0) {
                            break;
                        }
                        $take = min($remaining, $batch->quantity_remaining);
                        $unit = $batch->unit_cost_cup + $batch->indirect_cost;
                        $costAccum += $take * $unit;
                        $batch->quantity_remaining -= $take;
                        $batch->total_cost_cup -= $take * $unit;
                        $batch->save();
                        InventoryMovement::create([
                            'batch_id' => $batch->id,
                            'product_id' => $data['product_id'],
                            'warehouse_id' => $warehouse->id,
                            'movement_type' => MovementType::ADJUSTMENT_NEG,
                            'quantity' => $take,
                            'unit_cost_cup' => $batch->unit_cost_cup,
                            'indirect_cost_unit' => $batch->indirect_cost,
                            'currency' => 'CUP',
                            'exchange_rate_id' => null,
                            'total_cost_cup' => $unit * $take,
                            'user_id' => Auth::id(),
                        ]);
                        $remaining -= $take;
                    }
                    if ($remaining > 0) {
                        throw ValidationException::withMessages([
                            'quantity' => __('messages.insufficient_stock'),
                        ]);
                    }
                }

                $unitCost = $costAccum / $baseQty;

                $stock->decrement('quantity', $baseQty);

                StockMovement::create([
                    'stock_id' => $stock->id,
                    'type' => MovementType::ADJUSTMENT_NEG,
                    'quantity' => $baseQty,
                    'unit_cost' => $unitCost,
                    'currency' => 'CUP',
                    'reason' => $data['reason'],
                    'description' => $data['description'] ?? null,
                    'user_id' => Auth::id(),
                ]);
            });
        }

        return redirect()->route('warehouses.show', $request->warehouse_id);
    }
}
