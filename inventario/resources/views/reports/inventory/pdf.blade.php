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
                <th>{{ __('Date') }}</th>
                <th>{{ __('Inputs') }}</th>
                <th>{{ __('Outputs') }}</th>
                <th>{{ __('Input Value') }}</th>
                <th>{{ __('Output Value') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    <td>{{ $row->date }}</td>
                    <td>{{ $row->inputs }}</td>
                    <td>{{ $row->outputs }}</td>
                    <td>{{ number_format($row->input_value,2) }}</td>
                    <td>{{ number_format($row->output_value,2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
