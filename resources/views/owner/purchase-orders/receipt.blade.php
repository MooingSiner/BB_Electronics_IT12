<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt PO-{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</title>
    @vite('resources/css/app.css')
    <style>
        body { font-family: 'Inter', sans-serif; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body class="bg-slate-100 py-10">

<div class="max-w-sm mx-auto bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

    <div class="text-center mb-4">
        <h1 class="font-bold text-slate-800">B&amp;B Electronics</h1>
        <p class="text-xs text-slate-400">Purchase Order Receipt</p>
    </div>

    <div class="border-t border-dashed border-slate-300 my-3"></div>

    <div class="text-xs text-slate-500 space-y-0.5 mb-3">
        <div class="flex justify-between"><span>Order #</span><span>PO-{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</span></div>
        <div class="flex justify-between"><span>Store</span><span>{{ $order->store }}</span></div>
        @if($order->invoice_number)
        <div class="flex justify-between"><span>Invoice #</span><span>{{ $order->invoice_number }}</span></div>
        @endif
        <div class="flex justify-between"><span>Order Date</span><span>{{ isset($order->order_date) ? \Carbon\Carbon::parse($order->order_date)->format('M d, Y') : '—' }}</span></div>
        <div class="flex justify-between"><span>Received Date</span><span>{{ isset($order->expected_date) ? \Carbon\Carbon::parse($order->expected_date)->format('M d, Y') : '—' }}</span></div>
    </div>

    <div class="border-t border-dashed border-slate-300 my-3"></div>

    <table class="w-full text-xs">
        @foreach($order->items as $item)
        <tr>
            <td class="py-1 text-slate-700">{{ $item->product_name }} &times;{{ $item->qty_ordered }}</td>
            <td class="py-1 text-right text-slate-500">{{ $item->qty_received }} recv</td>
            <td class="py-1 text-right text-slate-800">₱{{ number_format($item->unit_cost * $item->qty_ordered, 2) }}</td>
        </tr>
        @endforeach
    </table>

    <div class="border-t border-dashed border-slate-300 my-3"></div>

    <div class="flex justify-between font-bold text-sm text-slate-800">
        <span>TOTAL</span><span>₱{{ number_format($order->total_cost, 2) }}</span>
    </div>

    <div class="border-t border-dashed border-slate-300 my-3"></div>

    <p class="text-center text-xs text-slate-400">For record-keeping purposes only.</p>

    <button onclick="window.print()"
            class="no-print w-full mt-4 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-opacity"
            style="background-color:#363E48;">
        Print
    </button>
    <a href="{{ route('owner.purchase-orders.show', $order->id) }}"
       class="no-print block text-center mt-2 text-xs text-slate-500 hover:underline">
        Back to Order
    </a>

</div>

</body>
</html>
