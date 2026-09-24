@extends('layouts.owner')

@section('title', 'Stock History — ' . $item->name)

@php $activeNav = 'inventory'; @endphp

@section('content')
    <a href="{{ route('owner.inventory.index') }}"
       class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700 mb-4 transition">
        ← Back to Inventory
    </a>

    {{-- Header Card --}}
    <div class="rounded-2xl shadow-sm border border-slate-200 p-6 mb-6" style="background: linear-gradient(135deg, #363E48 0%, #454f5c 100%);">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl flex items-center justify-center flex-shrink-0 accent-bg">
                <svg class="w-7 h-7 accent-text" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white">Stock History</h1>
                <p class="text-sm text-white/60 mt-1">{{ $item->name }} <span class="font-mono">({{ $item->code }})</span></p>
            </div>
        </div>
    </div>

    {{-- Summary Strip --}}
    @php
        $totalIn = $movements->where('type', 'In')->sum('quantity');
        $totalOut = $movements->where('type', 'Out')->sum('quantity');
        $net = $totalIn - $totalOut;
    @endphp
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex items-start gap-4">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-green-50 text-green-600 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m0 0l-6 6m6-6l6 6" />
                </svg>
            </span>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Total Stock In</p>
                <p class="text-xl font-bold text-green-600">+{{ number_format($totalIn) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex items-start gap-4">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-red-50 text-red-500 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m0 0l-6-6m6 6l6-6" />
                </svg>
            </span>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Total Stock Out</p>
                <p class="text-xl font-bold text-red-600">−{{ number_format($totalOut) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex items-start gap-4">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-slate-100 text-[#363E48] flex-shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4 4m-4-4l4-4" />
                </svg>
            </span>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Net Change</p>
                <p class="text-xl font-bold {{ $net >= 0 ? 'text-slate-800' : 'text-red-600' }}">{{ $net >= 0 ? '+' : '' }}{{ number_format($net) }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Date</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">Type</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Quantity</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Reason</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">By</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($movements as $movement)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3 text-slate-500 whitespace-nowrap">
                            {{ $movement->date ? \Carbon\Carbon::parse($movement->date)->format('M d, Y') : '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1.5">
                                @if($movement->type === 'In')
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-100 text-green-700">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m0 0l-6 6m6-6l6 6" />
                                        </svg>
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">In</span>
                                @else
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-700">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m0 0l-6-6m6 6l6-6" />
                                        </svg>
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Out</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold {{ $movement->type === 'In' ? 'text-green-700' : 'text-red-700' }}">
                            {{ $movement->type === 'In' ? '+' : '−' }}{{ $movement->quantity }}
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $movement->reason }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $movement->by }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-slate-400 text-sm">No stock movements recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
