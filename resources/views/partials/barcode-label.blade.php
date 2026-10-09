<div class="label">
    <div class="name">{{ $item->name }}</div>
    {!! \App\Support\Code128::svg($item->barcode, 50) !!}
    <div class="code">{{ $item->barcode }}</div>
    <div class="price">₱{{ number_format($item->unit_price, 2) }}</div>
    <div class="code">{{ $item->cost_code }}</div>
</div>
