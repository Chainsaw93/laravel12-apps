<?php

namespace App\Http\Controllers;

use App\Models\{Product, Category, Warehouse, Stock, StockMovement, ExchangeRate, Batch, InventoryMovement, Unit};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Storage, Validator, DB, Auth};
use Illuminate\Support\Arr;
use App\Enums\MovementType;

class ProductController extends Controller
{
    private function storeCroppedImage(string $image)
    {
        $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $image), true);

        if ($data === false) {
            throw new \RuntimeException('Invalid image data.');
        }

        $tmpPath = tempnam(sys_get_temp_dir(), 'img');
        file_put_contents($tmpPath, $data);

        Validator::make([
            'image' => new \Illuminate\Http\File($tmpPath),
        ], [
            'image' => 'required|image|mimes:jpeg,png|max:5120',
        ])->validate();

        $mime = mime_content_type($tmpPath);
        $extension = $mime === 'image/png' ? 'png' : 'jpg';
        $path = 'products/' . uniqid() . '.' . $extension;
        Storage::disk('public')->put($path, $data);
        unlink($tmpPath);

        return $path;
    }
    public function index()
    {
        return view('products.index', [
            'products' => Product::with('category')->get(),
        ]);
    }

    public function create()
    {
        return view('products.create', [
            'categories' => Category::all(),
            'warehouses' => Warehouse::all(),
            'units' => Unit::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'nullable|exists:units,id',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'currency' => 'nullable|in:CUP,USD,MLC',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'quantity' => 'nullable|integer|min:1',
            'purchase_price' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date|after:today',
            'sku' => 'required|string|unique:products,sku',
            'cropped_image' => 'nullable|string',
        ]);

        if ($request->filled('cropped_image')) {
            $data['image_path'] = $this->storeCroppedImage($request->cropped_image);
        }

        $productData = Arr::whereNotNull(Arr::except($data, ['warehouse_id', 'quantity', 'purchase_price']));

        DB::transaction(function () use ($data, $productData) {
            $product = Product::create($productData);

            if (
                !empty($data['warehouse_id']) &&
                !empty($data['quantity']) &&
                !empty($data['purchase_price']) &&
                !empty($data['currency'])
            ) {
                $stock = Stock::firstOrCreate(
                    ['warehouse_id' => $data['warehouse_id'], 'product_id' => $product->id],
                    ['quantity' => 0, 'average_cost' => 0]
                );

                $baseQty = $data['quantity'];

                $rate = null;
                if ($data['currency'] !== 'CUP') {
                    $rate = ExchangeRate::where('currency', $data['currency'])->orderByDesc('effective_date')->first();
                }

                $currencyPrice = $data['purchase_price'];
                $costCup = $rate ? $currencyPrice * $rate->rate_to_cup : $currencyPrice;

                $oldQuantity = $stock->quantity;
                $oldCost = $stock->average_cost;
                $stock->increment('quantity', $baseQty);
                $newAvg = ($oldQuantity + $baseQty) > 0
                    ? (($oldQuantity * $oldCost) + ($baseQty * $costCup)) / ($oldQuantity + $baseQty)
                    : $costCup;
                $stock->update(['average_cost' => $newAvg]);

                StockMovement::create([
                    'stock_id' => $stock->id,
                    'type' => MovementType::IN,
                    'quantity' => $baseQty,
                    'purchase_price' => $currencyPrice,
                    'currency' => $data['currency'],
                    'exchange_rate_id' => $rate?->id,
                    'user_id' => Auth::id(),
                ]);

                $batch = Batch::create([
                    'product_id' => $product->id,
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
                    'product_id' => $product->id,
                    'warehouse_id' => $data['warehouse_id'],
                    'movement_type' => MovementType::IN,
                    'quantity' => $baseQty,
                    'unit_cost_cup' => $costCup,
                    'indirect_cost_unit' => 0,
                    'currency' => $data['currency'],
                    'exchange_rate_id' => $rate?->id,
                    'total_cost_cup' => $costCup * $baseQty,
                    'user_id' => Auth::id(),
                ]);
            }
        });

        return redirect()->route('products.index');
    }

    public function edit(Product $product)
    {
        return view('products.edit', [
            'product' => $product,
            'categories' => Category::all(),
            'units' => Unit::all(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'nullable|exists:units,id',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'currency' => 'required|in:CUP,USD,MLC',
            'expiry_date' => 'nullable|date|after:today',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'cropped_image' => 'nullable|string',
        ]);

        if ($request->filled('cropped_image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $this->storeCroppedImage($request->cropped_image);
        }

        $product->update($data);

        return redirect()->route('products.index');
    }

    public function destroy(Product $product)
    {
        if (
            $product->stocks()->exists() ||
            $product->batches()->exists() ||
            $product->purchaseItems()->exists() ||
            $product->invoiceItems()->exists()
        ) {
            return redirect()->route('products.index')
                ->withErrors(['product' => __('This product has related records and cannot be deleted.')]);
        }

        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()->route('products.index');
    }
}
