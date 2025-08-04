<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Sales Reports') }}</h2>
    </x-slot>

    <div class="py-6 space-y-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total CUP</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">Daily</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ number_format($daily, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">Weekly</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ number_format($weekly, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">Monthly</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ number_format($monthly, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <form method="GET" action="{{ route('reports.index') }}" class="bg-white shadow sm:rounded-lg p-4 space-y-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <x-label for="start_date" value="{{ __('Start Date') }}" />
                        <x-input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <x-label for="end_date" value="{{ __('End Date') }}" />
                        <x-input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <x-label for="product_id" value="{{ __('Product') }}" />
                        <select id="product_id" name="product_id" class="mt-1 block w-full border-gray-300 rounded">
                            <option value="">{{ __('All') }}</option>
                            @foreach($products as $prod)
                                <option value="{{ $prod->id }}" @selected(request('product_id')==$prod->id)>{{ $prod->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-label for="warehouse_id" value="{{ __('Warehouse') }}" />
                        <select id="warehouse_id" name="warehouse_id" class="mt-1 block w-full border-gray-300 rounded">
                            <option value="">{{ __('All') }}</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" @selected(request('warehouse_id')==$wh->id)>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-label for="payment_method" value="{{ __('Payment Method') }}" />
                        <select id="payment_method" name="payment_method" class="mt-1 block w-full border-gray-300 rounded">
                            <option value="">{{ __('All') }}</option>
                            @foreach($methods as $method)
                                <option value="{{ $method->value }}" @selected(request('payment_method')==$method->value)>{{ $method->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <x-button type="submit">{{ __('Filter') }}</x-button>
                    @if($sales->isNotEmpty())
                        <a href="{{ route('reports.pdf', request()->query()) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">PDF</a>
                        <a href="{{ route('reports.excel', request()->query()) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Excel</a>
                    @endif
                </div>
            </form>

            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Date') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Product') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Warehouse') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Quantity') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Price') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Price CUP') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Total CUP') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Payment') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($sales as $sale)
                            @php $rate = $sale->exchangeRate->rate_to_cup ?? 1; $priceCup = $sale->price_per_unit * $rate; @endphp
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $sale->created_at->toDateString() }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $sale->product->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $sale->warehouse->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $sale->quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ number_format($sale->price_per_unit,2) }} {{ $sale->currency }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ number_format($priceCup,2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ number_format($sale->quantity * $priceCup,2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $sale->payment_method->name ?? $sale->payment_method }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 whitespace-nowrap text-center">{{ __('No data') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
