<?php

namespace App\Http\Controllers;

use App\Models\{Warehouse, Product, Stock, StockMovement, Batch, InventoryMovement};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\MovementType;

class StockTransferController extends Controller
{
    public function create()
    {
        $warehouses = Warehouse::all();
        $products = Product::all();
        return view('transfers.create', compact('warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $fromWarehouse = Warehouse::find($data['from_warehouse_id']);
        $toWarehouse = Warehouse::find($data['to_warehouse_id']);

        $from = Stock::where('warehouse_id', $fromWarehouse->id)
            ->where('product_id', $data['product_id'])
            ->first();

        if (!$from || $from->quantity < $data['quantity']) {
            return back()->withErrors([
                'quantity' => 'Not enough stock in origin warehouse',
            ])->withInput();
        }

        $to = Stock::firstOrCreate(
            ['warehouse_id' => $toWarehouse->id, 'product_id' => $data['product_id']],
            ['quantity' => 0, 'average_cost' => 0]
        );

        $costCup = $from->average_cost;
        $method = $fromWarehouse->valuation_method ?? 'average';

        $currency = 'CUP';
        $exchangeRateId = null;
        $purchasePrice = $costCup;

        $lastMovement = StockMovement::where('stock_id', $from->id)
            ->whereNotNull('purchase_price')
            ->orderByDesc('id')
            ->first();

        if ($lastMovement && $lastMovement->currency !== 'CUP' && $lastMovement->exchange_rate_id) {
            $currency = $lastMovement->currency;
            $exchangeRateId = $lastMovement->exchange_rate_id;
            $rate = $lastMovement->exchangeRate;
            if ($rate) {
                $purchasePrice = $costCup / $rate->rate_to_cup;
            }
        }

        $remaining = $data['quantity'];
        $costAccum = 0;

        if ($method === 'average') {
            $unitCost = $costCup;
            $costAccum = $unitCost * $remaining;
            $batches = Batch::where('warehouse_id', $fromWarehouse->id)
                ->where('product_id', $data['product_id'])
                ->where('quantity_remaining', '>', 0)
                ->orderBy('received_at', 'asc')
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
                    'warehouse_id' => $fromWarehouse->id,
                    'movement_type' => MovementType::TRANSFER_OUT,
                    'quantity' => $take,
                    'unit_cost_cup' => $unitCost,
                    'indirect_cost_unit' => 0,
                    'currency' => $currency,
                    'exchange_rate_id' => $exchangeRateId,
                    'total_cost_cup' => $unitCost * $take,
                    'user_id' => Auth::id(),
                ]);

                $newBatch = Batch::create([
                    'product_id' => $data['product_id'],
                    'warehouse_id' => $toWarehouse->id,
                    'quantity_remaining' => $take,
                    'unit_cost_cup' => $unitCost,
                    'currency' => $currency,
                    'indirect_cost' => 0,
                    'total_cost_cup' => $unitCost * $take,
                    'received_at' => now(),
                ]);
                InventoryMovement::create([
                    'batch_id' => $newBatch->id,
                    'product_id' => $data['product_id'],
                    'warehouse_id' => $toWarehouse->id,
                    'movement_type' => MovementType::TRANSFER_IN,
                    'quantity' => $take,
                    'unit_cost_cup' => $unitCost,
                    'indirect_cost_unit' => 0,
                    'currency' => $currency,
                    'exchange_rate_id' => $exchangeRateId,
                    'total_cost_cup' => $unitCost * $take,
                    'user_id' => Auth::id(),
                ]);

                $remaining -= $take;
            }
        } else {
            $order = $method === 'fifo' ? 'asc' : 'desc';
            $batches = Batch::where('warehouse_id', $fromWarehouse->id)
                ->where('product_id', $data['product_id'])
                ->where('quantity_remaining', '>', 0)
                ->orderBy('received_at', $order)
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
                    'warehouse_id' => $fromWarehouse->id,
                    'movement_type' => MovementType::TRANSFER_OUT,
                    'quantity' => $take,
                    'unit_cost_cup' => $batch->unit_cost_cup,
                    'indirect_cost_unit' => $batch->indirect_cost,
                    'currency' => $currency,
                    'exchange_rate_id' => $exchangeRateId,
                    'total_cost_cup' => $unit * $take,
                    'user_id' => Auth::id(),
                ]);

                $newBatch = Batch::create([
                    'product_id' => $data['product_id'],
                    'warehouse_id' => $toWarehouse->id,
                    'quantity_remaining' => $take,
                    'unit_cost_cup' => $batch->unit_cost_cup,
                    'currency' => $currency,
                    'indirect_cost' => $batch->indirect_cost,
                    'total_cost_cup' => $unit * $take,
                    'received_at' => now(),
                ]);
                InventoryMovement::create([
                    'batch_id' => $newBatch->id,
                    'product_id' => $data['product_id'],
                    'warehouse_id' => $toWarehouse->id,
                    'movement_type' => MovementType::TRANSFER_IN,
                    'quantity' => $take,
                    'unit_cost_cup' => $batch->unit_cost_cup,
                    'indirect_cost_unit' => $batch->indirect_cost,
                    'currency' => $currency,
                    'exchange_rate_id' => $exchangeRateId,
                    'total_cost_cup' => $unit * $take,
                    'user_id' => Auth::id(),
                ]);

                $remaining -= $take;
            }
            $unitCost = $costAccum / $data['quantity'];
        }

        $from->decrement('quantity', $data['quantity']);

        $oldQuantity = $to->quantity;
        $oldCost = $to->average_cost;

        $to->increment('quantity', $data['quantity']);

        $newAvg = (($oldQuantity * $oldCost) + ($data['quantity'] * $costCup)) / ($oldQuantity + $data['quantity']);
        $to->update(['average_cost' => $newAvg]);

        StockMovement::create([
            'stock_id' => $from->id,
            'type' => MovementType::TRANSFER_OUT,
            'quantity' => $data['quantity'],
            'purchase_price' => $purchasePrice,
            'currency' => $currency,
            'exchange_rate_id' => $exchangeRateId,
            'reason' => 'Transfer to warehouse ' . $toWarehouse->name,
            'user_id' => Auth::id(),
        ]);

        StockMovement::create([
            'stock_id' => $to->id,
            'type' => MovementType::TRANSFER_IN,
            'quantity' => $data['quantity'],
            'purchase_price' => $purchasePrice,
            'currency' => $currency,
            'exchange_rate_id' => $exchangeRateId,
            'reason' => 'Transfer from warehouse ' . $fromWarehouse->name,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('warehouses.show', $data['from_warehouse_id']);
    }
}
