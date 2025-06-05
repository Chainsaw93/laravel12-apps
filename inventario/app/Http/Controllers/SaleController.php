<?php

namespace App\Http\Controllers;

use App\Models\{Sale, Warehouse, Product};
use App\Enums\PaymentMethod;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index()
    {
        return view('sales.index', [
            'sales' => Sale::with(['product', 'warehouse'])->get(),
        ]);
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
        $methods = implode(',', array_column(PaymentMethod::cases(), 'value'));
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'price_per_unit' => 'required|numeric|min:0',
            'payment_method' => 'required|in:' . $methods,
        ]);

        Sale::create($data);

        return redirect()->route('sales.index');
    }
}
