{{-- Scan a barcode (or type a product code) and press Enter to add that product to the order. --}}
<div class="mb-4">
    <label for="orderScan" class="block text-xs font-medium text-slate-600 mb-1">Scan barcode or enter product code</label>
    <input type="text" id="orderScan" autocomplete="off" placeholder="Scan here, then the product is added to the list"
           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-[#363E48]/30">
    <p id="orderScanMessage" class="mt-1 text-xs text-red-600 hidden"></p>
</div>

<script>
    document.getElementById('orderScan').addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') {
            return;
        }

        e.preventDefault();

        const value = this.value.trim();
        const message = document.getElementById('orderScanMessage');
        message.classList.add('hidden');

        if (value === '') {
            return;
        }

        const container = document.getElementById('itemsContainer');
        const rows = () => Array.from(container.querySelectorAll('.item-row'));
        const matches = option => option.dataset.barcode === value || option.dataset.code === value;
        const sample = rows().map(r => Array.from(r.querySelectorAll('option')).find(matches)).find(Boolean);

        if (!sample) {
            message.textContent = 'No product found for "' + value + '".';
            message.classList.remove('hidden');
            this.select();
            return;
        }

        let row = rows().find(r => r.querySelector('.item-product-select').value === sample.value);
        let isNewLine = false;

        if (!row) {
            row = rows().find(r => r.querySelector('.item-product-select').value === '');
        }

        if (!row) {
            document.getElementById('addItem').click();
            row = rows().pop();
        }

        const select = row.querySelector('.item-product-select');
        if (select.value !== sample.value) {
            select.value = sample.value;
            select.dispatchEvent(new Event('change'));
            isNewLine = true;
        }

        const qty = row.querySelector('input[name$="[qty]"]');
        qty.value = isNewLine || qty.value === '' ? 1 : parseInt(qty.value, 10) + 1;

        this.value = '';
    });
</script>
