<?php

namespace App\Http\Controllers;

use App\Models\{Product, Category};
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return view('products.index', [
            'products' => Product::with('category')->get(),
        ]);
    }

    public function create()
    {
        return view('products.create', [
            'categories' => Category::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'category_id' => 'required|exists:categories,id',
        ]);

        Product::create($data);

        return redirect()->route('products.index');
    }
}
