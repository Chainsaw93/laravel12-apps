<?php

namespace App\Http\Controllers;

use App\Models\{Warehouse, Product, Stock, StockMovement, ExchangeRate, Batch, InventoryMovement};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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
        $product_id = $request->product_id;
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'unit_id' => ['nullable', Rule::exists('product_units', 'unit_id')
                ->where(fn ($q) => $q->where('product_id', $product_id))],
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'required|numeric|min:0',
            'currency' => 'required|in:CUP,USD,MLC',
            'exchange_rate_id' => 'required_if:currency,USD,MLC|nullable|exists:exchange_rates,id',
            'reason' => 'nullable|string',
            'description' => 'nullable|string',
        ], [
            'unit_id.exists' => 'La unidad seleccionada no corresponde al producto.',
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
                'type' => MovementType::IN,
                'quantity' => $baseQty,
                'unit_cost' => $currencyCost,
                'currency' => $data['currency'],
                'exchange_rate_id' => $rate?->id,
                'reason' => $data['reason'] ?? null,
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
                'movement_type' => MovementType::IN,
                'quantity' => $baseQty,
                'unit_cost_cup' => $costCup,
                'indirect_cost_unit' => 0,
                'currency' => $data['currency'],
                'exchange_rate_id' => $rate?->id,
                'total_cost_cup' => $costCup * $baseQty,
                'user_id' => Auth::id(),
            ]);
        });

        return redirect()->route('warehouses.show', $data['warehouse_id']);
    }
}
