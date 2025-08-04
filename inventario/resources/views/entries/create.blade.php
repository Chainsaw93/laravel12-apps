<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Stock Entry') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('entries.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-label for="product_id" :value="__('Product')" />
                        <select id="product_id" name="product_id" class="mt-1 block w-full rounded-md" required>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
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
                        <x-label for="quantity" :value="__('Quantity')" />
                        <x-input id="quantity" name="quantity" type="number" min="1" class="mt-1 block w-full" required />
                    </div>
                    <div>
                        <x-label for="purchase_price" :value="__('Purchase Price')" />
                        <x-input id="purchase_price" name="purchase_price" type="number" step="0.01" min="0" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <x-label for="description" :value="__('Description')" />
                        <textarea id="description" name="description" class="mt-1 block w-full rounded-md"></textarea>
                    </div>
                    <x-button>{{ __('Save') }}</x-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
