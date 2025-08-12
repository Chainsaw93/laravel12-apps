<?php

namespace App\Http\Controllers;

use App\Models\{Warehouse, Stock, Category, Product, Invoice, Purchase};
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        return view('warehouses.index', [
            'warehouses' => Warehouse::all(),
        ]);
    }

    public function create()
    {
        return view('warehouses.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required']);
        Warehouse::create($data);
        return redirect()->route('warehouses.index');
    }

    public function edit(Warehouse $warehouse)
    {
        return view('warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $data = $request->validate(['name' => 'required']);
        $warehouse->update($data);
        return redirect()->route('warehouses.index');
    }

    public function destroy(Warehouse $warehouse)
    {
        $hasDependencies =
            Stock::where('warehouse_id', $warehouse->id)->exists() ||
            Invoice::where('warehouse_id', $warehouse->id)->exists() ||
            Purchase::where('warehouse_id', $warehouse->id)->exists();

        if ($hasDependencies) {
            return redirect()->route('warehouses.index')
                ->withErrors(['warehouse' => __('This warehouse has associated records and cannot be deleted.')]);
        }

        $warehouse->delete();

        return redirect()->route('warehouses.index');
    }

    public function show(Warehouse $warehouse, Request $request)
    {
        $query = Stock::with('product')
            ->where('warehouse_id', $warehouse->id);

        if ($request->filled('category_id')) {
            $query->whereHas('product', fn($q) => $q->where('category_id', $request->category_id));
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        return view('warehouses.show', [
            'warehouse' => $warehouse,
            'stocks' => $query->get(),
            'categories' => Category::all(),
            'products' => Product::all(),
        ]);
    }
}
