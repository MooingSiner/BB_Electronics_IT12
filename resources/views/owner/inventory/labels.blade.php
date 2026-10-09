<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Barcode labels</title>
    @include('partials.barcode-label-styles')
</head>
<body>
    <form class="toolbar" method="GET" action="{{ route('owner.inventory.labels') }}">
        <a href="{{ route('owner.inventory.index') }}">&larr; Inventory</a>
        <a href="{{ route('owner.inventory.labels', ['size' => $size]) }}">Choose different products</a>
        @foreach($items as $id => $copies)
            <input type="hidden" name="items[{{ $id }}]" value="{{ $copies }}">
        @endforeach
        <label>Size
            <select name="size">
                @foreach(['xxsmall' => 'XX Small (5 per row)', 'xsmall' => 'X Small (4 per row)', 'small' => 'Small (3 per row)', 'medium' => 'Medium (2 per row)', 'large' => 'Large (1 per row)'] as $value => $text)
                    <option value="{{ $value }}" @selected($size === $value)>{{ $text }}</option>
                @endforeach
            </select>
        </label>
        <button type="submit">Update</button>
        <button type="button" onclick="window.print()">Print {{ $labels->sum('copies') }} labels</button>
    </form>

    <div class="sheet">
        @foreach($labels as $item)
            @for($i = 0; $i < $item->copies; $i++)
                @include('partials.barcode-label')
            @endfor
        @endforeach
    </div>
</body>
</html>
