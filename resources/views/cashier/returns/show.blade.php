@extends('layouts.cashier')

@section('title', 'Return #' . $ret->return_id)

@php $activeNav = 'returns'; @endphp

@section('breadcrumb')
<a href="{{ route('cashier.returns.index') }}" class="hover:underline" style="color:#363E48;">Returns &amp; Warranties</a>
<span class="mx-1 text-slate-400">/</span>
<span class="text-slate-600">#{{ $ret->return_id }}</span>
@endsection

@section('content')
<div class="p-6 max-w-xl mx-auto space-y-6">

    <a href="{{ route('cashier.returns.index') }}"
       class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
        Back to Returns &amp; Warranties
    </a>

    {{-- Header Card --}}
    <div class="rounded-2xl shadow-sm p-6" style="background: linear-gradient(135deg, #363E48 0%, #454f5c 100%);">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl flex items-center justify-center flex-shrink-0 accent-bg">
                <svg class="w-7 h-7 accent-text" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-white">Return #{{ $ret->return_id }}</h1>
                <p class="text-sm text-white/60 mt-0.5">{{ \Carbon\Carbon::parse($ret->return_date)->format('F d, Y') }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm divide-y divide-slate-100 text-sm">
        <div class="flex justify-between items-center px-5 py-3.5">
            <span class="text-slate-500">Transaction</span>
            @if($ret->sale_id)
            <a href="{{ route('cashier.sales.show', $ret->sale_id) }}" class="font-medium hover:underline" style="color:#363E48;">{{ $ret->sale?->code() }}</a>
            @else
            <span class="text-slate-400">—</span>
            @endif
        </div>
        <div class="flex justify-between items-center px-5 py-3.5">
            <span class="text-slate-500">Product</span>
            <span class="font-medium text-slate-800">{{ $ret->product->product_name ?? '—' }}</span>
        </div>
        <div class="flex justify-between items-center px-5 py-3.5">
            <span class="text-slate-500">Quantity</span>
            <span class="text-slate-700">{{ $ret->quantity }}</span>
        </div>
        <div class="flex justify-between items-center px-5 py-3.5">
            <span class="text-slate-500">Reason</span>
            <span class="text-slate-700">{{ $ret->reason }}</span>
        </div>
        <div class="flex justify-between items-center px-5 py-3.5">
            <span class="text-slate-500">Condition</span>
            <span class="text-slate-700">{{ ucfirst(str_replace('_', ' ', $ret->condition->value)) }}</span>
        </div>
        <div class="flex justify-between items-center px-5 py-3.5">
            <span class="text-slate-500">Resolution</span>
            <span class="text-slate-700">{{ ucfirst($ret->resolution->value) }}</span>
        </div>
        <div class="flex justify-between items-center px-5 py-3.5">
            <span class="text-slate-500">Status</span>
            @if($ret->status->value === 'open')
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Pending</span>
            @else
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Resolved</span>
            @endif
        </div>
    </div>

</div>
@endsection
