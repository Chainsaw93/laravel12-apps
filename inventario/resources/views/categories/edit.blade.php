<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Category') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('categories.update', $category) }}" class="space-y-4">
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
                    <x-button>{{ __('Save') }}</x-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
