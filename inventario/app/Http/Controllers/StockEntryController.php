<?php

namespace App\Http\Controllers;

use App\Models\{Warehouse, Product, Stock, StockMovement};
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
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'purchase_price' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $stock = Stock::firstOrCreate(
            ['warehouse_id' => $data['warehouse_id'], 'product_id' => $data['product_id']],
            ['quantity' => 0]
        );

        $stock->increment('quantity', $data['quantity']);

        StockMovement::create([
            'stock_id' => $stock->id,
            'type' => MovementType::IN,
            'quantity' => $data['quantity'],
            'purchase_price' => $data['purchase_price'] ?? null,
            'reason' => $data['reason'] ?? null,
            'description' => $data['description'] ?? null,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('warehouses.show', $data['warehouse_id']);
    }
}
