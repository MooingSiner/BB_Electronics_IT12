@extends('layouts.owner')

@section('title', 'Process Return')
@php $activeNav = 'returns'; @endphp

@section('content')
<div class="space-y-6 max-w-xl mx-auto">

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

    {{-- Success Alert --}}
    @if(session('success'))
    <div class="flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
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
        <form id="returnForm" method="POST" action="{{ route('owner.returns.store') }}" class="space-y-5" onsubmit="handleSubmit(event)">
            @csrf

            {{-- Transaction ID --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Transaction ID</label>
                <div class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-600 select-all">
                    {{ $txn->code }}
                </div>
                <input type="hidden" name="transaction_id" value="{{ $txn->id }}">
            </div>

            {{-- Product to Return --}}
            <div>
                <label for="product_id" class="block text-sm font-medium text-slate-700 mb-1">
                    Product to Return <span class="text-red-500">*</span>
                </label>
                <select id="product_id" name="product_id"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400" required>
                    <option value="">Select a product...</option>
                    @foreach($txn->items ?? [] as $item)
                        <option value="{{ $item->product_id }}" data-max="{{ $item->remaining }}" {{ old('product_id') == $item->product_id ? 'selected' : '' }}>
                            {{ $item->product_name }} ({{ $item->remaining }} returnable)
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Quantity to Return --}}
            <div>
                <label for="qty" class="block text-sm font-medium text-slate-700 mb-1">
                    Quantity to Return <span class="text-red-500">*</span>
                </label>
                <input type="number" id="qty" name="qty" min="1" value="{{ old('qty', 1) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400" required>
                <p id="qtyHint" class="mt-1 text-xs text-slate-400"></p>
            </div>

            {{-- Reason for Return --}}
            <div>
                <label for="reason" class="block text-sm font-medium text-slate-700 mb-1">
                    Reason for Return <span class="text-red-500">*</span>
                </label>
                <textarea id="reason" name="reason" rows="3"
                          class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"
                          placeholder="Describe the reason for the return..." required>{{ old('reason') }}</textarea>
            </div>

            {{-- Resolution --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
                    Resolution <span class="text-red-500">*</span>
                </label>
                <div class="flex flex-wrap gap-3">
                    @foreach(['replacement' => 'Replacement', 'refund' => 'Refund', 'repair' => 'Repair', 'supplier_exchange' => 'Supplier Exchange'] as $value => $label)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="resolution" value="{{ $value }}"
                               {{ old('resolution') === $value ? 'checked' : '' }}
                               class="accent-slate-700" required>
                        <span class="text-sm text-slate-700">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Item Condition --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
                    Item Condition <span class="text-red-500">*</span>
                </label>
                <div class="space-y-3">
                    <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer hover:bg-slate-50 has-[:checked]:border-slate-700 has-[:checked]:bg-slate-50 transition-colors">
                        <input type="radio" name="condition" value="wrong_item"
                               {{ old('condition') === 'wrong_item' ? 'checked' : '' }}
                               class="mt-0.5 accent-slate-700" required>
                        <div>
                            <p class="text-sm font-medium text-slate-800">Wrong item</p>
                            <p class="text-xs text-slate-500 mt-0.5">Unopened/unused — re-added to inventory.</p>
                        </div>
                    </label>
                    <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer hover:bg-slate-50 has-[:checked]:border-slate-700 has-[:checked]:bg-slate-50 transition-colors">
                        <input type="radio" name="condition" value="customer_changed_mind"
                               {{ old('condition') === 'customer_changed_mind' ? 'checked' : '' }}
                               class="mt-0.5 accent-slate-700">
                        <div>
                            <p class="text-sm font-medium text-slate-800">Customer changed mind</p>
                            <p class="text-xs text-slate-500 mt-0.5">Unopened/unused — re-added to inventory.</p>
                        </div>
                    </label>
                    <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer hover:bg-slate-50 has-[:checked]:border-slate-700 has-[:checked]:bg-slate-50 transition-colors">
                        <input type="radio" name="condition" value="defective"
                               {{ old('condition') === 'defective' ? 'checked' : '' }}
                               class="mt-0.5 accent-slate-700">
                        <div>
                            <p class="text-sm font-medium text-slate-800">Defective</p>
                            <p class="text-xs text-slate-500 mt-0.5">NOT added back to inventory.</p>
                        </div>
                    </label>
                    <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer hover:bg-slate-50 has-[:checked]:border-slate-700 has-[:checked]:bg-slate-50 transition-colors">
                        <input type="radio" name="condition" value="damaged"
                               {{ old('condition') === 'damaged' ? 'checked' : '' }}
                               class="mt-0.5 accent-slate-700">
                        <div>
                            <p class="text-sm font-medium text-slate-800">Damaged</p>
                            <p class="text-xs text-slate-500 mt-0.5">NOT added back to inventory.</p>
                        </div>
                    </label>
                    <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer hover:bg-slate-50 has-[:checked]:border-slate-700 has-[:checked]:bg-slate-50 transition-colors">
                        <input type="radio" name="condition" value="other"
                               {{ old('condition') === 'other' ? 'checked' : '' }}
                               class="mt-0.5 accent-slate-700">
                        <div>
                            <p class="text-sm font-medium text-slate-800">Other</p>
                            <p class="text-xs text-slate-500 mt-0.5">Re-added to inventory.</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <label for="notes" class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                <textarea id="notes" name="notes" rows="2"
                          class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"
                          placeholder="Optional additional notes...">{{ old('notes') }}</textarea>
            </div>

            {{-- Buttons --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="button" onclick="document.getElementById('confirmDialog').classList.remove('hidden')"
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

@push('modals')
<div id="confirmDialog" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl p-6 max-w-sm mx-4 shadow-xl">
        <h3 class="font-semibold text-slate-800 mb-2">Confirm Return</h3>
        <p class="text-sm text-slate-600 mb-4">Process this return? This action cannot be undone.</p>
        <div class="flex gap-2 justify-end">
            <button onclick="document.getElementById('confirmDialog').classList.add('hidden')"
                    class="px-4 py-2 text-sm border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition-colors">
                Cancel
            </button>
            <button onclick="document.getElementById('returnForm').submit()"
                    class="px-4 py-2 text-sm text-white rounded-lg transition-opacity hover:opacity-90"
                    style="background-color:#363E48">
                Process Return
            </button>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    function handleSubmit(e) {
        e.preventDefault();
        document.getElementById('confirmDialog').classList.remove('hidden');
    }

    (function () {
        const productSelect = document.getElementById('product_id');
        const qtyInput = document.getElementById('qty');
        const hint = document.getElementById('qtyHint');
        if (! productSelect || ! qtyInput) return;

        function syncMax() {
            const max = productSelect.options[productSelect.selectedIndex]?.dataset.max;
            if (!max) { hint.textContent = ''; return; }
            qtyInput.max = max;
            hint.textContent = `Up to ${max} unit(s) can be returned for this product.`;
            if (parseInt(qtyInput.value, 10) > parseInt(max, 10)) {
                qtyInput.value = max;
            }
        }

        productSelect.addEventListener('change', syncMax);
        syncMax();
    })();
</script>
@endpush
