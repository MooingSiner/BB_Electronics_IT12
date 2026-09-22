@extends('layouts.cashier')

@section('title', 'Sales Transactions')

@php $activeNav = 'sales'; @endphp

@section('breadcrumb')
<a href="{{ route('cashier.pos') }}" class="hover:underline" style="color:#363E48;">Point of Sale</a>
<span class="mx-1 text-slate-400">/</span>
<span class="text-slate-600">Sales Transactions</span>
@endsection

@section('content')
<div class="p-6 space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Sales Transactions</h1>
            <p class="text-sm text-slate-500 mt-1">View and manage your processed sales.</p>
        </div>
        <a href="{{ route('cashier.pos') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white shadow-sm hover:opacity-90 transition"
           style="background-color:#363E48;">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            New Sale
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <form method="GET" action="{{ route('cashier.sales.index') }}" class="flex flex-wrap gap-3 items-end">
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
                           placeholder="Transaction ID or product..."
                           class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2"
                           style="--tw-ring-color:#363E48;">
                </div>
            </div>
            <div class="min-w-36">
                <label class="block text-xs font-medium text-slate-500 mb-1">Status</label>
                <select name="status"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2"
                        style="--tw-ring-color:#363E48;">
                    <option value="">All Statuses</option>
                    <option value="Completed" {{ request('status') === 'Completed' ? 'selected' : '' }}>Completed</option>
                    <option value="Refunded" {{ request('status') === 'Refunded' ? 'selected' : '' }}>Refunded</option>
                    <option value="Voided" {{ request('status') === 'Voided' ? 'selected' : '' }}>Voided</option>
                </select>
            </div>
            <div class="min-w-36">
                <label class="block text-xs font-medium text-slate-500 mb-1">Discount</label>
                <select name="discount"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2"
                        style="--tw-ring-color:#363E48;">
                    <option value="">All</option>
                    <option value="with" {{ request('discount') === 'with' ? 'selected' : '' }}>With Discount</option>
                    <option value="without" {{ request('discount') === 'without' ? 'selected' : '' }}>No Discount</option>
                </select>
            </div>
            <button type="submit"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-white transition-opacity hover:opacity-90"
                    style="background-color:#363E48;">
                Filter
            </button>
            @if(request()->hasAny(['search','status','discount']))
            <a href="{{ route('cashier.sales.index') }}"
               class="px-4 py-2 rounded-lg text-sm font-medium border border-slate-200 text-slate-600 bg-white hover:bg-slate-50 transition-colors">
                Clear
            </a>
            @endif
        </form>
    </div>

    {{-- Transactions Table --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Transaction ID</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Product(s)</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Qty</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Total</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Discount</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Payment</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Date</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Processed By</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($transactions as $txn)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-4">
                            <a href="{{ route('cashier.sales.show', $txn->id) }}"
                               class="font-medium hover:underline" style="color:#363E48;">
                                #{{ $txn->id }}
                            </a>
                        </td>
                        <td class="px-5 py-4 text-slate-700 max-w-48 truncate">{{ $txn->items_summary ?? $txn->product_name ?? '—' }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ $txn->total_qty ?? '—' }}</td>
                        <td class="px-5 py-4 font-semibold text-slate-800">₱{{ number_format($txn->total, 2) }}</td>
                        <td class="px-5 py-4 text-slate-600">
                            @if(($txn->discount_amount ?? 0) > 0)
                                <span class="text-green-600">−₱{{ number_format($txn->discount_amount, 2) }}</span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-slate-600">{{ $txn->payment_method ?? '—' }}</td>
                        <td class="px-5 py-4 text-slate-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($txn->created_at)->format('M d, Y') }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ $txn->processed_by ?? $txn->user->name ?? '—' }}</td>
                        <td class="px-5 py-4">
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
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('cashier.sales.show', $txn->id) }}"
                                   class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium border border-slate-200 text-slate-600 bg-white hover:bg-slate-50 transition-colors">
                                    View
                                </a>
                                <a href="{{ route('cashier.sales.receipt', $txn->id) }}"
                                   class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium border border-slate-200 text-slate-600 bg-white hover:bg-slate-50 transition-colors">
                                    Print
                                </a>
                                @if(($txn->status ?? 'Completed') === 'Completed')
                                <a href="{{ route('cashier.returns.process', ['transaction_id' => $txn->id]) }}"
                                   class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium border border-amber-200 text-amber-700 bg-amber-50 hover:bg-amber-100 transition-colors">
                                    Return
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-5 py-16 text-center text-slate-400 text-sm">
                            <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            No transactions found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions instanceof \Illuminate\Pagination\LengthAwarePaginator && $transactions->hasPages())
        <div class="px-5 py-4 border-t border-slate-100">
            {{ $transactions->withQueryString()->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
