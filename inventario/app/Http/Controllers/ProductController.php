<?php

namespace App\Http\Controllers;

use App\Models\{Product, Category};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Storage, Validator};

class ProductController extends Controller
{
    private function storeCroppedImage(string $image)
    {
        $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $image), true);

        if ($data === false) {
            throw new \RuntimeException('Invalid image data.');
        }

        $tmpPath = tempnam(sys_get_temp_dir(), 'img');
        file_put_contents($tmpPath, $data);

        Validator::make([
            'image' => new \Illuminate\Http\File($tmpPath),
        ], [
            'image' => 'required|image|mimes:jpeg,png|max:5120',
        ])->validate();

        $mime = mime_content_type($tmpPath);
        $extension = $mime === 'image/png' ? 'png' : 'jpg';
        $path = 'products/' . uniqid() . '.' . $extension;
        Storage::disk('public')->put($path, $data);
        unlink($tmpPath);

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
        if (
            $product->stocks()->exists() ||
            $product->batches()->exists() ||
            $product->purchaseItems()->exists() ||
            $product->invoiceItems()->exists()
        ) {
            return redirect()->route('products.index')
                ->withErrors(['product' => __('This product has related records and cannot be deleted.')]);
        }

        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()->route('products.index');
    }
}
