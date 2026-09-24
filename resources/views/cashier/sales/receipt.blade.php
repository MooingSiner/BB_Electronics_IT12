<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt #{{ $txn->id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
        <p class="text-xs text-slate-400">Sales &amp; Inventory System</p>
    </div>

    <div class="border-t border-dashed border-slate-300 my-3"></div>

    <div class="text-xs text-slate-500 space-y-0.5 mb-3">
        <div class="flex justify-between"><span>Transaction #</span><span>{{ $txn->id }}</span></div>
        <div class="flex justify-between"><span>Date</span><span>{{ \Carbon\Carbon::parse($txn->created_at)->format('M d, Y g:i A') }}</span></div>
        <div class="flex justify-between"><span>Cashier</span><span>{{ $txn->processed_by }}</span></div>
    </div>

    <div class="border-t border-dashed border-slate-300 my-3"></div>

    <table class="w-full text-xs">
        @foreach($txn->items as $item)
        <tr>
            <td class="py-1 text-slate-700">{{ $item->name }} &times;{{ $item->quantity }}</td>
            <td class="py-1 text-right text-slate-800">₱{{ number_format($item->subtotal, 2) }}</td>
        </tr>
        @endforeach
    </table>

    <div class="border-t border-dashed border-slate-300 my-3"></div>

    <div class="text-xs space-y-1">
        <div class="flex justify-between text-slate-600"><span>Subtotal</span><span>₱{{ number_format($txn->subtotal, 2) }}</span></div>
        @if($txn->discount_amount > 0)
        <div class="flex justify-between text-green-600"><span>Discount</span><span>−₱{{ number_format($txn->discount_amount, 2) }}</span></div>
        @endif
        <div class="flex justify-between font-bold text-sm text-slate-800 pt-1 border-t border-slate-100"><span>TOTAL</span><span>₱{{ number_format($txn->total, 2) }}</span></div>
        <div class="flex justify-between text-slate-500"><span>{{ $txn->payment_method }}</span><span>₱{{ number_format($txn->amount_paid, 2) }}</span></div>
        @if($txn->payment_method === 'Cash')
        <div class="flex justify-between text-slate-500"><span>Change</span><span>₱{{ number_format($txn->change_amount, 2) }}</span></div>
        @endif
    </div>

    <div class="border-t border-dashed border-slate-300 my-3"></div>

    <p class="text-center text-xs text-slate-400">Thank you for shopping with us!</p>

    <button onclick="window.print()"
            class="no-print w-full mt-4 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-opacity"
            style="background-color:#363E48;">
        Print
    </button>
    <a href="{{ route('cashier.sales.show', $txn->id) }}"
       class="no-print block text-center mt-2 text-xs text-slate-500 hover:underline">
        Back to Transaction
    </a>

</div>

</body>
</html>
