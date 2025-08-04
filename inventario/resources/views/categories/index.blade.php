<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Categories') }}</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <a href="{{ route('categories.create') }}" class="text-blue-500">{{ __('Add Category') }}</a>
                <table class="min-w-full mt-4">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left">{{ __('Image') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('Name') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('Parent') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                            <tr class="border-t">
                                <td class="px-4 py-2">
                                    @if($category->image_path)
                                        <img src="{{ Storage::url($category->image_path) }}" class="h-12 w-12 object-cover" />
                                    @endif
                                </td>
                                <td class="px-4 py-2">{{ $category->name }}</td>
                                <td class="px-4 py-2">{{ $category->parent?->name }}</td>
                                <td class="px-4 py-2">
                                    <a href="{{ route('categories.edit', $category) }}" class="text-blue-500">{{ __('Edit') }}</a>
                                    <form method="POST" action="{{ route('categories.destroy', $category) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-500 ml-2" onclick="return confirm('Delete?')">{{ __('Delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
