@extends('layouts.owner')

@section('title', 'New Supplier Order')

@php $activeNav = 'supplier-orders'; @endphp

@section('content')
    {{-- Back Link --}}
    <a href="{{ route('owner.suppliers.index') }}"
       class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700 mb-4 transition">
        ← Back to Supplier Orders
    </a>

    <div class="max-w-2xl mx-auto">

    {{-- Page Header --}}
    <h1 class="text-2xl font-bold mb-6 text-center" style="color:#363E48">New Supplier Order</h1>

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
            <p class="font-semibold mb-1">Please fix the following errors:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="orderForm" method="POST" action="{{ route('owner.suppliers.store') }}" class="space-y-5">
        @csrf

        {{-- Card: Order Information --}}
        <div class="bg-white rounded-xl shadow border border-slate-200 p-6 space-y-5">
            <h2 class="text-base font-semibold text-slate-800">Order Information</h2>

            {{-- Supplier --}}
            <div>
                <label for="supplier" class="block text-sm font-medium text-slate-700 mb-1">
                    Supplier <span class="text-red-500">*</span>
                </label>
                <input type="text" id="supplier" name="supplier" list="supplier-options" value="{{ old('supplier') }}" required
                       autocomplete="off"
                       placeholder="e.g. TechWorld Distributors"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30 @error('supplier') border-red-400 @enderror">
                <datalist id="supplier-options">
                    @foreach($suppliers ?? [] as $s)
                        <option value="{{ $s }}"></option>
                    @endforeach
                </datalist>
                <p class="mt-1 text-xs text-slate-400">Type a new supplier name to add them automatically.</p>
                @error('supplier')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Order Date --}}
            <div>
                <label for="order_date" class="block text-sm font-medium text-slate-700 mb-1">
                    Order Date <span class="text-red-500">*</span>
                </label>
                <input type="date" id="order_date" name="order_date"
                       value="{{ old('order_date', date('Y-m-d')) }}" required
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30 @error('order_date') border-red-400 @enderror">
                @error('order_date')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Notes --}}
            <div>
                <label for="notes" class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                <textarea id="notes" name="notes" rows="3"
                          class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30 resize-none @error('notes') border-red-400 @enderror">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Card: Products to Order --}}
        <div class="bg-white rounded-xl shadow border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold text-slate-800">Products to Order</h2>
                <button type="button" id="addItem"
                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition">
                    + Add Item
                </button>
            </div>

            <div id="itemsContainer" class="space-y-3">
                {{-- Default empty row --}}
                <div class="item-row flex gap-3 items-end p-3 bg-slate-50 rounded-md border border-slate-200">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Product</label>
                        <select name="items[0][product_id]"
                                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30">
                            <option value="">Select product</option>
                            @foreach($products ?? [] as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-24">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Qty</label>
                        <input type="number" name="items[0][qty]" min="1" placeholder="Qty"
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30">
                    </div>
                    <div class="w-36">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Unit Cost (₱)</label>
                        <input type="number" name="items[0][unit_cost]" step="0.01" min="0" placeholder="0.00"
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30">
                    </div>
                    <div class="pb-0.5">
                        <button type="button" onclick="this.closest('.item-row').remove()"
                                class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-red-500 border border-slate-300 rounded-lg hover:border-red-300 transition text-lg leading-none">
                            &times;
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form Buttons --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                    id="submitBtn"
                    class="px-5 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition shadow-sm"
                    style="background-color:#363E48">
                Submit Order
            </button>
            <a href="{{ route('owner.suppliers.index') }}"
               class="px-5 py-2 text-sm font-medium text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50 transition">
                Cancel
            </a>
        </div>
    </form>
    </div>
@endsection

@push('modals')
    {{-- Confirm Submit Modal --}}
    <div id="confirmModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl p-6 max-w-sm w-full mx-4 shadow-xl">
            <h3 class="font-semibold text-slate-800 mb-2">Submit Supplier Order</h3>
            <p class="text-sm text-slate-600 mb-4">Submit this order? It will be recorded as a Pending order.</p>
            <div class="flex gap-2 justify-end">
                <button type="button"
                        onclick="document.getElementById('confirmModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="button"
                        onclick="document.getElementById('orderForm').submit()"
                        class="px-4 py-2 text-sm text-white rounded-lg hover:opacity-90 transition"
                        style="background-color:#363E48">
                    Submit Order
                </button>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        // Intercept submit to show confirm modal
        document.getElementById('submitBtn').addEventListener('click', function (e) {
            e.preventDefault();
            document.getElementById('confirmModal').classList.remove('hidden');
        });

        // Close modal on backdrop click
        document.getElementById('confirmModal').addEventListener('click', function (e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });

        // Dynamic add item row
        let itemCount = 1;

        document.getElementById('addItem').addEventListener('click', function () {
            const container = document.getElementById('itemsContainer');
            const idx = itemCount++;

            const productsOptions = `{!! collect($products ?? [])->map(fn($p) => '<option value="'.$p->id.'">'.$p->name.'</option>')->implode('') !!}`;

            const row = document.createElement('div');
            row.className = 'item-row flex gap-3 items-end p-3 bg-slate-50 rounded-md border border-slate-200';
            row.innerHTML = `
                <div class="flex-1">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Product</label>
                    <select name="items[${idx}][product_id]"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2">
                        <option value="">Select product</option>
                        ${productsOptions}
                    </select>
                </div>
                <div class="w-24">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Qty</label>
                    <input type="number" name="items[${idx}][qty]" min="1" placeholder="Qty"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2">
                </div>
                <div class="w-36">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Unit Cost (₱)</label>
                    <input type="number" name="items[${idx}][unit_cost]" step="0.01" min="0" placeholder="0.00"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2">
                </div>
                <div class="pb-0.5">
                    <button type="button" onclick="this.closest('.item-row').remove()"
                            class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-red-500 border border-slate-300 rounded-lg hover:border-red-300 transition text-lg leading-none">
                        &times;
                    </button>
                </div>
            `;
            container.appendChild(row);
        });
    </script>
@endpush
