<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Label — {{ $item->name }}</title>
    @include('partials.barcode-label-styles')
</head>
<body>
    <form class="toolbar" method="GET" action="{{ route('owner.inventory.label', $item->id) }}">
        <a href="{{ route('owner.inventory.show', $item->id) }}">&larr; Back</a>
        <a href="{{ route('owner.inventory.labels') }}">Print many products</a>
        <label>Copies <input type="number" name="copies" min="1" max="60" value="{{ $copies }}"></label>
        <label>Size
            <select name="size">
                @foreach(['xxsmall' => 'XX Small (5 per row)', 'xsmall' => 'X Small (4 per row)', 'small' => 'Small (3 per row)', 'medium' => 'Medium (2 per row)', 'large' => 'Large (1 per row)'] as $value => $text)
                    <option value="{{ $value }}" @selected($size === $value)>{{ $text }}</option>
                @endforeach
            </select>
        </label>
        <button type="submit">Update</button>
        <button type="button" onclick="window.print()">Print</button>
    </form>

    <div class="sheet">
        @for($i = 0; $i < $copies; $i++)
            @include('partials.barcode-label')
        @endfor
    </div>
</body>
</html>
