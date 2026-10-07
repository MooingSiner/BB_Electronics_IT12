@extends('layouts.owner')

@section('title', 'Purchase Orders')

@php $activeNav = 'purchase-orders'; @endphp

@section('content')
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold" style="color:#363E48">{{ $showArchived ? 'Archived Purchase Orders' : 'Purchase Orders' }}</h1>
            <p class="text-sm text-slate-500 mt-1">
                @if($showArchived)
                    Orders hidden from the active list. Restore to bring them back.
                @else
                    Track stock bought from stores outside your regular suppliers.
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('owner.purchase-orders.index', $showArchived ? [] : ['archived' => 1]) }}"
               class="px-4 py-2 text-sm font-medium text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50 transition">
                {{ $showArchived ? 'View Active' : 'View Archived' }}
            </a>
            @unless($showArchived)
            <a href="{{ route('owner.purchase-orders.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-lg shadow-sm hover:opacity-90 transition"
               style="background-color:#363E48">
                + New Purchase Order
            </a>
            @endunless
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow border border-slate-200 p-4 mb-5">
        <form method="GET" action="{{ route('owner.purchase-orders.index') }}" class="flex flex-wrap gap-3 items-end">
            @if($showArchived)
                <input type="hidden" name="archived" value="1">
            @endif
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-slate-600 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Order ID or store name…"
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
        <div class="overflow-x-auto">
<table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Order ID</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Store</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Invoice #</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Order Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Received Date</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Items</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($orders as $order)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs text-slate-500">PO-{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $order->store }}</td>
                        <td class="px-4 py-3 text-slate-600 font-mono text-xs">{{ $order->invoice_number ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ isset($order->order_date) ? \Carbon\Carbon::parse($order->order_date)->format('M d, Y') : '—' }}
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ isset($order->expected_date) ? \Carbon\Carbon::parse($order->expected_date)->format('M d, Y') : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right text-slate-700">{{ $order->items_count }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($order->status === 'Received')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Received</span>
                            @elseif($order->status === 'Partially Received')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Partially Received</span>
                            @elseif($order->status === 'Cancelled')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Cancelled</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Ordered</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('owner.purchase-orders.show', $order->id) }}" class="tip inline-flex items-center justify-center p-1.5 border rounded-md transition border-slate-300 text-slate-600 hover:bg-slate-100" data-tip="View" aria-label="View"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></a>
                                @if($showArchived)
                                <form method="POST" action="{{ route('owner.purchase-orders.restore', $order->id) }}">
                                    @csrf
                                    <button type="submit" class="tip inline-flex items-center justify-center p-1.5 border rounded-md transition border-green-200 text-green-700 bg-green-50 hover:bg-green-100" data-tip="Restore" aria-label="Restore"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.992 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg></button>
                                </form>
                                @else
                                <form method="POST" action="{{ route('owner.purchase-orders.archive', $order->id) }}"
                                      onsubmit="return confirm('Archive this order? It will be hidden from the active list but its records are kept.')">
                                    @csrf
                                    <button type="submit" class="tip inline-flex items-center justify-center p-1.5 border rounded-md transition border-red-200 text-red-600 bg-red-50 hover:bg-red-100" data-tip="Archive" aria-label="Archive"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg></button>
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
                                          d="M3 9l1.5-5h15L21 9M3 9h18M3 9v10a1 1 0 001 1h4a1 1 0 001-1v-4a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 001 1h4a1 1 0 001-1V9"/>
                                </svg>
                                <p class="text-sm font-medium text-slate-500">No purchase orders found.</p>
                                <a href="{{ route('owner.purchase-orders.create') }}"
                                   class="text-sm font-medium underline" style="color:#363E48">Create your first purchase order</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
</div>
@include('partials.pagination', ['paginator' => $orders])
    </div>
@endsection
