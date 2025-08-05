<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ExchangeRateController extends Controller
{
    public function index()
    {
        $rates = ExchangeRate::orderByDesc('effective_date')->get();
        return view('exchange_rates.index', compact('rates'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'currency' => 'required|string',
            'rate_to_cup' => 'required|numeric',
            'effective_date' => [
                'required',
                'date',
                Rule::unique('exchange_rates')->where(fn($q) => $q->where('currency', $request->currency)),
            ],
        ]);
        $data['user_id'] = Auth::id();
        ExchangeRate::create($data);
        return redirect()->route('exchange-rates.index');
    }

    public function update(Request $request, ExchangeRate $exchangeRate)
    {
        $data = $request->validate([
            'currency' => 'required|string',
            'rate_to_cup' => 'required|numeric',
            'effective_date' => [
                'required',
                'date',
                Rule::unique('exchange_rates')->where(fn($q) => $q->where('currency', $request->currency))->ignore($exchangeRate->id),
            ],
        ]);
        $exchangeRate->update($data);
        return redirect()->route('exchange-rates.index');
    }

    public function destroy(ExchangeRate $exchangeRate)
    {
        $inUse = $exchangeRate->purchases()->exists()
            || $exchangeRate->invoices()->exists()
            || $exchangeRate->stockMovements()->exists();

        if ($inUse) {
            return redirect()->route('exchange-rates.index')
                ->withErrors(__('This exchange rate is in use and cannot be deleted.'));
        }

        $exchangeRate->delete();

        return redirect()->route('exchange-rates.index');
    }
}
