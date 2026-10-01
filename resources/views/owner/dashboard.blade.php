@extends('layouts.owner')
@section('title', 'Dashboard')
@php $activeNav = 'dashboard'; @endphp

@section('content')
{{-- Page Header --}}
<div class="flex flex-wrap items-start justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl font-bold text-[#363E48]">
            Welcome, {{ auth()->user()->full_name }}
        </h1>
        <p class="mt-1 text-sm text-slate-500">
            Owner / Manager &mdash; {{ now()->format('F d, Y') }}
        </p>
    </div>

    {{-- Period Filter --}}
    <div class="flex flex-col items-end gap-2">
        <div class="flex gap-1 bg-white rounded-lg border border-slate-200 shadow-sm p-1">
            @foreach(['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year'] as $val => $label)
            <a href="{{ route('owner.dashboard', ['period' => $val]) }}"
               class="px-3 py-1.5 rounded-md text-xs font-medium transition-colors
               {{ ($period ?? 'today') === $val ? 'text-white' : 'text-slate-600 hover:bg-slate-100' }}"
               @if(($period ?? 'today') === $val) style="background-color:#363E48;" @endif>
                {{ $label }}
            </a>
            @endforeach
        </div>
        <form method="GET" action="{{ route('owner.dashboard') }}" class="flex items-center gap-1.5">
            <input type="hidden" name="period" value="custom">
            <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}" required
                   class="px-2 py-1 text-xs border border-slate-200 rounded-md focus:outline-none focus:ring-2"
                   style="--tw-ring-color:#363E48;">
            <span class="text-xs text-slate-400">to</span>
            <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" required
                   class="px-2 py-1 text-xs border border-slate-200 rounded-md focus:outline-none focus:ring-2"
                   style="--tw-ring-color:#363E48;">
            <button type="submit"
                    class="px-3 py-1 text-xs font-medium text-white rounded-md hover:opacity-90 transition"
                    style="background-color:#363E48;">
                Apply
            </button>
        </form>
    </div>
</div>

{{-- Stat Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

    {{-- Total Products --}}
    <a href="{{ route('owner.inventory.index') }}" class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 flex flex-col gap-3 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Products</span>
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 text-[#363E48]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" />
                </svg>
            </span>
        </div>
        <div>
            <p class="text-3xl font-bold text-[#363E48]">{{ $totalProducts ?? 11 }}</p>
            <p class="text-xs text-slate-400 mt-0.5">in inventory</p>
        </div>
    </a>

    {{-- Total Stock --}}
    <a href="{{ route('owner.inventory.index') }}" class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 flex flex-col gap-3 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Stock</span>
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 text-[#363E48]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8" />
                </svg>
            </span>
        </div>
        <div>
            <p class="text-3xl font-bold text-[#363E48]">{{ $totalStock ?? '1,922' }}</p>
            <p class="text-xs text-slate-400 mt-0.5">units available</p>
        </div>
    </a>

    {{-- Sales for the selected period (dark card) --}}
    <div class="bg-[#363E48] rounded-xl shadow-sm p-5 flex flex-col gap-3">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wide text-slate-300">Sales &mdash; {{ $periodLabel ?? 'Today' }}</span>
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-white/10 text-[#E0CD66]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </span>
        </div>
        <div>
            <p class="text-3xl font-bold text-white">{{ $todaySales ?? '₱372.50' }}</p>
            <p class="text-xs text-slate-400 mt-0.5">{{ $txnCount ?? 2 }} transactions</p>
        </div>
    </div>

    {{-- Low Stock Items --}}
    <a href="{{ route('owner.inventory.index', ['status' => 'Needs Restock']) }}" class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 flex flex-col gap-3 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Low Stock Items</span>
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-amber-50 text-amber-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
            </span>
        </div>
        <div>
            <p class="text-3xl font-bold text-[#363E48]">{{ $lowStockCount ?? 4 }}</p>
            <p class="text-xs text-slate-400 mt-0.5">{{ $outOfStockCount ?? 1 }} out of stock</p>
        </div>
    </a>
</div>

{{-- Main Grid --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Recent Transactions + Sales Trend (col-span-2) --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <div>
                    <h2 class="text-sm font-semibold text-[#363E48]">Recent Transactions</h2>
                    <p class="text-xs text-slate-400">{{ $periodLabel ?? 'Today' }}</p>
                </div>
                <a href="{{ route('owner.sales.index') }}"
                   class="text-xs font-medium text-[#363E48] hover:text-[#E0CD66] transition-colors">
                    View all &rarr;
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50">
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Transaction ID</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Product(s)</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Total</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Processed By</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($transactions ?? [] as $txn)
                            <tr class="hover:bg-slate-50 transition-colors cursor-pointer"
                                onclick="window.location='{{ route('owner.sales.show', $txn->id) }}'">
                                <td class="px-6 py-3 font-mono text-xs text-slate-700">{{ $txn->code }}</td>
                                <td class="px-6 py-3 text-slate-700">{{ $txn->products }}</td>
                                <td class="px-6 py-3 font-medium text-slate-800">{{ $txn->total }}</td>
                                <td class="px-6 py-3 text-slate-600">{{ $txn->processed_by }}</td>
                                <td class="px-6 py-3">
                                    @if($txn->status === 'Completed')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Completed</span>
                                    @elseif($txn->status === 'Pending')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Pending</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">{{ $txn->status }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-6 text-center text-sm text-slate-400">No transactions for {{ strtolower($periodLabel ?? 'today') }}.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Sales Trend --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-sm font-semibold text-[#363E48]">Sales Trend</h2>
                <p class="text-xs text-slate-400">{{ $periodLabel ?? 'Today' }}</p>
            </div>
            @php $trendTotal = collect($salesTrend ?? [])->sum('value'); @endphp
            @if($trendTotal > 0)
                @php
                    $maxVal = collect($salesTrend)->max('value') ?: 1;
                    $pointCount = count($salesTrend);
                    $labelStep = max(1, intdiv($pointCount, 8));
                @endphp
                <div class="flex items-end gap-1 h-44">
                    @foreach($salesTrend as $point)
                        <div class="flex-1 h-full flex items-end" title="{{ $point['label'] }}: ₱{{ number_format($point['value'], 2) }}">
                            <div class="w-full rounded-t transition-colors hover:opacity-80"
                                 style="height: {{ $point['value'] > 0 ? max(4, ($point['value'] / $maxVal) * 100) : 1 }}%; background-color: {{ $point['value'] > 0 ? '#363E48' : '#E2E8F0' }};">
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="flex gap-1 mt-2">
                    @foreach($salesTrend as $i => $point)
                        <div class="flex-1 text-center text-[10px] text-slate-400 truncate">
                            {{ $i % $labelStep === 0 ? $point['label'] : '' }}
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-400 text-center py-14">No sales recorded for {{ strtolower($periodLabel ?? 'today') }}.</p>
            @endif
        </div>
    </div>

    {{-- Right column --}}
    <div class="flex flex-col gap-4">

        {{-- Low / Out of Stock --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h2 class="text-sm font-semibold text-[#363E48]">Low / Out of Stock</h2>
                <a href="{{ route('owner.inventory.index', ['status' => 'Needs Restock']) }}"
                   class="text-xs text-[#363E48] hover:text-[#E0CD66] transition-colors font-medium">
                    View all &rarr;
                </a>
            </div>
            <ul class="divide-y divide-slate-100">
                @forelse($lowStockProducts ?? [] as $product)
                    <li class="flex items-center justify-between px-5 py-3">
                        <div>
                            <p class="text-sm font-medium text-slate-700">{{ $product->name }}</p>
                            <p class="text-xs text-slate-400">{{ $product->stock }} units left</p>
                        </div>
                        @if($product->stock === 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Out</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Low</span>
                        @endif
                    </li>
                @empty
                    <li class="px-5 py-6 text-center text-sm text-slate-400">All products are sufficiently stocked.</li>
                @endforelse
            </ul>
        </div>

        {{-- Fast & Slow Moving Products --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h2 class="text-sm font-semibold text-[#363E48]">Fast &amp; Slow Moving</h2>
                <a href="{{ route('owner.reports.index', ['type' => 'inventory']) }}"
                   class="text-xs text-[#363E48] hover:text-[#E0CD66] transition-colors font-medium">
                    View all &rarr;
                </a>
            </div>
            <div class="px-5 py-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-green-600 mb-2">Fast-Moving</p>
                <ul class="divide-y divide-slate-100 mb-3">
                    @forelse($fastMoving ?? [] as $product)
                        <li class="flex items-center justify-between py-2">
                            <p class="text-sm text-slate-700">{{ $product->name }}</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ $product->units_sold }} sold</span>
                        </li>
                    @empty
                        <li class="py-2 text-sm text-slate-400">No fast-moving products yet.</li>
                    @endforelse
                </ul>
                <p class="text-xs font-semibold uppercase tracking-wide text-amber-600 mb-2">Slow-Moving</p>
                <ul class="divide-y divide-slate-100">
                    @forelse($slowMoving ?? [] as $product)
                        <li class="flex items-center justify-between py-2">
                            <p class="text-sm text-slate-700">{{ $product->name }}</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">{{ $product->units_sold }} sold</span>
                        </li>
                    @empty
                        <li class="py-2 text-sm text-slate-400">No slow-moving products.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Supplier Orders --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h2 class="text-sm font-semibold text-[#363E48]">Supplier Orders</h2>
                <a href="{{ route('owner.suppliers.orders') }}"
                   class="text-xs text-[#363E48] hover:text-[#E0CD66] transition-colors font-medium">
                    View all &rarr;
                </a>
            </div>
            <ul class="divide-y divide-slate-100">
                @forelse($recentOrders ?? [] as $order)
                    <li class="px-5 py-3">
                        <p class="text-sm font-medium text-slate-700">{{ $order->supplier }}</p>
                        <p class="text-xs text-slate-400">{{ $order->items }} items &middot; {{ $order->date }}</p>
                    </li>
                @empty
                    <li class="px-5 py-6 text-center text-sm text-slate-400">No supplier orders yet.</li>
                @endforelse
            </ul>
        </div>

        {{-- Returns & Warranties --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h2 class="text-sm font-semibold text-[#363E48]">Returns &amp; Warranties</h2>
                <a href="{{ route('owner.returns.index') }}"
                   class="text-xs text-[#363E48] hover:text-[#E0CD66] transition-colors font-medium">
                    View all &rarr;
                </a>
            </div>
            <ul class="divide-y divide-slate-100">
                <li class="px-5 py-3">
                    <p class="text-sm font-medium text-slate-700">TXN-2024-005 &mdash; Return</p>
                    <p class="text-xs text-slate-400">Switch Panel 4-gang &middot; Jan 13, 2024</p>
                </li>
                <li class="px-5 py-3">
                    <p class="text-sm font-medium text-slate-700">TXN-2024-003 &mdash; Warranty</p>
                    <p class="text-xs text-slate-400">Circuit Breaker 15A &middot; Jan 12, 2024</p>
                </li>
            </ul>
        </div>

    </div>
</div>
@endsection
