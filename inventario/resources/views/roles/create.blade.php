<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Create Role') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('roles.store') }}">
                    @csrf
                    <div>
                        <label>{{ __('Name') }}</label>
                        <input type="text" name="name" class="border rounded w-full" required>
                    </div>
                    <button type="submit" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded">{{ __('Save') }}</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
