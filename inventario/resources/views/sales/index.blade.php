<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Sales') }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <a href="{{ route('sales.create') }}" class="mb-4 inline-block bg-blue-500 text-white px-4 py-2 rounded">{{ __('Add Sale') }}</a>
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Warehouse</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($sales as $sale)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $sale->product->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $sale->warehouse->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $sale->quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $sale->payment_method->name }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
