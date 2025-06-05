<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        return view('warehouses.index', [
            'warehouses' => Warehouse::all(),
        ]);
    }

    public function create()
    {
        return view('warehouses.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
        ]);

        Warehouse::create($data);

        return redirect()->route('warehouses.index');
    }
}
