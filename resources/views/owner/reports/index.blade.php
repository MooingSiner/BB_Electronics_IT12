@extends('layouts.owner')

@section('title', 'Reports')
@php $activeNav = 'reports'; @endphp

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Reports</h1>
        <p class="text-sm text-slate-500 mt-1">Generate sales and inventory reports.</p>
    </div>

    {{-- Filter Card --}}
    <div class="bg-white rounded-xl border shadow-sm p-6">
        <h2 class="font-semibold text-slate-800 mb-4">Generate Report</h2>
        <form method="GET" action="{{ route('owner.reports.index') }}" class="space-y-4">
            <div>
                <label for="type" class="block text-sm font-medium text-slate-700 mb-1">Report Type</label>
                <select id="type" name="type"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                    <option value="">Select a report type...</option>
                    <option value="sales" {{ request('type') === 'sales' ? 'selected' : '' }}>Sales Report</option>
                    <option value="inventory" {{ request('type') === 'inventory' ? 'selected' : '' }}>Inventory Report (incl. fast/slow-moving)</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="date_from" class="block text-sm font-medium text-slate-700 mb-1">Date From</label>
                    <input type="date" id="date_from" name="date_from" value="{{ request('date_from', $dateFrom->format('Y-m-d') ?? '') }}"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-medium text-slate-700 mb-1">Date To</label>
                    <input type="date" id="date_to" name="date_to" value="{{ request('date_to', $dateTo->format('Y-m-d') ?? '') }}"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                </div>
            </div>
            <div>
                <button type="submit"
                        class="px-5 py-2 text-sm font-medium text-white rounded-lg transition-opacity hover:opacity-90"
                        style="background-color:#363E48">
                    Generate Report
                </button>
            </div>
        </form>
    </div>

    {{-- Results --}}
    @if($report && $type === 'sales')
    <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <h2 class="font-semibold text-slate-800">
                Sales Report &mdash; {{ $dateFrom->format('M d, Y') }} to {{ $dateTo->format('M d, Y') }}
            </h2>
            <button onclick="window.print()" class="text-sm font-medium hover:underline" style="color:#363E48">Print</button>
        </div>

        {{-- Summary Stats --}}
        <div class="grid grid-cols-3 divide-x border-b">
            <div class="px-6 py-5">
                <p class="text-xs text-slate-500 uppercase tracking-wide font-medium">Total Revenue</p>
                <p class="text-2xl font-bold text-slate-800 mt-1">₱{{ number_format($report['revenue'], 2) }}</p>
            </div>
            <div class="px-6 py-5">
                <p class="text-xs text-slate-500 uppercase tracking-wide font-medium">Total Transactions</p>
                <p class="text-2xl font-bold text-slate-800 mt-1">{{ $report['count'] }}</p>
            </div>
            <div class="px-6 py-5">
                <p class="text-xs text-slate-500 uppercase tracking-wide font-medium">Avg Transaction</p>
                <p class="text-2xl font-bold text-slate-800 mt-1">₱{{ number_format($report['average'], 2) }}</p>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Date</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Transactions</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Revenue</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Top Product</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($report['rows'] as $row)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-slate-700">{{ $row['date'] }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $row['transactions'] }}</td>
                        <td class="px-6 py-4 text-slate-700 font-medium">₱{{ number_format($row['revenue'], 2) }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $row['top_product'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-slate-400 text-sm">No completed sales in this date range.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @elseif($report && $type === 'inventory')
    <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <h2 class="font-semibold text-slate-800">
                Inventory Report &mdash; movement from {{ $dateFrom->format('M d, Y') }} to {{ $dateTo->format('M d, Y') }}
            </h2>
            <button onclick="window.print()" class="text-sm font-medium hover:underline" style="color:#363E48">Print</button>
        </div>

        {{-- Summary Stats --}}
        <div class="grid grid-cols-3 divide-x border-b">
            <div class="px-6 py-5">
                <p class="text-xs text-slate-500 uppercase tracking-wide font-medium">Active Products</p>
                <p class="text-2xl font-bold text-slate-800 mt-1">{{ $report['total_products'] }}</p>
            </div>
            <div class="px-6 py-5">
                <p class="text-xs text-slate-500 uppercase tracking-wide font-medium">Stock Value</p>
                <p class="text-2xl font-bold text-slate-800 mt-1">₱{{ number_format($report['total_stock_value'], 2) }}</p>
            </div>
            <div class="px-6 py-5">
                <p class="text-xs text-slate-500 uppercase tracking-wide font-medium">Low / Out of Stock</p>
                <p class="text-2xl font-bold text-slate-800 mt-1">{{ $report['low_stock_count'] }}</p>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Product</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Category</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Stock</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Reorder Level</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Units Sold</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Movement</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($report['rows'] as $row)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-slate-700">{{ $row['name'] }} <span class="text-slate-400">({{ $row['code'] }})</span></td>
                        <td class="px-6 py-4 text-slate-600">{{ $row['category'] }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $row['stock'] }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $row['reorder_level'] }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $row['units_sold'] }}</td>
                        <td class="px-6 py-4">
                            @if($row['movement'] === 'Fast-Moving')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Fast-Moving</span>
                            @elseif($row['movement'] === 'Slow-Moving')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Slow-Moving</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">No Movement</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm">No active products found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
