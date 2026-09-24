@extends('layouts.cashier')

@section('title', $item->name)

@php $activeNav = 'inventory'; @endphp

@section('breadcrumb')
<a href="{{ route('cashier.inventory.index') }}" class="hover:underline" style="color:#363E48;">Inventory</a>
<span class="mx-1 text-slate-400">/</span>
<span class="text-slate-600">{{ $item->name }}</span>
@endsection

@section('content')
<div class="p-6 max-w-xl mx-auto space-y-6">

    {{-- Back Link --}}
    <a href="{{ route('cashier.inventory.index') }}"
       class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
        Back to Inventory
    </a>

    {{-- Header Card --}}
    <div class="rounded-2xl shadow-sm p-6" style="background: linear-gradient(135deg, #363E48 0%, #454f5c 100%);">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl flex items-center justify-center flex-shrink-0 accent-bg">
                <svg class="w-7 h-7 accent-text" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" />
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-white">{{ $item->name }}</h1>
                <p class="text-sm text-white/60 mt-0.5 font-mono">{{ $item->code }} &middot; {{ $item->category }}</p>
            </div>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1">Unit Price</p>
            <p class="text-2xl font-bold text-slate-800">₱{{ number_format($item->price, 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1">Current Stock</p>
            <p class="text-2xl font-bold {{ $item->stock == 0 ? 'text-red-600' : ($item->stock <= $item->reorder_level ? 'text-amber-600' : 'text-slate-800') }}">
                {{ $item->stock }}
            </p>
            <div class="w-full h-1.5 bg-slate-100 rounded-full mt-2 overflow-hidden">
                @php
                    $ceiling = max($item->reorder_level * 3, $item->stock, 1);
                    $pct = min(100, round(($item->stock / $ceiling) * 100));
                @endphp
                <div class="h-full rounded-full {{ $item->stock == 0 ? 'bg-red-500' : ($item->stock <= $item->reorder_level ? 'bg-amber-500' : 'bg-green-500') }}"
                     style="width: {{ $pct }}%"></div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm divide-y divide-slate-100 text-sm">
        <div class="flex justify-between items-center px-5 py-3.5">
            <span class="text-slate-500">Reorder Level</span>
            <span class="font-medium text-slate-700">{{ $item->reorder_level }}</span>
        </div>
        @if($item->warranty_period_days)
        <div class="flex justify-between items-center px-5 py-3.5">
            <span class="text-slate-500">Warranty Period</span>
            <span class="font-medium text-slate-700">{{ $item->warranty_period_days }} days</span>
        </div>
        @endif
        <div class="flex justify-between items-center px-5 py-3.5">
            <span class="text-slate-500">Status</span>
            @if($item->stock == 0)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Out of Stock</span>
            @elseif($item->stock <= $item->reorder_level)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Low Stock</span>
            @else
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">In Stock</span>
            @endif
        </div>
    </div>

    <a href="{{ route('cashier.pos') }}"
       class="flex items-center justify-center gap-2 py-3 text-sm font-semibold text-white rounded-xl hover:opacity-90 transition-opacity shadow-sm"
       style="background-color:#363E48;">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        Sell This Product
    </a>

</div>
@endsection
