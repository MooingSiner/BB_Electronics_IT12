@extends('layouts.owner')

@section('title', 'Inventory')

@php $activeNav = 'inventory'; @endphp

@section('breadcrumb')
    Dashboard / Inventory
@endsection

@section('content')
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold" style="color:#363E48">{{ $showArchived ? 'Archived Products' : 'Inventory' }}</h1>
            <p class="text-sm text-slate-500 mt-1">
                @if($showArchived)
                    Products hidden from active inventory. Restore to bring them back.
                @else
                    Manage products and stock levels.
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('owner.inventory.index', $showArchived ? [] : ['archived' => 1]) }}"
               class="px-4 py-2 text-sm font-medium text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50 transition">
                {{ $showArchived ? 'View Active' : 'View Archived' }}
            </a>
            <a href="{{ route('owner.inventory.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-lg shadow-sm hover:opacity-90 transition"
               style="background-color:#363E48">
                + Add Product
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter Card --}}
    <div class="bg-white rounded-xl shadow border border-slate-200 p-4 mb-5">
        <form method="GET" action="{{ route('owner.inventory.index') }}" class="flex flex-wrap gap-3 items-end">
            @if($showArchived)
                <input type="hidden" name="archived" value="1">
            @endif
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-medium text-slate-600 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Product name or ID…"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30">
            </div>
            <div class="min-w-[160px]">
                <label class="block text-xs font-medium text-slate-600 mb-1">Category</label>
                <select name="category"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[150px]">
                <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
                <select name="status"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30">
                    <option value="">All Statuses</option>
                    <option value="In Stock"      {{ request('status') === 'In Stock'      ? 'selected' : '' }}>In Stock</option>
                    <option value="Low Stock"     {{ request('status') === 'Low Stock'     ? 'selected' : '' }}>Low Stock</option>
                    <option value="Out of Stock"  {{ request('status') === 'Out of Stock'  ? 'selected' : '' }}>Out of Stock</option>
                    <option value="Needs Restock" {{ request('status') === 'Needs Restock' ? 'selected' : '' }}>Needs Restock (Low + Out)</option>
                </select>
            </div>
            <div>
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition"
                        style="background-color:#363E48">
                    Filter
                </button>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Product ID</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Product Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Category</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Unit Price</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Stock</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Reorder At</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($products as $product)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs text-slate-500">{{ $product->id ?? 'PRD-0001' }}</span>
                        </td>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $product->name ?? 'Product Name' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $product->category ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-slate-700">
                            ₱{{ number_format($product->unit_price ?? 0, 2) }}
                        </td>
                        <td class="px-4 py-3 text-right font-semibold
                            @if(($product->stock ?? 0) === 0) text-red-600
                            @elseif(($product->stock ?? 0) <= ($product->reorder_level ?? 0)) text-amber-600
                            @else text-slate-700
                            @endif">
                            {{ $product->stock ?? 0 }}
                        </td>
                        <td class="px-4 py-3 text-right text-slate-500">{{ $product->reorder_level ?? 0 }}</td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $stock = $product->stock ?? 0;
                                $reorder = $product->reorder_level ?? 0;
                            @endphp
                            @if($stock === 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                    Out of Stock
                                </span>
                            @elseif($stock <= $reorder)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                    Low Stock
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    In Stock
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('owner.inventory.show', $product->id) }}"
                                   class="px-2.5 py-1 text-xs border border-slate-300 rounded-md text-slate-600 hover:bg-slate-100 transition">
                                    View
                                </a>
                                <a href="{{ route('owner.inventory.edit', $product->id) }}"
                                   class="px-2.5 py-1 text-xs border border-slate-300 rounded-md text-slate-600 hover:bg-slate-100 transition">
                                    Edit
                                </a>
                                <a href="{{ route('owner.inventory.stockin', $product->id) }}"
                                   class="px-2.5 py-1 text-xs border border-slate-300 rounded-md text-slate-600 hover:bg-slate-100 transition">
                                    Stock In ↓
                                </a>
                                <a href="{{ route('owner.inventory.history', $product->id) }}"
                                   class="px-2.5 py-1 text-xs border border-slate-300 rounded-md text-slate-600 hover:bg-slate-100 transition">
                                    History
                                </a>
                                @if($showArchived)
                                <form method="POST" action="{{ route('owner.inventory.restore', $product->id) }}">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 text-xs border border-green-200 rounded-md text-green-700 bg-green-50 hover:bg-green-100 transition">
                                        Restore
                                    </button>
                                </form>
                                @else
                                <form method="POST" action="{{ route('owner.inventory.archive', $product->id) }}"
                                      onsubmit="return confirm('Archive {{ $product->name }}?')">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 text-xs border border-red-200 rounded-md text-red-600 bg-red-50 hover:bg-red-100 transition">
                                        Archive
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                </svg>
                                <p class="text-sm font-medium text-slate-500">No products found.</p>
                                <a href="{{ route('owner.inventory.create') }}"
                                   class="text-sm font-medium underline" style="color:#363E48">Add your first product</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
