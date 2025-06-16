<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('New Sale') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('sales.store') }}" class="space-y-4">
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
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-label for="quantity" :value="__('Quantity')" />
                            <x-input id="quantity" name="quantity" type="number" min="1" class="mt-1 block w-full" required />
                        </div>
                        <div>
                            <x-label for="price_per_unit" :value="__('Price per unit')" />
                            <x-input id="price_per_unit" name="price_per_unit" type="number" step="0.01" class="mt-1 block w-full" required />
                        </div>
                    </div>
                    <div>
                        <x-label for="payment_method" :value="__('Payment Method')" />
                        <select id="payment_method" name="payment_method" class="mt-1 block w-full rounded-md">
                            @foreach(\App\Enums\PaymentMethod::cases() as $method)
                                <option value="{{ $method->value }}">{{ $method->value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-button>{{ __('Save') }}</x-button>
                </form>
            </div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Add Sale') }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('sales.store') }}" class="bg-white shadow sm:rounded-lg p-6 space-y-4">
                @csrf
                <div>
                    <label for="product_id" class="block text-sm font-medium text-gray-700">Product</label>
                    <select id="product_id" name="product_id" class="mt-1 block w-full rounded-md border-gray-300" required>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="warehouse_id" class="block text-sm font-medium text-gray-700">Warehouse</label>
                    <select id="warehouse_id" name="warehouse_id" class="mt-1 block w-full rounded-md border-gray-300" required>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                    <input type="number" id="quantity" name="quantity" class="mt-1 block w-full rounded-md border-gray-300" min="1" required>
                </div>
                <div>
                    <label for="price_per_unit" class="block text-sm font-medium text-gray-700">Price Per Unit</label>
                    <input type="number" step="0.01" id="price_per_unit" name="price_per_unit" class="mt-1 block w-full rounded-md border-gray-300" required>
                </div>
                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method</label>
                    <select id="payment_method" name="payment_method" class="mt-1 block w-full rounded-md border-gray-300" required>
                        @foreach ($paymentMethods as $method)
                            <option value="{{ $method->value }}">{{ $method->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
