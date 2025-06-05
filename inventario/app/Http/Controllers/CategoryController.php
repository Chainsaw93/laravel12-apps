<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return view('categories.index', [
            'categories' => Category::with('parent')->get(),
        ]);
    }

    public function create()
    {
        return view('categories.create', [
            'categories' => Category::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        Category::create($data);

        return redirect()->route('categories.index');
    }
}
