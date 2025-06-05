<?php

namespace App\Http\Controllers;

use App\Models\{Sale, Warehouse, Product};
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with(['product', 'warehouse'])->latest()->get();
        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $warehouses = Warehouse::all();
        $products = Product::all();
        return view('sales.create', compact('warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'price_per_unit' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
        ]);

        Sale::create($data);
        return redirect()->route('sales.index');
    }
}
