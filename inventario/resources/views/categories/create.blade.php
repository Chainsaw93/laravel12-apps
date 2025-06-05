<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Add Category') }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('categories.store') }}" class="bg-white shadow sm:rounded-lg p-6 space-y-4">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input id="name" name="name" type="text" class="mt-1 block w-full rounded-md border-gray-300" required>
                </div>
                <div>
                    <label for="parent_id" class="block text-sm font-medium text-gray-700">Parent</label>
                    <select id="parent_id" name="parent_id" class="mt-1 block w-full rounded-md border-gray-300">
                        <option value="">None</option>
                        @foreach ($categories as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
