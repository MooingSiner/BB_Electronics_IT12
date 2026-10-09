@extends('layouts.owner')

@section('title', 'Print Barcode Labels')

@php $activeNav = 'inventory'; @endphp

@section('breadcrumb')
    <a href="{{ route('owner.inventory.index') }}" class="hover:underline">Inventory</a> / Print Labels
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-5">
        <h1 class="text-2xl font-bold" style="color:#363E48">Print Barcode Labels</h1>
        <p class="text-sm text-slate-500 mt-1">Scan or search the products, set how many labels each needs, then print them all on one sheet.</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-5 relative">
        <div class="flex items-center justify-between mb-1">
            <label for="productSearch" class="block text-sm font-medium text-slate-700">Search Product</label>
            <button type="button" id="addAll" class="text-xs font-medium text-slate-600 border border-slate-300 rounded-md px-2 py-1 hover:bg-slate-50">Add all products</button>
        </div>
        <div class="relative">
            <input type="text" id="productSearch" autocomplete="off" data-scan-search placeholder="Scan a barcode or type a product name or code…"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30">
            <div id="productResults" class="hidden absolute z-10 left-0 right-0 mt-1 bg-white border border-slate-200 rounded-lg shadow-lg max-h-64 overflow-y-auto"></div>
        </div>
    </div>

    <form method="GET" action="{{ route('owner.inventory.labels') }}">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 mb-5 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Product</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Barcode</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide w-28">Labels</th>
                        <th class="px-4 py-3 w-10"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    <tr id="emptyRow">
                        <td colspan="4" class="px-4 py-10 text-center text-slate-400 text-sm">No products added yet. Search or scan above to add one.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <label class="text-sm text-slate-600">Label size
                <select name="size" class="ml-2 border border-slate-300 rounded-lg px-2 py-1.5 text-sm">
                    @foreach(['xxsmall' => 'XX Small (5 per row)', 'xsmall' => 'X Small (4 per row)', 'small' => 'Small (3 per row)', 'medium' => 'Medium (2 per row)', 'large' => 'Large (1 per row)'] as $value => $text)
                        <option value="{{ $value }}" @selected($size === $value)>{{ $text }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit" id="printBtn" disabled
                    class="px-5 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition shadow-sm opacity-40 cursor-not-allowed"
                    style="background-color:#363E48">
                Show labels to print
            </button>
            <a href="{{ route('owner.inventory.index') }}" class="px-5 py-2 text-sm font-medium text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50 transition">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const catalog = @json($catalog);
    const added = new Set();

    const searchInput = document.getElementById('productSearch');
    const resultsBox = document.getElementById('productResults');
    const itemsBody = document.getElementById('itemsBody');
    const printBtn = document.getElementById('printBtn');

    function syncButton() {
        printBtn.disabled = added.size === 0;
        printBtn.classList.toggle('opacity-40', added.size === 0);
        printBtn.classList.toggle('cursor-not-allowed', added.size === 0);

        if (added.size === 0 && !document.getElementById('emptyRow')) {
            itemsBody.innerHTML = '<tr id="emptyRow"><td colspan="4" class="px-4 py-10 text-center text-slate-400 text-sm">No products added yet. Search or scan above to add one.</td></tr>';
        }
    }

    function addProduct(product, copies) {
        if (added.has(product.id)) {
            const input = itemsBody.querySelector('tr[data-id="' + product.id + '"] input');
            input.value = Math.min(60, (parseInt(input.value, 10) || 0) + copies);
            return;
        }

        added.add(product.id);
        const empty = document.getElementById('emptyRow');
        if (empty) {
            empty.remove();
        }

        const row = document.createElement('tr');
        row.dataset.id = product.id;
        row.className = 'border-b border-slate-100';
        row.innerHTML = `
            <td class="px-4 py-2"><p class="font-medium text-slate-800"></p></td>
            <td class="px-4 py-2 font-mono text-xs text-slate-500"></td>
            <td class="px-4 py-2"><input type="number" name="items[${product.id}]" min="1" max="60" value="${copies}" required
                   class="w-20 border border-slate-300 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30"></td>
            <td class="px-4 py-2 text-right"><button type="button" class="text-slate-400 hover:text-red-600 text-lg leading-none" aria-label="Remove">&times;</button></td>`;
        row.querySelector('p').textContent = product.name;
        row.children[1].textContent = product.barcode || product.code;
        row.querySelector('button').addEventListener('click', () => { added.delete(product.id); row.remove(); syncButton(); });
        itemsBody.appendChild(row);
        syncButton();
    }

    function addScanned(value) {
        const product = catalog.find(p => p.code === value || (p.barcode && p.barcode === value));

        if (!product) {
            return false;
        }

        addProduct(product, 1);
        searchInput.value = '';
        resultsBox.classList.add('hidden');

        return true;
    }

    searchInput.addEventListener('input', function () {
        const term = this.value.trim().toLowerCase();

        if (term === '') {
            resultsBox.classList.add('hidden');
            return;
        }

        const matches = catalog.filter(p => p.name.toLowerCase().includes(term) || p.code.toLowerCase().includes(term) || (p.barcode || '').toLowerCase().includes(term)).slice(0, 8);

        resultsBox.innerHTML = '';
        matches.forEach(p => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'w-full text-left px-3 py-2 text-sm hover:bg-slate-50 border-b border-slate-100 last:border-b-0 flex items-center justify-between';
            button.innerHTML = '<span><span class="font-medium text-slate-800"></span> <span class="text-xs text-slate-400"></span></span>';
            button.querySelector('.font-medium').textContent = p.name;
            button.querySelector('.text-xs').textContent = p.code;
            button.addEventListener('click', () => { addProduct(p, 1); searchInput.value = ''; resultsBox.classList.add('hidden'); searchInput.focus(); });
            resultsBox.appendChild(button);
        });
        resultsBox.classList.toggle('hidden', matches.length === 0);
    });

    searchInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            addScanned(this.value.trim());
        }
    });

    // A scanner that does not send Enter: a full barcode or code that matches a product is added by itself.
    let scanTimer = null;
    searchInput.addEventListener('input', function () {
        clearTimeout(scanTimer);
        scanTimer = setTimeout(() => addScanned(this.value.trim()), 200);
    });

    document.getElementById('addAll').addEventListener('click', () => catalog.forEach(p => addProduct(p, 1)));

    document.addEventListener('click', event => {
        if (!resultsBox.contains(event.target) && event.target !== searchInput) {
            resultsBox.classList.add('hidden');
        }
    });
</script>
@endpush
