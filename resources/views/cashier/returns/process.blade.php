@extends('layouts.cashier')

@section('title', 'Process Return')

@php $activeNav = 'returns'; @endphp

@section('breadcrumb')
<a href="{{ route('cashier.returns.index') }}" class="hover:underline" style="color:#363E48;">Returns &amp; Warranties</a>
<span class="mx-1 text-slate-400">/</span>
<span class="text-slate-600">Process Return</span>
@endsection

@section('content')
<div class="p-6 max-w-xl mx-auto space-y-6">

    <a href="{{ route('cashier.returns.index') }}"
       class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
        Back to Returns &amp; Warranties
    </a>

    <div>
        <h1 class="text-2xl font-bold text-slate-800">Process Return</h1>
        <p class="text-sm text-slate-500 mt-1">Record a customer return for a completed sale.</p>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Step 1: find the transaction --}}
    <form method="GET" action="{{ route('cashier.returns.process') }}" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-3">
        <label class="block text-sm font-medium text-slate-700">Find Transaction</label>
        <div class="flex gap-2">
            <input type="number" name="transaction_id" min="1" placeholder="Transaction number, e.g. 12"
                   value="{{ $transactionId ?? '' }}"
                   class="flex-1 px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2"
                   style="--tw-ring-color:#363E48;">
            <button type="submit"
                    class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl hover:opacity-90 transition-opacity"
                    style="background-color:#363E48;">
                Find
            </button>
        </div>
        <p class="text-xs text-slate-400">Look up the transaction number printed on the customer's receipt.</p>
        @if(($transactionId ?? null) && ! $sale)
        <p class="text-xs text-red-500">No completed transaction found with that number.</p>
        @endif
    </form>

    {{-- Step 2: return details, once a transaction is loaded --}}
    @if($sale && $remaining->sum() === 0)
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 text-sm text-slate-500">
        Every item from this transaction has already been fully returned.
    </div>
    @elseif($sale)
    <form method="POST" action="{{ route('cashier.returns.store') }}" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
        @csrf
        <input type="hidden" name="sale_id" value="{{ $sale->sale_id }}">

        <div class="flex items-center justify-between px-3.5 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm">
            <span class="text-slate-500">Transaction</span>
            <span class="font-semibold text-slate-800">{{ $sale->code() }}</span>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Product</label>
            <select id="product_id" name="product_id" required class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2" style="--tw-ring-color:#363E48;">
                @foreach($sale->items as $item)
                    @php $left = $remaining[$item->product_id] ?? $item->quantity; @endphp
                    @if($left > 0)
                    <option value="{{ $item->product_id }}" data-max="{{ $left }}">{{ $item->product->product_name ?? '—' }} ({{ $left }} returnable)</option>
                    @endif
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Quantity</label>
            <input type="number" id="quantity" name="quantity" min="1" value="{{ old('quantity', 1) }}" required
                   class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2"
                   style="--tw-ring-color:#363E48;">
            <p id="quantityHint" class="mt-1 text-xs text-slate-400"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Reason</label>
            <textarea name="reason" rows="2" required
                      class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2"
                      style="--tw-ring-color:#363E48;">{{ old('reason') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Condition</label>
            <select name="condition" required class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2" style="--tw-ring-color:#363E48;">
                <option value="defective">Defective</option>
                <option value="damaged">Damaged</option>
                <option value="wrong_item">Wrong Item</option>
                <option value="customer_changed_mind">Customer Changed Mind</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Requested Resolution</label>
            <select name="resolution" required class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2" style="--tw-ring-color:#363E48;">
                <option value="pending">Pending Review</option>
                <option value="refund">Refund</option>
                <option value="replacement">Replacement</option>
                <option value="repair">Repair</option>
                <option value="supplier_exchange">Supplier Exchange</option>
            </select>
        </div>

        <button type="submit"
                class="w-full py-2.5 text-sm font-semibold text-white rounded-xl hover:opacity-90 transition-opacity"
                style="background-color:#363E48;">
            Submit Return
        </button>
    </form>
    @endif

</div>

@if($sale && $remaining->sum() > 0)
<script>
    (function () {
        const productSelect = document.getElementById('product_id');
        const quantityInput = document.getElementById('quantity');
        const hint = document.getElementById('quantityHint');

        function syncMax() {
            const max = productSelect.options[productSelect.selectedIndex]?.dataset.max;
            if (!max) return;
            quantityInput.max = max;
            hint.textContent = `Up to ${max} unit(s) can be returned for this product.`;
            if (parseInt(quantityInput.value, 10) > parseInt(max, 10)) {
                quantityInput.value = max;
            }
        }

        productSelect.addEventListener('change', syncMax);
        syncMax();
    })();
</script>
@endif
@endsection
