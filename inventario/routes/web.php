<?php
use Illuminate\Support\Facades\Route;
use App\Enums\PaymentMethod;
use App\Models\{Category, Product, Warehouse, Stock, Sale};
use App\Services\SalesReport;
use App\Http\Controllers\{
    CategoryController,
    ProductController,
    WarehouseController,
    StockTransferController,
    SaleController,
    SalesReportController,
    StockEntryController,
    ClientController,
    InvoiceController
};

Route::view('/', 'welcome')->name('welcome');

Route::get('/example', function () {
    $electronics = Category::firstOrCreate(['name' => 'Electronics']);
    $phones = Category::firstOrCreate(['name' => 'Phones', 'parent_id' => $electronics->id]);
    $product = Product::firstOrCreate([
        'sku' => 'iphone',
        'name' => 'iPhone',
        'category_id' => $phones->id,
    ]);

    $main = Warehouse::firstOrCreate(['name' => 'Main']);
    Warehouse::firstOrCreate(['name' => 'Secondary']);

    Stock::updateOrCreate(
        ['warehouse_id' => $main->id, 'product_id' => $product->id],
        ['quantity' => 10]
    );

    Sale::create([
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
})->name('example');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('products', ProductController::class)->except('show');
    Route::resource('warehouses', WarehouseController::class);
    Route::resource('clients', ClientController::class);
    Route::resource('invoices', InvoiceController::class)->only(['index','create','store']);

    Route::prefix('transfers')->name('transfers.')->group(function () {
        Route::get('create', [StockTransferController::class, 'create'])->name('create');
        Route::post('/', [StockTransferController::class, 'store'])->name('store');
    });

    Route::get('entries/create', [StockEntryController::class, 'create'])->name('entries.create');
    Route::post('entries', [StockEntryController::class, 'store'])->name('entries.store');

    Route::resource('sales', SaleController::class)->only(['index', 'create', 'store']);

    Route::get('reports', [SalesReportController::class, 'index'])->name('reports.index');
    Route::get('reports/pdf', [SalesReportController::class, 'pdf'])->name('reports.pdf');
    Route::get('reports/excel', [SalesReportController::class, 'excel'])->name('reports.excel');
    Route::prefix('reports/inventory')->name('reports.inventory.')->group(function () {
        Route::get('/', [\App\Http\Controllers\InventoryReportController::class, 'index'])->name('index');
        Route::get('generate', [\App\Http\Controllers\InventoryReportController::class, 'generate'])->name('generate');
        Route::get('chart-data', [\App\Http\Controllers\InventoryReportController::class, 'chartData'])->name('chartData');
        Route::get('pdf', [\App\Http\Controllers\InventoryReportController::class, 'pdf'])->name('pdf');
    });
});
