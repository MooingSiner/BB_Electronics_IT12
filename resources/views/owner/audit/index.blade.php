@extends('layouts.owner')

@section('title', 'Audit Log')
@php $activeNav = 'audit'; @endphp

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Audit Log</h1>
        <p class="text-sm text-slate-500 mt-1">Traceable record of price changes, stock adjustments, voids, refunds, and warranty outcomes.</p>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-xl border shadow-sm p-4">
        <form method="GET" action="{{ route('owner.audit.index') }}" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Action</label>
                <select name="action" class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                    <option value="">All Actions</option>
                    <option value="price_change" {{ request('action') === 'price_change' ? 'selected' : '' }}>Price Change</option>
                    <option value="stock_adjustment" {{ request('action') === 'stock_adjustment' ? 'selected' : '' }}>Stock Adjustment</option>
                    <option value="void" {{ request('action') === 'void' ? 'selected' : '' }}>Void</option>
                    <option value="refund" {{ request('action') === 'refund' ? 'selected' : '' }}>Refund</option>
                    <option value="warranty_outcome" {{ request('action') === 'warranty_outcome' ? 'selected' : '' }}>Warranty Outcome</option>
                </select>
            </div>
            <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white rounded-lg transition-opacity hover:opacity-90"
                    style="background-color:#363E48">
                Filter
            </button>
            @if(request()->hasAny(['action']))
            <a href="{{ route('owner.audit.index') }}"
               class="px-4 py-2 text-sm text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                Clear
            </a>
            @endif
        </form>
    </div>

    {{-- Log Table --}}
    <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Date &amp; Time</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Action</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Description</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">User</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-slate-500 whitespace-nowrap">{{ $log->created_at->format('M d, Y g:i A') }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                {{ ucwords(str_replace('_', ' ', $log->action)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-700">{{ $log->description }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $log->by }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                            <p class="text-sm">No audit log entries found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $logs->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
