<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Roles') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <a href="{{ route('roles.create') }}" class="mb-4 inline-block bg-blue-500 text-white px-4 py-2 rounded">{{ __('New') }}</a>
                <a href="{{ route('roles.assign_form') }}" class="mb-4 inline-block bg-green-500 text-white px-4 py-2 rounded">{{ __('Assign') }}</a>
                <table class="w-full text-left border">
                    <thead>
                        <tr>
                            <th class="border px-2 py-1">{{ __('Name') }}</th>
                            <th class="border px-2 py-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $role)
                            <tr>
                                <td class="border px-2 py-1">{{ $role->name }}</td>
                                <td class="border px-2 py-1">
                                    <a href="{{ route('roles.edit', $role) }}" class="text-blue-600">{{ __('Edit') }}</a>
                                    <form method="POST" action="{{ route('roles.destroy', $role) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600" onclick="return confirm('Are you sure?')">{{ __('Delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
