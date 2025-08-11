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
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-label for="invoice_number" :value="__('Invoice Number')" />
                            <x-input id="invoice_number" name="invoice_number" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <x-label for="invoice_date" :value="__('Invoice Date')" />
                            <x-input id="invoice_date" type="date" name="invoice_date" class="mt-1 block w-full" />
                        </div>
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
                        <div id="items-container" class="space-y-2">
                            <div class="grid grid-cols-3 gap-4 item-row">
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
                        <button type="button" id="add-item" class="mt-2 px-2 py-1 bg-blue-500 text-white rounded">+</button>
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

            const addBtn = document.getElementById('add-item');
            addBtn.addEventListener('click', () => {
                const container = document.getElementById('items-container');
                const index = container.children.length;
                const template = container.querySelector('.item-row').cloneNode(true);
                template.querySelectorAll('input').forEach(input => {
                    input.value = '';
                    input.name = input.name.replace(/\d+/, index);
                });
                template.querySelectorAll('select').forEach(select => {
                    select.selectedIndex = 0;
                    select.name = select.name.replace(/\d+/, index);
                });
                container.appendChild(template);
            });
        });
    </script>
</x-app-layout>
