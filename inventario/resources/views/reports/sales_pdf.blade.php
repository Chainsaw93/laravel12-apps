<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('Sales Report') }}</title>
</head>
<body>
    <h1>{{ __('Sales Report') }}</h1>
    <table style="width:100%;border-collapse:collapse;" border="1">
        <thead>
            <tr>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Product') }}</th>
                <th>{{ __('Warehouse') }}</th>
                <th>{{ __('Quantity') }}</th>
                <th>{{ __('Price') }}</th>
                <th>{{ __('Total') }}</th>
                <th>{{ __('Payment') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
                <tr>
                    <td>{{ $sale->created_at->toDateString() }}</td>
                    <td>{{ $sale->product->name }}</td>
                    <td>{{ $sale->warehouse->name }}</td>
                    <td>{{ $sale->quantity }}</td>
                    <td>{{ number_format($sale->price_per_unit,2) }}</td>
                    <td>{{ number_format($sale->quantity * $sale->price_per_unit,2) }}</td>
                    <td>{{ $sale->payment_method->name ?? $sale->payment_method }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
