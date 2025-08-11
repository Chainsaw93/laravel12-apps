<?php

namespace App\Http\Controllers;

use App\Enums\MovementType;
use App\Models\Batch;
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
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'unit_id' => ['nullable', Rule::exists('product_units', 'unit_id')
                ->where(fn ($query) => $query->where('product_id', $request->product_id))],
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string',
            'description' => 'nullable|string',
        ], [
            'unit_id.exists' => 'La unidad seleccionada no corresponde al producto.',
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
                    'quantity' => 'Not enough stock',
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
                        'quantity' => 'Not enough stock',
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
                        'quantity' => 'Not enough stock',
                    ]);
                }
            }

            $unitCost = $costAccum / $baseQty;

            $stock->decrement('quantity', $baseQty);

            StockMovement::create([
                'stock_id' => $stock->id,
                'type' => MovementType::ADJUSTMENT_NEG,
                'quantity' => $baseQty,
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
