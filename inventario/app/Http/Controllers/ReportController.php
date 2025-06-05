<?php

namespace App\Http\Controllers;

use App\Services\SalesReport;

class ReportController extends Controller
{
    public function index(SalesReport $report)
    {
        return view('reports.index', [
            'daily' => $report->total('daily', usdToCup: 120, mlcToCup: 130),
            'weekly' => $report->total('weekly', usdToCup: 120, mlcToCup: 130),
            'monthly' => $report->total('monthly', usdToCup: 120, mlcToCup: 130),
        ]);
    }
}
