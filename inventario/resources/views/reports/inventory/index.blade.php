<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Inventory Report') }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <form method="GET" action="{{ route('reports.inventory.generate') }}" class="bg-white shadow sm:rounded-lg p-4 space-y-4">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div>
                        <x-label for="start_date" value="{{ __('Start Date') }}" />
                        <x-input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <x-label for="end_date" value="{{ __('End Date') }}" />
                        <x-input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <x-label for="type" value="{{ __('Type') }}" />
                        <select id="type" name="type" class="mt-1 block w-full border-gray-300 rounded">
                            <option value="">{{ __('Both') }}</option>
                            <option value="in" @selected(request('type')==='in')>{{ __('In') }}</option>
                            <option value="out" @selected(request('type')==='out')>{{ __('Out') }}</option>
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
                        <x-label for="product_id" value="{{ __('Product') }}" />
                        <select id="product_id" name="product_id" class="mt-1 block w-full border-gray-300 rounded">
                            <option value="">{{ __('All') }}</option>
                            @foreach($products as $prod)
                                <option value="{{ $prod->id }}" @selected(request('product_id')==$prod->id)>{{ $prod->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <x-button type="submit">{{ __('Generate') }}</x-button>
                    @if($data->isNotEmpty())
                    <x-button type="button" id="pdf-btn">{{ __('Export PDF') }}</x-button>
                    @endif
                </div>
            </form>

            @if($data->isNotEmpty())
                <div class="bg-white p-4 shadow sm:rounded-lg">
                    <canvas id="inventoryChart" class="w-full h-64"></canvas>
                </div>

                <div class="bg-white p-4 shadow sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2">{{ __('Type') }}</th>
                                <th class="px-4 py-2">{{ __('Quantity') }}</th>
                                <th class="px-4 py-2">{{ __('CUP Value') }}</th>
                                <th class="px-4 py-2">{{ __('USD Value') }}</th>
                                <th class="px-4 py-2">{{ __('MLC Value') }}</th>
                                <th class="px-4 py-2">{{ __('Total CUP') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($data as $row)
                                <tr>
                                    <td class="px-4 py-2">{{ $row->type }}</td>
                                    <td class="px-4 py-2">{{ $row->quantity }}</td>
                                    <td class="px-4 py-2">{{ number_format($row->cup_value, 2) }}</td>
                                    <td class="px-4 py-2">{{ number_format($row->usd_value, 2) }}</td>
                                    <td class="px-4 py-2">{{ number_format($row->mlc_value, 2) }}</td>
                                    <td class="px-4 py-2">{{ number_format($row->total_cup, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(isset($valuation) && $valuation->isNotEmpty())
                <div class="bg-white p-4 shadow sm:rounded-lg mt-4">
                    <h3 class="font-semibold mb-2">{{ __('Valuation by Warehouse') }}</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2">{{ __('Warehouse') }}</th>
                                <th class="px-4 py-2">{{ __('Inventory Value') }}</th>
                                <th class="px-4 py-2">{{ __('Average Cost') }}</th>
                                <th class="px-4 py-2">{{ __('Profit Margin') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($valuation as $row)
                                <tr>
                                    <td class="px-4 py-2">{{ $row['warehouse'] }}</td>
                                    <td class="px-4 py-2">{{ number_format($row['inventory_value'], 2) }}</td>
                                    <td class="px-4 py-2">{{ number_format($row['average_cost'], 2) }}</td>
                                    <td class="px-4 py-2">{{ number_format($row['profit_margin'] * 100, 2) }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            @endif
        </div>
    </div>

    <form id="pdf-form" method="GET" action="{{ route('reports.inventory.pdf') }}">
        <input type="hidden" name="start_date" value="{{ request('start_date') }}">
        <input type="hidden" name="end_date" value="{{ request('end_date') }}">
        <input type="hidden" name="type" value="{{ request('type') }}">
        <input type="hidden" name="warehouse_id" value="{{ request('warehouse_id') }}">
        <input type="hidden" name="product_id" value="{{ request('product_id') }}">
        <input type="hidden" name="chart" id="chart-input">
    </form>

    @if($data->isNotEmpty())
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('inventoryChart').getContext('2d');
        const labels = @json($data->pluck('type'));
        const quantities = @json($data->pluck('quantity'));
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    { label: 'Quantity', data: quantities, backgroundColor: 'blue' }
                ]
            }
        });
        document.getElementById('pdf-btn').addEventListener('click', () => {
            document.getElementById('chart-input').value = document.getElementById('inventoryChart').toDataURL('image/png');
            document.getElementById('pdf-form').submit();
        });
    </script>
    @endif
</x-app-layout>
