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
        if ($category->children()->exists() || $category->products()->exists()) {
            return redirect()->route('categories.index')
                ->withErrors(['category' => __('This category has child categories or products and cannot be deleted.')]);
        }

        if ($category->image_path) {
            Storage::disk('public')->delete($category->image_path);
        }

        $category->delete();

        return redirect()->route('categories.index');
    }
    private function saveCroppedImage(string $imageData): string
    {
        $parts = explode(',', $imageData);
        if (count($parts) < 2) {
            abort(422, 'Invalid image data');
        }

        $image = base64_decode($parts[1]);
        if ($image === false) {
            abort(422, 'Invalid image data');
        }

        // Ensure the decoded image does not exceed 5MB
        if (strlen($image) > 5 * 1024 * 1024) {
            abort(422, 'Image exceeds maximum size of 5MB');
        }

        // Validate MIME type using finfo
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($image);
        if (!in_array($mime, ['image/png', 'image/jpeg'])) {
            abort(422, 'Invalid image type');
        }

        $extension = $mime === 'image/png' ? 'png' : 'jpg';
        $path = 'categories/' . uniqid() . '.' . $extension;

        Storage::disk('public')->put($path, $image);
        return $path;
    }
}
