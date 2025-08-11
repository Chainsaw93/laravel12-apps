<?php

namespace App\Http\Controllers;

use App\Enums\MovementType;
use App\Enums\PaymentMethod;
use App\Models\Batch;
use App\Models\Client;
use App\Models\ExchangeRate;
use App\Models\InventoryMovement;
use App\Models\Invoice;
use App\Models\InvoiceCancellation;
use App\Models\InvoiceItem;
use App\Models\InvoiceReturn;
use App\Models\InvoiceReturnItem;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
            'paymentMethods' => PaymentMethod::cases(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'currency' => 'required|in:CUP,USD,MLC',
            'exchange_rate_id' => 'required_if:currency,USD,MLC|exists:exchange_rates,id',
            'payment_method' => 'required|in:'.implode(',', array_map(fn ($m) => $m->value, PaymentMethod::cases())),
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_id' => 'nullable|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        foreach ($data['items'] as $item) {
            Validator::make($item, [
                'unit_id' => ['nullable', Rule::exists('product_units', 'unit_id')
                    ->where(fn ($query) => $query->where('product_id', $item['product_id']))],
            ], [
                'unit_id.exists' => 'La unidad seleccionada no corresponde al producto.',
            ])->validate();
        }
        $rate = null;
        if ($data['currency'] !== 'CUP') {
            $rate = ExchangeRate::find($data['exchange_rate_id']);
            if (! $rate) {
                return back()->withErrors([
                    'exchange_rate_id' => 'Invalid exchange rate',
                ])->withInput();
            }
        } else {
            $data['exchange_rate_id'] = null;
        }

        try {
            DB::transaction(function () use ($data, $rate) {
                $warehouse = Warehouse::find($data['warehouse_id']);

                $invoice = Invoice::create([
                    'client_id' => $data['client_id'],
                    'warehouse_id' => $data['warehouse_id'],
                    'user_id' => Auth::id(),
                    'currency' => $data['currency'],
                    'exchange_rate_id' => $rate?->id,
                    'total_amount' => 0,
                    'total_cost' => 0,
                    'status' => 'issued',
                    'payment_method' => $data['payment_method'],
                ]);

                $total = 0;
                $totalCost = 0;
                $method = $warehouse->valuation_method ?? 'average';

                foreach ($data['items'] as $itemData) {
                    $product = Product::find($itemData['product_id']);
                    $unitId = $itemData['unit_id'] ?? $product->unit_id;
                    $factor = $product->getConversionFactor($unitId);
                    $baseQty = $itemData['quantity'] * $factor;

                    $stock = Stock::where('warehouse_id', $data['warehouse_id'])
                        ->where('product_id', $itemData['product_id'])
                        ->first();
                    if (! $stock || $stock->quantity < $baseQty) {
                        throw new \Exception('Insufficient stock');
                    }

                    $currencyPrice = $itemData['price'] / $factor;
                    $priceCup = $rate ? $currencyPrice * $rate->rate_to_cup : $currencyPrice;
                    $lineTotal = $baseQty * $priceCup;

                    $remaining = $baseQty;
                    $costAccum = 0;
                    if ($method === 'average') {
                        $order = $warehouse->valuation_method === 'lifo' ? 'desc' : 'asc';
                        $batches = Batch::where('warehouse_id', $warehouse->id)
                            ->where('product_id', $itemData['product_id'])
                            ->where('quantity_remaining', '>', 0)
                            ->orderBy('received_at', $order)
                            ->get();
                        foreach ($batches as $batch) {
                            if ($remaining <= 0) {
                                break;
                            }
                            $qtyToRemove = min($remaining, $batch->quantity_remaining);
                            $batch->quantity_remaining -= $qtyToRemove;
                            $batchCost = ($batch->unit_cost_cup + $batch->indirect_cost) * $qtyToRemove;
                            $batch->total_cost_cup -= $batchCost;
                            $batch->save();
                            InventoryMovement::create([
                                'batch_id' => $batch->id,
                                'product_id' => $itemData['product_id'],
                                'warehouse_id' => $warehouse->id,
                                'movement_type' => MovementType::OUT,
                                'quantity' => $qtyToRemove,
                                'unit_cost_cup' => $batch->unit_cost_cup,
                                'indirect_cost_unit' => $batch->indirect_cost,
                                'currency' => 'CUP',
                                'exchange_rate_id' => null,
                                'total_cost_cup' => $batchCost,
                                'reference_type' => Invoice::class,
                                'reference_id' => $invoice->id,
                                'user_id' => Auth::id(),
                            ]);
                            $costAccum += $batchCost;
                            $remaining -= $qtyToRemove;
                        }
                        if ($remaining > 0) {
                            throw new \Exception('Insufficient stock');
                        }
                    } else {
                        $order = $method === 'fifo' ? 'asc' : 'desc';
                        $batches = Batch::where('warehouse_id', $warehouse->id)
                            ->where('product_id', $itemData['product_id'])
                            ->where('quantity_remaining', '>', 0)
                            ->orderBy('received_at', $order)
                            ->get();
                        foreach ($batches as $batch) {
                            if ($remaining <= 0) {
                                break;
                            }
                            $take = min($remaining, $batch->quantity_remaining);
                            $unitCost = $batch->unit_cost_cup + $batch->indirect_cost;
                            $costAccum += $take * $unitCost;
                            $batch->quantity_remaining -= $take;
                            $batch->total_cost_cup -= $take * $unitCost;
                            $batch->save();
                            InventoryMovement::create([
                                'batch_id' => $batch->id,
                                'product_id' => $itemData['product_id'],
                                'warehouse_id' => $warehouse->id,
                                'movement_type' => MovementType::OUT,
                                'quantity' => $take,
                                'unit_cost_cup' => $batch->unit_cost_cup,
                                'indirect_cost_unit' => $batch->indirect_cost,
                                'currency' => 'CUP',
                                'exchange_rate_id' => null,
                                'total_cost_cup' => $unitCost * $take,
                                'reference_type' => Invoice::class,
                                'reference_id' => $invoice->id,
                                'user_id' => Auth::id(),
                            ]);
                            $remaining -= $take;
                        }
                        if ($remaining > 0) {
                            throw new \Exception('Insufficient stock');
                        }
                    }

                    $unitCost = $costAccum / $baseQty;

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $itemData['product_id'],
                        'unit_id' => $unitId,
                        'quantity' => $baseQty,
                        'price' => $priceCup,
                        'currency_price' => $currencyPrice,
                        'total' => $lineTotal,
                        'cost' => $unitCost,
                        'total_cost' => $costAccum,
                    ]);

                    $stock->decrement('quantity', $baseQty);
                    StockMovement::create([
                        'stock_id' => $stock->id,
                        'type' => MovementType::OUT,
                        'quantity' => $baseQty,
                        'purchase_price' => $unitCost,
                        'currency' => 'CUP',
                        'reason' => 'Venta factura '.$invoice->id,
                        'user_id' => Auth::id(),
                    ]);

                    $total += $lineTotal;
                    $totalCost += $costAccum;
                }

                $invoice->update(['total_amount' => $total, 'total_cost' => $totalCost]);
            });
        } catch (\Throwable $e) {
            return back()->withErrors(['items' => $e->getMessage()])->withInput();
        }

        return redirect()->route('sales.index');
    }

    public function returnItems(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'reason' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.invoice_item_id' => 'required|exists:invoice_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);
        try {
            DB::transaction(function () use ($invoice, $data) {
                $this->processReturn($invoice, $data['items'], $data['reason'] ?? null);
            });
        } catch (\Throwable $e) {
            return back()->withErrors(['items' => $e->getMessage()]);
        }

        return back();
    }

    public function cancel(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'reason' => 'required|string',
        ]);
        try {
            DB::transaction(function () use ($invoice, $data) {
                $items = [];
                foreach ($invoice->items as $item) {
                    $remaining = $item->quantity - $item->returned_quantity;
                    if ($remaining > 0) {
                        $items[] = [
                            'invoice_item_id' => $item->id,
                            'quantity' => $remaining,
                        ];
                    }
                }
                if ($items) {
                    $this->processReturn($invoice, $items, 'Cancellation: '.$data['reason']);
                }
                $invoice->update(['status' => 'cancelled']);
                InvoiceCancellation::create([
                    'invoice_id' => $invoice->id,
                    'user_id' => Auth::id(),
                    'reason' => $data['reason'],
                ]);
                $invoice->recordActivity('cancelled', ['reason' => $data['reason']]);
            });
        } catch (\Throwable $e) {
            return back()->withErrors(['invoice' => $e->getMessage()]);
        }

        return redirect()->route('sales.index');
    }

    public function approve(Invoice $invoice)
    {
        $invoice->update(['status' => 'approved']);
        $invoice->recordActivity('approved');

        return back();
    }

    protected function processReturn(Invoice $invoice, array $items, ?string $reason = null): InvoiceReturn
    {
        $return = InvoiceReturn::create([
            'invoice_id' => $invoice->id,
            'user_id' => Auth::id(),
            'reason' => $reason,
            'total_amount' => 0,
            'total_cost' => 0,
        ]);

        $totalAmount = 0;
        $totalCost = 0;

        foreach ($items as $itemData) {
            $invoiceItem = InvoiceItem::where('invoice_id', $invoice->id)
                ->findOrFail($itemData['invoice_item_id']);
            $available = $invoiceItem->quantity - $invoiceItem->returned_quantity;
            if ($itemData['quantity'] > $available) {
                throw new \Exception('Return quantity exceeds available amount');
            }
            $amount = $itemData['quantity'] * $invoiceItem->price;
            $cost = $itemData['quantity'] * $invoiceItem->cost;

            InvoiceReturnItem::create([
                'invoice_return_id' => $return->id,
                'invoice_item_id' => $invoiceItem->id,
                'quantity' => $itemData['quantity'],
                'amount' => $amount,
                'cost' => $cost,
            ]);

            $invoiceItem->increment('returned_quantity', $itemData['quantity']);

            $stock = Stock::firstOrCreate(
                ['warehouse_id' => $invoice->warehouse_id, 'product_id' => $invoiceItem->product_id],
                ['quantity' => 0, 'average_cost' => 0]
            );

            $oldQuantity = $stock->quantity;
            $oldCost = $stock->average_cost;
            $stock->increment('quantity', $itemData['quantity']);
            $newAvg = (($oldQuantity * $oldCost) + ($itemData['quantity'] * $invoiceItem->cost)) / ($oldQuantity + $itemData['quantity']);
            $stock->update(['average_cost' => $newAvg]);

            StockMovement::create([
                'stock_id' => $stock->id,
                'type' => MovementType::IN,
                'quantity' => $itemData['quantity'],
                'purchase_price' => $invoiceItem->cost,
                'currency' => 'CUP',
                'exchange_rate_id' => null,
                'reason' => 'Devolución factura '.$invoice->id,
                'user_id' => Auth::id(),
            ]);

            $batch = Batch::create([
                'product_id' => $invoiceItem->product_id,
                'warehouse_id' => $invoice->warehouse_id,
                'quantity_remaining' => $itemData['quantity'],
                'unit_cost_cup' => $invoiceItem->cost,
                'currency' => 'CUP',
                'indirect_cost' => 0,
                'total_cost_cup' => $invoiceItem->cost * $itemData['quantity'],
                'received_at' => now(),
            ]);

            InventoryMovement::create([
                'batch_id' => $batch->id,
                'product_id' => $invoiceItem->product_id,
                'warehouse_id' => $invoice->warehouse_id,
                'movement_type' => MovementType::IN,
                'quantity' => $itemData['quantity'],
                'unit_cost_cup' => $invoiceItem->cost,
                'indirect_cost_unit' => 0,
                'currency' => 'CUP',
                'exchange_rate_id' => null,
                'total_cost_cup' => $invoiceItem->cost * $itemData['quantity'],
                'reference_type' => InvoiceReturn::class,
                'reference_id' => $return->id,
                'user_id' => Auth::id(),
            ]);

            $totalAmount += $amount;
            $totalCost += $cost;
        }

        $return->update([
            'total_amount' => $totalAmount,
            'total_cost' => $totalCost,
        ]);

        $invoice->decrement('total_amount', $totalAmount);
        $invoice->decrement('total_cost', $totalCost);

        return $return;
    }
}
