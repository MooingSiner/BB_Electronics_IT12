@extends('layouts.owner')

@section('title', 'Warranty Detail')
@php $activeNav = 'returns'; @endphp

@section('content')
<div class="space-y-6">

    {{-- Back Link --}}
    <a href="{{ route('owner.returns.index', ['tab' => 'warranty']) }}"
       class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700">
        ← Back to Returns & Warranties
    </a>

    {{-- Page Header --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">{{ $warranty->warranty_ref }}</h1>
            <p class="text-sm text-slate-500 mt-1">Warranty for {{ $warranty->productName ?? 'USB-A to USB-C Adapter' }}</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            @if(($warranty->status_value ?? '') === 'claimed')
            <form method="POST" action="{{ route('owner.returns.warranty.cancel', $warranty->id) }}"
                  onsubmit="return confirm('Cancel this warranty claim? The warranty stays active and can be claimed again.')">
                @csrf
                @method('PATCH')
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium rounded-lg border border-red-200 text-red-600 bg-red-50 hover:bg-red-100 transition">
                    Cancel Claim
                </button>
            </form>
            @endif
            <button onclick="document.getElementById('updateModal').classList.remove('hidden')"
                    class="px-4 py-2 text-sm font-medium text-white rounded-lg transition-opacity hover:opacity-90"
                    style="background-color:#363E48">
                Update Status
            </button>
        </div>
    </div>

    {{-- Main Grid --}}
    <div class="grid grid-cols-3 gap-6 items-start">

        {{-- LEFT: 1/3 --}}
        <div class="space-y-4">

            {{-- Warranty Information --}}
            <div class="bg-white rounded-xl border shadow-sm p-5">
                <h2 class="font-semibold text-slate-800 mb-4">Warranty Information</h2>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs text-slate-500 uppercase tracking-wide font-medium">Warranty Ref</dt>
                        <dd class="mt-0.5 text-slate-800 font-medium">{{ $warranty->warranty_ref }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500 uppercase tracking-wide font-medium">Transaction</dt>
                        <dd class="mt-0.5">
                            @if(!empty($warranty->transaction_id))
                                <a href="{{ route('owner.sales.show', $warranty->transaction_id) }}"
                                   class="font-medium hover:underline" style="color:#363E48">
                                    {{ $warranty->transaction_code }}
                                </a>
                            @else
                                <span class="text-slate-500">—</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500 uppercase tracking-wide font-medium">Product</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $warranty->productName ?? 'USB-A to USB-C Adapter' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500 uppercase tracking-wide font-medium">Warranty Start</dt>
                        <dd class="mt-0.5 text-slate-700">{{ isset($warranty->warranty_start) ? \Carbon\Carbon::parse($warranty->warranty_start)->format('M d, Y') : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500 uppercase tracking-wide font-medium">Warranty End</dt>
                        <dd class="mt-0.5 text-slate-700">{{ isset($warranty->warranty_end) ? \Carbon\Carbon::parse($warranty->warranty_end)->format('M d, Y') : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500 uppercase tracking-wide font-medium">Claim Date</dt>
                        <dd class="mt-0.5 text-slate-700">{{ isset($warranty->claim_date) ? \Carbon\Carbon::parse($warranty->claim_date)->format('M d, Y') : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500 uppercase tracking-wide font-medium">Status</dt>
                        <dd class="mt-1">
                            @php $status = $warranty->status ?? 'Under Review'; @endphp
                            @if($status === 'Under Review')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Under Review</span>
                            @elseif($status === 'Active')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                            @elseif($status === 'In Repair')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">In Repair</span>
                            @elseif($status === 'Replaced')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">Replaced</span>
                            @elseif($status === 'Refunded')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Refunded</span>
                            @elseif($status === 'Completed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $status }}</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Customer Information --}}
            <div class="bg-white rounded-xl border shadow-sm p-5">
                <h2 class="font-semibold text-slate-800 mb-4">Customer Information</h2>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs text-slate-500 uppercase tracking-wide font-medium">Customer Name</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $warranty->customerName ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500 uppercase tracking-wide font-medium">Contact Number</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $warranty->contactNumber ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

        </div>

        {{-- RIGHT: 2/3 --}}
        <div class="col-span-2 space-y-4">

            {{-- Issue / Concern --}}
            <div class="bg-white rounded-xl border shadow-sm p-5">
                <h2 class="font-semibold text-slate-800 mb-3">Issue / Concern</h2>
                <p class="text-sm text-slate-700 leading-relaxed">{{ $warranty->issue ?? 'No issue description provided.' }}</p>
            </div>

            {{-- Resolution / Outcome --}}
            <div class="bg-white rounded-xl border shadow-sm p-5">
                <h2 class="font-semibold text-slate-800 mb-3">Resolution / Outcome</h2>
                @if(!empty($warranty->resolution))
                    <p class="text-sm font-medium text-slate-800">{{ $warranty->resolution }}</p>
                    @if(!empty($warranty->resolution_notes))
                        <p class="text-sm text-slate-700 leading-relaxed mt-2">{{ $warranty->resolution_notes }}</p>
                    @endif
                @elseif(!empty($warranty->resolution_notes))
                    <p class="text-sm text-slate-700 leading-relaxed">{{ $warranty->resolution_notes }}</p>
                @else
                    <p class="text-sm text-slate-400 italic leading-relaxed">No resolution recorded yet.</p>
                @endif
            </div>

            {{-- Notes (conditional) --}}
            @if($warranty->notes ?? false)
            <div class="bg-white rounded-xl border shadow-sm p-5">
                <h2 class="font-semibold text-slate-800 mb-3">Notes</h2>
                <p class="text-sm text-slate-700 leading-relaxed">{{ $warranty->notes }}</p>
            </div>
            @endif

        </div>
    </div>

</div>
@endsection

@push('modals')
<div id="updateModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-xl">
        <h3 class="font-semibold text-slate-800 mb-4">Update Warranty Status</h3>
        <form method="POST" action="{{ route('owner.returns.warrantyUpdate', $warranty->id ?? 1) }}">
            @csrf
            @method('PATCH')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Warranty Status</label>
                    <select name="status" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                        @foreach(['none' => 'Active', 'claimed' => 'Under Review', 'in_progress' => 'In Repair', 'resolved' => 'Completed'] as $value => $label)
                            <option value="{{ $value }}" {{ ($warranty->status_value ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Outcome</label>
                    <select name="outcome" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                        <option value="">Not yet determined</option>
                        @foreach(['replacement' => 'Replacement', 'refund' => 'Refund', 'repair' => 'Repair', 'supplier_exchange' => 'Supplier Exchange', 'denied' => 'Denied'] as $value => $label)
                            <option value="{{ $value }}" {{ old('outcome', $warranty->outcome_value ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Repair / Resolution Notes</label>
                    <textarea name="resolution_notes" rows="3" maxlength="1000"
                              placeholder="e.g. Sent to supplier for repair on Oct 5; replacement unit given to customer."
                              class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400 resize-none">{{ old('resolution_notes', $warranty->resolution_notes ?? '') }}</textarea>
                </div>
            </div>
            <div class="flex gap-2 justify-end mt-4 pt-4 border-t">
                <button type="button" onclick="document.getElementById('updateModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 text-sm text-white rounded-lg transition-opacity hover:opacity-90"
                        style="background-color:#363E48">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endpush

@push('scripts')
<script>
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.getElementById('updateModal').classList.add('hidden');
        }
    });

    document.getElementById('updateModal').addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
        }
    });
</script>
@endpush
