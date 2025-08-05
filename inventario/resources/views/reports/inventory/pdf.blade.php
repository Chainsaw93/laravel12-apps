<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('Inventory Report') }}</title>
</head>
<body>
    <h1>{{ __('Inventory Report') }}</h1>
    @if($chart)
        <img src="{{ $chart }}" style="max-width:100%;">
    @endif
    <table style="width:100%;border-collapse:collapse;" border="1">
        <thead>
            <tr>
                <th>{{ __('Type') }}</th>
                <th>{{ __('Quantity') }}</th>
                <th>{{ __('CUP Value') }}</th>
                <th>{{ __('USD Value') }}</th>
                <th>{{ __('MLC Value') }}</th>
                <th>{{ __('Total CUP') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    <td>{{ $row->type }}</td>
                    <td>{{ $row->quantity }}</td>
                    <td>{{ number_format($row->cup_value,2) }}</td>
                    <td>{{ number_format($row->usd_value,2) }}</td>
                    <td>{{ number_format($row->mlc_value,2) }}</td>
                    <td>{{ number_format($row->total_cup,2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @if(isset($valuation) && $valuation->isNotEmpty())
    <h2>{{ __('Valuation by Warehouse') }}</h2>
    <table style="width:100%;border-collapse:collapse;" border="1">
        <thead>
            <tr>
                <th>{{ __('Warehouse') }}</th>
                <th>{{ __('Inventory Value') }}</th>
                <th>{{ __('Average Cost') }}</th>
                <th>{{ __('Profit Margin') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($valuation as $row)
                <tr>
                    <td>{{ $row['warehouse'] }}</td>
                    <td>{{ number_format($row['inventory_value'],2) }}</td>
                    <td>{{ number_format($row['average_cost'],2) }}</td>
                    <td>{{ number_format($row['profit_margin'] * 100,2) }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</body>
</html>
