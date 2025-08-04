<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Client') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('clients.update', $client) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <x-label for="name" :value="__('Name')" />
                        <x-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ $client->name }}" required />
                    </div>
                    <div>
                        <x-label for="email" :value="__('Email')" />
                        <x-input id="email" name="email" type="email" class="mt-1 block w-full" value="{{ $client->email }}" />
                    </div>
                    <div>
                        <x-label for="phone" :value="__('Phone')" />
                        <x-input id="phone" name="phone" type="text" class="mt-1 block w-full" value="{{ $client->phone }}" />
                    </div>
                    <div>
                        <x-label for="address" :value="__('Address')" />
                        <x-input id="address" name="address" type="text" class="mt-1 block w-full" value="{{ $client->address }}" />
                    </div>
                    <x-button>{{ __('Update') }}</x-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
