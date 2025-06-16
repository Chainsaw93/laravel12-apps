<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Warehouses') }}</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <a href="{{ route('warehouses.create') }}" class="text-blue-500">{{ __('Add Warehouse') }}</a>
                <table class="min-w-full mt-4">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left">{{ __('Name') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($warehouses as $warehouse)
                            <tr class="border-t">
                                <td class="px-4 py-2">{{ $warehouse->name }}</td>
                                <td class="px-4 py-2">
                                    <a href="{{ route('warehouses.edit', $warehouse) }}" class="text-blue-500">{{ __('Edit') }}</a>
                                    <form method="POST" action="{{ route('warehouses.destroy', $warehouse) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-500 ml-2" onclick="return confirm('Delete?')">{{ __('Delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
