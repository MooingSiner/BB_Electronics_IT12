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

    <div>
        <h1 class="text-2xl font-bold text-slate-800">Return #{{ $ret->return_id }}</h1>
        <p class="text-sm text-slate-500 mt-1">{{ \Carbon\Carbon::parse($ret->return_date)->format('F d, Y') }}</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-3 text-sm">
        <div class="flex justify-between">
            <span class="text-slate-500">Transaction</span>
            @if($ret->sale_id)
            <a href="{{ route('cashier.sales.show', $ret->sale_id) }}" class="font-medium hover:underline" style="color:#363E48;">{{ $ret->sale?->code() }}</a>
            @else
            <span class="text-slate-400">—</span>
            @endif
        </div>
        <div class="flex justify-between">
            <span class="text-slate-500">Product</span>
            <span class="font-medium text-slate-800">{{ $ret->product->product_name ?? '—' }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-500">Quantity</span>
            <span class="text-slate-700">{{ $ret->quantity }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-500">Reason</span>
            <span class="text-slate-700">{{ $ret->reason }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-500">Condition</span>
            <span class="text-slate-700">{{ ucfirst(str_replace('_', ' ', $ret->condition->value)) }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-500">Resolution</span>
            <span class="text-slate-700">{{ ucfirst($ret->resolution->value) }}</span>
        </div>
        <div class="flex justify-between">
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
