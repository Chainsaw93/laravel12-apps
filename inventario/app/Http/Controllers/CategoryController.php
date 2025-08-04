<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('parent')->get();
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('categories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image',
            'image_data' => 'nullable|string',
        ]);

        if ($request->filled('image_data')) {
            $data['image_path'] = $this->saveCroppedImage($request->input('image_data'));
        } elseif ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('categories', 'public');
        }

        Category::create($data);
        return redirect()->route('categories.index');
    }

    public function edit(Category $category)
    {
        $categories = Category::where('id', '!=', $category->id)->get();
        return view('categories.edit', compact('category', 'categories'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image',
            'image_data' => 'nullable|string',
        ]);

        if ($request->filled('image_data')) {
            if ($category->image_path) {
                Storage::disk('public')->delete($category->image_path);
            }
            $data['image_path'] = $this->saveCroppedImage($request->input('image_data'));
        } elseif ($request->hasFile('image')) {
            if ($category->image_path) {
                Storage::disk('public')->delete($category->image_path);
            }
            $data['image_path'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);
        return redirect()->route('categories.index');
    }

    public function destroy(Category $category)
    {
        $category->delete();
    }
    private function saveCroppedImage(string $imageData): string
    {
        $image = base64_decode(explode(',', $imageData)[1]);
        $path = 'categories/' . uniqid() . '.png';
        Storage::disk('public')->put($path, $image);
        return $path;
    }
}
