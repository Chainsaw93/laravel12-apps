<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Category') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('categories.update', $category) }}" enctype="multipart/form-data" id="category-form" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <x-label for="name" :value="__('Name')" />
                        <x-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $category->name) }}" required />
                    </div>
                    <div>
                        <x-label for="parent_id" :value="__('Parent')" />
                        <select id="parent_id" name="parent_id" class="mt-1 block w-full rounded-md">
                            <option value="">-- {{ __('None') }} --</option>
                            @foreach($categories as $item)
                                <option value="{{ $item->id }}" @selected($item->id == $category->parent_id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-label for="image" :value="__('Image')" />
                        <input id="image" name="image" type="file" accept="image/*" class="mt-1 block w-full" />
                        <img id="image-preview" src="{{ $category->image_path ? Storage::url($category->image_path) : '' }}" class="mt-2 max-h-64 {{ $category->image_path ? '' : 'hidden' }}" />
                        <input type="hidden" name="image_data" id="image_data">
                    </div>
                    <x-button>{{ __('Save') }}</x-button>
                </form>
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />
                <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const imageInput = document.getElementById('image');
                        const preview = document.getElementById('image-preview');
                        const imageData = document.getElementById('image_data');
                        const form = document.getElementById('category-form');
                        let cropper;

                        if (preview.getAttribute('src')) {
                            cropper = new Cropper(preview, { aspectRatio: 1 });
                        }

                        imageInput.addEventListener('change', function (e) {
                            const file = e.target.files[0];
                            if (!file) return;
                            const url = URL.createObjectURL(file);
                            preview.src = url;
                            preview.classList.remove('hidden');
                            if (cropper) cropper.destroy();
                            cropper = new Cropper(preview, { aspectRatio: 1 });
                        });

                        form.addEventListener('submit', function () {
                            if (cropper) {
                                const canvas = cropper.getCroppedCanvas();
                                imageData.value = canvas.toDataURL('image/png');
                                imageInput.removeAttribute('name');
                            }
                        });
                    });
                </script>
            </div>
        </div>
    </div>
</x-app-layout>
