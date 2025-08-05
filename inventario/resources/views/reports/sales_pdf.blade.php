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
                <th>{{ __('Price CUP') }}</th>
                <th>{{ __('Total CUP') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
                <tr>
                    <td>{{ $sale->invoice->created_at->toDateString() }}</td>
                    <td>{{ $sale->product->name }}</td>
                    <td>{{ $sale->invoice->warehouse->name }}</td>
                    <td>{{ $sale->quantity }}</td>
                    <td>{{ number_format($sale->currency_price,2) }} {{ $sale->invoice->currency }}</td>
                    <td>{{ number_format($sale->price,2) }}</td>
                    <td>{{ number_format($sale->total,2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
