@extends('layouts.owner')

@section('title', 'File Warranty Claim')
@php $activeNav = 'returns'; @endphp

@section('content')
<div class="max-w-xl mx-auto space-y-6">

    <a href="{{ route('owner.returns.index', ['tab' => 'warranty']) }}"
       class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700">
        ← Back to Returns &amp; Warranties
    </a>

    <div>
        <h1 class="text-2xl font-bold text-slate-800">File Warranty Claim</h1>
        <p class="text-sm text-slate-500 mt-1">Record a customer's warranty claim for an item they bought and that is still under warranty.</p>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Step 1: find the transaction --}}
    <form method="GET" action="{{ route('owner.returns.claim') }}" class="bg-white rounded-xl border shadow-sm p-6 space-y-3">
        <label class="block text-sm font-medium text-slate-700">Find Transaction</label>
        <div class="flex gap-2">
            <input type="number" name="transaction_id" min="1" placeholder="Transaction number, e.g. 12"
                   value="{{ $transactionId ?: '' }}"
                   class="flex-1 border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
            <button type="submit" class="px-5 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-opacity" style="background-color:#363E48">
                Find
            </button>
        </div>
        <p class="text-xs text-slate-400">Look up the transaction number printed on the customer's receipt.</p>
        @if($transactionId && ! $sale)
        <p class="text-xs text-red-500">No completed transaction found with that number.</p>
        @endif
    </form>

    {{-- Step 2: claim details --}}
    @if($sale && $items->isEmpty())
    <div class="bg-white rounded-xl border shadow-sm p-6 text-sm text-slate-500">
        None of the items in {{ $sale->code() }} can be claimed: they either have no warranty or the warranty has already expired.
    </div>
    @elseif($sale)
    <form method="POST" action="{{ route('owner.returns.claim.store') }}" class="bg-white rounded-xl border shadow-sm p-6 space-y-4">
        @csrf

        <div class="flex items-center justify-between px-3 py-2 bg-slate-50 border rounded-lg text-sm">
            <span class="text-slate-500">Transaction</span>
            <span class="font-semibold text-slate-800">{{ $sale->code() }}</span>
        </div>

        <div>
            <label for="sale_item_id" class="block text-sm font-medium text-slate-700 mb-1">Product</label>
            <select id="sale_item_id" name="sale_item_id" required
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                @foreach($items as $item)
                    @php $claimed = $item->warranty && $item->warranty->claim_status->value !== 'none'; @endphp
                    <option value="{{ $item->sale_item_id }}" @disabled($claimed) @selected(old('sale_item_id') == $item->sale_item_id)>
                        {{ $item->product->product_name }} &mdash; warranty until {{ $item->sale->sale_date->copy()->addDays($item->product->warranty_period_days)->format('M d, Y') }}{{ $claimed ? ' (claim already filed)' : '' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="customer_name" class="block text-sm font-medium text-slate-700 mb-1">Customer Name</label>
            <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required maxlength="100"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
        </div>

        <div>
            <label for="contact_number" class="block text-sm font-medium text-slate-700 mb-1">Contact Number</label>
            <input type="text" id="contact_number" name="contact_number" value="{{ old('contact_number') }}" maxlength="30"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
        </div>

        <div>
            <label for="issue" class="block text-sm font-medium text-slate-700 mb-1">What is wrong with the item?</label>
            <textarea id="issue" name="issue" rows="4" required maxlength="1000"
                      placeholder="Describe the problem the customer reported, e.g. stopped working after 2 weeks."
                      class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400 resize-none">{{ old('issue') }}</textarea>
        </div>

        <button type="submit" class="w-full py-2.5 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-opacity" style="background-color:#363E48">
            File Claim
        </button>
    </form>
    @endif

</div>
@endsection
