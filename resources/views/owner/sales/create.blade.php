@extends('layouts.owner')
@section('title', 'New Sale')
@php $activeNav = 'sales'; @endphp

@section('content')
{{-- Page Header & Breadcrumb --}}
<div class="mb-6">
    <nav class="flex items-center gap-2 text-sm text-slate-500 mb-2">
        <a href="{{ route('owner.dashboard') }}" class="hover:text-[#363E48] transition-colors">Dashboard</a>
        <span>/</span>
        <a href="{{ route('owner.sales.index') }}" class="hover:text-[#363E48] transition-colors">Sales Transactions</a>
        <span>/</span>
        <span class="text-[#363E48] font-medium">New Sale</span>
    </nav>
    <h1 class="text-2xl font-bold text-[#363E48]">New Sale</h1>
    <p class="mt-0.5 text-sm text-slate-500">Add products to cart and process a transaction</p>
</div>

<form method="POST" action="{{ route('owner.sales.store') }}" id="sale-form">
@csrf

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT: col-span-2 --}}
    <div class="lg:col-span-2 flex flex-col gap-5">

        {{-- Product Search Card --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <h2 class="text-sm font-semibold text-[#363E48] mb-3">Add Products</h2>
            <div class="relative" x-data="{ open: false, query: '', results: [] }">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <input
                        type="text"
                        name="product_search"
                        id="product_search"
                        placeholder="Search product by name or SKU..."
                        autocomplete="off"
                        class="w-full pl-9 pr-3 py-2.5 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#363E48]/20 focus:border-[#363E48] transition"
                        x-model="query"
                        @input.debounce.300ms="open = query.length >= 2"
                        @focus="open = query.length >= 2"
                        @click.outside="open = false"
                    />
                </div>

                {{-- Search Results Dropdown --}}
                <div x-show="open"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="absolute z-20 mt-1 w-full bg-white rounded-xl border border-slate-200 shadow-lg overflow-hidden"
                     style="display: none;">
                    <ul class="py-1 max-h-64 overflow-y-auto">
                        {{-- Sample results — replace with AJAX in production --}}
                        @foreach($products ?? [] as $product)
                            <li>
                                <button type="button"
                                        class="w-full flex items-center justify-between px-4 py-2.5 text-sm hover:bg-slate-50 transition-colors text-left"
                                        onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price }})">
                                    <div>
                                        <p class="font-medium text-slate-700">{{ $product->name }}</p>
                                        <p class="text-xs text-slate-400">{{ $product->sku }} &middot; {{ $product->stock }} in stock</p>
                                    </div>
                                    <span class="font-semibold text-[#363E48]">₱{{ number_format($product->price, 2) }}</span>
                                </button>
                            </li>
                        @endforeach
                        {{-- Fallback sample items --}}
                        @if(($products ?? collect())->isEmpty())
                            @foreach([
                                ['id'=>1,'name'=>'LED Bulb 9W','sku'=>'ELEC-001','stock'=>45,'price'=>40.50],
                                ['id'=>2,'name'=>'Extension Cord 5m','sku'=>'ELEC-002','stock'=>12,'price'=>85.00],
                                ['id'=>3,'name'=>'Circuit Breaker 15A','sku'=>'ELEC-003','stock'=>8,'price'=>450.00],
                                ['id'=>4,'name'=>'Wire 2.0mm (10m)','sku'=>'ELEC-004','stock'=>3,'price'=>95.00],
                            ] as $p)
                                <li>
                                    <button type="button"
                                            class="w-full flex items-center justify-between px-4 py-2.5 text-sm hover:bg-slate-50 transition-colors text-left"
                                            onclick="addToCart({{ $p['id'] }}, '{{ $p['name'] }}', {{ $p['price'] }})">
                                        <div>
                                            <p class="font-medium text-slate-700">{{ $p['name'] }}</p>
                                            <p class="text-xs text-slate-400">{{ $p['sku'] }} &middot; {{ $p['stock'] }} in stock</p>
                                        </div>
                                        <span class="font-semibold text-[#363E48]">₱{{ number_format($p['price'], 2) }}</span>
                                    </button>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        {{-- Cart Items Card --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h2 class="text-sm font-semibold text-[#363E48]">Cart Items</h2>
                <span id="cart-count" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">0 items</span>
            </div>

            {{-- Empty State --}}
            <div id="cart-empty" class="flex flex-col items-center justify-center py-16 text-center">
                <svg class="w-12 h-12 text-slate-200 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                </svg>
                <p class="text-sm font-medium text-slate-500">Cart is empty</p>
                <p class="text-xs text-slate-400 mt-1">Search and add products above</p>
            </div>

            {{-- Cart Table --}}
            <div id="cart-table" class="hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50">
                                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Product</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Unit Price</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Quantity</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Subtotal</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody id="cart-tbody" class="divide-y divide-slate-100">
                            {{-- Cart rows injected by JS --}}
                        </tbody>
                    </table>
                </div>
                <div class="flex items-center justify-between px-5 py-4 border-t border-slate-100 bg-slate-50">
                    <span class="text-sm font-semibold text-slate-700">Cart Total</span>
                    <span id="cart-total-display" class="text-lg font-bold text-[#363E48]">₱0.00</span>
                </div>
            </div>
        </div>

    </div>

    {{-- RIGHT: col-span-1 --}}
    <div class="flex flex-col gap-5">

        {{-- Discount Card --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <h2 class="text-sm font-semibold text-[#363E48] mb-4">Discount</h2>
            <label for="discount_pct" class="block text-xs font-medium text-slate-600 mb-1.5">Discount (%)</label>
            <input
                type="number"
                name="discount_pct"
                id="discount_pct"
                min="0"
                max="100"
                step="0.01"
                value="{{ old('discount_pct', 0) }}"
                placeholder="0"
                class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#363E48]/20 focus:border-[#363E48] transition"
                oninput="updateSummary()"
            />
            <p class="text-xs text-slate-400 mt-1.5">Enter 0 for no discount</p>
            <div class="mt-3 p-3 bg-amber-50 rounded-lg border border-amber-100" id="discount-preview" style="display:none;">
                <p class="text-xs font-medium text-amber-700">Discount Amount: <span id="discount-amount" class="font-bold">₱0.00</span></p>
            </div>
        </div>

        {{-- Payment Card --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <h2 class="text-sm font-semibold text-[#363E48] mb-4">Payment</h2>

            {{-- Payment Method --}}
            <p class="text-xs font-medium text-slate-600 mb-2">Payment Method</p>
            <div class="flex flex-col gap-2 mb-5" id="payment-methods">
                @foreach(['Cash', 'GCash', 'Cheque'] as $method)
                    <label class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 cursor-pointer hover:border-[#363E48] transition-colors has-[:checked]:border-[#363E48] has-[:checked]:bg-[#363E48]/5">
                        <input type="radio" name="payment_method" value="{{ $method }}"
                               {{ old('payment_method', 'Cash') === $method ? 'checked' : '' }}
                               class="accent-[#363E48]"
                               onchange="updatePaymentUI()">
                        <span class="text-sm font-medium text-slate-700">{{ $method }}</span>
                    </label>
                @endforeach
            </div>

            {{-- Summary --}}
            <div class="space-y-2 py-4 border-t border-b border-slate-100 mb-4">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-500">Subtotal</span>
                    <span id="summary-subtotal" class="font-medium text-slate-700">₱0.00</span>
                </div>
                <div class="flex items-center justify-between text-sm" id="summary-discount-row" style="display:none!important">
                    <span class="text-amber-600">Discount</span>
                    <span id="summary-discount" class="font-medium text-amber-600">-₱0.00</span>
                </div>
                <div class="flex items-center justify-between text-base font-bold">
                    <span class="text-[#363E48]">Total Amount</span>
                    <span id="summary-total" class="text-[#363E48]">₱0.00</span>
                </div>
            </div>

            {{-- Hidden inputs for totals --}}
            <input type="hidden" name="subtotal" id="input-subtotal" value="0">
            <input type="hidden" name="total_amount" id="input-total" value="0">
            <input type="hidden" name="discount_amount" id="input-discount-amount" value="0">

            {{-- Cash Fields --}}
            <div id="cash-fields" class="space-y-3">
                <div>
                    <label for="amount_received" class="block text-xs font-medium text-slate-600 mb-1.5">Amount Received</label>
                    <input type="number" name="amount_received" id="amount_received"
                           min="0" step="0.01" value="{{ old('amount_received') }}"
                           placeholder="0.00"
                           class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#363E48]/20 focus:border-[#363E48] transition"
                           oninput="updateChange()">
                </div>
                <div class="p-3 bg-green-50 rounded-lg border border-green-100">
                    <p class="text-xs font-medium text-green-700">Change: <span id="change-display" class="font-bold text-green-800">₱0.00</span></p>
                    <input type="hidden" name="change_given" id="input-change" value="0">
                </div>
            </div>

            {{-- GCash Fields --}}
            <div id="gcash-fields" class="hidden space-y-3">
                <div>
                    <label for="gcash_ref" class="block text-xs font-medium text-slate-600 mb-1.5">GCash Reference Number</label>
                    <input type="text" name="gcash_ref" id="gcash_ref"
                           value="{{ old('gcash_ref') }}"
                           placeholder="Enter reference number..."
                           class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#363E48]/20 focus:border-[#363E48] transition">
                </div>
            </div>

            {{-- Cheque Fields --}}
            <div id="cheque-fields" class="hidden space-y-3">
                <div>
                    <label for="cheque_number" class="block text-xs font-medium text-slate-600 mb-1.5">Cheque Number</label>
                    <input type="text" name="cheque_number" id="cheque_number"
                           value="{{ old('cheque_number') }}"
                           placeholder="Enter cheque number..."
                           class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#363E48]/20 focus:border-[#363E48] transition">
                </div>
            </div>

            {{-- Processed By --}}
            <div class="mt-4 p-3 rounded-lg bg-slate-50 border border-slate-100">
                <p class="text-xs text-slate-500">Processed by</p>
                <p class="text-sm font-semibold text-[#363E48]">{{ auth()->user()->name ?? 'Maria Santos' }}</p>
                <input type="hidden" name="processed_by" value="{{ auth()->user()->id ?? '' }}">
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-col gap-3">
            <button type="submit"
                    class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-[#363E48] text-white text-sm font-bold hover:bg-[#2a3039] transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Confirm Sale
            </button>
            <a href="{{ route('owner.sales.index') }}"
               class="w-full flex items-center justify-center px-4 py-3 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition-colors">
                Cancel
            </a>
        </div>

    </div>
</div>
</form>

@push('modals')
{{-- Transaction Confirmed Modal --}}
@if(session('sale_confirmed'))
<div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" id="success-modal">
    <div class="bg-white rounded-2xl p-8 max-w-md w-full shadow-xl">
        <div class="flex flex-col items-center text-center">
            <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h2 class="text-xl font-bold text-[#363E48] mb-1">Transaction Recorded!</h2>
            <p class="text-sm text-slate-500 mb-6">The sale has been successfully processed.</p>

            <div class="w-full space-y-3 mb-6 text-left bg-slate-50 rounded-xl p-4 border border-slate-100">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Transaction ID</span>
                    <span class="font-mono font-semibold text-[#363E48]">{{ session('sale_confirmed.id') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Total Amount</span>
                    <span class="font-bold text-[#363E48]">{{ session('sale_confirmed.total') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Payment Method</span>
                    <span class="font-medium text-slate-700">{{ session('sale_confirmed.payment_method') }}</span>
                </div>
                @if(session('sale_confirmed.change'))
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Change</span>
                        <span class="font-medium text-green-600">{{ session('sale_confirmed.change') }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Processed By</span>
                    <span class="font-medium text-slate-700">{{ session('sale_confirmed.processed_by') }}</span>
                </div>
            </div>

            <div class="flex gap-3 w-full">
                <a href="{{ route('owner.sales.receipt', session('sale_confirmed.id')) }}"
                   class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border border-[#363E48] text-[#363E48] text-sm font-semibold hover:bg-[#363E48]/5 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.056 48.056 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                    </svg>
                    Print Receipt
                </a>
                <a href="{{ route('owner.sales.create') }}"
                   class="flex-1 flex items-center justify-center px-4 py-2.5 rounded-lg bg-[#363E48] text-white text-sm font-semibold hover:bg-[#2a3039] transition-colors">
                    New Sale
                </a>
            </div>
        </div>
    </div>
</div>
@endif
@endpush

@push('scripts')
<script>
    // Cart state
    let cart = {};

    function formatPHP(amount) {
        return '₱' + parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function addToCart(id, name, price) {
        if (cart[id]) {
            cart[id].qty += 1;
        } else {
            cart[id] = { id, name, price, qty: 1 };
        }
        renderCart();
        document.getElementById('product_search').value = '';
    }

    function removeFromCart(id) {
        delete cart[id];
        renderCart();
    }

    function updateQty(id, delta) {
        if (!cart[id]) return;
        cart[id].qty += delta;
        if (cart[id].qty <= 0) removeFromCart(id);
        else renderCart();
    }

    function renderCart() {
        const keys = Object.keys(cart);
        const emptyEl = document.getElementById('cart-empty');
        const tableEl = document.getElementById('cart-table');
        const tbody   = document.getElementById('cart-tbody');
        const countEl = document.getElementById('cart-count');

        if (keys.length === 0) {
            emptyEl.classList.remove('hidden');
            tableEl.classList.add('hidden');
            countEl.textContent = '0 items';
            updateSummary();
            return;
        }

        emptyEl.classList.add('hidden');
        tableEl.classList.remove('hidden');
        countEl.textContent = keys.length + ' item' + (keys.length !== 1 ? 's' : '');

        tbody.innerHTML = keys.map(id => {
            const item = cart[id];
            const sub = (item.price * item.qty).toFixed(2);
            return `
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3">
                        <p class="text-sm font-medium text-slate-700">${item.name}</p>
                        <input type="hidden" name="cart[${id}][id]" value="${id}">
                        <input type="hidden" name="cart[${id}][name]" value="${item.name}">
                        <input type="hidden" name="cart[${id}][price]" value="${item.price}">
                    </td>
                    <td class="px-5 py-3 text-slate-700">${formatPHP(item.price)}</td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="updateQty(${id}, -1)"
                                    class="w-7 h-7 rounded-lg border border-slate-200 flex items-center justify-center text-slate-500 hover:border-[#363E48] hover:text-[#363E48] transition-colors text-base leading-none">
                                &minus;
                            </button>
                            <input type="number" name="cart[${id}][qty]" value="${item.qty}" min="1"
                                   class="w-14 text-center text-sm border border-slate-200 rounded-lg py-1 focus:outline-none focus:ring-2 focus:ring-[#363E48]/20 focus:border-[#363E48]"
                                   onchange="setQty(${id}, this.value)">
                            <button type="button" onclick="updateQty(${id}, 1)"
                                    class="w-7 h-7 rounded-lg border border-slate-200 flex items-center justify-center text-slate-500 hover:border-[#363E48] hover:text-[#363E48] transition-colors text-base leading-none">
                                +
                            </button>
                        </div>
                    </td>
                    <td class="px-5 py-3 font-semibold text-slate-800">${formatPHP(sub)}</td>
                    <td class="px-5 py-3">
                        <button type="button" onclick="removeFromCart(${id})"
                                class="text-slate-300 hover:text-red-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');

        updateSummary();
    }

    function setQty(id, val) {
        const q = parseInt(val);
        if (q > 0) { cart[id].qty = q; renderCart(); }
    }

    function updateSummary() {
        const subtotal = Object.values(cart).reduce((s, i) => s + i.price * i.qty, 0);
        const discountPct = parseFloat(document.getElementById('discount_pct').value) || 0;
        const discountAmt = subtotal * (discountPct / 100);
        const total = subtotal - discountAmt;

        document.getElementById('summary-subtotal').textContent = formatPHP(subtotal);
        document.getElementById('summary-total').textContent = formatPHP(total);
        document.getElementById('cart-total-display').textContent = formatPHP(total);
        document.getElementById('input-subtotal').value = subtotal.toFixed(2);
        document.getElementById('input-total').value = total.toFixed(2);
        document.getElementById('input-discount-amount').value = discountAmt.toFixed(2);

        const discountRow = document.getElementById('summary-discount-row');
        const discountPreview = document.getElementById('discount-preview');
        if (discountPct > 0) {
            discountRow.style.removeProperty('display');
            document.getElementById('summary-discount').textContent = '-' + formatPHP(discountAmt);
            document.getElementById('discount-amount').textContent = formatPHP(discountAmt);
            discountPreview.style.display = '';
        } else {
            discountRow.style.setProperty('display', 'none', 'important');
            discountPreview.style.display = 'none';
        }

        updateChange();
    }

    function updateChange() {
        const total = parseFloat(document.getElementById('input-total').value) || 0;
        const received = parseFloat(document.getElementById('amount_received').value) || 0;
        const change = Math.max(0, received - total);
        document.getElementById('change-display').textContent = formatPHP(change);
        document.getElementById('input-change').value = change.toFixed(2);
    }

    function updatePaymentUI() {
        const method = document.querySelector('input[name="payment_method"]:checked')?.value;
        document.getElementById('cash-fields').classList.toggle('hidden', method !== 'Cash');
        document.getElementById('gcash-fields').classList.toggle('hidden', method !== 'GCash');
        document.getElementById('cheque-fields').classList.toggle('hidden', method !== 'Cheque');
    }

    // Init
    updatePaymentUI();
</script>
@endpush
@endsection
