<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Transfer Stock') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('transfers.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-label for="product_id" :value="__('Product')" />
                        <select id="product_id" name="product_id" class="mt-1 block w-full rounded-md" required>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-label for="from_warehouse_id" :value="__('From Warehouse')" />
                            <select id="from_warehouse_id" name="from_warehouse_id" class="mt-1 block w-full rounded-md" required>
                                @foreach($warehouses as $w)
                                    <option value="{{ $w->id }}">{{ $w->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-label for="to_warehouse_id" :value="__('To Warehouse')" />
                            <select id="to_warehouse_id" name="to_warehouse_id" class="mt-1 block w-full rounded-md" required>
                                @foreach($warehouses as $w)
                                    <option value="{{ $w->id }}">{{ $w->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <x-label for="quantity" :value="__('Quantity')" />
                        <x-input id="quantity" name="quantity" type="number" min="1" class="mt-1 block w-full" required />
                    </div>
                    <x-button>{{ __('Transfer') }}</x-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
