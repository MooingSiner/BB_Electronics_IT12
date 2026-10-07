<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Slip #{{ $ret->return_id }}</title>
    @vite('resources/css/app.css')
    <style>
        body { font-family: 'Inter', sans-serif; }
        @media print {
            .no-print { display: none; }
            body { background: #fff !important; padding: 0 !important; }
            .receipt-card { box-shadow: none !important; border: 0 !important; border-radius: 0 !important; max-width: none !important; margin: 0 !important; }
            @page { margin: 8mm; }
        }
    </style>
</head>
<body class="bg-slate-100 py-10">

@php
    $resolved = $ret->status === \App\Enums\ReturnStatus::Resolved;
    $condition = ucwords(str_replace('_', ' ', $ret->condition->value));
    $resolution = ucwords(str_replace('_', ' ', $ret->resolution->value));
@endphp

<div class="receipt-card max-w-md mx-auto bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

    <div class="text-center mb-4">
        <h1 class="font-bold text-slate-800">B&amp;B Electronics</h1>
        <p class="text-xs text-slate-400">Customer Return Slip</p>
    </div>

    <div class="border-t border-dashed border-slate-300 my-3"></div>

    <div class="text-xs text-slate-500 space-y-0.5 mb-3">
        <div class="flex justify-between"><span>Return #</span><span class="font-semibold text-slate-700">{{ $ret->return_id }}</span></div>
        <div class="flex justify-between"><span>Date</span><span>{{ $ret->return_date->format('M d, Y g:i A') }}</span></div>
        <div class="flex justify-between"><span>Original sale</span><span>{{ $ret->sale?->code() ?? '—' }}</span></div>
        <div class="flex justify-between"><span>Status</span><span class="font-semibold {{ $resolved ? 'text-green-600' : 'text-amber-600' }}">{{ $resolved ? 'Resolved' : 'Pending owner approval' }}</span></div>
    </div>

    <div class="border-t border-dashed border-slate-300 my-3"></div>

    <div class="text-xs space-y-1.5">
        <div class="flex justify-between gap-4"><span class="text-slate-500">Returned item</span><span class="text-right text-slate-800">{{ $ret->product->product_name ?? '—' }} &times;{{ $ret->quantity }}</span></div>
        <div class="flex justify-between gap-4"><span class="text-slate-500">Condition</span><span class="text-right text-slate-700">{{ $condition }}</span></div>
        <div class="flex justify-between gap-4"><span class="text-slate-500">Reason</span><span class="text-right text-slate-700">{{ $ret->reason }}</span></div>
        <div class="flex justify-between gap-4"><span class="text-slate-500">Resolution</span><span class="text-right font-semibold text-slate-800">{{ $ret->isExchange() ? 'Exchange' : $resolution }}</span></div>

        @if($ret->isExchange())
            <div class="flex justify-between gap-4"><span class="text-slate-500">Exchanged for</span><span class="text-right text-slate-800">{{ $ret->replacementProduct->product_name ?? '—' }} &times;{{ $ret->quantity }}</span></div>
            <div class="flex justify-between gap-4"><span class="text-slate-500">Price difference</span><span class="text-right text-slate-800">{{ $ret->exchangeDifferenceNote() }}</span></div>
        @elseif($ret->replacementUnits() > 0)
            <div class="flex justify-between gap-4"><span class="text-slate-500">Replacement</span><span class="text-right text-slate-800">Same product &times;{{ $ret->quantity }}</span></div>
        @elseif($ret->resolution === \App\Enums\ReturnResolution::Refund)
            <div class="flex justify-between gap-4"><span class="text-slate-500">{{ $resolved ? 'Refund' : 'Refund (once approved)' }}</span><span class="text-right font-bold text-slate-800">₱{{ number_format($ret->refundAmount(), 2) }}</span></div>
        @endif
    </div>

    <div class="border-t border-dashed border-slate-300 my-4"></div>

    <div class="grid grid-cols-2 gap-6 pt-6 text-center text-[10px] text-slate-400">
        <div><div class="border-t border-slate-400 pt-1">Customer signature</div></div>
        <div><div class="border-t border-slate-400 pt-1">Staff signature</div></div>
    </div>

    <p class="text-center text-xs text-slate-400 mt-5">Please keep this slip as proof of your return.</p>

    <button onclick="window.print()"
            class="no-print w-full mt-4 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-opacity"
            style="background-color:#363E48;">
        Print
    </button>
    <a href="{{ $backUrl }}"
       class="no-print block text-center mt-2 text-xs text-slate-500 hover:underline">
        Back to Return
    </a>

</div>

</body>
</html>
