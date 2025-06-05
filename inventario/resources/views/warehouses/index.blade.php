<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Warehouses') }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <a href="{{ route('warehouses.create') }}" class="mb-4 inline-block bg-blue-500 text-white px-4 py-2 rounded">{{ __('Add Warehouse') }}</a>
            <div class="bg-white shadow sm:rounded-lg">
                <ul class="divide-y divide-gray-200">
                    @foreach ($warehouses as $warehouse)
                        <li class="px-6 py-4">{{ $warehouse->name }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
