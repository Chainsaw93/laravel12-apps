<?php

namespace App\Http\Controllers;

use App\Models\{Warehouse, Product, Stock, StockMovement};
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

        $from = Stock::firstOrCreate(
            ['warehouse_id' => $data['from_warehouse_id'], 'product_id' => $data['product_id']],
            ['quantity' => 0]
        );
        $to = Stock::firstOrCreate(
            ['warehouse_id' => $data['to_warehouse_id'], 'product_id' => $data['product_id']],
            ['quantity' => 0]
        );

        if ($from->quantity < $data['quantity']) {
            return back()->withErrors(['quantity' => 'Not enough stock in origin warehouse'])->withInput();
        }


        $from->decrement('quantity', $data['quantity']);
        $to->increment('quantity', $data['quantity']);

        StockMovement::create([
            'stock_id' => $from->id,
            'type' => MovementType::OUT,
            'quantity' => $data['quantity'],
            'user_id' => Auth::id(),
        ]);

        StockMovement::create([
            'stock_id' => $to->id,
            'type' => MovementType::IN,
            'quantity' => $data['quantity'],
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('warehouses.show', $data['from_warehouse_id']);
    }
}
