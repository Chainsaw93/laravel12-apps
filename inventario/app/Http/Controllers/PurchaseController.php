<?php

namespace App\Http\Controllers;

use App\Models\{Purchase, PurchaseItem, Supplier, Warehouse, Product, Stock, StockMovement, ExchangeRate};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\MovementType;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::with('supplier')->latest()->get();
        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $rates = ExchangeRate::orderByDesc('effective_date')
            ->get()
            ->keyBy('currency');
        return view('purchases.create', [
            'suppliers' => Supplier::all(),
            'warehouses' => Warehouse::all(),
            'products' => Product::all(),
            'rates' => $rates,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'currency' => 'required|in:CUP,USD,MLC',
            'exchange_rate_id' => 'required_if:currency,USD,MLC|nullable|exists:exchange_rates,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.cost' => 'required|numeric|min:0',
        ]);

        $rate = null;
        if ($data['currency'] !== 'CUP') {
            $rate = ExchangeRate::find($data['exchange_rate_id']);
        } else {
            $data['exchange_rate_id'] = null;
        }

        $purchase = Purchase::create([
            'supplier_id' => $data['supplier_id'],
            'warehouse_id' => $data['warehouse_id'],
            'currency' => $data['currency'],
            'exchange_rate_id' => $rate?->id,
            'total' => 0,
            'user_id' => Auth::id(),
        ]);

        $total = 0;
        foreach ($data['items'] as $item) {
            $stock = Stock::firstOrCreate(
                ['warehouse_id' => $data['warehouse_id'], 'product_id' => $item['product_id']],
                ['quantity' => 0, 'average_cost' => 0]
            );

            $costCup = $rate ? $item['cost'] * $rate->rate_to_cup : $item['cost'];
            $lineTotal = $item['quantity'] * $costCup;

            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'currency_cost' => $item['cost'],
                'cost_cup' => $costCup,
                'exchange_rate_id' => $rate?->id,
            ]);

            Product::where('id', $item['product_id'])->update([
                'cost' => $item['cost'],
                'currency' => $data['currency'],
            ]);

            $oldQuantity = $stock->quantity;
            $oldCost = $stock->average_cost;

            $stock->increment('quantity', $item['quantity']);

            $newAvg = (($oldQuantity * $oldCost) + ($item['quantity'] * $costCup)) / ($oldQuantity + $item['quantity']);
            $stock->update(['average_cost' => $newAvg]);

            StockMovement::create([
                'stock_id' => $stock->id,
                'type' => MovementType::IN,
                'quantity' => $item['quantity'],
                'purchase_price' => $item['cost'],
                'currency' => $data['currency'],
                'exchange_rate_id' => $rate?->id,
                'reason' => 'Compra ' . $purchase->id,
                'user_id' => Auth::id(),
            ]);

            $total += $lineTotal;
        }

        $purchase->update(['total' => $total]);

        return redirect()->route('purchases.index');
    }
}
