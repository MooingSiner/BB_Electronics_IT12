@extends('layouts.owner')

@section('title', 'Order Detail')

@php $activeNav = 'supplier-orders'; @endphp

@section('content')
    {{-- Back Link --}}
    <a href="{{ route('owner.suppliers.index') }}"
       class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700 mb-4 transition">
        ← Back to Supplier Orders
    </a>

    {{-- Page Header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold" style="color:#363E48">{{ $order->id ?? 'ORD-2024-002' }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ $order->supplier ?? 'TechWorld Distributors' }}</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" id="openReceiveModal"
                    class="px-4 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition shadow-sm"
                    style="background-color:#363E48">
                Receive Delivery
            </button>
            <button type="button" id="openDamageModal"
                    class="px-4 py-2 text-sm font-medium text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50 transition">
                Report Damage
            </button>
            <form method="POST" action="{{ route('owner.suppliers.return', $order->id ?? 0) }}" class="inline">
                @csrf
                @method('PATCH')
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50 transition"
                        onclick="return confirm('Mark all open damaged items for this supplier as returned?')">
                    Mark Returned
                </button>
            </form>
        </div>
    </div>

    {{-- Two-column layout --}}
    <div class="grid grid-cols-3 gap-5">

        {{-- LEFT: 2/3 --}}
        <div class="col-span-2 space-y-5">

            {{-- Card: Order Information --}}
            <div class="bg-white rounded-xl shadow border border-slate-200 p-6">
                <h2 class="text-base font-semibold text-slate-800 mb-4">Order Information</h2>
                <dl class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <dt class="text-slate-500">Order ID</dt>
                        <dd class="font-mono text-slate-700 font-medium">{{ $order->id ?? 'ORD-2024-002' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-slate-500">Supplier</dt>
                        <dd class="text-slate-700">{{ $order->supplier ?? 'TechWorld Distributors' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-slate-500">Order Date</dt>
                        <dd class="text-slate-700">
                            {{ isset($order->order_date) ? \Carbon\Carbon::parse($order->order_date)->format('M d, Y') : '—' }}
                        </dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-slate-500">Expected Date</dt>
                        <dd class="text-slate-700">
                            {{ isset($order->expected_date) ? \Carbon\Carbon::parse($order->expected_date)->format('M d, Y') : '—' }}
                        </dd>
                    </div>
                    <div class="flex justify-between text-sm items-center">
                        <dt class="text-slate-500">Status</dt>
                        <dd>
                            @php $status = $order->status ?? 'Ordered'; @endphp
                            @if($status === 'Received')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Received</span>
                            @elseif($status === 'Partially Received')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Partially Received</span>
                            @elseif($status === 'Cancelled')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Cancelled</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Ordered</span>
                            @endif
                        </dd>
                    </div>
                    @if(!empty($order->notes))
                        <div class="pt-2 border-t border-slate-100 text-sm">
                            <dt class="text-slate-500 mb-1">Notes</dt>
                            <dd class="text-slate-700">{{ $order->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Card: Order Items --}}
            <div class="bg-white rounded-xl shadow border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h2 class="text-base font-semibold text-slate-800">Order Items</h2>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Product</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Product ID</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Ordered</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Received</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Damaged</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Accepted</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Unit Cost</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Total Cost</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($order->items ?? [] as $item)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3 font-medium text-slate-800">{{ $item->product->name ?? $item->product_name ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="font-mono text-xs text-slate-500">{{ $item->product->id ?? $item->product_id ?? '—' }}</span>
                                </td>
                                <td class="px-4 py-3 text-right text-slate-700">{{ $item->qty_ordered ?? 0 }}</td>
                                <td class="px-4 py-3 text-right text-slate-700">{{ $item->qty_received ?? 0 }}</td>
                                <td class="px-4 py-3 text-right text-red-600">{{ $item->qty_damaged ?? 0 }}</td>
                                <td class="px-4 py-3 text-right text-green-700">{{ $item->qty_accepted ?? 0 }}</td>
                                <td class="px-4 py-3 text-right text-slate-700">₱{{ number_format($item->unit_cost ?? 0, 2) }}</td>
                                <td class="px-4 py-3 text-right font-medium text-slate-800">
                                    ₱{{ number_format(($item->unit_cost ?? 0) * ($item->qty_ordered ?? 0), 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t border-slate-200 bg-slate-50">
                        <tr>
                            <td colspan="7" class="px-4 py-3 text-right text-sm font-semibold text-slate-700">Total Order Cost</td>
                            <td class="px-4 py-3 text-right text-sm font-bold text-slate-900">
                                ₱{{ number_format($order->total_cost ?? collect($order->items ?? [])->sum(fn($i) => ($i->unit_cost ?? 0) * ($i->qty_ordered ?? 0)), 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- RIGHT: 1/3 --}}
        <div class="col-span-1">
            <div class="bg-white rounded-xl shadow border border-slate-200 p-6">
                <h2 class="text-base font-semibold text-slate-800 mb-4">Quick Actions</h2>
                <div class="space-y-2">
                    <button type="button" id="openReceiveModalSide"
                            class="w-full px-4 py-2.5 text-sm font-medium text-white rounded-lg hover:opacity-90 transition text-left"
                            style="background-color:#363E48">
                        Receive Delivery
                    </button>
                    <button type="button" id="openDamageModalSide"
                            class="w-full px-4 py-2.5 text-sm font-medium text-slate-700 border border-slate-300 rounded-lg hover:bg-slate-50 transition text-left">
                        Report Damage
                    </button>
                    @if(($openDamaged ?? collect())->isNotEmpty())
                    <button type="button" id="openReplacementModal"
                            class="w-full px-4 py-2.5 text-sm font-medium text-slate-700 border border-slate-300 rounded-lg hover:bg-slate-50 transition text-left">
                        Record Replacement
                    </button>
                    @endif
                    <form method="POST" action="{{ route('owner.suppliers.return', $order->id ?? 0) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="w-full px-4 py-2.5 text-sm font-medium text-slate-700 border border-slate-300 rounded-lg hover:bg-slate-50 transition text-left"
                                onclick="return confirm('Mark all open damaged items for this supplier as returned?')">
                            Mark Returned
                        </button>
                    </form>
                    <a href="{{ route('owner.suppliers.damaged') }}"
                       class="block w-full px-4 py-2.5 text-sm font-medium text-center hover:underline transition"
                       style="color:#363E48">
                        View Damaged Products →
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    {{-- Receive Delivery Modal --}}
    <div id="receiveModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl p-6 max-w-lg w-full mx-4 shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-slate-800">Receive Delivery</h3>
                <button type="button" id="closeReceiveModal"
                        class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('owner.suppliers.receive', $order->id ?? 0) }}">
                @csrf

                {{-- Date Received --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Date Received <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="date_received" value="{{ date('Y-m-d') }}" required
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30">
                </div>

                {{-- Per-item qty table --}}
                <div class="mb-4">
                    <p class="text-sm font-medium text-slate-700 mb-2">Quantities Received</p>
                    <div class="border border-slate-200 rounded-lg overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500">Product</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold text-slate-500">Ordered</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold text-slate-500">Qty Received</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($order->items ?? [] as $item)
                                    <tr>
                                        <td class="px-3 py-2 text-slate-700">{{ $item->product->name ?? '—' }}</td>
                                        <td class="px-3 py-2 text-right text-slate-500">{{ $item->qty_ordered ?? 0 }}</td>
                                        <td class="px-3 py-2 text-right">
                                            <input type="number" name="items[{{ $item->id ?? 0 }}][qty_received]"
                                                   value="{{ $item->qty_ordered ?? 0 }}" min="0"
                                                   max="{{ $item->qty_ordered ?? 9999 }}"
                                                   class="w-20 border border-slate-300 rounded-md px-2 py-1 text-sm text-right focus:outline-none focus:ring-2 focus:ring-[#363E48]/30">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="mb-5">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                    <textarea name="receive_notes" rows="2"
                              class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30 resize-none"></textarea>
                </div>

                <div class="flex gap-2 justify-end">
                    <button type="button" id="cancelReceiveModal"
                            class="px-4 py-2 text-sm border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm text-white rounded-lg hover:opacity-90 transition"
                            style="background-color:#363E48">
                        Confirm Receipt
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Report Damage Modal --}}
    <div id="damageModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-slate-800">Report Damage</h3>
                <button type="button" id="closeDamageModal" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('owner.suppliers.damage', $order->id ?? 0) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Product</label>
                    <select name="product_id" required
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30">
                        <option value="">Select product</option>
                        @foreach($order->items ?? [] as $item)
                            <option value="{{ $item->product->id }}">{{ $item->product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Quantity Damaged</label>
                    <input type="number" name="quantity" min="1" required
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                    <textarea name="reason" rows="2" required
                              class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30"></textarea>
                </div>
                <div class="flex gap-2 justify-end pt-2">
                    <button type="button" onclick="document.getElementById('damageModal').classList.add('hidden')"
                            class="px-4 py-2 text-sm border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm text-white rounded-lg hover:opacity-90 transition"
                            style="background-color:#363E48">
                        Report Damage
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Record Replacement Modal --}}
    <div id="replacementModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-slate-800">Record Replacement</h3>
                <button type="button" id="closeReplacementModal" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('owner.suppliers.replacement', $order->id ?? 0) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Damaged Report</label>
                    <select name="return_id" required
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30">
                        <option value="">Select a report</option>
                        @foreach($openDamaged ?? [] as $dmg)
                            <option value="{{ $dmg->id }}">{{ $dmg->product_name }} — {{ $dmg->quantity }} unit(s)</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-400">Marks the report resolved and adds the replaced quantity back to stock.</p>
                </div>
                <div class="flex gap-2 justify-end pt-2">
                    <button type="button" onclick="document.getElementById('replacementModal').classList.add('hidden')"
                            class="px-4 py-2 text-sm border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm text-white rounded-lg hover:opacity-90 transition"
                            style="background-color:#363E48">
                        Record Replacement
                    </button>
                </div>
            </form>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        const modal = document.getElementById('receiveModal');

        function openModal() {
            modal.classList.remove('hidden');
        }

        function closeModal() {
            modal.classList.add('hidden');
        }

        document.getElementById('openReceiveModal').addEventListener('click', openModal);
        document.getElementById('openReceiveModalSide').addEventListener('click', openModal);
        document.getElementById('closeReceiveModal').addEventListener('click', closeModal);
        document.getElementById('cancelReceiveModal').addEventListener('click', closeModal);

        // Close on backdrop click
        modal.addEventListener('click', function (e) {
            if (e.target === this) closeModal();
        });

        const damageModal = document.getElementById('damageModal');
        document.getElementById('openDamageModal')?.addEventListener('click', () => damageModal.classList.remove('hidden'));
        document.getElementById('openDamageModalSide')?.addEventListener('click', () => damageModal.classList.remove('hidden'));
        document.getElementById('closeDamageModal').addEventListener('click', () => damageModal.classList.add('hidden'));
        damageModal.addEventListener('click', function (e) { if (e.target === this) this.classList.add('hidden'); });

        const replacementModal = document.getElementById('replacementModal');
        document.getElementById('openReplacementModal')?.addEventListener('click', () => replacementModal.classList.remove('hidden'));
        document.getElementById('closeReplacementModal').addEventListener('click', () => replacementModal.classList.add('hidden'));
        replacementModal.addEventListener('click', function (e) { if (e.target === this) this.classList.add('hidden'); });
    </script>
@endpush
