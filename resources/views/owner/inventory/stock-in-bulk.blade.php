@extends('layouts.owner')

@section('title', 'Stock In — Multiple Products')

@php $activeNav = 'inventory'; @endphp

@section('content')
    <a href="{{ route('owner.inventory.index') }}"
       class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700 mb-4 transition">
        ← Back to Inventory
    </a>

    <div class="max-w-2xl mx-auto">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-1" style="color:#363E48">Stock In</h1>
            <p class="text-sm text-slate-500">Scan a barcode or search for a product to add it below. You can add as many as you need.</p>
        </div>

        @if($errors->any())
            <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Search --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-5 relative">
            <label for="productSearch" class="block text-sm font-medium text-slate-700 mb-1">Search Product</label>
            <div class="relative">
                <input type="text" id="productSearch" autocomplete="off" placeholder="Scan a barcode or type a product name or code…"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30">
                <div id="productResults"
                     class="hidden absolute z-10 left-0 right-0 mt-1 bg-white border border-slate-200 rounded-lg shadow-lg max-h-64 overflow-y-auto"></div>
            </div>
        </div>

        <form id="stockInForm" method="POST" action="{{ route('owner.inventory.stockin.bulk.store') }}">
            @csrf

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-5 mb-5">
                <div>
                    <label for="date_received" class="block text-sm font-medium text-slate-700 mb-1">
                        Date Received <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="date_received" name="date_received" value="{{ old('date_received', date('Y-m-d')) }}"
                           max="{{ date('Y-m-d') }}" required
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30 @error('date_received') border-red-400 @enderror">
                    <p class="mt-1 text-xs text-slate-400">Applies to all products below. Backdate this if the stock actually arrived earlier.</p>
                    @error('date_received')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-5">
                <div class="overflow-x-auto">
<table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Product</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide w-24">Current</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide w-28">Qty to Add</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Reason</th>
                            <th class="px-4 py-3 w-10"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <tr id="emptyRow">
                            <td colspan="5" class="px-4 py-10 text-center text-slate-400 text-sm">
                                No products added yet. Search above to add one.
                            </td>
                        </tr>
                    </tbody>
                </table>
</div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" id="submitBtn"
                        class="px-5 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition shadow-sm opacity-40 cursor-not-allowed" disabled
                        style="background-color:#363E48">
                    Add to Stock
                </button>
                <a href="{{ route('owner.inventory.index') }}"
                   class="px-5 py-2 text-sm font-medium text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    const catalog = @json($products);
    const addedIds = new Set();

    const searchInput = document.getElementById('productSearch');
    const resultsBox = document.getElementById('productResults');
    const itemsBody = document.getElementById('itemsBody');
    const emptyRow = document.getElementById('emptyRow');
    const submitBtn = document.getElementById('submitBtn');

    function syncSubmitButton() {
        const hasItems = addedIds.size > 0;
        submitBtn.disabled = !hasItems;
        submitBtn.classList.toggle('opacity-40', !hasItems);
        submitBtn.classList.toggle('cursor-not-allowed', !hasItems);
    }

    function renderResults(matches) {
        if (matches.length === 0) {
            resultsBox.classList.add('hidden');
            resultsBox.innerHTML = '';
            return;
        }

        resultsBox.innerHTML = matches.map(p => `
            <button type="button" data-id="${p.id}"
                    class="product-result w-full text-left px-3 py-2 text-sm hover:bg-slate-50 border-b border-slate-100 last:border-b-0 flex items-center justify-between">
                <span>
                    <span class="font-medium text-slate-800">${p.name}</span>
                    <span class="text-xs text-slate-400 ml-1">${p.code}</span>
                </span>
                <span class="text-xs text-slate-400">${p.stock} in stock</span>
            </button>
        `).join('');
        resultsBox.classList.remove('hidden');

        resultsBox.querySelectorAll('.product-result').forEach(btn => {
            btn.addEventListener('click', function () {
                addProduct(parseInt(this.dataset.id, 10));
                searchInput.value = '';
                resultsBox.classList.add('hidden');
                searchInput.focus();
            });
        });
    }

    searchInput.addEventListener('input', function () {
        const term = this.value.trim().toLowerCase();
        if (term === '') {
            resultsBox.classList.add('hidden');
            return;
        }

        const matches = catalog
            .filter(p => !addedIds.has(p.id))
            .filter(p => p.name.toLowerCase().includes(term) || p.code.toLowerCase().includes(term))
            .slice(0, 8);

        renderResults(matches);
    });

    searchInput.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') {
            return;
        }

        e.preventDefault();

        const value = this.value.trim();
        const product = catalog.find(p => p.code === value || (p.barcode && p.barcode === value));

        if (!product) {
            return;
        }

        if (addedIds.has(product.id)) {
            const quantity = itemsBody.querySelector('tr[data-product-id="' + product.id + '"] input[name$="[quantity]"]');
            quantity.value = quantity.value === '' ? 1 : parseInt(quantity.value, 10) + 1;
        } else {
            addProduct(product.id);
            itemsBody.querySelector('tr[data-product-id="' + product.id + '"] input[name$="[quantity]"]').value = 1;
        }

        this.value = '';
        resultsBox.classList.add('hidden');
    });

    document.addEventListener('click', function (e) {
        if (!resultsBox.contains(e.target) && e.target !== searchInput) {
            resultsBox.classList.add('hidden');
        }
    });

    let rowIndex = 0;

    function addProduct(productId) {
        const product = catalog.find(p => p.id === productId);
        if (!product || addedIds.has(productId)) return;

        addedIds.add(productId);
        emptyRow.remove();

        const idx = rowIndex++;
        const row = document.createElement('tr');
        row.dataset.productId = productId;
        row.innerHTML = `
            <td class="px-4 py-3">
                <p class="font-medium text-slate-800">${product.name}</p>
                <p class="font-mono text-xs text-slate-400">${product.code}</p>
                <input type="hidden" name="items[${idx}][product_id]" value="${product.id}">
            </td>
            <td class="px-4 py-3 text-right text-slate-500">${product.stock}</td>
            <td class="px-4 py-3">
                <input type="number" name="items[${idx}][quantity]" min="1" required
                       class="w-24 border border-slate-300 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30">
            </td>
            <td class="px-4 py-3">
                <input type="text" name="items[${idx}][reason]" maxlength="255" placeholder="e.g. Physical count correction"
                       class="w-full border border-slate-300 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30">
            </td>
            <td class="px-4 py-3 text-center">
                <button type="button" class="remove-row text-slate-400 hover:text-red-500 transition text-lg leading-none">&times;</button>
            </td>
        `;
        row.querySelector('.remove-row').addEventListener('click', function () {
            addedIds.delete(productId);
            row.remove();
            if (addedIds.size === 0) {
                itemsBody.appendChild(emptyRow);
            }
            syncSubmitButton();
        });

        itemsBody.appendChild(row);
        syncSubmitButton();
    }
</script>
@endpush
