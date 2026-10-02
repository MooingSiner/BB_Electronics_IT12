<div class="h-full">
<div class="flex h-full" x-data="{ cartOpen: false }">

    {{-- ===================== LEFT PANEL ===================== --}}
    <div class="flex-1 min-w-0 flex flex-col bg-white">

        {{-- Header --}}
        <div class="p-4 border-b border-slate-200 space-y-3">
            <h2 class="text-lg font-semibold text-slate-800">Point of Sale</h2>

            @if($errorMessage)
            <div class="bg-red-50 border border-red-200 rounded-lg px-3 py-2 text-sm text-red-700">
                {{ $errorMessage }}
            </div>
            @endif

            {{-- Search --}}
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                    </svg>
                </div>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Search or scan product..."
                       class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                       style="--tw-ring-color:#363E48;" />
            </div>

            {{-- Category Filter Pills --}}
            <div class="flex gap-2 flex-wrap">
                @foreach(['' => 'All', 'Lighting' => 'Lighting', 'Components' => 'Components', 'Switches' => 'Switches', 'Wiring' => 'Wiring', 'Adapters' => 'Adapters', 'Batteries' => 'Batteries'] as $val => $label)
                <button type="button"
                        wire:click="$set('category', '{{ $val }}')"
                        class="px-3 py-1 rounded-full text-xs font-medium transition-colors
                        {{ $category === $val ? 'text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                        @if($category === $val) style="background-color:#363E48;" @endif>
                    {{ $label }}
                </button>
                @endforeach
            </div>
        </div>

        {{-- Product Grid --}}
        <div class="flex-1 overflow-y-auto p-4" wire:loading.class="opacity-60">
            <div class="grid gap-4" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));">
                @forelse($products as $product)
                @php $outOfStock = $product->stock <= 0; @endphp
                <div @if(! $outOfStock) wire:click="addToCart({{ $product->id }})" @endif
                     class="bg-white border border-slate-200 rounded-xl p-4 relative transition-shadow
                    {{ $outOfStock ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:shadow-md' }}">
                    <div class="aspect-square w-full mb-3 rounded-lg bg-slate-100 flex items-center justify-center overflow-hidden">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover" loading="lazy">
                        @else
                            <svg class="w-12 h-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        @endif
                    </div>
                    <p class="font-medium text-sm text-slate-800 leading-tight mb-1">{{ $product->name }}</p>
                    <p class="font-semibold text-sm mb-2" style="color:#363E48;">₱{{ number_format($product->price, 2) }}</p>
                    @if($product->stock == 0)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Out of stock</span>
                    @elseif($product->stock <= ($product->reorder_level ?? 5))
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Low: {{ $product->stock }}</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">{{ $product->stock }} in stock</span>
                    @endif
                </div>
                @empty
                <div class="col-span-full py-16 text-center text-slate-400 text-sm">No products found.</div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Floating re-show button (visible when cart is hidden) --}}
    <button type="button"
            x-show="!cartOpen"
            x-cloak
            @click="cartOpen = true"
            class="fixed right-4 top-24 z-20 flex items-center gap-2 pl-3 pr-4 py-2 rounded-full text-white text-sm font-medium shadow-lg hover:opacity-90 transition-opacity"
            style="background-color:#363E48;">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        Cart
        @if(count($cart) > 0)
        <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full bg-white text-xs font-bold" style="color:#363E48;">{{ count($cart) }}</span>
        @endif
    </button>

    {{-- Backdrop (click to close) --}}
    <div x-show="cartOpen"
         x-cloak
         @click="cartOpen = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/40 z-30"></div>

    {{-- ===================== CART MODAL ===================== --}}
    <div x-show="cartOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @click.self="cartOpen = false"
         class="fixed inset-0 z-40 flex items-center justify-center p-4">
        <div class="bg-slate-50 rounded-2xl shadow-2xl w-full max-w-4xl max-h-[88vh] flex flex-col overflow-hidden">

            {{-- Cart Header --}}
            <div class="p-4 border-b border-slate-200 flex items-center justify-between flex-shrink-0 bg-white">
                <span class="font-semibold text-slate-800">Current Order</span>
                <div class="flex items-center gap-3">
                    <button type="button" wire:click="clearCart" class="text-red-500 text-sm hover:underline transition-colors">Clear Cart</button>
                    <button type="button" @click="cartOpen = false" title="Close" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex-1 min-h-0 flex flex-col md:flex-row">

                {{-- Cart Items (left) --}}
                <div class="flex-1 min-h-0 overflow-y-auto p-4 space-y-2 md:border-r border-slate-200">
                    @forelse($cart as $cartKey => $item)
                    <div class="bg-white rounded-lg p-3 border border-slate-200" wire:key="cart-{{ $cartKey }}">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex-1 min-w-0 mr-2">
                                <p class="font-medium text-sm text-slate-800 truncate">{{ $item['name'] }}</p>
                                <p class="text-xs text-slate-500">₱{{ number_format($item['price'], 2) }} each</p>
                            </div>
                            <button type="button" wire:click="removeFromCart('{{ $cartKey }}')" class="text-slate-400 hover:text-red-500 transition-colors flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1">
                                <input type="number"
                                       value="{{ $item['quantity'] }}"
                                       min="1"
                                       wire:change="updateQuantity('{{ $cartKey }}', $event.target.value)"
                                       class="w-16 h-7 text-center text-sm border border-slate-200 rounded-md focus:outline-none focus:ring-1">
                            </div>
                            <span class="font-medium text-sm text-slate-800">₱{{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                        </div>
                    </div>
                    @empty
                    <div class="py-16 text-center text-slate-400 text-sm">
                        <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        No items added yet.
                    </div>
                    @endforelse
                </div>

                {{-- Discount / Totals / Payment (right) --}}
                <div class="w-full md:w-80 flex-shrink-0 overflow-y-auto flex flex-col">

                    {{-- Discount Section --}}
                    <div class="p-4 border-b border-slate-200 flex-shrink-0">
                        <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase tracking-wide">Discount</label>
                        <div class="flex gap-2 mb-3">
                            @foreach(['none' => 'None', 'percent' => '% Off', 'fixed' => '₱ Off'] as $val => $label)
                            <button type="button"
                                    wire:click="setDiscountType('{{ $val }}')"
                                    class="flex-1 py-1.5 rounded-lg text-xs font-medium border transition-colors
                                    {{ $discountType === $val ? 'text-white border-transparent' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50' }}"
                                    @if($discountType === $val) style="background-color:#363E48;" @endif>
                                {{ $label }}
                            </button>
                            @endforeach
                        </div>
                        <div class="flex gap-2 {{ $discountType === 'none' ? 'hidden' : '' }}">
                            <input type="number"
                                   wire:model.live.debounce.400ms="discountValue"
                                   min="0"
                                   step="0.01"
                                   placeholder="{{ $discountType === 'percent' ? 'e.g. 10' : 'e.g. 50.00' }}"
                                   class="flex-1 px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2"
                                   style="--tw-ring-color:#363E48;">
                        </div>
                    </div>

                    {{-- Totals --}}
                    <div class="p-4 bg-white border-b border-slate-200 space-y-1.5 text-sm flex-shrink-0">
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Cart Subtotal</span>
                            <span>₱{{ number_format($subtotal, 2) }}</span>
                        </div>
                        @if($discountAmount > 0)
                        <div class="flex items-center justify-between text-green-600">
                            <span>Discount</span>
                            <span>−₱{{ number_format($discountAmount, 2) }}</span>
                        </div>
                        @endif
                        <div class="flex items-center justify-between font-bold text-base text-slate-800 pt-1 border-t border-slate-100">
                            <span>TOTAL</span>
                            <span>₱{{ number_format($total, 2) }}</span>
                        </div>
                    </div>

                    {{-- Payment Method --}}
                    <div class="p-4 space-y-3 flex-shrink-0">
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide">Payment Method</label>
                        <div class="flex gap-2 mb-3">
                            @foreach(['Cash', 'GCash', 'Cheque'] as $method)
                            <button type="button"
                                    wire:click="setPayment('{{ $method }}')"
                                    class="flex-1 py-1.5 rounded-lg text-xs font-medium border transition-colors
                                    {{ $payment === $method ? 'text-white border-transparent' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50' }}"
                                    @if($payment === $method) style="background-color:#363E48;" @endif>
                                {{ $method }}
                            </button>
                            @endforeach
                        </div>

                        @if($payment === 'Cash')
                        <label class="block text-xs text-slate-500 mb-1">Amount Received</label>
                        <input type="number"
                               wire:model.live.debounce.300ms="amountReceived"
                               min="0"
                               step="0.01"
                               placeholder="0.00"
                               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 mb-2"
                               style="--tw-ring-color:#363E48;">
                        @if($change !== null && $change >= 0)
                        <div class="bg-green-50 border border-green-200 rounded-lg px-3 py-2 text-sm text-green-700 flex items-center justify-between">
                            <span class="font-medium">Change</span>
                            <span class="font-bold">₱{{ number_format($change, 2) }}</span>
                        </div>
                        @endif
                        @endif

                        {{-- Complete Sale --}}
                        <button type="button"
                                wire:click="completeSale"
                                wire:loading.attr="disabled"
                                wire:target="completeSale"
                                class="w-full py-3 rounded-xl font-bold text-sm text-white transition-opacity mt-1
                                {{ count($cart) === 0 ? 'opacity-40 cursor-not-allowed' : 'hover:opacity-90' }}"
                                style="background-color:#363E48;"
                                @if(count($cart) === 0) disabled @endif>
                            Complete Sale
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </div>

</div>

@if($completedSale)
<div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl p-8 max-w-sm w-full mx-4 shadow-xl text-center">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h2 class="text-xl font-bold text-slate-800 mb-1">Sale Complete!</h2>
        <p class="text-sm text-slate-500 mb-4">Transaction ID: {{ $completedSale['code'] }}</p>
        <div class="text-left bg-slate-50 rounded-lg p-4 text-sm space-y-1 mb-4">
            <div class="flex justify-between">
                <span class="text-slate-500">Subtotal</span>
                <span>₱{{ $completedSale['subtotal'] }}</span>
            </div>
            @if($completedSale['discount'] > 0)
            <div class="flex justify-between text-green-600">
                <span>Discount</span>
                <span>−₱{{ $completedSale['discount'] }}</span>
            </div>
            @endif
            <div class="flex justify-between font-bold">
                <span>Total</span>
                <span>₱{{ $completedSale['total'] }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Payment</span>
                <span>{{ $completedSale['payment'] }}</span>
            </div>
            @if($completedSale['payment'] === 'Cash')
            <div class="flex justify-between">
                <span class="text-slate-500">Received</span>
                <span>₱{{ $completedSale['received'] }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Change</span>
                <span>₱{{ $completedSale['change'] }}</span>
            </div>
            @endif
            <div class="flex justify-between">
                <span class="text-slate-500">Processed By</span>
                <span>{{ auth()->user()->full_name }}</span>
            </div>
        </div>
        <div class="space-y-2">
            <a href="{{ route('cashier.sales.receipt', $completedSale['id']) }}"
               class="block w-full py-2 text-sm font-medium border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                Print Receipt
            </a>
            <button type="button" wire:click="startNewSale"
               class="block w-full py-2.5 text-sm font-bold text-white rounded-xl transition-opacity hover:opacity-90"
               style="background-color:#363E48;">
                New Sale
            </button>
            <a href="{{ route('cashier.sales.show', $completedSale['id']) }}"
               class="block w-full py-2 text-sm text-slate-500 hover:underline transition-colors">
                View Transaction
            </a>
        </div>
    </div>
</div>
@endif
</div>
