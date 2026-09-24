@extends('layouts.cashier')

@section('title', 'Warranty #' . $warranty->warranty_id)

@php $activeNav = 'returns'; @endphp

@section('breadcrumb')
<a href="{{ route('cashier.returns.index', ['tab' => 'warranty']) }}" class="hover:underline" style="color:#363E48;">Returns &amp; Warranties</a>
<span class="mx-1 text-slate-400">/</span>
<span class="text-slate-600">#{{ $warranty->warranty_id }}</span>
@endsection

@section('content')
<div class="p-6 max-w-xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-slate-800">Warranty #{{ $warranty->warranty_id }}</h1>
        <p class="text-sm text-slate-500 mt-1">{{ $warranty->saleItem->product->product_name ?? '—' }}</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-3 text-sm">
        <div class="flex justify-between">
            <span class="text-slate-500">Customer</span>
            <span class="text-slate-700">{{ $warranty->customer_name }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-500">Contact Number</span>
            <span class="text-slate-700">{{ $warranty->contact_number ?? '—' }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-500">Warranty Start</span>
            <span class="text-slate-700">{{ \Carbon\Carbon::parse($warranty->start_date)->format('M d, Y') }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-500">Warranty Ends</span>
            <span class="text-slate-700">{{ \Carbon\Carbon::parse($warranty->end_date)->format('M d, Y') }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-500">Claim Status</span>
            <span class="text-slate-700">{{ ucfirst(str_replace('_', ' ', $warranty->claim_status->value)) }}</span>
        </div>
        @if($warranty->claim_date)
        <div class="flex justify-between">
            <span class="text-slate-500">Claim Date</span>
            <span class="text-slate-700">{{ \Carbon\Carbon::parse($warranty->claim_date)->format('M d, Y') }}</span>
        </div>
        @endif
        <div class="flex justify-between">
            <span class="text-slate-500">Outcome</span>
            <span class="text-slate-700">{{ $warranty->outcome->value === 'n_a' ? 'N/A' : ucfirst(str_replace('_', ' ', $warranty->outcome->value)) }}</span>
        </div>
    </div>

</div>
@endsection
