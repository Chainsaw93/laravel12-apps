<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('New Purchase') }}</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('purchases.store') }}" class="space-y-4">
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
                        <x-label for="warehouse_id" :value="__('Warehouse')" />
                        <select id="warehouse_id" name="warehouse_id" class="mt-1 block w-full rounded-md" required>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-label for="currency" :value="__('Currency')" />
                        <select id="currency" name="currency" class="mt-1 block w-full rounded-md" required>
                            @foreach(['CUP','USD','MLC'] as $cur)
                                @php $rate = $rates[$cur] ?? null; @endphp
                                <option value="{{ $cur }}" data-rate="{{ $rate?->rate_to_cup }}" data-id="{{ $rate?->id }}">{{ $cur }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-label value="{{ __('Exchange Rate to CUP') }}" />
                        <span id="rate_display"></span>
                        <input type="hidden" name="exchange_rate_id" id="exchange_rate_id" />
                    </div>
                    <div class="border-t pt-4">
                        <h3 class="font-semibold">{{ __('Items') }}</h3>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <x-label :value="__('Product')" />
                                <select name="items[0][product_id]" class="mt-1 block w-full rounded-md" required>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-label :value="__('Quantity')" />
                                <x-input name="items[0][quantity]" type="number" min="1" class="mt-1 block w-full" required />
                            </div>
                            <div>
                                <x-label :value="__('Cost')" />
                                <x-input name="items[0][cost]" type="number" step="0.01" min="0" class="mt-1 block w-full" required />
                            </div>
                        </div>
                    </div>
                    <x-button>{{ __('Save') }}</x-button>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const select = document.getElementById('currency');
            const rateDisplay = document.getElementById('rate_display');
            const rateInput = document.getElementById('exchange_rate_id');
            function updateRate(){
                const opt = select.options[select.selectedIndex];
                rateDisplay.textContent = opt.dataset.rate || '1';
                rateInput.value = opt.dataset.id || '';
            }
            updateRate();
            select.addEventListener('change', updateRate);
        });
    </script>
</x-app-layout>
