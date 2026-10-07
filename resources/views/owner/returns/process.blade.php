@extends('layouts.owner')

@section('title', 'Process Return')
@php $activeNav = 'returns'; @endphp

@section('content')
<div class="space-y-6 max-w-2xl mx-auto">

    {{-- Back Link --}}
    <a href="{{ route('owner.returns.index') }}"
       class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700">
        ← Back to Returns & Warranties
    </a>

    {{-- Page Header --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Process Return</h1>
        <p class="text-sm text-slate-500 mt-1">
            @if($txn) For transaction {{ $txn->code }} @else Look up a completed transaction to begin. @endif
        </p>
    </div>

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="px-4 py-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
            <p class="font-semibold mb-1">Please fix the following errors:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(! $txn)
    {{-- Find Transaction --}}
    <div class="bg-white rounded-xl border shadow-sm p-6 mb-6">
        <form method="GET" action="{{ route('owner.returns.process') }}" class="space-y-2">
            <label class="block text-sm font-medium text-slate-700">Find Transaction</label>
            <div class="flex gap-2">
                <input type="number" name="transaction_id" min="1" placeholder="Transaction number, e.g. 12"
                       value="{{ request('transaction_id') }}"
                       class="flex-1 border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                <button type="submit"
                        class="px-5 py-2 text-sm font-medium text-white rounded-lg transition-opacity hover:opacity-90"
                        style="background-color:#363E48">
                    Find
                </button>
            </div>
            @if(request('transaction_id'))
            <p class="text-xs text-red-500">No completed transaction found with that number.</p>
            @else
            <p class="text-xs text-slate-400">Or start a return from a transaction's detail page.</p>
            @endif
        </form>
    </div>
    @endif

    {{-- Form Card --}}
    @if($txn && $txn->items->isEmpty())
    <div class="bg-white rounded-xl border shadow-sm p-6 text-sm text-slate-500">
        Every item from this transaction has already been fully returned.
    </div>
    @elseif($txn)
    <div class="bg-white rounded-xl border shadow-sm p-6">
        <form data-confirm="Submit this return for the selected items?" id="returnForm" method="POST" action="{{ route('owner.returns.store') }}" class="space-y-5">
            @csrf

            {{-- Transaction ID --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Transaction ID</label>
                <div class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-600 select-all">
                    {{ $txn->code }}
                </div>
                <input type="hidden" name="transaction_id" value="{{ $txn->id }}">
            </div>

            {{-- Products to Return --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-medium text-slate-700">
                        Products to Return <span class="text-red-500">*</span>
                    </label>
                    <button type="button" id="addItem"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition">
                        + Add Item
                    </button>
                </div>

                <div id="itemsContainer" class="space-y-3">
                    {{-- Default first row --}}
                    <div class="item-row p-3 bg-slate-50 rounded-md border border-slate-200 space-y-3">
                        <div class="flex gap-3 items-end">
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Product</label>
                                <select name="items[0][product_id]"
                                        class="product-select w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400" required>
                                    <option value="">Select product</option>
                                    @foreach($txn->items as $item)
                                        <option value="{{ $item->product_id }}" data-max="{{ $item->remaining }}" data-paid="{{ $item->paid_unit }}">
                                            {{ $item->product_name }} ({{ $item->remaining }} returnable)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-24">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Qty</label>
                                <input type="number" name="items[0][qty]" min="1" value="1"
                                       class="qty-input w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400" required>
                            </div>
                        </div>
                        <p class="qty-hint text-xs text-slate-400"></p>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Resolution</label>
                                <select name="items[0][resolution]"
                                        class="resolution-select w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400" required>
                                    <option value="">Select...</option>
                                    <option value="replacement">Replacement</option>
                                    <option value="refund">Refund</option>
                                    <option value="repair">Repair</option>
                                    <option value="supplier_exchange">Supplier Exchange</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Item Condition</label>
                                <select name="items[0][condition]"
                                        class="condition-select w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400" required>
                                    <option value="">Select...</option>
                                    <option value="wrong_item">Wrong item</option>
                                    <option value="customer_changed_mind">Customer changed mind</option>
                                    <option value="defective">Defective</option>
                                    <option value="damaged">Damaged</option>
                                    <option value="other">Other</option>
                                </select>
                                <p class="condition-hint mt-1 text-xs text-slate-400"></p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Reason</label>
                            <input type="text" name="items[0][reason]" maxlength="255"
                                   placeholder="Describe the reason for the return..."
                                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400" required>
                        </div>
                        <div class="exchange-block hidden rounded-lg border border-slate-200 bg-white p-3 space-y-2">
                            <label class="block text-xs font-medium text-slate-600">Exchange for a different product <span class="font-normal text-slate-400">(optional)</span></label>
                            <select name="items[0][replacement_product_id]"
                                    class="exchange-select w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                                <option value="">Same product</option>
                                @foreach($exchangeProducts as $exchangeProduct)
                                    <option value="{{ $exchangeProduct->product_id }}" data-price="{{ $exchangeProduct->unit_price }}">{{ $exchangeProduct->product_name }} — ₱{{ number_format($exchangeProduct->unit_price, 2) }} ({{ $exchangeProduct->quantity_on_hand }} in stock)</option>
                                @endforeach
                            </select>
                            <p class="exchange-estimate text-xs font-medium text-slate-600"></p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Buttons --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="px-5 py-2 text-sm font-medium text-white rounded-lg transition-opacity hover:opacity-90"
                        style="background-color:#363E48">
                    Process Return
                </button>
                <a href="{{ route('owner.returns.index') }}"
                   class="px-5 py-2 text-sm font-medium text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
    @endif

</div>
@endsection


@push('scripts')
<script>
    const conditionHints = {
        wrong_item: 'Unopened/unused — re-added to inventory.',
        customer_changed_mind: 'Unopened/unused — re-added to inventory.',
        defective: 'NOT added back to inventory.',
        damaged: 'NOT added back to inventory.',
        other: 'Re-added to inventory.',
    };

    function wireItemRow(row) {
        const productSelect = row.querySelector('.product-select');
        const qtyInput = row.querySelector('.qty-input');
        const qtyHint = row.querySelector('.qty-hint');
        const conditionSelect = row.querySelector('.condition-select');
        const conditionHint = row.querySelector('.condition-hint');

        function syncMax() {
            const max = productSelect.options[productSelect.selectedIndex]?.dataset.max;
            if (!max) { qtyHint.textContent = ''; return; }
            qtyInput.max = max;
            qtyHint.textContent = `Up to ${max} unit(s) can be returned for this product.`;
            if (parseInt(qtyInput.value, 10) > parseInt(max, 10)) {
                qtyInput.value = max;
            }
        }

        function syncCondition() {
            conditionHint.textContent = conditionHints[conditionSelect.value] || '';
        }

        const resolutionSelect = row.querySelector('.resolution-select');
        const exchangeBlock = row.querySelector('.exchange-block');
        const exchangeSelect = row.querySelector('.exchange-select');
        const exchangeEstimate = row.querySelector('.exchange-estimate');
        const money = value => '₱' + Math.abs(value).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        function syncExchange() {
            const show = resolutionSelect.value === 'replacement';
            exchangeBlock.classList.toggle('hidden', !show);

            if (!show) {
                exchangeSelect.value = '';
            }

            const chosen = exchangeSelect.options[exchangeSelect.selectedIndex];
            const returned = productSelect.options[productSelect.selectedIndex];

            if (!show || !chosen || !chosen.value || chosen.value === productSelect.value || !returned?.dataset.paid) {
                exchangeEstimate.textContent = '';
                return;
            }

            const quantity = parseInt(qtyInput.value, 10) || 1;
            const difference = parseFloat(chosen.dataset.price) * quantity - parseFloat(returned.dataset.paid) * quantity;
            exchangeEstimate.textContent = difference > 0
                ? `Customer pays ${money(difference)} for the exchange.`
                : difference < 0
                    ? `Give the customer ${money(difference)} back for the exchange.`
                    : 'Even exchange, no money changes hands.';
        }

        [resolutionSelect, exchangeSelect, productSelect].forEach(element => element.addEventListener('change', syncExchange));
        qtyInput.addEventListener('input', syncExchange);
        productSelect.addEventListener('change', syncMax);
        conditionSelect.addEventListener('change', syncCondition);
        row.querySelector('.remove-item')?.addEventListener('click', () => row.remove());
        syncMax();
        syncCondition();
        syncExchange();
    }

    document.querySelectorAll('#itemsContainer .item-row').forEach(wireItemRow);

    let itemCount = 1;

    document.getElementById('addItem')?.addEventListener('click', function () {
        const container = document.getElementById('itemsContainer');
        const idx = itemCount++;

        const exchangeOptions = `{!! collect($exchangeProducts ?? [])->map(fn ($p) => '<option value="'.$p->product_id.'" data-price="'.$p->unit_price.'">'.e($p->product_name).' — ₱'.number_format($p->unit_price, 2).' ('.$p->quantity_on_hand.' in stock)</option>')->implode('') !!}`;

        const productsOptions = `{!! collect($txn->items ?? [])->map(fn($i) => '<option value="'.$i->product_id.'" data-max="'.$i->remaining.'" data-paid="'.$i->paid_unit.'">'.$i->product_name.' ('.$i->remaining.' returnable)</option>')->implode('') !!}`;

        const row = document.createElement('div');
        row.className = 'item-row p-3 bg-slate-50 rounded-md border border-slate-200 space-y-3';
        row.innerHTML = `
            <div class="flex gap-3 items-end">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Product</label>
                    <select name="items[${idx}][product_id]" class="product-select w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2" required>
                        <option value="">Select product</option>
                        ${productsOptions}
                    </select>
                </div>
                <div class="w-24">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Qty</label>
                    <input type="number" name="items[${idx}][qty]" min="1" value="1"
                           class="qty-input w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2" required>
                </div>
                <div class="pb-0.5">
                    <button type="button" class="remove-item w-8 h-8 flex items-center justify-center text-slate-400 hover:text-red-500 border border-slate-300 rounded-lg hover:border-red-300 transition text-lg leading-none">
                        &times;
                    </button>
                </div>
            </div>
            <p class="qty-hint text-xs text-slate-400"></p>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Resolution</label>
                    <select name="items[${idx}][resolution]" class="resolution-select w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2" required>
                        <option value="">Select...</option>
                        <option value="replacement">Replacement</option>
                        <option value="refund">Refund</option>
                        <option value="repair">Repair</option>
                        <option value="supplier_exchange">Supplier Exchange</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Item Condition</label>
                    <select name="items[${idx}][condition]" class="condition-select w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2" required>
                        <option value="">Select...</option>
                        <option value="wrong_item">Wrong item</option>
                        <option value="customer_changed_mind">Customer changed mind</option>
                        <option value="defective">Defective</option>
                        <option value="damaged">Damaged</option>
                        <option value="other">Other</option>
                    </select>
                    <p class="condition-hint mt-1 text-xs text-slate-400"></p>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Reason</label>
                <input type="text" name="items[${idx}][reason]" maxlength="255"
                       placeholder="Describe the reason for the return..."
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2" required>
            </div>
                        <div class="exchange-block hidden rounded-lg border border-slate-200 bg-white p-3 space-y-2">
                            <label class="block text-xs font-medium text-slate-600">Exchange for a different product <span class="font-normal text-slate-400">(optional)</span></label>
                            <select name="items[${idx}][replacement_product_id]"
                                    class="exchange-select w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                                <option value="">Same product</option>
                                ${exchangeOptions}
                            </select>
                            <p class="exchange-estimate text-xs font-medium text-slate-600"></p>
                        </div>
        `;
        container.appendChild(row);
        wireItemRow(row);
    });
</script>
@endpush
