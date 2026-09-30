@extends('layouts.owner')

@section('title', 'Purchase Order Detail')

@php $activeNav = 'purchase-orders'; @endphp

@section('content')
    {{-- Back Link --}}
    <a href="{{ route('owner.purchase-orders.index') }}"
       class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700 mb-4 transition">
        ← Back to Purchase Orders
    </a>

    {{-- Page Header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold" style="color:#363E48">PO-{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ $order->store }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if($order->status !== 'Received' && $order->status !== 'Cancelled')
            <button type="button" id="openReceiveModal"
                    class="px-4 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition shadow-sm"
                    style="background-color:#363E48">
                Receive Delivery
            </button>
            @endif
        </div>
    </div>

    <div class="max-w-3xl space-y-5">

            {{-- Card: Order Information --}}
            <div class="bg-white rounded-xl shadow border border-slate-200 p-6">
                <h2 class="text-base font-semibold text-slate-800 mb-4">Order Information</h2>
                <dl class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <dt class="text-slate-500">Order ID</dt>
                        <dd class="font-mono text-slate-700 font-medium">PO-{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-slate-500">Store</dt>
                        <dd class="text-slate-700">{{ $order->store }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-slate-500">Order Date</dt>
                        <dd class="text-slate-700">
                            {{ isset($order->order_date) ? \Carbon\Carbon::parse($order->order_date)->format('M d, Y') : '—' }}
                        </dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-slate-500">Received Date</dt>
                        <dd class="text-slate-700">
                            {{ isset($order->expected_date) ? \Carbon\Carbon::parse($order->expected_date)->format('M d, Y') : '—' }}
                        </dd>
                    </div>
                    <div class="flex justify-between text-sm items-center">
                        <dt class="text-slate-500">Status</dt>
                        <dd>
                            @if($order->status === 'Received')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Received</span>
                            @elseif($order->status === 'Partially Received')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Partially Received</span>
                            @elseif($order->status === 'Cancelled')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Cancelled</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Ordered</span>
                            @endif
                        </dd>
                    </div>
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
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Ordered</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Received</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Unit Cost</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Total Cost</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($order->items as $item)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3 font-medium text-slate-800">{{ $item->product->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-right text-slate-700">{{ $item->qty_ordered }}</td>
                                <td class="px-4 py-3 text-right text-slate-700">{{ $item->qty_received }}</td>
                                <td class="px-4 py-3 text-right text-slate-700">₱{{ number_format($item->unit_cost, 2) }}</td>
                                <td class="px-4 py-3 text-right font-medium text-slate-800">
                                    ₱{{ number_format($item->unit_cost * $item->qty_ordered, 2) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($item->is_cancelled)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Cancelled</span>
                                    @elseif($item->qty_received >= $item->qty_ordered)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Received</span>
                                    @elseif($item->qty_received > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Partial</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Pending</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t border-slate-200 bg-slate-50">
                        <tr>
                            <td colspan="5" class="px-4 py-3 text-right text-sm font-semibold text-slate-700">Total Order Cost</td>
                            <td class="px-4 py-3 text-right text-sm font-bold text-slate-900">
                                ₱{{ number_format($order->total_cost, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
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

            <form method="POST" action="{{ route('owner.purchase-orders.receive', $order->id) }}">
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
                <div class="mb-5">
                    <p class="text-sm font-medium text-slate-700 mb-2">Quantities Received</p>
                    <div class="border border-slate-200 rounded-lg overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500">Product</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold text-slate-500">Ordered</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold text-slate-500">Qty Received</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-slate-500">Not Available</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($order->items as $item)
                                    <tr>
                                        <td class="px-3 py-2 text-slate-700">{{ $item->product->name ?? '—' }}</td>
                                        <td class="px-3 py-2 text-right text-slate-500">{{ $item->qty_ordered }}</td>
                                        <td class="px-3 py-2 text-right">
                                            <input type="number" name="items[{{ $item->id }}][qty_received]"
                                                   value="{{ $item->qty_ordered }}" min="0"
                                                   max="{{ $item->qty_ordered }}"
                                                   {{ $item->is_cancelled ? 'readonly' : '' }}
                                                   class="qty-received-input w-20 border border-slate-300 rounded-md px-2 py-1 text-sm text-right focus:outline-none focus:ring-2 focus:ring-[#363E48]/30 disabled:bg-slate-100 disabled:text-slate-400">
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <input type="checkbox" name="items[{{ $item->id }}][cancelled]" value="1"
                                                   {{ $item->is_cancelled ? 'checked' : '' }}
                                                   class="cancel-item-checkbox w-4 h-4 rounded border-slate-300 text-red-500 focus:ring-red-400">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
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

        document.getElementById('openReceiveModal')?.addEventListener('click', openModal);
        document.getElementById('closeReceiveModal').addEventListener('click', closeModal);
        document.getElementById('cancelReceiveModal').addEventListener('click', closeModal);

        // Close on backdrop click
        modal.addEventListener('click', function (e) {
            if (e.target === this) closeModal();
        });

        // Toggle the qty-received input when a product is marked not available
        document.querySelectorAll('.cancel-item-checkbox').forEach(function (checkbox) {
            const row = checkbox.closest('tr');
            const qtyInput = row.querySelector('.qty-received-input');

            function syncQtyInput() {
                qtyInput.readOnly = checkbox.checked;
                qtyInput.classList.toggle('bg-slate-100', checkbox.checked);
                qtyInput.classList.toggle('text-slate-400', checkbox.checked);
            }

            checkbox.addEventListener('change', syncQtyInput);
            syncQtyInput();
        });
    </script>
@endpush
