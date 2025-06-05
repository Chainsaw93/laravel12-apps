<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Warehouse') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('warehouses.update', $warehouse) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <x-label for="name" :value="__('Name')" />
                        <x-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $warehouse->name) }}" required />
                    </div>
                    <x-button>{{ __('Save') }}</x-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
