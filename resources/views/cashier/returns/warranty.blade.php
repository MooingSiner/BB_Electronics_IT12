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

    <a href="{{ route('cashier.returns.index', ['tab' => 'warranty']) }}"
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
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-white">Warranty #{{ $warranty->warranty_id }}</h1>
                <p class="text-sm text-white/60 mt-0.5">{{ $warranty->saleItem->product->product_name ?? '—' }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm divide-y divide-slate-100 text-sm">
        <div class="flex justify-between items-center px-5 py-3.5">
            <span class="text-slate-500">Customer</span>
            <span class="text-slate-700">{{ $warranty->customer_name }}</span>
        </div>
        <div class="flex justify-between items-center px-5 py-3.5">
            <span class="text-slate-500">Contact Number</span>
            <span class="text-slate-700">{{ $warranty->contact_number ?? '—' }}</span>
        </div>
        <div class="flex justify-between items-center px-5 py-3.5">
            <span class="text-slate-500">Warranty Start</span>
            <span class="text-slate-700">{{ \Carbon\Carbon::parse($warranty->start_date)->format('M d, Y') }}</span>
        </div>
        <div class="flex justify-between items-center px-5 py-3.5">
            <span class="text-slate-500">Warranty Ends</span>
            <span class="text-slate-700">{{ \Carbon\Carbon::parse($warranty->end_date)->format('M d, Y') }}</span>
        </div>
        <div class="flex justify-between items-center px-5 py-3.5">
            <span class="text-slate-500">Claim Status</span>
            <span class="text-slate-700">{{ ucfirst(str_replace('_', ' ', $warranty->claim_status->value)) }}</span>
        </div>
        @if($warranty->claim_date)
        <div class="flex justify-between items-center px-5 py-3.5">
            <span class="text-slate-500">Claim Date</span>
            <span class="text-slate-700">{{ \Carbon\Carbon::parse($warranty->claim_date)->format('M d, Y') }}</span>
        </div>
        @endif
        <div class="flex justify-between items-center px-5 py-3.5">
            <span class="text-slate-500">Outcome</span>
            <span class="text-slate-700">{{ $warranty->outcome->value === 'n_a' ? 'N/A' : ucfirst(str_replace('_', ' ', $warranty->outcome->value)) }}</span>
        </div>
    </div>

</div>
@endsection
