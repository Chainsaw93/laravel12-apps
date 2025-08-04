<?php

namespace App\Http\Controllers;

use App\Models\{Invoice, InvoiceItem, Client, Warehouse, Product, Stock, StockMovement, ExchangeRate};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\MovementType;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with('client')->latest()->get();
        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        $rates = ExchangeRate::orderByDesc('effective_date')
            ->get()
            ->keyBy('currency');
        return view('invoices.create', [
            'clients' => Client::all(),
            'warehouses' => Warehouse::all(),
            'products' => Product::all(),
            'rates' => $rates,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'currency' => 'required|in:CUP,USD,MLC',
            'exchange_rate_id' => 'nullable|exists:exchange_rates,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);
        $rate = null;
        if ($data['currency'] !== 'CUP') {
            $rate = ExchangeRate::find($data['exchange_rate_id']);
        } else {
            $data['exchange_rate_id'] = null;
        }

        $invoice = Invoice::create([
            'client_id' => $data['client_id'],
            'warehouse_id' => $data['warehouse_id'],
            'user_id' => Auth::id(),
            'currency' => $data['currency'],
            'exchange_rate_id' => $rate?->id,
            'total_amount' => 0,
            'status' => 'issued',
        ]);

        $total = 0;
        foreach ($data['items'] as $itemData) {
            $stock = Stock::where('warehouse_id', $data['warehouse_id'])
                ->where('product_id', $itemData['product_id'])
                ->first();
            if (!$stock || $stock->quantity < $itemData['quantity']) {
                $invoice->delete();
                return back()->withErrors(['items' => 'Insufficient stock'])->withInput();
            }
            $priceCup = $rate ? $itemData['price'] * $rate->rate_to_cup : $itemData['price'];
            $lineTotal = $itemData['quantity'] * $priceCup;
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $itemData['product_id'],
                'quantity' => $itemData['quantity'],
                'price' => $priceCup,
                'currency_price' => $itemData['price'],
                'total' => $lineTotal,
            ]);
            $stock->decrement('quantity', $itemData['quantity']);
            StockMovement::create([
                'stock_id' => $stock->id,
                'type' => MovementType::OUT,
                'quantity' => $itemData['quantity'],
                'reason' => 'Invoice ID: ' . $invoice->id,
                'user_id' => Auth::id(),
            ]);
            $total += $lineTotal;
        }

        $invoice->update(['total_amount' => $total]);

        return redirect()->route('invoices.index');
    }
}
