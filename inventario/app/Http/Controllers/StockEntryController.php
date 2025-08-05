<?php

namespace App\Http\Controllers;

use App\Models\{Warehouse, Product, Stock, StockMovement, ExchangeRate, Batch, InventoryMovement};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Enums\MovementType;

class StockEntryController extends Controller
{
    public function create()
    {
        return view('entries.create', [
            'warehouses' => Warehouse::all(),
            'products' => Product::all(),
            'rates' => ExchangeRate::orderByDesc('effective_date')->get()->keyBy('currency'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'purchase_price' => 'required|numeric|min:0',
            'currency' => 'required|in:CUP,USD,MLC',
            'exchange_rate_id' => 'required_if:currency,USD,MLC|nullable|exists:exchange_rates,id',
            'reason' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        DB::transaction(function () use ($data) {
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

            $costCup = $rate ? $data['purchase_price'] * $rate->rate_to_cup : $data['purchase_price'];

            $oldQuantity = $stock->quantity;
            $oldCost = $stock->average_cost;

            $stock->increment('quantity', $data['quantity']);

            $newAvg = (($oldQuantity * $oldCost) + ($data['quantity'] * $costCup)) / ($oldQuantity + $data['quantity']);
            $stock->update(['average_cost' => $newAvg]);

            StockMovement::create([
                'stock_id' => $stock->id,
                'type' => MovementType::IN,
                'quantity' => $data['quantity'],
                'purchase_price' => $data['purchase_price'],
                'currency' => $data['currency'],
                'exchange_rate_id' => $rate?->id,
                'reason' => $data['reason'] ?? null,
                'description' => $data['description'] ?? null,
                'user_id' => Auth::id(),
            ]);

            $batch = Batch::create([
                'product_id' => $data['product_id'],
                'warehouse_id' => $data['warehouse_id'],
                'quantity_remaining' => $data['quantity'],
                'unit_cost_cup' => $costCup,
                'currency' => $data['currency'],
                'indirect_cost' => 0,
                'total_cost_cup' => $costCup * $data['quantity'],
                'received_at' => now(),
            ]);

            InventoryMovement::create([
                'batch_id' => $batch->id,
                'product_id' => $data['product_id'],
                'warehouse_id' => $data['warehouse_id'],
                'movement_type' => MovementType::IN,
                'quantity' => $data['quantity'],
                'unit_cost_cup' => $costCup,
                'indirect_cost_unit' => 0,
                'currency' => $data['currency'],
                'exchange_rate_id' => $rate?->id,
                'total_cost_cup' => $costCup * $data['quantity'],
                'user_id' => Auth::id(),
            ]);
        });

        return redirect()->route('warehouses.show', $data['warehouse_id']);
    }
}
