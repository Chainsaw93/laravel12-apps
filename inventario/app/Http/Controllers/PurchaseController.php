<?php

namespace App\Http\Controllers;

use App\Models\{Purchase, PurchaseItem, Supplier, Warehouse, Product, Stock, StockMovement, ExchangeRate, Batch, InventoryMovement, SupplierInvoice};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
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
            'invoice_number' => 'nullable|string',
            'invoice_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_id' => 'nullable|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.cost' => 'required|numeric|min:0',
        ]);

        foreach ($data['items'] as $item) {
            $product_id = $item['product_id'];
            Validator::make($item, [
                'unit_id' => ['nullable', Rule::exists('product_units', 'unit_id')
                    ->where(fn ($q) => $q->where('product_id', $product_id))],
            ], [
                'unit_id.exists' => 'La unidad seleccionada no corresponde al producto.',
            ])->validate();
        }

        $rate = null;
        if ($data['currency'] !== 'CUP') {
            $rate = ExchangeRate::find($data['exchange_rate_id']);
        } else {
            $data['exchange_rate_id'] = null;
        }

        DB::transaction(function () use ($data, $rate) {
            $invoice = null;
            if (!empty($data['invoice_number'])) {
                $invoice = SupplierInvoice::create([
                    'supplier_id' => $data['supplier_id'],
                    'number' => $data['invoice_number'],
                    'invoice_date' => $data['invoice_date'],
                ]);
            }

            $purchase = Purchase::create([
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'currency' => $data['currency'],
                'exchange_rate_id' => $rate?->id,
                'total' => 0,
                'user_id' => Auth::id(),
                'supplier_invoice_id' => $invoice?->id,
            ]);

            $total = 0;
            foreach ($data['items'] as $item) {
                $product = Product::find($item['product_id']);
                $unitId = $item['unit_id'] ?? $product->unit_id;
                $factor = $product->getConversionFactor($unitId);
                $baseQty = $item['quantity'] * $factor;
                $currencyCost = $item['cost'] / $factor;
                $costCup = $rate ? $currencyCost * $rate->rate_to_cup : $currencyCost;
                $lineTotal = $baseQty * $costCup;

                $stock = Stock::firstOrCreate(
                    ['warehouse_id' => $data['warehouse_id'], 'product_id' => $item['product_id']],
                    ['quantity' => 0, 'average_cost' => 0]
                );

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'unit_id' => $unitId,
                    'quantity' => $baseQty,
                    'currency_cost' => $currencyCost,
                    'cost_cup' => $costCup,
                    'exchange_rate_id' => $rate?->id,
                ]);

                $totalQty = Stock::where('product_id', $item['product_id'])->sum('quantity');
                $currentAvg = $product->cost_cup ?? 0;
                $newAvgCostCup = ($totalQty + $baseQty) > 0
                    ? (($totalQty * $currentAvg) + ($baseQty * $costCup)) / ($totalQty + $baseQty)
                    : $costCup;

                $productUpdates = [
                    'cost_cup' => $newAvgCostCup,
                    'cost' => $newAvgCostCup,
                    'currency' => 'CUP',
                ];

                if ($rate) {
                    $currencyField = 'cost_' . strtolower($data['currency']);
                    $productUpdates[$currencyField] = $newAvgCostCup / $rate->rate_to_cup;
                }

                Product::where('id', $item['product_id'])->update($productUpdates);

                $oldQuantity = $stock->quantity;
                $oldCost = $stock->average_cost;

                $stock->increment('quantity', $baseQty);

                $newAvg = (($oldQuantity * $oldCost) + ($baseQty * $costCup)) / ($oldQuantity + $baseQty);
                $stock->update(['average_cost' => $newAvg]);

                StockMovement::create([
                    'stock_id' => $stock->id,
                    'type' => MovementType::IN,
                    'quantity' => $baseQty,
                    'unit_cost' => $currencyCost,
                    'currency' => $data['currency'],
                    'exchange_rate_id' => $rate?->id,
                    'reason' => 'Compra ' . $purchase->id,
                    'user_id' => Auth::id(),
                ]);

                $batch = Batch::create([
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $data['warehouse_id'],
                    'quantity_remaining' => $baseQty,
                    'unit_cost_cup' => $costCup,
                    'currency' => $data['currency'],
                    'indirect_cost' => 0,
                    'total_cost_cup' => $costCup * $baseQty,
                    'received_at' => now(),
                ]);

                InventoryMovement::create([
                    'batch_id' => $batch->id,
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $data['warehouse_id'],
                    'movement_type' => MovementType::IN,
                    'quantity' => $baseQty,
                    'unit_cost_cup' => $costCup,
                    'indirect_cost_unit' => 0,
                    'currency' => $data['currency'],
                    'exchange_rate_id' => $rate?->id,
                    'total_cost_cup' => $costCup * $baseQty,
                    'reference_type' => Purchase::class,
                    'reference_id' => $purchase->id,
                    'user_id' => Auth::id(),
                ]);

                $total += $lineTotal;
            }

            $purchase->update(['total' => $total]);
        });

        return redirect()->route('purchases.index');
    }
}
