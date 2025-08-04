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
</body>
</html>
