<?php

namespace App\Http\Controllers;

use App\Models\{Warehouse, Product, Stock, StockMovement, Batch, InventoryMovement};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Enums\MovementType;

class StockAdjustmentController extends Controller
{
    public function create()
    {
        return view('adjustments.create', [
            'warehouses' => Warehouse::all(),
            'products' => Product::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string',
            'description' => 'nullable|string',
        ]);

        DB::transaction(function () use ($data) {
            $warehouse = Warehouse::find($data['warehouse_id']);
            $stock = Stock::where('warehouse_id', $warehouse->id)
                ->where('product_id', $data['product_id'])
                ->lockForUpdate()
                ->first();

            if (!$stock || $stock->quantity < $data['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => 'Not enough stock',
                ]);
            }

            $method = $warehouse->valuation_method ?? 'average';
            $remaining = $data['quantity'];
            $costAccum = 0;

            if ($method === 'average') {
                $unitCost = $stock->average_cost;
                $costAccum = $unitCost * $remaining;
                $batches = Batch::where('warehouse_id', $warehouse->id)
                    ->where('product_id', $data['product_id'])
                    ->where('quantity_remaining', '>', 0)
                    ->orderBy('received_at', 'asc')
                    ->lockForUpdate()
                    ->get();
                foreach ($batches as $batch) {
                    if ($remaining <= 0) break;
                    $take = min($remaining, $batch->quantity_remaining);
                    $batch->quantity_remaining -= $take;
                    $batch->total_cost_cup -= $take * $unitCost;
                    $batch->save();
                    InventoryMovement::create([
                        'batch_id' => $batch->id,
                        'product_id' => $data['product_id'],
                        'warehouse_id' => $warehouse->id,
                        'movement_type' => MovementType::ADJUSTMENT_NEG,
                        'quantity' => $take,
                        'unit_cost_cup' => $unitCost,
                        'indirect_cost_unit' => 0,
                        'currency' => 'CUP',
                        'exchange_rate_id' => null,
                        'total_cost_cup' => $unitCost * $take,
                        'user_id' => Auth::id(),
                    ]);
                    $remaining -= $take;
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
                    if ($remaining <= 0) break;
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
                        'quantity' => 'Not enough stock',
                    ]);
                }
                $unitCost = $costAccum / $data['quantity'];
            }

            $stock->decrement('quantity', $data['quantity']);

            StockMovement::create([
                'stock_id' => $stock->id,
                'type' => MovementType::ADJUSTMENT_NEG,
                'quantity' => $data['quantity'],
                'purchase_price' => $unitCost,
                'currency' => 'CUP',
                'reason' => $data['reason'],
                'description' => $data['description'] ?? null,
                'user_id' => Auth::id(),
            ]);
        });

        return redirect()->route('warehouses.show', $data['warehouse_id']);
    }
}
