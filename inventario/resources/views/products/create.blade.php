<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Add Product') }}</h2>
    </x-slot>

    <link rel="stylesheet" href="https://unpkg.com/cropperjs/dist/cropper.min.css">
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="bg-white shadow sm:rounded-lg p-6 space-y-4">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" class="mt-1 block w-full rounded-md border-gray-300" required>
                </div>
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                    <select id="category_id" name="category_id" class="mt-1 block w-full rounded-md border-gray-300" required>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="description" name="description" class="mt-1 block w-full rounded-md border-gray-300">{{ old('description') }}</textarea>
                </div>
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
                    <input id="price" name="price" type="number" step="0.01" min="0" value="{{ old('price') }}" class="mt-1 block w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label for="cost" class="block text-sm font-medium text-gray-700">Cost</label>
                    <input id="cost" name="cost" type="number" step="0.01" min="0" value="{{ old('cost') }}" class="mt-1 block w-full rounded-md border-gray-300" required>
                </div>
                <div>
                    <label for="currency" class="block text-sm font-medium text-gray-700">Currency</label>
                    <select id="currency" name="currency" class="mt-1 block w-full rounded-md border-gray-300" required>
                        @foreach(['CUP','USD','MLC'] as $cur)
                            <option value="{{ $cur }}" @selected(old('currency') == $cur)>{{ $cur }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="expiry_date" class="block text-sm font-medium text-gray-700">Expiry Date</label>
                    <input id="expiry_date" name="expiry_date" type="date" value="{{ old('expiry_date') }}" class="mt-1 block w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label for="sku" class="block text-sm font-medium text-gray-700">SKU</label>
                    <input id="sku" name="sku" type="text" value="{{ old('sku') }}" class="mt-1 block w-full rounded-md border-gray-300" required>
                </div>
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700">Image</label>
                    <input id="image" name="image" type="file" class="mt-1 block w-full text-sm text-gray-500" accept="image/*">
                    <img id="image-preview" class="mt-2 max-h-64" />
                    <input type="hidden" name="cropped_image" id="cropped_image">
                </div>
                <div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save</button>
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
