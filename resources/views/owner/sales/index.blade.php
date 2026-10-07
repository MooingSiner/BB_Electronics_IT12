@extends('layouts.owner')
@section('title', 'Sales Transactions')
@php $activeNav = 'sales'; @endphp

@section('breadcrumb')
    <nav class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('owner.dashboard') }}" class="hover:text-[#363E48] transition-colors">Dashboard</a>
        <span>/</span>
        <span class="text-[#363E48] font-medium">Sales Transactions</span>
    </nav>
@endsection

@section('content')
{{-- Page Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-[#363E48]">Sales Transactions</h1>
        <p class="mt-0.5 text-sm text-slate-500">All recorded transactions in the system</p>
    </div>
</div>

{{-- Main Card --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200">

    {{-- Filters --}}
    <div class="p-4 border-b border-slate-100">
        <form method="GET" action="{{ route('owner.sales.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search transaction ID or product..."
                    class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#363E48]/20 focus:border-[#363E48] transition"
                />
            </div>

            <select name="status"
                    class="px-3 py-2 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#363E48]/20 focus:border-[#363E48] transition bg-white text-slate-700">
                <option value="">All Statuses</option>
                <option value="Completed" @selected(request('status') === 'Completed')>Completed</option>
                <option value="Pending"   @selected(request('status') === 'Pending')>Pending</option>
                <option value="Returned"  @selected(request('status') === 'Returned')>Returned</option>
                <option value="Voided"    @selected(request('status') === 'Voided')>Voided</option>
            </select>

            <select name="discount"
                    class="px-3 py-2 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#363E48]/20 focus:border-[#363E48] transition bg-white text-slate-700">
                <option value="">All Discounts</option>
                <option value="with"    @selected(request('discount') === 'with')>With Discount</option>
                <option value="without" @selected(request('discount') === 'without')>No Discount</option>
            </select>

            <button type="submit"
                    class="px-4 py-2 rounded-lg bg-[#363E48] text-white text-sm font-semibold hover:bg-[#2a3039] transition-colors">
                Filter
            </button>

            @if(request()->hasAny(['search','status','discount']))
                <a href="{{ route('owner.sales.index') }}"
                   class="px-3 py-2 rounded-lg text-sm text-slate-500 hover:text-[#363E48] transition-colors">
                    Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Transaction ID</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Product(s)</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Qty</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Unit Price</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Total</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Discount</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Payment</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Date</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Processed By</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($transactions ?? [] as $txn)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3">
                            <span class="font-mono text-xs text-slate-700">{{ $txn->code }}</span>
                        </td>
                        <td class="px-5 py-3 text-slate-700 max-w-[180px] truncate">{{ $txn->products }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $txn->qty ?? '—' }}</td>
                        <td class="px-5 py-3 text-slate-600 max-w-[140px] truncate" title="{{ $txn->unit_prices }}">{{ $txn->unit_prices }}</td>
                        <td class="px-5 py-3 font-medium text-slate-800">{{ $txn->total }}</td>
                        <td class="px-5 py-3">
                            @if($txn->discount)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                    {{ $txn->discount }}%
                                </span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-slate-600">{{ $txn->payment_method ?? '—' }}</td>
                        <td class="px-5 py-3 text-slate-600 whitespace-nowrap">{{ $txn->date ?? '—' }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $txn->processed_by ?? '—' }}</td>
                        <td class="px-5 py-3">
                            @if($txn->status === 'Completed')
                                @if($txn->return_label ?? null)
                                    @include('partials.return-badge', ['label' => $txn->return_label ?? null])
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Completed</span>
                                @endif
                            @elseif($txn->status === 'Pending')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Pending</span>
                            @elseif($txn->status === 'Returned')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">Returned</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">{{ $txn->status }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('owner.sales.show', $txn->id) }}" class="tip inline-flex items-center justify-center p-1.5 border rounded-md transition border-slate-300 text-slate-600 hover:bg-slate-100" data-tip="View" aria-label="View"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></a>
                                <a href="{{ route('owner.sales.receipt', $txn->id) }}" class="tip inline-flex items-center justify-center p-1.5 border rounded-md transition border-slate-300 text-slate-600 hover:bg-slate-100" data-tip="Print" aria-label="Print"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.056 48.056 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/></svg></a>
                                @if($txn->status === 'Completed')
                                    <a href="{{ route('owner.returns.process', ['transaction_id' => $txn->id]) }}" class="tip inline-flex items-center justify-center p-1.5 border rounded-md transition border-red-200 text-red-600 bg-red-50 hover:bg-red-100" data-tip="Return" aria-label="Return"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3"/></svg></a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    {{-- Sample hardcoded rows --}}
                    @php
                        $sampleRows = [
                            ['id'=>'TXN-2024-001','products'=>'LED Bulb 9W','qty'=>5,'total'=>'₱202.50','discount'=>null,'payment'=>'Cash','date'=>'Jan 15, 2024','by'=>'Ana Reyes','status'=>'Completed'],
                            ['id'=>'TXN-2024-002','products'=>'Extension Cord 5m','qty'=>2,'total'=>'₱170.00','discount'=>null,'payment'=>'GCash','date'=>'Jan 15, 2024','by'=>'Ana Reyes','status'=>'Completed'],
                            ['id'=>'TXN-2024-003','products'=>'Circuit Breaker 15A','qty'=>1,'total'=>'₱405.00','discount'=>10,'payment'=>'Cash','date'=>'Jan 14, 2024','by'=>'Carlo Mena','status'=>'Pending'],
                            ['id'=>'TXN-2024-004','products'=>'Wire 2.0mm (10m)','qty'=>3,'total'=>'₱320.00','discount'=>null,'payment'=>'Cheque','date'=>'Jan 14, 2024','by'=>'Ana Reyes','status'=>'Completed'],
                            ['id'=>'TXN-2024-005','products'=>'Switch Panel 4-gang','qty'=>1,'total'=>'₱285.00','discount'=>null,'payment'=>'Cash','date'=>'Jan 13, 2024','by'=>'Carlo Mena','status'=>'Returned'],
                        ];
                    @endphp
                    @foreach($sampleRows as $row)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3"><span class="font-mono text-xs text-slate-700">{{ $row['id'] }}</span></td>
                            <td class="px-5 py-3 text-slate-700">{{ $row['products'] }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $row['qty'] }}</td>
                            <td class="px-5 py-3 text-slate-600">—</td>
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $row['total'] }}</td>
                            <td class="px-5 py-3">
                                @if($row['discount'])
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">{{ $row['discount'] }}%</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $row['payment'] }}</td>
                            <td class="px-5 py-3 text-slate-600 whitespace-nowrap">{{ $row['date'] }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $row['by'] }}</td>
                            <td class="px-5 py-3">
                                @if($row['status'] === 'Completed')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Completed</span>
                                @elseif($row['status'] === 'Pending')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Pending</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $row['status'] }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('owner.sales.show', $row['id']) }}" class="tip inline-flex items-center justify-center p-1.5 border rounded-md transition border-slate-300 text-slate-600 hover:bg-slate-100" data-tip="View" aria-label="View"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></a>
                                    <a href="{{ route('owner.sales.receipt', $row['id']) }}" class="tip inline-flex items-center justify-center p-1.5 border rounded-md transition border-slate-300 text-slate-600 hover:bg-slate-100" data-tip="Print" aria-label="Print"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.056 48.056 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/></svg></a>
                                    @if($row['status'] === 'Completed')
                                        <button  class="tip inline-flex items-center justify-center p-1.5 border rounded-md transition border-red-200 text-red-600 bg-red-50 hover:bg-red-100" data-tip="Return" aria-label="Return"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3"/></svg></button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if(isset($transactions) && $transactions->hasPages())
        <div class="px-5 py-4 border-t border-slate-100">
            {{ $transactions->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
