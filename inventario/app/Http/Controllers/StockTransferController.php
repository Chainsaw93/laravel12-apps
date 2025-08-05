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

        $cost = $from->average_cost;

        $from->decrement('quantity', $data['quantity']);

        $oldQuantity = $to->quantity;
        $oldCost = $to->average_cost;

        $to->increment('quantity', $data['quantity']);

        $newAvg = (($oldQuantity * $oldCost) + ($data['quantity'] * $cost)) / ($oldQuantity + $data['quantity']);
        $to->update(['average_cost' => $newAvg]);

        StockMovement::create([
            'stock_id' => $from->id,
            'type' => MovementType::TRANSFER_OUT,
            'quantity' => $data['quantity'],
            'purchase_price' => $cost,
            'currency' => 'CUP',
            'reason' => 'Transfer to warehouse ' . $toWarehouse->name,
            'user_id' => Auth::id(),
        ]);

        StockMovement::create([
            'stock_id' => $to->id,
            'type' => MovementType::TRANSFER_IN,
            'quantity' => $data['quantity'],
            'purchase_price' => $cost,
            'currency' => 'CUP',
            'reason' => 'Transfer from warehouse ' . $fromWarehouse->name,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('warehouses.show', $data['from_warehouse_id']);
    }
}
