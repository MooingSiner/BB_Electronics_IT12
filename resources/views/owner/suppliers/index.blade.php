@extends('layouts.owner')

@section('title', 'Supplier Orders')

@php $activeNav = 'supplier-orders'; @endphp

@section('content')
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold" style="color:#363E48">Supplier Orders</h1>
            <p class="text-sm text-slate-500 mt-1">Track and manage purchase orders from suppliers.</p>
        </div>
        <a href="{{ route('owner.suppliers.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-lg shadow-sm hover:opacity-90 transition"
           style="background-color:#363E48">
            + New Order
        </a>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow border border-slate-200 p-4 mb-5">
        <form method="GET" action="{{ route('owner.suppliers.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-slate-600 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Order ID or supplier name…"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30">
            </div>
            <div class="min-w-[200px]">
                <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
                <select name="status"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30">
                    <option value="">All Statuses</option>
                    <option value="Ordered"             {{ request('status') === 'Ordered'              ? 'selected' : '' }}>Ordered</option>
                    <option value="Partially Received"  {{ request('status') === 'Partially Received'   ? 'selected' : '' }}>Partially Received</option>
                    <option value="Received"            {{ request('status') === 'Received'             ? 'selected' : '' }}>Received</option>
                    <option value="Cancelled"           {{ request('status') === 'Cancelled'            ? 'selected' : '' }}>Cancelled</option>
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
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Order ID</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Supplier</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Order Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Expected Date</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Items</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($orders as $order)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs text-slate-500">{{ $order->id ?? 'ORD-0001' }}</span>
                        </td>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $order->supplier ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ isset($order->order_date) ? \Carbon\Carbon::parse($order->order_date)->format('M d, Y') : '—' }}
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ isset($order->expected_date) ? \Carbon\Carbon::parse($order->expected_date)->format('M d, Y') : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right text-slate-700">
                            {{ $order->items_count ?? $order->items?->count() ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php $status = $order->status ?? 'Ordered'; @endphp
                            @if($status === 'Received')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Received</span>
                            @elseif($status === 'Partially Received')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Partially Received</span>
                            @elseif($status === 'Cancelled')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Cancelled</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Ordered</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('owner.suppliers.show', $order->id) }}"
                               class="px-3 py-1 text-xs border border-slate-300 rounded-md text-slate-600 hover:bg-slate-100 transition">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p class="text-sm font-medium text-slate-500">No supplier orders found.</p>
                                <a href="{{ route('owner.suppliers.create') }}"
                                   class="text-sm font-medium underline" style="color:#363E48">Create your first order</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Footer link --}}
    <div class="mt-4 text-right">
        <a href="{{ route('owner.suppliers.damaged') }}"
           class="text-sm font-medium hover:underline" style="color:#363E48">
            → View Damaged Products
        </a>
    </div>
@endsection
