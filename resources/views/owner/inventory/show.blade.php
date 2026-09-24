@extends('layouts.owner')

@section('title', $item->name)

@php $activeNav = 'inventory'; @endphp

@section('breadcrumb')
    <a href="{{ route('owner.inventory.index') }}" class="hover:underline">Inventory</a> / {{ $item->name }}
@endsection

@section('content')
    <a href="{{ route('owner.inventory.index') }}"
       class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700 mb-4 transition">
        ← Back to Inventory
    </a>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex items-start justify-between mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold" style="color:#363E48">{{ $item->name }}</h1>
                @if(! $item->is_active)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-200 text-slate-600">Archived</span>
                @endif
            </div>
            <p class="text-sm text-slate-500 mt-1">#{{ $item->id }} &middot; {{ $item->category }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('owner.inventory.edit', $item->id) }}"
               class="px-3 py-1.5 text-xs border border-slate-300 rounded-md text-slate-600 hover:bg-slate-100 transition">
                Edit
            </a>
            <a href="{{ route('owner.inventory.stockin', $item->id) }}"
               class="px-3 py-1.5 text-xs border border-slate-300 rounded-md text-slate-600 hover:bg-slate-100 transition">
                Stock In
            </a>
            <a href="{{ route('owner.inventory.history', $item->id) }}"
               class="px-3 py-1.5 text-xs border border-slate-300 rounded-md text-slate-600 hover:bg-slate-100 transition">
                History
            </a>
            @if($item->is_active)
            <form method="POST" action="{{ route('owner.inventory.archive', $item->id) }}"
                  onsubmit="return confirm('Archive {{ $item->name }}? It will be hidden from the active inventory list but its records are kept.')">
                @csrf
                <button type="submit" class="px-3 py-1.5 text-xs border border-red-200 rounded-md text-red-600 bg-red-50 hover:bg-red-100 transition">
                    Archive
                </button>
            </form>
            @else
            <form method="POST" action="{{ route('owner.inventory.restore', $item->id) }}">
                @csrf
                <button type="submit" class="px-3 py-1.5 text-xs border border-green-200 rounded-md text-green-700 bg-green-50 hover:bg-green-100 transition">
                    Restore
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow border border-slate-200 p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Unit Price</p>
            <p class="text-xl font-bold" style="color:#363E48">₱{{ number_format($item->unit_price, 2) }}</p>
            <p class="text-xs text-slate-400 mt-1">Cost: ₱{{ number_format($item->cost_price, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow border border-slate-200 p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Current Stock</p>
            <p class="text-xl font-bold {{ $item->stock == 0 ? 'text-red-600' : ($item->stock <= $item->reorder_level ? 'text-amber-600' : 'text-slate-800') }}">
                {{ $item->stock }}
            </p>
            <p class="text-xs text-slate-400 mt-1">Reorder at {{ $item->reorder_level }}</p>
        </div>
        <div class="bg-white rounded-xl shadow border border-slate-200 p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Warranty</p>
            <p class="text-xl font-bold" style="color:#363E48">
                @if($item->warranty_period_days)
                    {{ $item->warranty_period_days }} days
                @else
                    None
                @endif
            </p>
        </div>
    </div>

    @if($item->description)
    <div class="bg-white rounded-xl shadow border border-slate-200 p-5 mt-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Description</p>
        <p class="text-sm text-slate-700">{{ $item->description }}</p>
    </div>
    @endif
@endsection
