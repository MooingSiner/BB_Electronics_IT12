@extends('layouts.cashier')

@section('title', 'Dashboard')

@php $activeNav = 'dashboard'; @endphp

@section('content')
<div class="p-6 space-y-6">

    {{-- Welcome Heading --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Welcome, {{ auth()->user()->name ?? 'Ana' }}</h1>
        <p class="text-sm text-slate-500 mt-1">Cashier / Store Attendant — {{ now()->format('F d, Y') }}</p>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Today's Sales --}}
        <div class="rounded-2xl p-5 text-white" style="background-color:#363E48;">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium opacity-80">Today's Sales</span>
                <div class="w-9 h-9 rounded-full flex items-center justify-center" style="background-color:rgba(224,205,102,0.2);">
                    <svg class="w-5 h-5" style="color:#E0CD66;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold">₱{{ number_format($todaysSales ?? 0, 2) }}</p>
            <p class="text-xs opacity-60 mt-1">{{ $todaysSalesCount ?? 0 }} transactions</p>
        </div>

        {{-- Low Stock Items --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-slate-600">Low Stock Items</span>
                <div class="w-9 h-9 bg-amber-50 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-800">{{ $lowStockCount ?? 0 }}</p>
            <p class="text-xs text-slate-400 mt-1">items need restocking</p>
        </div>

        {{-- Pending Returns --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-slate-600">Pending Returns</span>
                <div class="w-9 h-9 bg-rose-50 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-800">{{ $pendingReturnsCount ?? 0 }}</p>
            <p class="text-xs text-slate-400 mt-1">awaiting processing</p>
        </div>

        {{-- My Sales Today --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-slate-600">My Sales Today</span>
                <div class="w-9 h-9 bg-blue-50 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-800">{{ $mySalesTodayCount ?? 0 }}</p>
            <p class="text-xs text-slate-400 mt-1">₱{{ number_format($mySalesTodayTotal ?? 0, 2) }} total</p>
        </div>

    </div>

    {{-- Quick Actions --}}
    <div class="flex gap-3 flex-wrap">
        <a href="{{ route('cashier.pos') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white shadow-sm hover:opacity-90 transition"
           style="background-color:#363E48;">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            New Sale
        </a>
        <a href="{{ route('cashier.returns.process') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 transition shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
            </svg>
            Process Return
        </a>
    </div>

    {{-- Recent Transactions --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-semibold text-slate-800">Recent Transactions</h2>
            <a href="{{ route('cashier.sales.index') }}" class="text-xs font-medium hover:underline" style="color:#363E48;">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Transaction ID</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Product</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Total</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Processed By</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Date</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($transactions as $txn)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4">
                            <a href="{{ route('cashier.sales.show', $txn->id) }}"
                               class="font-medium hover:underline" style="color:#363E48;">
                                #{{ $txn->id }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-slate-700">{{ $txn->product_name ?? $txn->items_summary ?? '—' }}</td>
                        <td class="px-6 py-4 font-semibold text-slate-800">₱{{ number_format($txn->total, 2) }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $txn->processed_by ?? $txn->user->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-slate-500">{{ \Carbon\Carbon::parse($txn->created_at)->format('M d, Y h:i A') }}</td>
                        <td class="px-6 py-4">
                            @php $status = $txn->status ?? 'Completed'; @endphp
                            @if($status === 'Completed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Completed</span>
                            @elseif($status === 'Refunded')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Refunded</span>
                            @elseif($status === 'Voided')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Voided</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $status }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm">No recent transactions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Bottom 2-col Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Low / Out of Stock --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <h2 class="font-semibold text-slate-800">Low / Out of Stock</h2>
            </div>
            <ul class="divide-y divide-slate-50">
                @forelse($lowStockItems ?? [] as $item)
                <li class="px-6 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-800">{{ $item->name }}</p>
                        <p class="text-xs text-slate-400">{{ $item->category }}</p>
                    </div>
                    @if($item->stock == 0)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Out of stock</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">{{ $item->stock }} left</span>
                    @endif
                </li>
                @empty
                <li class="px-6 py-8 text-center text-slate-400 text-sm">All items are well stocked.</li>
                @endforelse
            </ul>
        </div>

        {{-- Returns & Warranties --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                    </svg>
                    <h2 class="font-semibold text-slate-800">Returns &amp; Warranties</h2>
                </div>
                <a href="{{ route('cashier.returns.index') }}" class="text-xs font-medium hover:underline" style="color:#363E48;">View all</a>
            </div>
            <ul class="divide-y divide-slate-50">
                @forelse($recentReturns ?? [] as $ret)
                <li class="px-6 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-800">{{ $ret->product_name ?? '—' }}</p>
                        <p class="text-xs text-slate-400">{{ $ret->reason ?? '' }} · {{ \Carbon\Carbon::parse($ret->created_at)->format('M d, Y') }}</p>
                    </div>
                    @if($ret->status === 'Pending')
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Pending</span>
                    @elseif($ret->status === 'Approved')
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Approved</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $ret->status }}</span>
                    @endif
                </li>
                @empty
                <li class="px-6 py-8 text-center text-slate-400 text-sm">No recent returns or warranties.</li>
                @endforelse
            </ul>
        </div>

    </div>

</div>
@endsection
