<?php

namespace App\Http\Controllers;

use App\Services\SalesReport;
use Illuminate\Http\Request;
use App\Models\{Sale, Product, Warehouse};
use App\Enums\PaymentMethod;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesReportController extends Controller
{
    public function index(Request $request, SalesReport $report)
    {
        $usd = (float) $request->query('usd_to_cup', 120);
        $mlc = (float) $request->query('mlc_to_cup', 130);

        $sales = $this->filteredSales($request);

        return view('reports.index', [
            'daily' => $report->total('daily', $usd, $mlc),
            'weekly' => $report->total('weekly', $usd, $mlc),
            'monthly' => $report->total('monthly', $usd, $mlc),
            'sales' => $sales,
            'products' => Product::all(),
            'warehouses' => Warehouse::all(),
            'methods' => PaymentMethod::cases(),
        ]);
    }

    public function pdf(Request $request)
    {
        $sales = $this->filteredSales($request);
        return Pdf::loadView('reports.sales_pdf', ['sales' => $sales])->download('sales_report.pdf');
    }

    public function excel(Request $request)
    {
        $sales = $this->filteredSales($request);
        $callback = function () use ($sales) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Product', 'Warehouse', 'Quantity', 'Price', 'Total', 'Payment Method']);
            foreach ($sales as $sale) {
                fputcsv($out, [
                    $sale->created_at->toDateString(),
                    $sale->product->name,
                    $sale->warehouse->name,
                    $sale->quantity,
                    $sale->price_per_unit,
                    $sale->quantity * $sale->price_per_unit,
                    $sale->payment_method->value ?? $sale->payment_method,
                ]);
            }
            fclose($out);
        };
        return response()->streamDownload($callback, 'sales_report.csv', ['Content-Type' => 'text/csv']);
    }

    protected function filteredSales(Request $request)
    {
        return Sale::with(['product', 'warehouse'])
            ->when($request->start_date, fn($q) => $q->whereDate('created_at', '>=', $request->start_date))
            ->when($request->end_date, fn($q) => $q->whereDate('created_at', '<=', $request->end_date))
            ->when($request->product_id, fn($q, $p) => $q->where('product_id', $p))
            ->when($request->warehouse_id, fn($q, $w) => $q->where('warehouse_id', $w))
            ->when($request->payment_method, fn($q, $m) => $q->where('payment_method', $m))
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
