<?php

namespace App\Http\Controllers;

use App\Models\{Warehouse, Product, Stock};
use Illuminate\Http\Request;

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

        $from = Stock::firstOrCreate(['warehouse_id' => $data['from_warehouse_id'], 'product_id' => $data['product_id']], ['quantity' => 0]);
        $to = Stock::firstOrCreate(['warehouse_id' => $data['to_warehouse_id'], 'product_id' => $data['product_id']], ['quantity' => 0]);

        $from->decrement('quantity', $data['quantity']);
        $to->increment('quantity', $data['quantity']);

        return redirect()->route('warehouses.index');
    }
}
