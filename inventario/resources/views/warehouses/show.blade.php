<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $warehouse->name }} {{ __('Stock') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="GET" class="mb-4 flex space-x-4">
                    <div>
                        <select name="category_id" class="rounded-md">
                            <option value="">{{ __('All Categories') }}</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}" @selected(request('category_id') == $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <select name="product_id" class="rounded-md">
                            <option value="">{{ __('All Products') }}</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" @selected(request('product_id') == $p->id)>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-button>{{ __('Filter') }}</x-button>
                </form>
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left">{{ __('Product') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('Quantity') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stocks as $stock)
                            <tr>
                                <td class="border px-4 py-2">{{ $stock->product->name }}</td>
                                <td class="border px-4 py-2">{{ $stock->quantity }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
