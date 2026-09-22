@extends('layouts.cashier')

@section('title', 'Inventory')

@php $activeNav = 'inventory'; @endphp

@section('content')
<div class="p-6 space-y-6">

    {{-- Page Header --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Inventory</h1>
        <p class="text-sm text-slate-500 mt-1">Browse available products and stock levels.</p>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <form method="GET" action="{{ route('cashier.inventory.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-medium text-slate-500 mb-1">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                        </svg>
                    </div>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Product name or ID..."
                           class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2"
                           style="--tw-ring-color:#363E48;">
                </div>
            </div>
            <div class="min-w-40">
                <label class="block text-xs font-medium text-slate-500 mb-1">Category</label>
                <select name="category"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2"
                        style="--tw-ring-color:#363E48;">
                    <option value="">All Categories</option>
                    @foreach($categories ?? ['Lighting','Components','Switches','Wiring','Adapters','Batteries'] as $cat)
                    <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-36">
                <label class="block text-xs font-medium text-slate-500 mb-1">Status</label>
                <select name="status"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2"
                        style="--tw-ring-color:#363E48;">
                    <option value="">All</option>
                    <option value="in_stock" {{ request('status') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                    <option value="low_stock" {{ request('status') === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                    <option value="out_of_stock" {{ request('status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                </select>
            </div>
            <button type="submit"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-white transition-opacity hover:opacity-90"
                    style="background-color:#363E48;">
                Filter
            </button>
            @if(request()->hasAny(['search','category','status']))
            <a href="{{ route('cashier.inventory.index') }}"
               class="px-4 py-2 rounded-lg text-sm font-medium border border-slate-200 text-slate-600 bg-white hover:bg-slate-50 transition-colors">
                Clear
            </a>
            @endif
        </form>
    </div>

    {{-- Products Table --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Product ID</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Product Name</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Category</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Unit Price</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Stock</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Reorder At</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($products as $product)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-4 text-slate-500 font-mono text-xs">#{{ $product->id }}</td>
                        <td class="px-5 py-4">
                            <p class="font-medium text-slate-800">{{ $product->name }}</p>
                            @if($product->sku ?? false)
                            <p class="text-xs text-slate-400 font-mono">{{ $product->sku }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                {{ $product->category }}
                            </span>
                        </td>
                        <td class="px-5 py-4 font-semibold text-slate-800">₱{{ number_format($product->price, 2) }}</td>
                        <td class="px-5 py-4">
                            <span class="font-semibold {{ $product->stock == 0 ? 'text-red-600' : ($product->stock <= ($product->reorder_level ?? 5) ? 'text-amber-600' : 'text-slate-800') }}">
                                {{ $product->stock }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-slate-500">{{ $product->reorder_level ?? '—' }}</td>
                        <td class="px-5 py-4">
                            @if($product->stock == 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Out of Stock</span>
                            @elseif($product->stock <= ($product->reorder_level ?? 5))
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Low Stock</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">In Stock</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <a href="{{ route('cashier.inventory.show', $product->id) }}"
                               class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium border border-slate-200 text-slate-600 bg-white hover:bg-slate-50 transition-colors">
                                View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-16 text-center text-slate-400 text-sm">
                            <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            No products found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products instanceof \Illuminate\Pagination\LengthAwarePaginator && $products->hasPages())
        <div class="px-5 py-4 border-t border-slate-100">
            {{ $products->withQueryString()->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
