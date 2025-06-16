<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Sales Reports') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <table class="min-w-full">
                    <tbody>
                        <tr>
                            <th class="px-4 py-2 text-left">{{ __('Daily') }}</th>
                            <td class="px-4 py-2">{{ $daily }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="px-4 py-2 text-left">{{ __('Weekly') }}</th>
                            <td class="px-4 py-2">{{ $weekly }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="px-4 py-2 text-left">{{ __('Monthly') }}</th>
                            <td class="px-4 py-2">{{ $monthly }}</td>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Sales Report') }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
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
        </div>
    </div>
</x-app-layout>
