@extends('layouts.owner')
@section('title', 'Dashboard')
@php $activeNav = 'dashboard'; @endphp

@section('content')
{{-- Page Header --}}
<div class="flex flex-wrap items-start justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl font-bold text-[#363E48]">
            Welcome, {{ auth()->user()->full_name }}
        </h1>
        <p class="mt-1 text-sm text-slate-500">
            Owner / Manager &mdash; {{ now()->format('F d, Y') }}
        </p>
    </div>

    {{-- Period Filter --}}
    <div class="flex flex-col items-end gap-2">
        <div class="flex gap-1 bg-white rounded-lg border border-slate-200 shadow-sm p-1">
            @foreach(['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year'] as $val => $label)
            <a href="{{ route('owner.dashboard', ['period' => $val]) }}"
               class="px-3 py-1.5 rounded-md text-xs font-medium transition-colors
               {{ ($period ?? 'today') === $val ? 'text-white' : 'text-slate-600 hover:bg-slate-100' }}"
               @if(($period ?? 'today') === $val) style="background-color:#363E48;" @endif>
                {{ $label }}
            </a>
            @endforeach
        </div>
        <form method="GET" action="{{ route('owner.dashboard') }}" class="flex items-center gap-1.5">
            <input type="hidden" name="period" value="custom">
            <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}" required
                   class="px-2 py-1 text-xs border border-slate-200 rounded-md focus:outline-none focus:ring-2"
                   style="--tw-ring-color:#363E48;">
            <span class="text-xs text-slate-400">to</span>
            <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" required
                   class="px-2 py-1 text-xs border border-slate-200 rounded-md focus:outline-none focus:ring-2"
                   style="--tw-ring-color:#363E48;">
            <button type="submit"
                    class="px-3 py-1 text-xs font-medium text-white rounded-md hover:opacity-90 transition"
                    style="background-color:#363E48;">
                Apply
            </button>
        </form>
    </div>
</div>

{{-- Stat Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

    {{-- Total Products --}}
    <a href="{{ route('owner.inventory.index') }}" class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 flex flex-col gap-3 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Products</span>
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 text-[#363E48]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" />
                </svg>
            </span>
        </div>
        <div>
            <p class="text-3xl font-bold text-[#363E48]">{{ $totalProducts ?? 11 }}</p>
            <p class="text-xs text-slate-400 mt-0.5">in inventory</p>
        </div>
    </a>

    {{-- Total Stock --}}
    <a href="{{ route('owner.inventory.index') }}" class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 flex flex-col gap-3 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Stock</span>
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 text-[#363E48]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8" />
                </svg>
            </span>
        </div>
        <div>
            <p class="text-3xl font-bold text-[#363E48]">{{ $totalStock ?? '1,922' }}</p>
            <p class="text-xs text-slate-400 mt-0.5">units available</p>
        </div>
    </a>

    {{-- Sales for the selected period (dark card) --}}
    <div class="bg-[#363E48] rounded-xl shadow-sm p-5 flex flex-col gap-3">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wide text-slate-300">Sales &mdash; {{ $periodLabel ?? 'Today' }}</span>
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-white/10 text-[#E0CD66]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </span>
        </div>
        <div>
            <p class="text-3xl font-bold text-white">{{ $todaySales ?? '₱372.50' }}</p>
            <p class="text-xs text-slate-400 mt-0.5">{{ $txnCount ?? 2 }} transactions</p>
        </div>
    </div>

    {{-- Low Stock Items --}}
    <a href="{{ route('owner.inventory.index', ['status' => 'Needs Restock']) }}" class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 flex flex-col gap-3 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Low Stock Items</span>
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-amber-50 text-amber-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
            </span>
        </div>
        <div>
            <p class="text-3xl font-bold text-[#363E48]">{{ $lowStockCount ?? 4 }}</p>
            <p class="text-xs text-slate-400 mt-0.5">{{ $outOfStockCount ?? 1 }} out of stock</p>
        </div>
    </a>
</div>

{{-- Main Grid --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Recent Transactions + Sales Trend (col-span-2) --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <div>
                    <h2 class="text-sm font-semibold text-[#363E48]">Recent Transactions</h2>
                    <p class="text-xs text-slate-400">{{ $periodLabel ?? 'Today' }}</p>
                </div>
                <a href="{{ route('owner.sales.index') }}"
                   class="text-xs font-medium text-[#363E48] hover:text-[#E0CD66] transition-colors">
                    View all &rarr;
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50">
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Transaction ID</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Product(s)</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Total</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Processed By</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($transactions ?? [] as $txn)
                            <tr class="hover:bg-slate-50 transition-colors cursor-pointer"
                                onclick="window.location='{{ route('owner.sales.show', $txn->id) }}'">
                                <td class="px-6 py-3 font-mono text-xs text-slate-700">{{ $txn->code }}</td>
                                <td class="px-6 py-3 text-slate-700">{{ $txn->products }}</td>
                                <td class="px-6 py-3 font-medium text-slate-800">{{ $txn->total }}</td>
                                <td class="px-6 py-3 text-slate-600">{{ $txn->processed_by }}</td>
                                <td class="px-6 py-3">
                                    @if($txn->status === 'Completed')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Completed</span>
                                    @elseif($txn->status === 'Pending')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Pending</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">{{ $txn->status }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-6 text-center text-sm text-slate-400">No transactions for {{ strtolower($periodLabel ?? 'today') }}.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Sales Trend --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center justify-between mb-1">
                <h2 class="text-sm font-semibold text-[#363E48]">Sales Trend</h2>
                <p class="text-xs text-slate-400">{{ $periodLabel ?? 'Today' }}</p>
            </div>
            @php
                $trendPoints = collect($salesTrend['points'] ?? []);
                $trendUnit = $salesTrend['unit'] ?? 'day';
                $trendTotal = $trendPoints->sum('value');
            @endphp
            @if($trendTotal > 0)
                @php
                    $maxVal = $trendPoints->max('value') ?: 1;
                    $pointCount = $trendPoints->count();
                    $labelStep = max(1, intdiv($pointCount, 8));
                    $activePoints = $trendPoints->where('value', '>', 0);
                    $peakPoint = $activePoints->sortByDesc('value')->first();
                    $avgValue = $activePoints->count() > 0 ? $trendTotal / $activePoints->count() : 0;
                @endphp

                {{-- Summary --}}
                <p class="text-xs text-slate-500 mb-4">
                    Total <span class="font-semibold text-slate-700">₱{{ number_format($trendTotal, 2) }}</span>
                    &middot; Peak <span class="font-semibold text-slate-700">{{ $peakPoint['label'] }}</span> (₱{{ number_format($peakPoint['value'], 2) }})
                    &middot; Avg <span class="font-semibold text-slate-700">₱{{ number_format($avgValue, 2) }}</span> / active {{ $trendUnit }}
                </p>

                {{-- Line chart --}}
                @php
                    $chartW = 640; $chartH = 220; $padL = 52; $padR = 22; $padT = 30; $padB = 28;
                    $plotW = $chartW - $padL - $padR; $plotH = $chartH - $padT - $padB;
                    $xAt = fn (int $i) => $pointCount > 1 ? $padL + $i * ($plotW / ($pointCount - 1)) : $padL + $plotW / 2;
                    $yAt = fn (float $v) => $padT + (1 - $v / $maxVal) * $plotH;
                    $coords = $trendPoints->values()->map(fn ($pt, $i) => ['x' => round($xAt($i), 1), 'y' => round($yAt((float) $pt['value']), 1), 'label' => $pt['label'], 'value' => (float) $pt['value']]);
                    $linePath = $coords->map(fn ($c, $i) => ($i === 0 ? 'M' : 'L').$c['x'].' '.$c['y'])->implode(' ');
                    $baseY = $padT + $plotH;
                    $areaPath = $linePath.' L'.$coords->last()['x'].' '.$baseY.' L'.$coords->first()['x'].' '.$baseY.' Z';
                    $yTicks = [$maxVal, $maxVal / 2, 0];
                    $peakIndex = $coords->search(fn ($c) => $c['value'] === (float) $maxVal);
                    $lastActiveIndex = $coords->keys()->filter(fn ($i) => $coords[$i]['value'] > 0)->last();
                @endphp
                <div id="salesTrendChart" class="relative">
                    <svg viewBox="0 0 {{ $chartW }} {{ $chartH }}" class="w-full h-auto" role="img"
                         aria-label="Line chart of revenue per {{ $trendUnit }}. Total ₱{{ number_format($trendTotal, 2) }}.">
                        <text x="{{ $padL - 8 }}" y="10" text-anchor="end" font-size="10" font-weight="600" fill="#64748B">Revenue</text>
                        @foreach($yTicks as $tick)
                            <line x1="{{ $padL }}" x2="{{ $chartW - $padR }}" y1="{{ $yAt((float) $tick) }}" y2="{{ $yAt((float) $tick) }}" stroke="#E2E8F0" stroke-width="1" @if($tick > 0) stroke-dasharray="3 4" @endif/>
                            <text x="{{ $padL - 8 }}" y="{{ $yAt((float) $tick) + 3.5 }}" text-anchor="end" font-size="10" fill="#94A3B8">₱{{ number_format($tick, 0) }}</text>
                        @endforeach
                        <path d="{{ $areaPath }}" fill="#363E48" fill-opacity="0.07"/>
                        <path d="{{ $linePath }}" fill="none" stroke="#363E48" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/>
                        @foreach($coords as $i => $c)
                            @if($c['value'] > 0)
                                @if($i === $peakIndex)
                                    <circle cx="{{ $c['x'] }}" cy="{{ $c['y'] }}" r="6" fill="#E0CD66" stroke="#363E48" stroke-width="2"/>
                                @else
                                    <circle cx="{{ $c['x'] }}" cy="{{ $c['y'] }}" r="3.5" fill="#363E48" stroke="#fff" stroke-width="2"/>
                                @endif
                                @if($i === $peakIndex || $i === $lastActiveIndex)
                                    <text x="{{ min(max($c['x'], $padL + 24), $chartW - $padR - 24) }}" y="{{ $c['y'] - 12 }}" text-anchor="middle" font-size="11" font-weight="600" fill="#363E48">₱{{ number_format($c['value'], 0) }}</text>
                                @endif
                            @endif
                            @if($i % $labelStep === 0)
                                <text x="{{ $c['x'] }}" y="{{ $chartH - 8 }}" text-anchor="middle" font-size="10" fill="#94A3B8">{{ $c['label'] }}</text>
                            @endif
                        @endforeach
                        <line id="trendCursor" y1="{{ $padT }}" y2="{{ $baseY }}" stroke="#94A3B8" stroke-width="1" stroke-dasharray="3 3" style="display:none"/>
                        <circle id="trendDot" r="5" fill="#363E48" stroke="#fff" stroke-width="2" style="display:none"/>
                    </svg>
                    <div id="trendTip" class="pointer-events-none absolute z-10 hidden rounded-lg bg-[#363E48] px-3 py-1.5 text-xs text-white shadow-lg whitespace-nowrap"></div>
                </div>

                {{-- Legend --}}
                <div class="flex flex-wrap items-center gap-x-5 gap-y-1 mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500">
                    <span class="flex items-center gap-1.5">
                        <svg width="22" height="10" viewBox="0 0 22 10"><line x1="0" y1="5" x2="22" y2="5" stroke="#363E48" stroke-width="2" stroke-linecap="round"/><circle cx="11" cy="5" r="3.5" fill="#363E48" stroke="#fff" stroke-width="2"/></svg>
                        Revenue per {{ $trendUnit }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <svg width="14" height="14" viewBox="0 0 14 14"><circle cx="7" cy="7" r="5" fill="#E0CD66" stroke="#363E48" stroke-width="2"/></svg>
                        Peak {{ $trendUnit }} ({{ $peakPoint['label'] }})
                    </span>
                    <span class="flex items-center gap-1.5">
                        <svg width="22" height="10" viewBox="0 0 22 10"><line x1="0" y1="5" x2="22" y2="5" stroke="#CBD5E1" stroke-width="2"/></svg>
                        Flat at ₱0 = no sales
                    </span>
                    <span class="ml-auto text-slate-400">Hover the chart for exact amounts</span>
                </div>
                <script>
                    (function () {
                        const points = @json($coords->values());
                        const box = document.getElementById('salesTrendChart');
                        const svg = box.querySelector('svg');
                        const cursor = document.getElementById('trendCursor');
                        const dot = document.getElementById('trendDot');
                        const tip = document.getElementById('trendTip');
                        const viewWidth = {{ $chartW }};

                        function hide() { cursor.style.display = dot.style.display = 'none'; tip.classList.add('hidden'); }

                        svg.addEventListener('mousemove', event => {
                            const rect = svg.getBoundingClientRect();
                            const x = (event.clientX - rect.left) * (viewWidth / rect.width);
                            const nearest = points.reduce((best, point) => Math.abs(point.x - x) < Math.abs(best.x - x) ? point : best, points[0]);
                            const scale = rect.width / viewWidth;

                            cursor.setAttribute('x1', nearest.x); cursor.setAttribute('x2', nearest.x);
                            dot.setAttribute('cx', nearest.x); dot.setAttribute('cy', nearest.y);
                            cursor.style.display = dot.style.display = '';

                            tip.innerHTML = '<span class="text-white/70">' + nearest.label + '</span><br><strong>₱' + nearest.value.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</strong>';
                            tip.classList.remove('hidden');
                            const left = Math.min(Math.max(nearest.x * scale - tip.offsetWidth / 2, 0), rect.width - tip.offsetWidth);
                            tip.style.left = left + 'px';
                            tip.style.top = Math.max(nearest.y * scale - tip.offsetHeight - 12, 0) + 'px';
                        });
                        svg.addEventListener('mouseleave', hide);
                    })();
                </script>
            @else
                <p class="text-sm text-slate-400 text-center py-14">No sales recorded for {{ strtolower($periodLabel ?? 'today') }}.</p>
            @endif
        </div>
    </div>

    {{-- Right column --}}
    <div class="flex flex-col gap-4">

        {{-- Low / Out of Stock --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h2 class="text-sm font-semibold text-[#363E48]">Low / Out of Stock</h2>
                <a href="{{ route('owner.inventory.index', ['status' => 'Needs Restock']) }}"
                   class="text-xs text-[#363E48] hover:text-[#E0CD66] transition-colors font-medium">
                    View all &rarr;
                </a>
            </div>
            <ul class="divide-y divide-slate-100">
                @forelse($lowStockProducts ?? [] as $product)
                    <li class="flex items-center justify-between px-5 py-3">
                        <div>
                            <p class="text-sm font-medium text-slate-700">{{ $product->name }}</p>
                            <p class="text-xs text-slate-400">{{ $product->stock }} units left</p>
                        </div>
                        @if($product->stock === 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Out</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Low</span>
                        @endif
                    </li>
                @empty
                    <li class="px-5 py-6 text-center text-sm text-slate-400">All products are sufficiently stocked.</li>
                @endforelse
            </ul>
        </div>

        {{-- Fast & Slow Moving Products --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h2 class="text-sm font-semibold text-[#363E48]">Fast &amp; Slow Moving</h2>
                <a href="{{ route('owner.reports.index', ['type' => 'inventory']) }}"
                   class="text-xs text-[#363E48] hover:text-[#E0CD66] transition-colors font-medium">
                    View all &rarr;
                </a>
            </div>
            <div class="px-5 py-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-green-600 mb-2">Fast-Moving</p>
                <ul class="divide-y divide-slate-100 mb-3">
                    @forelse($fastMoving ?? [] as $product)
                        <li class="flex items-center justify-between py-2">
                            <p class="text-sm text-slate-700">{{ $product->name }}</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ $product->units_sold }} sold</span>
                        </li>
                    @empty
                        <li class="py-2 text-sm text-slate-400">No fast-moving products yet.</li>
                    @endforelse
                </ul>
                <p class="text-xs font-semibold uppercase tracking-wide text-amber-600 mb-2">Slow-Moving</p>
                <ul class="divide-y divide-slate-100">
                    @forelse($slowMoving ?? [] as $product)
                        <li class="flex items-center justify-between py-2">
                            <p class="text-sm text-slate-700">{{ $product->name }}</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">{{ $product->units_sold }} sold</span>
                        </li>
                    @empty
                        <li class="py-2 text-sm text-slate-400">No slow-moving products.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Supplier Orders --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h2 class="text-sm font-semibold text-[#363E48]">Supplier Orders</h2>
                <a href="{{ route('owner.suppliers.orders') }}"
                   class="text-xs text-[#363E48] hover:text-[#E0CD66] transition-colors font-medium">
                    View all &rarr;
                </a>
            </div>
            <ul class="divide-y divide-slate-100">
                @forelse($recentOrders ?? [] as $order)
                    <li class="px-5 py-3">
                        <p class="text-sm font-medium text-slate-700">{{ $order->supplier }}</p>
                        <p class="text-xs text-slate-400">{{ $order->items }} items &middot; {{ $order->date }}</p>
                    </li>
                @empty
                    <li class="px-5 py-6 text-center text-sm text-slate-400">No supplier orders yet.</li>
                @endforelse
            </ul>
        </div>

        {{-- Returns & Warranties --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h2 class="text-sm font-semibold text-[#363E48]">Returns &amp; Warranties</h2>
                <a href="{{ route('owner.returns.index') }}"
                   class="text-xs text-[#363E48] hover:text-[#E0CD66] transition-colors font-medium">
                    View all &rarr;
                </a>
            </div>
            <ul class="divide-y divide-slate-100">
                <li class="px-5 py-3">
                    <p class="text-sm font-medium text-slate-700">TXN-2024-005 &mdash; Return</p>
                    <p class="text-xs text-slate-400">Switch Panel 4-gang &middot; Jan 13, 2024</p>
                </li>
                <li class="px-5 py-3">
                    <p class="text-sm font-medium text-slate-700">TXN-2024-003 &mdash; Warranty</p>
                    <p class="text-xs text-slate-400">Circuit Breaker 15A &middot; Jan 12, 2024</p>
                </li>
            </ul>
        </div>

    </div>
</div>
@endsection
