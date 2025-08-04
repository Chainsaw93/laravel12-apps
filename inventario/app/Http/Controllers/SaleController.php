<?php

namespace App\Http\Controllers;

use App\Models\{Sale, Warehouse, Product, Stock, StockMovement};
use App\Enums\{PaymentMethod, MovementType};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with(['product', 'warehouse'])->latest()->get();
        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        return view('sales.create', [
            'warehouses' => Warehouse::all(),
            'products' => Product::all(),
            'paymentMethods' => PaymentMethod::cases(),
        ]);
    }

    public function store(Request $request)
    {
        $methods = implode(',', array_map(fn($m) => $m->value, PaymentMethod::cases()));

        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'price_per_unit' => 'required|numeric|min:0',
            'payment_method' => 'required|in:' . $methods,
        ]);

        $stock = Stock::where('warehouse_id', $data['warehouse_id'])
            ->where('product_id', $data['product_id'])
            ->first();

        if (!$stock || $stock->quantity < $data['quantity']) {
            return back()->withErrors(['quantity' => 'Insufficient stock'])->withInput();
        }

        $sale = Sale::create($data);

        $stock->decrement('quantity', $data['quantity']);

        StockMovement::create([
            'stock_id' => $stock->id,
            'type' => MovementType::OUT,
            'quantity' => $data['quantity'],
            'description' => 'Venta ID: ' . $sale->id,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('sales.index');
    }
}
