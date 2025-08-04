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
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    <td>{{ $row->date }}</td>
                    <td>{{ $row->inputs }}</td>
                    <td>{{ $row->outputs }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
