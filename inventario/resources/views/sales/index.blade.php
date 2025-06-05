<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Sales') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <a href="{{ route('sales.create') }}" class="text-blue-500">{{ __('Add Sale') }}</a>
                <table class="min-w-full mt-4">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left">{{ __('Product') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('Warehouse') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('Quantity') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('Price') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales as $sale)
                            <tr class="border-t">
                                <td class="px-4 py-2">{{ $sale->product->name }}</td>
                                <td class="px-4 py-2">{{ $sale->warehouse->name }}</td>
                                <td class="px-4 py-2">{{ $sale->quantity }}</td>
                                <td class="px-4 py-2">{{ $sale->price_per_unit }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
