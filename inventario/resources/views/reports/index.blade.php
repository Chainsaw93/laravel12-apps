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
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
