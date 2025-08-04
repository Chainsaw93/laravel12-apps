<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Models\{Sale, Product, Warehouse, ExchangeRate};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with(['product','warehouse'])->latest()->get();
        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $rates = ExchangeRate::orderByDesc('effective_date')
            ->get()
            ->keyBy('currency');
        return view('sales.create', [
            'products' => Product::all(),
            'warehouses' => Warehouse::all(),
            'rates' => $rates,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'price_per_unit' => 'required|numeric|min:0',
            'payment_method' => ['required', Rule::in(array_map(fn($m) => $m->value, PaymentMethod::cases()))],
            'currency' => 'required|in:CUP,USD,MLC',
            'exchange_rate_id' => 'nullable|exists:exchange_rates,id',
        ]);

        if ($data['currency'] === 'CUP') {
            $data['exchange_rate_id'] = null;
        }

        Sale::create($data);

        return redirect()->route('sales.index');
    }
}
