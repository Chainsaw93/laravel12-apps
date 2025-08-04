<?php

namespace App\Http\Controllers;

use App\Services\SalesReport;

class ReportController extends Controller
{
    public function index(SalesReport $report)
    {
        return view('reports.index', [
            'daily' => $report->total('daily'),
            'weekly' => $report->total('weekly'),
            'monthly' => $report->total('monthly'),
        ]);
    }
}
