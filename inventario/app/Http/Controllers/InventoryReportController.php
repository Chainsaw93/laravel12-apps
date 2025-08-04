<?php

namespace App\Http\Controllers;

use App\Models\{StockMovement, Warehouse, Product};
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class InventoryReportController extends Controller
{
    public function index()
    {
        return view('reports.inventory.index', [
            'warehouses' => Warehouse::all(),
            'products' => Product::all(),
            'data' => collect(),
        ]);
    }

    public function generate(Request $request)
    {
        $data = $this->aggregate($request);
        return view('reports.inventory.index', [
            'warehouses' => Warehouse::all(),
            'products' => Product::all(),
            'data' => $data,
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
            });

        return $query
            ->selectRaw('DATE(created_at) as date, '
                . 'sum(case when type in ("in","transfer_in") then quantity else 0 end) as inputs, '
                . 'sum(case when type in ("out","transfer_out","adjustment") then quantity else 0 end) as outputs, '
                . 'sum(case when type in ("in","transfer_in") then quantity * purchase_price else 0 end) as input_value, '
                . 'sum(case when type in ("out","transfer_out","adjustment") then quantity * purchase_price else 0 end) as output_value')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }
}
