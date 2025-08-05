<?php

namespace App\Http\Controllers;

use App\Services\SalesReport;
use Illuminate\Http\Request;
use App\Models\{InvoiceItem, Product, Warehouse};
use Barryvdh\DomPDF\Facade\Pdf;

class SalesReportController extends Controller
{
    public function index(Request $request, SalesReport $report)
    {
        $sales = $this->filteredSales($request);

        return view('reports.index', [
            'daily' => $report->total('daily'),
            'weekly' => $report->total('weekly'),
            'monthly' => $report->total('monthly'),
            'sales' => $sales,
            'products' => Product::all(),
            'warehouses' => Warehouse::all(),
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
            fputcsv($out, ['Date', 'Product', 'Warehouse', 'Quantity', 'Price', 'Price CUP', 'Total CUP']);
            foreach ($sales as $sale) {
                $priceCup = $sale->price;
                fputcsv($out, [
                    $sale->invoice->created_at->toDateString(),
                    $sale->product->name,
                    $sale->invoice->warehouse->name,
                    $sale->quantity,
                    $sale->currency_price,
                    $priceCup,
                    $sale->total,
                ]);
            }
            fclose($out);
        };
        return response()->streamDownload($callback, 'sales_report.csv', ['Content-Type' => 'text/csv']);
    }

    protected function filteredSales(Request $request)
    {
        return InvoiceItem::with(['product', 'invoice.warehouse', 'invoice'])
            ->when($request->product_id, fn($q, $p) => $q->where('product_id', $p))
            ->whereHas('invoice', function ($q) use ($request) {
                $q->when($request->start_date, fn($q) => $q->whereDate('created_at', '>=', $request->start_date))
                  ->when($request->end_date, fn($q) => $q->whereDate('created_at', '<=', $request->end_date))
                  ->when($request->warehouse_id, fn($q, $w) => $q->where('warehouse_id', $w));
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
