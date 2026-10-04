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
            @unless($showArchived)
            <a href="{{ route('owner.inventory.stockin.bulk') }}"
               class="px-4 py-2 text-sm font-medium text-white rounded-lg shadow-sm hover:opacity-90 transition"
               style="background-color:#363E48">
                Stock In
            </a>
            @endunless
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

    {{-- Filter Card --}}
    <div class="bg-white rounded-xl shadow border border-slate-200 p-4 mb-5">
        <form method="GET" action="{{ route('owner.inventory.index') }}" class="flex flex-wrap gap-3 items-end">
            @if($showArchived)
                <input type="hidden" name="archived" value="1">
            @endif
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-medium text-slate-600 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Product name or code…"
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
        <div class="overflow-x-auto">
<table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Product ID</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Product Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Category</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Stock</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Capital Price</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Unit Price</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Reorder At</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($products as $product)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs text-slate-500">{{ $product->code ?? 'PRD-0001' }}</span>
                        </td>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $product->name ?? 'Product Name' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $product->category ?? '—' }}</td>
                        <td class="px-4 py-3 text-right font-semibold
                            @if(($product->stock ?? 0) === 0) text-red-600
                            @elseif(($product->stock ?? 0) <= ($product->reorder_level ?? 0)) text-amber-600
                            @else text-slate-700
                            @endif">
                            {{ $product->stock ?? 0 }}
                        </td>
                        <td class="px-4 py-3 text-right text-slate-500">
                            ₱{{ number_format($product->cost_price ?? 0, 2) }}
                        </td>
                        <td class="px-4 py-3 text-right text-slate-700">
                            ₱{{ number_format($product->unit_price ?? 0, 2) }}
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
                                   class="p-1.5 border border-slate-300 rounded-md text-slate-600 hover:bg-slate-100 transition" title="View" aria-label="View"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></a>
                                <a href="{{ route('owner.inventory.edit', $product->id) }}"
                                   class="p-1.5 border border-slate-300 rounded-md text-slate-600 hover:bg-slate-100 transition" title="Edit" aria-label="Edit"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg></a>
                                <a href="{{ route('owner.inventory.stockin', $product->id) }}"
                                   class="p-1.5 border border-slate-300 rounded-md text-slate-600 hover:bg-slate-100 transition" title="Stock In" aria-label="Stock In"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg></a>
                                <a href="{{ route('owner.inventory.stockout', $product->id) }}"
                                   class="p-1.5 border border-slate-300 rounded-md text-slate-600 hover:bg-slate-100 transition" title="Stock Out" aria-label="Stock Out"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg></a>
                                <a href="{{ route('owner.inventory.history', $product->id) }}"
                                   class="p-1.5 border border-slate-300 rounded-md text-slate-600 hover:bg-slate-100 transition" title="History" aria-label="History"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></a>
                                @if($showArchived)
                                <form method="POST" action="{{ route('owner.inventory.restore', $product->id) }}">
                                    @csrf
                                    <button type="submit" class="p-1.5 border border-green-200 rounded-md text-green-700 bg-green-50 hover:bg-green-100 transition" title="Restore" aria-label="Restore"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.992 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg></button>
                                </form>
                                @else
                                <form method="POST" action="{{ route('owner.inventory.archive', $product->id) }}"
                                      onsubmit="return confirm('Archive {{ $product->name }}?')">
                                    @csrf
                                    <button type="submit" class="p-1.5 border border-red-200 rounded-md text-red-600 bg-red-50 hover:bg-red-100 transition" title="Archive" aria-label="Archive"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center text-slate-400">
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
    </div>
@endsection
