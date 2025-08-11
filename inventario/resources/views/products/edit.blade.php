<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Product') }}</h2>
    </x-slot>

    <link rel="stylesheet" href="https://unpkg.com/cropperjs/dist/cropper.min.css">
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data" class="bg-white shadow sm:rounded-lg p-6 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $product->name) }}" class="mt-1 block w-full rounded-md border-gray-300" required>
                </div>
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                    <select id="category_id" name="category_id" class="mt-1 block w-full rounded-md border-gray-300" required>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="unit_id" class="block text-sm font-medium text-gray-700">Unit</label>
                    <select id="unit_id" name="unit_id" class="mt-1 block w-full rounded-md border-gray-300">
                        <option value="">--</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" @selected(old('unit_id', $product->unit_id) == $unit->id)>{{ $unit->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="description" name="description" class="mt-1 block w-full rounded-md border-gray-300">{{ old('description', $product->description) }}</textarea>
                </div>
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
                    <input id="price" name="price" type="number" step="0.01" min="0" value="{{ old('price', $product->price) }}" class="mt-1 block w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label for="cost" class="block text-sm font-medium text-gray-700">Cost</label>
                    <input id="cost" name="cost" type="number" step="0.01" min="0" value="{{ old('cost', $product->cost) }}" class="mt-1 block w-full rounded-md border-gray-300" required>
                </div>
                <div>
                    <label for="currency" class="block text-sm font-medium text-gray-700">Currency</label>
                    <select id="currency" name="currency" class="mt-1 block w-full rounded-md border-gray-300" required>
                        @foreach(['CUP','USD','MLC'] as $cur)
                            <option value="{{ $cur }}" @selected(old('currency', $product->currency) == $cur)>{{ $cur }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="expiry_date" class="block text-sm font-medium text-gray-700">Expiry Date</label>
                    <input id="expiry_date" name="expiry_date" type="date" value="{{ old('expiry_date', optional($product->expiry_date)->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label for="sku" class="block text-sm font-medium text-gray-700">SKU</label>
                    <input id="sku" name="sku" type="text" value="{{ old('sku', $product->sku) }}" class="mt-1 block w-full rounded-md border-gray-300" required>
                </div>
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700">Image</label>
                    <input id="image" name="image" type="file" class="mt-1 block w-full text-sm text-gray-500" accept="image/*">
                    <img id="image-preview" src="{{ $product->image_path ? Storage::url($product->image_path) : '' }}" class="mt-2 max-h-64" @if(!$product->image_path) style="display:none" @endif />
                    <input type="hidden" name="cropped_image" id="cropped_image">
                </div>
                <div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Update</button>
                </div>
            </form>
        </div>
    </div>
    <script src="https://unpkg.com/cropperjs"></script>
    <script>
        let cropper;
        const input = document.getElementById('image');
        const image = document.getElementById('image-preview');
        input.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const url = URL.createObjectURL(file);
                image.src = url;
                image.style.display = '';
                if (cropper) cropper.destroy();
                cropper = new Cropper(image, {
                    aspectRatio: 1,
                    viewMode: 1,
                });
            }
        });
        document.querySelector('form').addEventListener('submit', function (e) {
            if (cropper) {
                e.preventDefault();
                cropper.getCroppedCanvas().toBlob((blob) => {
                    const reader = new FileReader();
                    reader.onloadend = () => {
                        document.getElementById('cropped_image').value = reader.result;
                        input.name = '';
                        this.submit();
                    };
                    reader.readAsDataURL(blob);
                });
            }
        });
    </script>
</x-app-layout>
