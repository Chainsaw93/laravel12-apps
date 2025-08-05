<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Exchange Rates') }}</h2>
    </x-slot>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ route('exchange-rates.store') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <x-label for="currency" value="{{ __('Currency') }}" />
                            <select id="currency" name="currency" class="mt-1 block w-full rounded-md">
                                <option value="CUP">CUP</option>
                                <option value="USD">USD</option>
                                <option value="MLC">MLC</option>
                            </select>
                        </div>
                        <div>
                            <x-label for="rate_to_cup" value="{{ __('Rate to CUP') }}" />
                            <x-input id="rate_to_cup" name="rate_to_cup" type="number" step="0.000001" class="mt-1 block w-full" required />
                        </div>
                        <div>
                            <x-label for="effective_date" value="{{ __('Date') }}" />
                            <x-input id="effective_date" name="effective_date" type="date" class="mt-1 block w-full" required />
                        </div>
                    </div>
                    <x-button>{{ __('Save') }}</x-button>
                </form>
            </div>
            <div class="bg-white shadow sm:rounded-lg">
                @if($errors->any())
                    <div class="text-red-500 p-4">{{ $errors->first() }}</div>
                @endif
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Currency') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Rate') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Date') }}</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($rates as $rate)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $rate->currency }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $rate->rate_to_cup }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $rate->effective_date->toDateString() }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="space-y-2">
                                        <form method="POST" action="{{ route('exchange-rates.update', $rate) }}" class="flex space-x-2">
                                            @csrf
                                            @method('PUT')
                                            <x-input name="rate_to_cup" type="number" step="0.000001" value="{{ $rate->rate_to_cup }}" class="w-24" />
                                            <x-input name="effective_date" type="date" value="{{ $rate->effective_date->toDateString() }}" class="w-32" />
                                            <x-button>{{ __('Update') }}</x-button>
                                        </form>
                                        <form method="POST" action="{{ route('exchange-rates.destroy', $rate) }}" onsubmit="return confirm('{{ __('Are you sure?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <x-danger-button>{{ __('Delete') }}</x-danger-button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
