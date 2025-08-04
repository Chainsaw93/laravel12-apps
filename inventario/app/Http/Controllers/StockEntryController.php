<?php

namespace App\Http\Controllers;

use App\Models\{Warehouse, Product, Stock, StockMovement, ExchangeRate};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\MovementType;

class StockEntryController extends Controller
{
    public function create()
    {
        return view('entries.create', [
            'warehouses' => Warehouse::all(),
            'products' => Product::all(),
            'rates' => ExchangeRate::orderByDesc('effective_date')->get(),
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

        $stock = Stock::firstOrCreate(
            ['warehouse_id' => $data['warehouse_id'], 'product_id' => $data['product_id']],
            ['quantity' => 0]
        );

        $stock->increment('quantity', $data['quantity']);

        $rate = null;
        if ($data['currency'] !== 'CUP') {
            $rate = ExchangeRate::find($data['exchange_rate_id']);
        } else {
            $data['exchange_rate_id'] = null;
        }

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

        return redirect()->route('warehouses.show', $data['warehouse_id']);
    }
}
