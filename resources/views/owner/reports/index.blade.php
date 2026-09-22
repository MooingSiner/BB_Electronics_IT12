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
                    <option value="inventory" {{ request('type') === 'inventory' ? 'selected' : '' }}>Inventory Report</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="date_from" class="block text-sm font-medium text-slate-700 mb-1">Date From</label>
                    <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-medium text-slate-700 mb-1">Date To</label>
                    <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}"
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
    @if(request('type') || isset($report))
    <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h2 class="font-semibold text-slate-800">
                {{ ucfirst(request('type', 'Sales')) }} Report
                &mdash;
                {{ request('date_from', 'Jan 1') }} to {{ request('date_to', 'Jan 15') }}, 2024
            </h2>
        </div>

        {{-- Summary Stats --}}
        <div class="grid grid-cols-3 divide-x border-b">
            <div class="px-6 py-5">
                <p class="text-xs text-slate-500 uppercase tracking-wide font-medium">Total Revenue</p>
                <p class="text-2xl font-bold text-slate-800 mt-1">{{ $report['revenue'] ?? '₱792.50' }}</p>
            </div>
            <div class="px-6 py-5">
                <p class="text-xs text-slate-500 uppercase tracking-wide font-medium">Total Transactions</p>
                <p class="text-2xl font-bold text-slate-800 mt-1">{{ $report['count'] ?? 6 }}</p>
            </div>
            <div class="px-6 py-5">
                <p class="text-xs text-slate-500 uppercase tracking-wide font-medium">Avg Transaction</p>
                <p class="text-2xl font-bold text-slate-800 mt-1">{{ $report['average'] ?? '₱132.08' }}</p>
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
                    @forelse($report['rows'] ?? [] as $row)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-slate-700">{{ $row['date'] }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $row['transactions'] }}</td>
                        <td class="px-6 py-4 text-slate-700 font-medium">{{ $row['revenue'] }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $row['top_product'] }}</td>
                    </tr>
                    @empty
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-slate-700">Jan 1, 2024</td>
                        <td class="px-6 py-4 text-slate-600">2</td>
                        <td class="px-6 py-4 text-slate-700 font-medium">₱250.00</td>
                        <td class="px-6 py-4 text-slate-600">USB-C Hub</td>
                    </tr>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-slate-700">Jan 3, 2024</td>
                        <td class="px-6 py-4 text-slate-600">1</td>
                        <td class="px-6 py-4 text-slate-700 font-medium">₱89.00</td>
                        <td class="px-6 py-4 text-slate-600">HDMI Cable</td>
                    </tr>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-slate-700">Jan 7, 2024</td>
                        <td class="px-6 py-4 text-slate-600">1</td>
                        <td class="px-6 py-4 text-slate-700 font-medium">₱145.00</td>
                        <td class="px-6 py-4 text-slate-600">USB-A to USB-C Adapter</td>
                    </tr>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-slate-700">Jan 10, 2024</td>
                        <td class="px-6 py-4 text-slate-600">1</td>
                        <td class="px-6 py-4 text-slate-700 font-medium">₱175.50</td>
                        <td class="px-6 py-4 text-slate-600">Wireless Mouse</td>
                    </tr>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-slate-700">Jan 12, 2024</td>
                        <td class="px-6 py-4 text-slate-600">1</td>
                        <td class="px-6 py-4 text-slate-700 font-medium">₱133.00</td>
                        <td class="px-6 py-4 text-slate-600">Mechanical Keyboard</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
