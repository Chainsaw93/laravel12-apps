<?php

namespace App\Http\Controllers;

use App\Models\{Product, Category};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    private function storeCroppedImage(string $image)
    {
        $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $image));
        $path = 'products/' . uniqid() . '.jpg';
        Storage::disk('public')->put($path, $data);
        return $path;
    }
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
            'name' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date|after:today',
            'sku' => 'required|string|unique:products,sku',
            'cropped_image' => 'nullable|string',
        ]);

        if ($request->filled('cropped_image')) {
            $data['image_path'] = $this->storeCroppedImage($request->cropped_image);
        }

        Product::create($data);

        return redirect()->route('products.index');
    }

    public function edit(Product $product)
    {
        return view('products.edit', [
            'product' => $product,
            'categories' => Category::all(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date|after:today',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'cropped_image' => 'nullable|string',
        ]);

        if ($request->filled('cropped_image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $this->storeCroppedImage($request->cropped_image);
        }

        $product->update($data);

        return redirect()->route('products.index');
    }

    public function destroy(Product $product)
    {
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()->route('products.index');
    }
}
