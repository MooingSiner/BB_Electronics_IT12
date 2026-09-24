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
                            <span class="font-mono text-xs text-slate-700">{{ $txn->id }}</span>
                        </td>
                        <td class="px-5 py-3 text-slate-700 max-w-[180px] truncate">{{ $txn->products }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $txn->qty ?? '—' }}</td>
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
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Completed</span>
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
                                <a href="{{ route('owner.sales.show', $txn->id) }}"
                                   class="text-xs font-medium text-[#363E48] hover:text-[#E0CD66] transition-colors">
                                    View
                                </a>
                                <span class="text-slate-300">|</span>
                                <a href="{{ route('owner.sales.receipt', $txn->id) }}"
                                   class="text-xs font-medium text-slate-500 hover:text-[#363E48] transition-colors">
                                    Print
                                </a>
                                @if($txn->status === 'Completed')
                                    <span class="text-slate-300">|</span>
                                    <form method="POST" action="{{ route('owner.returns.store') }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="transaction_id" value="{{ $txn->id }}">
                                        <button type="submit"
                                                class="text-xs font-medium text-red-500 hover:text-red-700 transition-colors"
                                                onclick="return confirm('Process a return for {{ $txn->id }}?')">
                                            Return
                                        </button>
                                    </form>
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
                                    <a href="{{ route('owner.sales.show', $row['id']) }}"
                                       class="text-xs font-medium text-[#363E48] hover:text-[#E0CD66] transition-colors">View</a>
                                    <span class="text-slate-300">|</span>
                                    <a href="{{ route('owner.sales.receipt', $row['id']) }}"
                                       class="text-xs font-medium text-slate-500 hover:text-[#363E48] transition-colors">Print</a>
                                    @if($row['status'] === 'Completed')
                                        <span class="text-slate-300">|</span>
                                        <button class="text-xs font-medium text-red-500 hover:text-red-700 transition-colors">Return</button>
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
