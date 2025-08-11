<?php

namespace App\Http\Controllers;

use App\Models\SupplierInvoice;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierInvoiceController extends Controller
{
    public function index()
    {
        $invoices = SupplierInvoice::with('supplier')->latest()->get();
        return view('supplier_invoices.index', compact('invoices'));
    }

    public function create()
    {
        return view('supplier_invoices.create', ['suppliers' => Supplier::all()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'number' => 'required|string',
            'invoice_date' => 'nullable|date',
        ]);

        SupplierInvoice::create($data);

        return redirect()->route('supplier-invoices.index');
    }
}
