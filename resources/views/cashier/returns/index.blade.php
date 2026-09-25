@extends('layouts.cashier')

@section('title', 'Returns & Warranties')

@php $activeNav = 'returns'; @endphp

@section('content')
<div class="p-6 space-y-6">

    {{-- Page Header --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Returns &amp; Warranties</h1>
        <p class="text-sm text-slate-500 mt-1">Process customer returns and manage warranty claims.</p>
    </div>

    {{-- Tab Navigation --}}
    @php $activeTab = request('tab', 'returns'); @endphp
    <div class="flex gap-1 border-b border-slate-200">
        <a href="{{ route('cashier.returns.index', ['tab' => 'returns']) }}"
           class="px-5 py-2.5 text-sm font-medium border-b-2 transition-colors
           {{ $activeTab === 'returns'
              ? 'border-current'
              : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}"
           @if($activeTab === 'returns') style="color:#363E48; border-color:#363E48;" @endif>
            Customer Returns
        </a>
        <a href="{{ route('cashier.returns.index', ['tab' => 'warranty']) }}"
           class="px-5 py-2.5 text-sm font-medium border-b-2 transition-colors
           {{ $activeTab === 'warranty'
              ? 'border-current'
              : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}"
           @if($activeTab === 'warranty') style="color:#363E48; border-color:#363E48;" @endif>
            Warranty
        </a>
    </div>

    @if($activeTab === 'returns')

    {{-- Customer Returns Tab --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-semibold text-slate-800">Customer Returns</h2>
            <a href="{{ route('cashier.returns.process') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white shadow-sm hover:opacity-90 transition"
               style="background-color:#363E48;">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Process Return
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Return ID</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Transaction</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Product</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Reason</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Amount</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Date</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Processed By</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                        <th class="sticky right-0 text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide bg-slate-50 shadow-[-6px_0_8px_-6px_rgba(0,0,0,0.15)]">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($returns ?? [] as $ret)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-4 text-slate-500 font-mono text-xs">#{{ $ret->id }}</td>
                        <td class="px-5 py-4">
                            @if($ret->transaction_id)
                            <a href="{{ route('cashier.sales.show', $ret->transaction_id) }}"
                               class="font-medium hover:underline text-xs" style="color:#363E48;">
                                {{ $ret->transaction_code }}
                            </a>
                            @else
                            <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 font-medium text-slate-800">{{ $ret->product_name ?? '—' }}</td>
                        <td class="px-5 py-4 text-slate-600 max-w-36 truncate">{{ $ret->reason ?? '—' }}</td>
                        <td class="px-5 py-4 font-semibold text-slate-800">₱{{ number_format($ret->amount ?? 0, 2) }}</td>
                        <td class="px-5 py-4 text-slate-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($ret->created_at)->format('M d, Y') }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ $ret->processed_by ?? $ret->user->name ?? '—' }}</td>
                        <td class="px-5 py-4">
                            @if(($ret->status ?? '') === 'Pending')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Pending</span>
                            @elseif(($ret->status ?? '') === 'Approved')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Approved</span>
                            @elseif(($ret->status ?? '') === 'Rejected')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Rejected</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $ret->status ?? '—' }}</span>
                            @endif
                        </td>
                        <td class="sticky right-0 px-5 py-4 bg-white shadow-[-6px_0_8px_-6px_rgba(0,0,0,0.15)]">
                            <a href="{{ route('cashier.returns.show', $ret->id) }}"
                               class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium border border-slate-200 text-slate-600 bg-white hover:bg-slate-50 transition-colors">
                                View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-16 text-center text-slate-400 text-sm">
                            <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                            </svg>
                            No customer returns found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(isset($returns) && $returns instanceof \Illuminate\Pagination\LengthAwarePaginator && $returns->hasPages())
        <div class="px-5 py-4 border-t border-slate-100">
            {{ $returns->withQueryString()->links() }}
        </div>
        @endif
    </div>

    @else

    {{-- Warranty Tab --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-800">Warranty Claims</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Claim ID</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Product</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Issue</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Purchase Date</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Warranty Until</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Date Filed</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                        <th class="sticky right-0 text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide bg-slate-50 shadow-[-6px_0_8px_-6px_rgba(0,0,0,0.15)]">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($warranties ?? [] as $w)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-4 text-slate-500 font-mono text-xs">#{{ $w->id }}</td>
                        <td class="px-5 py-4 font-medium text-slate-800">{{ $w->product_name ?? '—' }}</td>
                        <td class="px-5 py-4 text-slate-600 max-w-40 truncate">{{ $w->issue ?? '—' }}</td>
                        <td class="px-5 py-4 text-slate-500 whitespace-nowrap">
                            {{ $w->purchase_date ? \Carbon\Carbon::parse($w->purchase_date)->format('M d, Y') : '—' }}
                        </td>
                        <td class="px-5 py-4 text-slate-500 whitespace-nowrap">
                            {{ $w->warranty_until ? \Carbon\Carbon::parse($w->warranty_until)->format('M d, Y') : '—' }}
                        </td>
                        <td class="px-5 py-4 text-slate-500 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($w->created_at)->format('M d, Y') }}
                        </td>
                        <td class="px-5 py-4">
                            @if(($w->status ?? '') === 'Pending')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Pending</span>
                            @elseif(($w->status ?? '') === 'Approved')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Approved</span>
                            @elseif(($w->status ?? '') === 'Rejected')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Rejected</span>
                            @elseif(($w->status ?? '') === 'In Repair')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">In Repair</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $w->status ?? '—' }}</span>
                            @endif
                        </td>
                        <td class="sticky right-0 px-5 py-4 bg-white shadow-[-6px_0_8px_-6px_rgba(0,0,0,0.15)]">
                            <a href="{{ route('cashier.returns.warranty', $w->id) }}"
                               class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium border border-slate-200 text-slate-600 bg-white hover:bg-slate-50 transition-colors">
                                View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-16 text-center text-slate-400 text-sm">
                            <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            No warranty claims found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(isset($warranties) && $warranties instanceof \Illuminate\Pagination\LengthAwarePaginator && $warranties->hasPages())
        <div class="px-5 py-4 border-t border-slate-100">
            {{ $warranties->withQueryString()->links() }}
        </div>
        @endif
    </div>

    @endif

</div>
@endsection
