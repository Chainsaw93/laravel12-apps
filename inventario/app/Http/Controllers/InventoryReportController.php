<?php

namespace App\Http\Controllers;

use App\Models\{StockMovement, Warehouse, Product};
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use App\Services\InventoryReport as InventoryReportService;

class InventoryReportController extends Controller
{
    public function index()
    {
        return view('reports.inventory.index', [
            'warehouses' => Warehouse::all(),
            'products' => Product::all(),
            'data' => collect(),
            'valuation' => (new InventoryReportService())->valuationByWarehouse(),
        ]);
    }

    public function generate(Request $request)
    {
        $data = $this->aggregate($request);
        return view('reports.inventory.index', [
            'warehouses' => Warehouse::all(),
            'products' => Product::all(),
            'data' => $data,
            'valuation' => (new InventoryReportService())->valuationByWarehouse(),
        ]);
    }

    public function chartData(Request $request)
    {
        return response()->json($this->aggregate($request));
    }

    public function pdf(Request $request)
    {
        $data = $this->aggregate($request);
        $chart = $request->input('chart');
        return Pdf::loadView('reports.inventory.pdf', [
            'data' => $data,
            'chart' => $chart,
            'valuation' => (new InventoryReportService())->valuationByWarehouse(),
        ])->download('inventory_report.pdf');
    }

    protected function aggregate(Request $request): Collection
    {
        $query = StockMovement::query()
            ->when($request->start_date, fn($q) => $q->whereDate('created_at', '>=', $request->start_date))
            ->when($request->end_date, fn($q) => $q->whereDate('created_at', '<=', $request->end_date))
            ->when($request->warehouse_id, fn($q, $w) => $q->whereHas('stock', fn($sq) => $sq->where('warehouse_id', $w)))
            ->when($request->product_id, fn($q, $p) => $q->whereHas('stock', fn($sq) => $sq->where('product_id', $p)))
            ->when($request->type && in_array($request->type, ['in','out']), function($q, $t) {
                $types = $t === 'in'
                    ? ['in', 'transfer_in']
                    : ['out', 'transfer_out', 'adjustment'];
                $q->whereIn('type', $types);
            })
            ->leftJoin('exchange_rates', 'exchange_rates.id', '=', 'stock_movements.exchange_rate_id');

        return $query
            ->selectRaw(
                'stock_movements.type, '
                . 'sum(stock_movements.quantity) as quantity, '
                . "sum(case when stock_movements.currency = 'CUP' then stock_movements.quantity * coalesce(stock_movements.purchase_price,0) else 0 end) as cup_value, "
                . "sum(case when stock_movements.currency = 'USD' then stock_movements.quantity * coalesce(stock_movements.purchase_price,0) else 0 end) as usd_value, "
                . "sum(case when stock_movements.currency = 'MLC' then stock_movements.quantity * coalesce(stock_movements.purchase_price,0) else 0 end) as mlc_value, "
                . 'sum(stock_movements.quantity * coalesce(stock_movements.purchase_price,0) * coalesce(exchange_rates.rate_to_cup,1)) as total_cup'
            )
            ->groupBy('stock_movements.type')
            ->orderBy('stock_movements.type')
            ->get();
    }
}
