<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('New Supplier Invoice') }}</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('supplier-invoices.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-label for="supplier_id" :value="__('Supplier')" />
                        <select id="supplier_id" name="supplier_id" class="mt-1 block w-full rounded-md" required>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-label for="number" :value="__('Number')" />
                        <x-input id="number" name="number" class="mt-1 block w-full" required />
                    </div>
                    <div>
                        <x-label for="invoice_date" :value="__('Date')" />
                        <x-input id="invoice_date" type="date" name="invoice_date" class="mt-1 block w-full" />
                    </div>
                    <x-button>{{ __('Save') }}</x-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
