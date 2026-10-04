<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Label — {{ $item->name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: ui-sans-serif, system-ui, sans-serif; color: #111; background: #f1f5f9; }
        .toolbar { display: flex; gap: 12px; align-items: center; padding: 12px 16px; background: #363E48; color: #fff; font-size: 14px; }
        .toolbar a, .toolbar button { color: #fff; background: rgba(255,255,255,.15); border: 0; border-radius: 6px; padding: 6px 12px; font-size: 13px; text-decoration: none; cursor: pointer; }
        .toolbar input { width: 64px; padding: 4px 6px; border-radius: 4px; border: 0; }
        .sheet { display: flex; flex-wrap: wrap; gap: 8px; padding: 16px; }
        .label { width: 62mm; padding: 3mm; background: #fff; border: 1px dashed #94a3b8; text-align: center; page-break-inside: avoid; break-inside: avoid; }
        .label .name { font-size: 11px; font-weight: 600; line-height: 1.2; max-height: 2.4em; overflow: hidden; }
        .label svg { display: block; margin: 2mm auto 0; max-width: 100%; height: auto; }
        .label .code { font-family: ui-monospace, monospace; font-size: 11px; letter-spacing: .12em; margin-top: 1mm; }
        .label .price { font-size: 13px; font-weight: 700; margin-top: 1mm; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .sheet { padding: 0; gap: 0; }
            .label { border: 1px dashed #bbb; }
            @page { margin: 8mm; }
        }
    </style>
</head>
<body>
    <form class="toolbar" method="GET" action="{{ route('owner.inventory.label', $item->id) }}">
        <a href="{{ route('owner.inventory.show', $item->id) }}">&larr; Back</a>
        <label>Copies <input type="number" name="copies" min="1" max="60" value="{{ $copies }}"></label>
        <button type="submit">Update</button>
        <button type="button" onclick="window.print()">Print</button>
    </form>

    <div class="sheet">
        @for($i = 0; $i < $copies; $i++)
            <div class="label">
                <div class="name">{{ $item->name }}</div>
                {!! \App\Support\Code128::svg($item->barcode, 50) !!}
                <div class="code">{{ $item->barcode }}</div>
                <div class="price">₱{{ number_format($item->unit_price, 2) }}</div>
            </div>
        @endfor
    </div>
</body>
</html>
