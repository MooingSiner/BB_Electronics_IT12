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

    <div>
        <h1 class="text-2xl font-bold text-slate-800">{{ $item->name }}</h1>
        <p class="text-sm text-slate-500 mt-1 font-mono">{{ $item->code }} &middot; {{ $item->category }}</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-3 text-sm">
        <div class="flex justify-between">
            <span class="text-slate-500">Unit Price</span>
            <span class="font-semibold text-slate-800">₱{{ number_format($item->price, 2) }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-500">Current Stock</span>
            <span class="font-semibold {{ $item->stock == 0 ? 'text-red-600' : ($item->stock <= $item->reorder_level ? 'text-amber-600' : 'text-slate-800') }}">
                {{ $item->stock }}
            </span>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-500">Reorder Level</span>
            <span class="text-slate-700">{{ $item->reorder_level }}</span>
        </div>
        @if($item->warranty_period_days)
        <div class="flex justify-between">
            <span class="text-slate-500">Warranty Period</span>
            <span class="text-slate-700">{{ $item->warranty_period_days }} days</span>
        </div>
        @endif
        <div class="flex justify-between">
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
       class="block text-center py-2.5 text-sm font-semibold text-white rounded-xl hover:opacity-90 transition-opacity"
       style="background-color:#363E48;">
        Sell This Product
    </a>

</div>
@endsection
