@extends('layouts.owner')
@section('title', ($txn->code ?? 'TXN-2024-001') . ' — Transaction Detail')
@php $activeNav = 'sales'; @endphp

@section('content')
{{-- Back Button --}}
<div class="mb-5">
    <a href="{{ route('owner.sales.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-[#363E48] transition-colors font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
        Back to Sales
    </a>
</div>

{{-- Page Header --}}
<div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-[#363E48] font-mono">{{ $txn->code ?? 'TXN-2024-001' }}</h1>
        <p class="mt-0.5 text-sm text-slate-500">
            Transaction details for {{ isset($txn->created_at) ? \Carbon\Carbon::parse($txn->created_at)->format('F d, Y') : '2024-01-15' }}
        </p>
    </div>
    <div class="flex items-center gap-3 flex-shrink-0">
        <a href="{{ route('owner.sales.receipt', $txn->id ?? 'TXN-2024-001') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-[#363E48] text-[#363E48] text-sm font-semibold hover:bg-[#363E48]/5 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.056 48.056 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
            </svg>
            Print Receipt
        </a>
        @if(($txn->status ?? 'Completed') === 'Completed')
            <a href="{{ route('owner.returns.create', ['transaction' => $txn->id ?? 'TXN-2024-001']) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-500 text-white text-sm font-semibold hover:bg-red-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                </svg>
                Process Return
            </a>
        @endif
    </div>
</div>

{{-- Main Grid --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT: col-span-2 --}}
    <div class="lg:col-span-2 flex flex-col gap-5">

        {{-- Transaction Information Card --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="text-sm font-semibold text-[#363E48]">Transaction Information</h2>
            </div>
            <div class="px-6 py-5">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">

                    <div>
                        <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Transaction ID</dt>
                        <dd class="font-mono font-semibold text-[#363E48]">{{ $txn->code ?? 'TXN-2024-001' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Date &amp; Time</dt>
                        <dd class="text-slate-700">
                            {{ isset($txn->created_at) ? \Carbon\Carbon::parse($txn->created_at)->format('F d, Y \a\t g:i A') : 'January 15, 2024 at 10:32 AM' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Status</dt>
                        <dd>
                            @php $status = $txn->status ?? 'Completed'; @endphp
                            @if($status === 'Completed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Completed</span>
                            @elseif($status === 'Pending')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Pending</span>
                            @elseif($status === 'Returned')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">Returned</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">{{ $status }}</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Processed By</dt>
                        <dd class="text-slate-700">{{ $txn->cashier->name ?? ($txn->processed_by ?? 'Ana Reyes') }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Payment Method</dt>
                        <dd class="text-slate-700">{{ $txn->payment_method ?? 'Cash' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Amount Received</dt>
                        <dd class="font-semibold text-slate-700">
                            @if(isset($txn->amount_received) && $txn->amount_received)
                                ₱{{ number_format($txn->amount_received, 2) }}
                            @else
                                ₱202.50
                            @endif
                        </dd>
                    </div>

                    @php $change = $txn->change_given ?? null; @endphp
                    @if($change !== null && ($txn->payment_method ?? 'Cash') === 'Cash')
                        <div>
                            <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Change Given</dt>
                            <dd class="font-semibold text-green-600">₱{{ number_format($change, 2) }}</dd>
                        </div>
                    @endif

                    @php $discount = $txn->discount_pct ?? null; @endphp
                    @if($discount)
                        <div>
                            <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Discount Applied</dt>
                            <dd>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                                    {{ $discount }}% OFF
                                </span>
                            </dd>
                        </div>
                    @endif

                    @if(isset($txn->gcash_ref) && $txn->gcash_ref)
                        <div>
                            <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-0.5">GCash Reference</dt>
                            <dd class="font-mono text-slate-700">{{ $txn->gcash_ref }}</dd>
                        </div>
                    @endif

                    @if(isset($txn->cheque_number) && $txn->cheque_number)
                        <div>
                            <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Cheque Number</dt>
                            <dd class="font-mono text-slate-700">{{ $txn->cheque_number }}</dd>
                        </div>
                    @endif

                </dl>
            </div>
        </div>

        {{-- Items Card --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="text-sm font-semibold text-[#363E48]">Items</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50">
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Product</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Product ID</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Qty</th>
                            <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Unit Price</th>
                            <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($txn->items ?? [] as $item)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-3 font-medium text-slate-700">{{ $item->product->name ?? $item->product_name }}</td>
                                <td class="px-6 py-3 font-mono text-xs text-slate-500">{{ $item->product->sku ?? $item->product_id }}</td>
                                <td class="px-6 py-3 text-slate-600">{{ $item->qty }}</td>
                                <td class="px-6 py-3 text-right text-slate-700">₱{{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-6 py-3 text-right font-semibold text-slate-800">₱{{ number_format($item->subtotal ?? ($item->unit_price * $item->qty), 2) }}</td>
                            </tr>
                        @empty
                            {{-- Hardcoded sample items --}}
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-3 font-medium text-slate-700">LED Bulb 9W</td>
                                <td class="px-6 py-3 font-mono text-xs text-slate-500">ELEC-001</td>
                                <td class="px-6 py-3 text-slate-600">5</td>
                                <td class="px-6 py-3 text-right text-slate-700">₱40.50</td>
                                <td class="px-6 py-3 text-right font-semibold text-slate-800">₱202.50</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Financial Summary --}}
            <div class="bg-slate-50 border-t border-slate-100 px-6 py-4">
                <div class="max-w-xs ml-auto space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Cart Subtotal</span>
                        <span class="font-medium text-slate-700">
                            ₱{{ isset($txn->subtotal) ? number_format($txn->subtotal, 2) : '202.50' }}
                        </span>
                    </div>

                    @if(isset($txn->discount_amount) && $txn->discount_amount > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-amber-600">
                                Discount ({{ $txn->discount_pct ?? 0 }}%)
                            </span>
                            <span class="font-medium text-amber-600">
                                -₱{{ number_format($txn->discount_amount, 2) }}
                            </span>
                        </div>
                    @endif

                    <div class="flex justify-between text-base font-bold pt-2 border-t border-slate-200">
                        <span class="text-[#363E48]">Total</span>
                        <span class="text-[#363E48]">
                            ₱{{ isset($txn->total_amount) ? number_format($txn->total_amount, 2) : '202.50' }}
                        </span>
                    </div>

                    <p class="text-right text-xs text-slate-400">
                        VAT (12%) included in total
                    </p>
                </div>
            </div>
        </div>

    </div>

    {{-- RIGHT: col-span-1 --}}
    <div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <h2 class="text-sm font-semibold text-[#363E48] mb-4">Quick Actions</h2>
            <div class="flex flex-col gap-3">

                <a href="{{ route('owner.sales.receipt', $txn->id ?? 'TXN-2024-001') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg border border-slate-200 hover:border-[#363E48] hover:bg-[#363E48]/5 transition-colors group">
                    <span class="w-9 h-9 rounded-lg bg-slate-100 group-hover:bg-[#363E48]/10 flex items-center justify-center text-[#363E48] transition-colors flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.056 48.056 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-[#363E48]">Print Receipt</p>
                        <p class="text-xs text-slate-400">Generate printable receipt</p>
                    </div>
                </a>

                @if(($txn->status ?? 'Completed') === 'Completed')
                    <a href="{{ route('owner.returns.create', ['transaction' => $txn->id ?? 'TXN-2024-001']) }}"
                       class="flex items-center gap-3 px-4 py-3 rounded-lg border border-slate-200 hover:border-red-300 hover:bg-red-50 transition-colors group">
                        <span class="w-9 h-9 rounded-lg bg-red-50 group-hover:bg-red-100 flex items-center justify-center text-red-500 transition-colors flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                            </svg>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-red-600">Process Return</p>
                            <p class="text-xs text-slate-400">Initiate a return or refund</p>
                        </div>
                    </a>
                @endif

                <a href="{{ route('owner.sales.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg border border-slate-200 hover:border-[#363E48] hover:bg-[#363E48]/5 transition-colors group">
                    <span class="w-9 h-9 rounded-lg bg-slate-100 group-hover:bg-[#363E48]/10 flex items-center justify-center text-[#363E48] transition-colors flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M12 17.25h8.25" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-[#363E48]">All Transactions</p>
                        <p class="text-xs text-slate-400">Back to transactions list</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
