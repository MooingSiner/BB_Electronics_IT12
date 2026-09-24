@extends('layouts.cashier')

@section('title', 'Transaction #' . $txn->id)

@php $activeNav = 'sales'; @endphp

@section('breadcrumb')
<a href="{{ route('cashier.sales.index') }}" class="hover:underline" style="color:#363E48;">Sales Transactions</a>
<span class="mx-1 text-slate-400">/</span>
<span class="text-slate-600">#{{ $txn->id }}</span>
@endsection

@section('content')
<div class="p-6 max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Transaction #{{ $txn->id }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ \Carbon\Carbon::parse($txn->created_at)->format('F d, Y \a\t g:i A') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('cashier.sales.receipt', $txn->id) }}"
               class="px-4 py-2 text-sm font-medium border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                Print Receipt
            </a>
            @if($txn->status === 'Completed')
            <a href="{{ route('cashier.returns.process', ['transaction_id' => $txn->id]) }}"
               class="px-4 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-opacity"
               style="background-color:#363E48;">
                Process Return
            </a>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-800">Items</h2>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Product</th>
                    <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Qty</th>
                    <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Unit Price</th>
                    <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($txn->items as $item)
                <tr>
                    <td class="px-6 py-3 text-slate-800">{{ $item->name }}</td>
                    <td class="px-6 py-3 text-right text-slate-600">{{ $item->quantity }}</td>
                    <td class="px-6 py-3 text-right text-slate-600">₱{{ number_format($item->unit_price, 2) }}</td>
                    <td class="px-6 py-3 text-right font-medium text-slate-800">₱{{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-1.5 text-sm">
        <div class="flex justify-between text-slate-600">
            <span>Subtotal</span>
            <span>₱{{ number_format($txn->subtotal, 2) }}</span>
        </div>
        @if($txn->discount_amount > 0)
        <div class="flex justify-between text-green-600">
            <span>Discount</span>
            <span>−₱{{ number_format($txn->discount_amount, 2) }}</span>
        </div>
        @endif
        <div class="flex justify-between font-bold text-base text-slate-800 pt-1 border-t border-slate-100">
            <span>Total</span>
            <span>₱{{ number_format($txn->total, 2) }}</span>
        </div>
        <div class="flex justify-between text-slate-500 pt-2">
            <span>Payment Method</span>
            <span>{{ $txn->payment_method }}</span>
        </div>
        @if($txn->payment_method === 'Cash')
        <div class="flex justify-between text-slate-500">
            <span>Amount Received</span>
            <span>₱{{ number_format($txn->amount_paid, 2) }}</span>
        </div>
        <div class="flex justify-between text-slate-500">
            <span>Change</span>
            <span>₱{{ number_format($txn->change_amount, 2) }}</span>
        </div>
        @endif
        <div class="flex justify-between text-slate-500">
            <span>Processed By</span>
            <span>{{ $txn->processed_by }}</span>
        </div>
        <div class="flex justify-between text-slate-500">
            <span>Status</span>
            <span>{{ $txn->status }}</span>
        </div>
    </div>

</div>
@endsection
