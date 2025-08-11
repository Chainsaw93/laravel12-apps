<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Supplier Invoices') }}</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="text-left">#</th>
                            <th class="text-left">{{ __('Supplier') }}</th>
                            <th class="text-left">{{ __('Number') }}</th>
                            <th class="text-left">{{ __('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $inv)
                            <tr>
                                <td>{{ $inv->id }}</td>
                                <td>{{ $inv->supplier->name }}</td>
                                <td>{{ $inv->number }}</td>
                                <td>{{ $inv->invoice_date }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
