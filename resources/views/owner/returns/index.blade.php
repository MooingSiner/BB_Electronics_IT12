@extends('layouts.owner')

@section('title', 'Returns & Warranties')
@php $activeNav = 'returns'; @endphp

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Returns & Warranties</h1>
        <p class="text-sm text-slate-500 mt-1">Manage customer returns and product warranty requests.</p>
    </div>

    {{-- Tab Navigation --}}
    <div class="flex gap-1 bg-white rounded-lg p-1 border shadow-sm w-fit">
        <a href="{{ route('owner.returns.index', ['tab' => 'returns']) }}"
           class="px-4 py-2 rounded-md text-sm font-medium {{ request('tab', 'returns') === 'returns' ? 'bg-slate-800 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
            Customer Returns
        </a>
        <a href="{{ route('owner.returns.index', ['tab' => 'warranty']) }}"
           class="px-4 py-2 rounded-md text-sm font-medium {{ request('tab') === 'warranty' ? 'bg-slate-800 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
            Warranty
        </a>
    </div>

    @if(request('tab', 'returns') === 'returns')
        {{-- Customer Returns Tab --}}
        <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b">
                <h2 class="font-semibold text-slate-800">Customer Returns</h2>
                <a href="{{ route('owner.returns.process') }}"
                   class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-white rounded-lg"
                   style="background-color:#363E48">
                    + Process Return
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b">
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Return ID</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Transaction</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Product</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Qty</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Reason</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Resolution</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Date</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($returns as $return)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-slate-800">{{ $return->id }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $return->transaction_id }}</td>
                            <td class="px-6 py-4 text-slate-700">{{ $return->product_name }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $return->qty }}</td>
                            <td class="px-6 py-4 text-slate-600 max-w-xs truncate">{{ $return->reason }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $return->resolution }}</td>
                            <td class="px-6 py-4 text-slate-500">{{ $return->created_at?->format('M d, Y') }}</td>
                            <td class="px-6 py-4">
                                @if($return->status === 'Pending')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Pending</span>
                                @elseif($return->status === 'Completed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>
                                @elseif($return->status === 'Refunded')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Refunded</span>
                                @elseif($return->status === 'Replaced')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">Replaced</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $return->status }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('owner.returns.show', $return->id) }}"
                                   class="text-sm font-medium hover:underline"
                                   style="color:#363E48">View</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-slate-400">
                                <p class="text-sm">No returns found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @else
        {{-- Warranty Tab --}}
        <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b">
                <h2 class="font-semibold text-slate-800">Warranty Claims</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b">
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Warranty ID</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Transaction</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Product</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Warranty Ref</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Issue</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Date</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($warranties as $w)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-slate-800">{{ $w->id }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $w->transaction_id }}</td>
                            <td class="px-6 py-4 text-slate-700">{{ $w->product_name }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $w->warranty_ref }}</td>
                            <td class="px-6 py-4 text-slate-600 max-w-xs truncate">{{ $w->issue }}</td>
                            <td class="px-6 py-4 text-slate-500">{{ $w->created_at?->format('M d, Y') }}</td>
                            <td class="px-6 py-4">
                                @if($w->status === 'Under Review')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Under Review</span>
                                @elseif($w->status === 'Active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                                @elseif($w->status === 'Repaired')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Repaired</span>
                                @elseif($w->status === 'Completed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>
                                @elseif($w->status === 'Replaced')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">Replaced</span>
                                @elseif($w->status === 'Refunded')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Refunded</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $w->status }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('owner.returns.warranty', $w->id) }}"
                                   class="text-sm font-medium hover:underline"
                                   style="color:#363E48">View</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                <p class="text-sm">No warranty claims found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
@endsection
