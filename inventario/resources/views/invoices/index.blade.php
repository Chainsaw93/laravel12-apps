<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Invoices') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <a href="{{ route('sales.create') }}" class="mb-4 inline-block bg-blue-500 text-white px-4 py-2 rounded">{{ __('New') }}</a>
                <table class="w-full text-left border">
                    <thead>
                        <tr>
                            <th class="border px-2 py-1">{{ __('ID') }}</th>
                            <th class="border px-2 py-1">{{ __('Client') }}</th>
                            <th class="border px-2 py-1">{{ __('Total') }}</th>
                            <th class="border px-2 py-1">{{ __('Payment Method') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                            <tr>
                                <td class="border px-2 py-1">{{ $invoice->id }}</td>
                                <td class="border px-2 py-1">{{ $invoice->client->name }}</td>
                                <td class="border px-2 py-1">{{ $invoice->total_amount }} {{ $invoice->currency }}</td>
                                <td class="border px-2 py-1">{{ $invoice->payment_method->value }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
