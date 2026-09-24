@extends('layouts.owner')

@section('title', 'Damaged Supplier Products')

@php $activeNav = 'supplier-orders'; @endphp

@section('content')
    {{-- Back Link --}}
    <a href="{{ route('owner.suppliers.index') }}"
       class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700 mb-3 transition">
        ← Back to Supplier Orders
    </a>

    {{-- Breadcrumb --}}
    <nav class="text-xs text-slate-400 mb-4" aria-label="Breadcrumb">
        <ol class="flex items-center gap-1.5">
            <li><a href="{{ route('owner.dashboard') }}" class="hover:text-slate-600 transition">Dashboard</a></li>
            <li class="select-none">/</li>
            <li><a href="{{ route('owner.suppliers.index') }}" class="hover:text-slate-600 transition">Supplier Orders</a></li>
            <li class="select-none">/</li>
            <li class="text-slate-600 font-medium">Damaged Products</li>
        </ol>
    </nav>

    {{-- Page Header --}}
    <h1 class="text-2xl font-bold mb-6" style="color:#363E48">Damaged Supplier Products</h1>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Damage ID</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Supplier</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Product</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Qty Damaged</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Description</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($damaged as $item)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs text-slate-500">{{ $item->id ?? 'DMG-0001' }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-700">{{ $item->supplier ?? '—' }}</td>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $item->product->name ?? $item->product_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-red-600">{{ $item->qty_damaged ?? 0 }}</td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ isset($item->date) ? \Carbon\Carbon::parse($item->date)->format('M d, Y') : '—' }}
                        </td>
                        <td class="px-4 py-3 text-slate-600 max-w-[200px]">
                            <p class="truncate" title="{{ $item->description ?? '' }}">
                                {{ $item->description ?? '—' }}
                            </p>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php $dmgStatus = $item->status ?? 'Reported'; @endphp
                            @if($dmgStatus === 'Returned to Supplier')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                    Returned to Supplier
                                </span>
                            @elseif($dmgStatus === 'Replacement Received')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    Replacement Received
                                </span>
                            @elseif($dmgStatus === 'Resolved')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                    Resolved
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                    Reported
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('owner.suppliers.damaged.show', $item->return_id ?? 0) }}"
                               class="px-3 py-1 text-xs border border-slate-300 rounded-md text-slate-600 hover:bg-slate-100 transition">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <p class="text-sm font-medium text-slate-500">No damaged products recorded.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
