@extends('layouts.cashier')

@section('title', 'Transaction ' . $txn->code)

@php $activeNav = 'sales'; @endphp

@section('breadcrumb')
<a href="{{ route('cashier.sales.index') }}" class="hover:underline" style="color:#363E48;">Sales Transactions</a>
<span class="mx-1 text-slate-400">/</span>
<span class="text-slate-600">{{ $txn->code }}</span>
@endsection

@section('content')
<div class="p-6 max-w-3xl mx-auto space-y-6">

    <a href="{{ route('cashier.sales.index') }}"
       class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
        Back to Sales Transactions
    </a>

    {{-- Header Card --}}
    <div class="rounded-2xl shadow-sm p-6" style="background: linear-gradient(135deg, #363E48 0%, #454f5c 100%);">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center flex-shrink-0 accent-bg">
                    <svg class="w-7 h-7 accent-text" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white">Transaction {{ $txn->code }}</h1>
                    <p class="text-sm text-white/60 mt-0.5">{{ \Carbon\Carbon::parse($txn->created_at)->format('F d, Y \a\t g:i A') }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('cashier.sales.receipt', $txn->id) }}"
                   class="px-3 py-1.5 text-xs font-medium rounded-md bg-white/10 text-white hover:bg-white/20 transition">
                    Print Receipt
                </a>
                @if($txn->status === 'Completed')
                <a href="{{ route('cashier.returns.process', ['transaction_id' => $txn->id]) }}"
                   class="px-3 py-1.5 text-xs font-medium rounded-md bg-white/10 text-white hover:bg-white/20 transition">
                    Process Return
                </a>
                @endif
            </div>
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
