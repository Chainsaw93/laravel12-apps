<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Enums\PaymentMethod;
use App\Models\{Category, Product, Warehouse, Stock, Sale};
use App\Services\SalesReport;

Route::get('/example', function () {
    $electronics = Category::firstOrCreate(['name' => 'Electronics']);
    $phones = Category::firstOrCreate(['name' => 'Phones', 'parent_id' => $electronics->id]);
    $product = Product::firstOrCreate(['name' => 'iPhone', 'category_id' => $phones->id]);

    $main = Warehouse::firstOrCreate(['name' => 'Main']);
    $secondary = Warehouse::firstOrCreate(['name' => 'Secondary']);

    Stock::updateOrCreate(
        ['warehouse_id' => $main->id, 'product_id' => $product->id],
        ['quantity' => 10]
    );

    $sale = Sale::create([
        'warehouse_id' => $main->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'price_per_unit' => 1000,
        'payment_method' => PaymentMethod::CASH_USD,
    ]);

    $report = new SalesReport();
    return [
        'daily_total_cup' => $report->total('daily', usdToCup: 120, mlcToCup: 130),
    ];
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
