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

    {{-- Header Card --}}
    <div class="rounded-2xl shadow-sm border border-slate-200 p-6 mb-6" style="background: linear-gradient(135deg, #363E48 0%, #454f5c 100%);">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center flex-shrink-0 accent-bg">
                    <svg class="w-7 h-7 accent-text" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-2xl font-bold text-white">{{ $item->name }}</h1>
                        @if(! $item->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white/15 text-white">Archived</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-400/20 text-green-300">Active</span>
                        @endif
                    </div>
                    <p class="text-sm text-white/60 mt-1 font-mono">{{ $item->code }} &middot; {{ $item->category }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('owner.inventory.edit', $item->id) }}"
                   class="px-3 py-1.5 text-xs font-medium rounded-md bg-white/10 text-white hover:bg-white/20 transition">
                    Edit
                </a>
                <a href="{{ route('owner.inventory.stockin', $item->id) }}"
                   class="px-3 py-1.5 text-xs font-medium rounded-md bg-white/10 text-white hover:bg-white/20 transition">
                    Stock In
                </a>
                <a href="{{ route('owner.inventory.history', $item->id) }}"
                   class="px-3 py-1.5 text-xs font-medium rounded-md bg-white/10 text-white hover:bg-white/20 transition">
                    History
                </a>
                @if($item->is_active)
                <form method="POST" action="{{ route('owner.inventory.archive', $item->id) }}"
                      onsubmit="return confirm('Archive {{ $item->name }}? It will be hidden from the active inventory list but its records are kept.')">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 text-xs font-medium rounded-md bg-red-500/20 text-red-200 hover:bg-red-500/30 transition">
                        Archive
                    </button>
                </form>
                @else
                <form method="POST" action="{{ route('owner.inventory.restore', $item->id) }}">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 text-xs font-medium rounded-md bg-green-500/20 text-green-200 hover:bg-green-500/30 transition">
                        Restore
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex items-start gap-4">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-slate-100 text-[#363E48] flex-shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2m0-12a9 9 0 100 18 9 9 0 000-18z" />
                </svg>
            </span>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Unit Price</p>
                <p class="text-xl font-bold" style="color:#363E48">₱{{ number_format($item->unit_price, 2) }}</p>
                <p class="text-xs text-slate-400 mt-1">Cost: ₱{{ number_format($item->cost_price, 2) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex items-start gap-4">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg flex-shrink-0
                {{ $item->stock == 0 ? 'bg-red-50 text-red-500' : ($item->stock <= $item->reorder_level ? 'bg-amber-50 text-amber-500' : 'bg-green-50 text-green-600') }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8" />
                </svg>
            </span>
            <div class="flex-1">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Current Stock</p>
                <p class="text-xl font-bold {{ $item->stock == 0 ? 'text-red-600' : ($item->stock <= $item->reorder_level ? 'text-amber-600' : 'text-slate-800') }}">
                    {{ $item->stock }}
                </p>
                <p class="text-xs text-slate-400 mt-1">Reorder at {{ $item->reorder_level }}</p>
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
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex items-start gap-4">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-slate-100 text-[#363E48] flex-shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
            <div>
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
    </div>

    @if($item->description)
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 mt-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">Description</p>
        <p class="text-sm text-slate-700 leading-relaxed">{{ $item->description }}</p>
    </div>
    @endif
@endsection
