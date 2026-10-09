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
            <h1 class="text-2xl font-bold" style="color:#363E48">PO-{{ str_pad($order->id ?? 0, 4, '0', STR_PAD_LEFT) }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ $order->store ?? '—' }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if(($order->status ?? 'Ordered') !== 'Received' && ($order->status ?? 'Ordered') !== 'Cancelled')
            <button type="button" id="openReceiveModal"
                    class="px-4 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition shadow-sm"
                    style="background-color:#363E48">
                Update Delivery Status
            </button>
            @endif
            <button type="button" id="openDamageModal"
                    class="px-4 py-2 text-sm font-medium text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50 transition">
                Report Damage
            </button>
            @if(($openDamaged ?? collect())->isNotEmpty())
            <button type="button" id="openReturnModal"
                    class="px-4 py-2 text-sm font-medium text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50 transition">
                Mark Returned
            </button>
            @endif
            <a href="{{ route('owner.purchase-orders.receipt', $order->id ?? 0) }}"
               class="px-4 py-2 text-sm font-medium text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50 transition">
                Receipt
            </a>
            @if($order->can_cancel ?? false)
            @include('owner.partials.cancel-order', ['action' => route('owner.purchase-orders.cancel', $order->id)])
            @endif
            @if($order->is_archived ?? false)
            <form method="POST" action="{{ route('owner.purchase-orders.restore', $order->id ?? 0) }}">
                @csrf
                <button type="submit" class="px-4 py-2 text-sm font-medium rounded-lg border border-green-200 text-green-700 bg-green-50 hover:bg-green-100 transition">
                    Restore
                </button>
            </form>
            @else
            <form method="POST" action="{{ route('owner.purchase-orders.archive', $order->id ?? 0) }}"
                  onsubmit="return confirm('Archive this order? It will be hidden from the active list but its records are kept.')">
                @csrf
                <button type="submit" class="px-4 py-2 text-sm font-medium rounded-lg border border-red-200 text-red-600 bg-red-50 hover:bg-red-100 transition">
                    Archive
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 2xl:grid-cols-3 gap-5">

        {{-- LEFT: 2/3 --}}
        <div class="2xl:col-span-2 space-y-5">

            {{-- Card: Order Information --}}
            <div class="bg-white rounded-xl shadow border border-slate-200 p-6">
                <h2 class="text-base font-semibold text-slate-800 mb-4">Order Information</h2>
                <dl class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <dt class="text-slate-500">Order ID</dt>
                        <dd class="font-mono text-slate-700 font-medium">PO-{{ str_pad($order->id ?? 0, 4, '0', STR_PAD_LEFT) }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-slate-500">Store</dt>
                        <dd class="text-slate-700">{{ $order->store ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-slate-500">Invoice Number</dt>
                        <dd class="text-slate-700 font-mono">{{ $order->invoice_number ?? '—' }}</dd>
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
                            @php $status = $order->status ?? 'Ordered'; @endphp
                            @if($status === 'Received')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Received</span>
                            @elseif($status === 'Partially Received')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Partially Received</span>
                            @elseif($status === 'Cancelled')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Cancelled</span>
                                @if($order->cancel_reason ?? null)
                                    <span class="block text-xs text-slate-500 mt-1 text-right">{{ $order->cancel_reason }}</span>
                                @endif
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Ordered</span>
                            @endif
                        </dd>
                    </div>
                    @php $openDamageCount = ($damageReports ?? collect())->where('status', 'Reported')->count(); @endphp
                    @if($openDamageCount > 0)
                    <div class="flex justify-between text-sm items-center">
                        <dt class="text-slate-500">Damage Status</dt>
                        <dd>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                {{ $openDamageCount }} Open Report{{ $openDamageCount === 1 ? '' : 's' }}
                            </span>
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>

            {{-- Card: Order Items --}}
            <div class="bg-white rounded-xl shadow border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h2 class="text-base font-semibold text-slate-800">Order Items</h2>
                </div>
                <div class="overflow-x-auto">
<table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Product</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Ordered</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Received</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Damaged</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Accepted</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Unit Cost</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Total Cost</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($order->items as $item)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-3 py-3 font-medium text-slate-800">{{ $item->product->name ?? '—' }}</td>
                                <td class="px-3 py-3 text-right text-slate-700">{{ $item->qty_ordered }}</td>
                                <td class="px-3 py-3 text-right text-slate-700">{{ $item->qty_received }}</td>
                                <td class="px-3 py-3 text-right text-red-600">{{ $item->qty_damaged ?? 0 }}</td>
                                <td class="px-3 py-3 text-right text-green-700">{{ $item->qty_accepted ?? 0 }}</td>
                                <td class="px-3 py-3 text-right text-slate-700">₱{{ number_format($item->unit_cost, 2) }}</td>
                                <td class="px-3 py-3 text-right font-medium text-slate-800">
                                    ₱{{ number_format($item->unit_cost * $item->qty_ordered, 2) }}
                                </td>
                                <td class="px-3 py-3 text-center">
                                    @php
                                        $qtyReceived = $item->qty_received ?? 0;
                                        $qtyOrdered = $item->qty_ordered ?? 0;
                                        $qtyDamaged = $item->qty_damaged ?? 0;
                                        $qtyOpenDamaged = $item->qty_open_damaged ?? 0;
                                        $qtyAccepted = $item->qty_accepted ?? 0;
                                        $resolutionLabel = $item->damage_resolution_label ?? null;
                                    @endphp
                                    @if($item->is_cancelled)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Cancelled</span>
                                    @elseif($qtyOpenDamaged > 0 && $qtyReceived > 0 && $qtyAccepted <= 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Damaged</span>
                                    @elseif($qtyOpenDamaged > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700">Partial Damage</span>
                                    @elseif($qtyDamaged > 0 && $resolutionLabel === 'Replaced')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Replaced</span>
                                    @elseif($qtyDamaged > 0 && $resolutionLabel === 'Returned to Store')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Returned to Store</span>
                                    @elseif($qtyDamaged > 0 && $resolutionLabel)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">Resolved</span>
                                    @elseif($qtyReceived >= $qtyOrdered)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Received</span>
                                    @elseif($qtyReceived > 0)
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
                            <td colspan="7" class="px-3 py-3 text-right text-sm font-semibold text-slate-700">Total Order Cost</td>
                            <td class="px-3 py-3 text-right text-sm font-bold text-slate-900">
                                ₱{{ number_format($order->total_cost, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
</div>
            </div>
        </div>

        {{-- RIGHT: 1/3 --}}
        <div class="2xl:col-span-1">
            <div class="bg-white rounded-xl shadow border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-slate-800">Damage Reports</h2>
                    @if(($openDamaged ?? collect())->isNotEmpty())
                    <button type="button" id="openReplacementModal"
                            class="px-2.5 py-1 text-xs font-medium border border-slate-300 rounded-md text-slate-600 hover:bg-slate-50 transition">
                        Replace
                    </button>
                    @endif
                </div>
                @if(($damageReports ?? collect())->isEmpty())
                    <p class="px-5 py-8 text-center text-sm text-slate-400">No damage reported for this order.</p>
                @else
                    <div class="divide-y divide-slate-100 max-h-[480px] overflow-y-auto">
                        @foreach($damageReports as $report)
                            <div class="px-5 py-3">
                                <div class="flex items-start justify-between gap-2 mb-1">
                                    <p class="text-sm font-medium text-slate-800">{{ $report->product_name }}</p>
                                    <span class="text-xs font-semibold text-red-600 flex-shrink-0">{{ $report->quantity }} pcs</span>
                                </div>
                                <p class="text-xs text-slate-400 mb-1">{{ optional($report->date)->format('M d, Y') }}</p>
                                <p class="text-xs text-slate-600 mb-2">{{ $report->description }}</p>
                                <div class="flex items-center justify-between gap-2">
                                    @if($report->status === 'Returned to Store')
                                        <div>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Returned to Store</span>
                                            @if($report->resolved_date)
                                                <p class="text-[11px] text-slate-400 mt-1">{{ abs($report->resolved_qty_change) }} unit(s) removed from stock on {{ \Carbon\Carbon::parse($report->resolved_date)->format('M d, Y') }}</p>
                                            @endif
                                        </div>
                                    @elseif($report->status === 'Replacement Received')
                                        <div>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Replacement Received</span>
                                            @if($report->resolved_date)
                                                <p class="text-[11px] text-slate-400 mt-1">{{ $report->resolved_qty_change }} unit(s) added to stock on {{ \Carbon\Carbon::parse($report->resolved_date)->format('M d, Y') }}</p>
                                            @endif
                                        </div>
                                    @elseif($report->status === 'Resolved')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">Resolved</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Reported</span>
                                        <form method="POST" action="{{ route('owner.purchase-orders.damage.cancel', [$order->id, $report->id]) }}"
                                              onsubmit="return confirm('Cancel this damage report? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-slate-400 hover:text-red-600 transition">Cancel</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('modals')
    {{-- Receive Delivery Modal --}}
    <div id="receiveModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl p-6 max-w-lg w-full mx-4 shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-slate-800">Update Delivery Status</h3>
                <button type="button" id="closeReceiveModal"
                        class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form data-confirm="Record this delivery? The received quantities will be added to stock." method="POST" action="{{ route('owner.purchase-orders.receive', $order->id) }}">
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

    {{-- Report Damage Modal --}}
    <div id="damageModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl p-6 max-w-2xl w-full mx-4 shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-slate-800">Report Damage</h3>
                <button type="button" id="closeDamageModal" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form data-confirm="Report these damaged products?" method="POST" action="{{ route('owner.purchase-orders.damage', $order->id) }}">
                @csrf

                {{-- Date Reported --}}
                <div class="mb-4">
                    <label for="damageDate" class="block text-sm font-medium text-slate-700 mb-1">
                        Date Reported <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="damageDate" name="date" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required
                           class="w-full sm:w-56 border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30">
                    <p class="mt-1 text-xs text-slate-400">Backdate this if the damage was actually found earlier.</p>
                </div>

                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium text-slate-700">Products</p>
                    <button type="button" id="addDamageItem"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition">
                        + Add Item
                    </button>
                </div>

                <div id="damageItemsContainer" class="space-y-3 h-[312px] overflow-y-auto pr-1 mb-4">
                    {{-- Default row --}}
                    <div class="damage-item-row p-3 bg-slate-50 rounded-md border border-slate-200 space-y-2">
                        <div class="flex gap-3 items-end">
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Product</label>
                                <select name="items[0][product_id]" class="damage-product-select w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30" required>
                                    <option value="">Select product</option>
                                    @foreach($order->items as $item)
                                        <option value="{{ $item->product->id }}" data-max="{{ $item->qty_accepted ?? 0 }}">{{ $item->product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-28">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Qty</label>
                                <input type="number" name="items[0][quantity]" min="1" class="damage-qty-input w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30" required>
                            </div>
                            <div class="pb-0.5">
                                <button type="button" class="remove-damage-row w-8 h-8 flex items-center justify-center text-slate-400 hover:text-red-500 border border-slate-300 rounded-lg hover:border-red-300 transition text-lg leading-none">&times;</button>
                            </div>
                        </div>
                        <p class="damage-qty-hint text-xs text-slate-400"></p>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Description</label>
                            <input type="text" name="items[0][reason]" maxlength="255" placeholder="Describe the damage..."
                                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30" required>
                        </div>
                    </div>
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
            <form data-confirm="Record the replacement units from the supplier?" method="POST" action="{{ route('owner.purchase-orders.replacement', $order->id) }}" class="space-y-4">
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

    {{-- Mark Returned Modal --}}
    <div id="returnModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl p-6 max-w-lg w-full mx-4 shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-slate-800">Mark Returned to Store</h3>
                <button type="button" id="closeReturnModal" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form data-confirm="Mark these damaged units as returned? They will be removed from stock." method="POST" action="{{ route('owner.purchase-orders.return', $order->id) }}">
                @csrf
                @method('PATCH')

                <p class="text-sm text-slate-500 mb-3">Select which open damage report(s) you're sending back to the store.</p>

                <label class="flex items-center gap-2 mb-2 text-xs font-medium text-slate-600">
                    <input type="checkbox" id="selectAllReturns" class="rounded border-slate-300">
                    Select all
                </label>

                <div class="border border-slate-200 rounded-lg divide-y divide-slate-100 mb-5 max-h-72 overflow-y-auto">
                    @foreach($openDamaged ?? [] as $dmg)
                        <label class="flex items-start gap-3 p-3 cursor-pointer hover:bg-slate-50 transition">
                            <input type="checkbox" name="return_ids[]" value="{{ $dmg->id }}"
                                   class="return-checkbox mt-0.5 rounded border-slate-300">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-medium text-slate-800">{{ $dmg->product_name }}</p>
                                    <span class="text-xs font-semibold text-red-600 flex-shrink-0">{{ $dmg->quantity }} pcs</span>
                                </div>
                                <p class="text-xs text-slate-400">{{ optional($dmg->date)->format('M d, Y') }}</p>
                                <p class="text-xs text-slate-600">{{ $dmg->description }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>

                <div class="flex gap-2 justify-end">
                    <button type="button" id="cancelReturnModal"
                            class="px-4 py-2 text-sm border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm text-white rounded-lg hover:opacity-90 transition"
                            style="background-color:#363E48">
                        Mark Selected as Returned
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

        // Cap each damage-report row's quantity to what's actually available for its selected product
        function wireDamageRow(row) {
            const productSelect = row.querySelector('.damage-product-select');
            const qtyInput = row.querySelector('.damage-qty-input');
            const qtyHint = row.querySelector('.damage-qty-hint');

            function syncMax() {
                const selected = productSelect.options[productSelect.selectedIndex];
                const max = selected ? parseInt(selected.dataset.max || '0', 10) : 0;
                qtyInput.max = max;
                qtyHint.textContent = productSelect.value ? `Up to ${max} unit(s) available to report.` : '';
            }

            productSelect.addEventListener('change', syncMax);
            row.querySelector('.remove-damage-row')?.addEventListener('click', function () {
                if (document.querySelectorAll('#damageItemsContainer .damage-item-row').length > 1) {
                    row.remove();
                }
            });
            syncMax();
        }

        document.querySelectorAll('#damageItemsContainer .damage-item-row').forEach(wireDamageRow);

        let damageItemCount = 1;

        document.getElementById('addDamageItem')?.addEventListener('click', function () {
            const container = document.getElementById('damageItemsContainer');
            const idx = damageItemCount++;

            const productsOptions = `{!! collect($order->items ?? [])->map(fn($i) => '<option value="'.$i->product->id.'" data-max="'.($i->qty_accepted ?? 0).'">'.$i->product->name.'</option>')->implode('') !!}`;

            const row = document.createElement('div');
            row.className = 'damage-item-row p-3 bg-slate-50 rounded-md border border-slate-200 space-y-2';
            row.innerHTML = `
                <div class="flex gap-3 items-end">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Product</label>
                        <select name="items[${idx}][product_id]" class="damage-product-select w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2" required>
                            <option value="">Select product</option>
                            ${productsOptions}
                        </select>
                    </div>
                    <div class="w-28">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Qty</label>
                        <input type="number" name="items[${idx}][quantity]" min="1" class="damage-qty-input w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2" required>
                    </div>
                    <div class="pb-0.5">
                        <button type="button" class="remove-damage-row w-8 h-8 flex items-center justify-center text-slate-400 hover:text-red-500 border border-slate-300 rounded-lg hover:border-red-300 transition text-lg leading-none">&times;</button>
                    </div>
                </div>
                <p class="damage-qty-hint text-xs text-slate-400"></p>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Description</label>
                    <input type="text" name="items[${idx}][reason]" maxlength="255" placeholder="Describe the damage..."
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2" required>
                </div>
            `;
            container.appendChild(row);
            wireDamageRow(row);
        });

        const damageModal = document.getElementById('damageModal');
        document.getElementById('openDamageModal')?.addEventListener('click', () => damageModal.classList.remove('hidden'));
        document.getElementById('closeDamageModal').addEventListener('click', () => damageModal.classList.add('hidden'));
        damageModal.addEventListener('click', function (e) { if (e.target === this) this.classList.add('hidden'); });

        const replacementModal = document.getElementById('replacementModal');
        document.getElementById('openReplacementModal')?.addEventListener('click', () => replacementModal.classList.remove('hidden'));
        document.getElementById('closeReplacementModal').addEventListener('click', () => replacementModal.classList.add('hidden'));
        replacementModal.addEventListener('click', function (e) { if (e.target === this) this.classList.add('hidden'); });

        const returnModal = document.getElementById('returnModal');
        document.getElementById('openReturnModal')?.addEventListener('click', () => returnModal.classList.remove('hidden'));
        document.getElementById('closeReturnModal').addEventListener('click', () => returnModal.classList.add('hidden'));
        document.getElementById('cancelReturnModal').addEventListener('click', () => returnModal.classList.add('hidden'));
        returnModal.addEventListener('click', function (e) { if (e.target === this) this.classList.add('hidden'); });

        const selectAllReturns = document.getElementById('selectAllReturns');
        const returnCheckboxes = document.querySelectorAll('.return-checkbox');
        selectAllReturns?.addEventListener('change', function () {
            returnCheckboxes.forEach(cb => { cb.checked = selectAllReturns.checked; });
        });
    </script>
@endpush
