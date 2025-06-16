<?php

namespace App\Http\Controllers;

use App\Services\SalesReport;
use Illuminate\Http\Request;

class SalesReportController extends Controller
{
    public function index(Request $request, SalesReport $report)
    {
        $usd = (float) $request->query('usd_to_cup', 120);
        $mlc = (float) $request->query('mlc_to_cup', 130);
        return view('reports.index', [
            'daily' => $report->total('daily', $usd, $mlc),
            'weekly' => $report->total('weekly', $usd, $mlc),
            'monthly' => $report->total('monthly', $usd, $mlc),
        ]);
    }
}
