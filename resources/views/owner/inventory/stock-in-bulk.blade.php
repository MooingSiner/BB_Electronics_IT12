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
            <h1 class="text-2xl font-bold mb-1" style="color:#363E48">Stock In — Multiple Products</h1>
            <p class="text-sm text-slate-500">Add received stock for {{ $products->count() }} product(s) at once.</p>
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

        @if($products->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 text-sm text-slate-500 text-center">
                No products selected.
                <a href="{{ route('owner.inventory.index') }}" class="underline" style="color:#363E48">Go back and check some products first.</a>
            </div>
        @else
        <form method="POST" action="{{ route('owner.inventory.stockin.bulk.store') }}">
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
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Product</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide w-28">Current</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide w-28">Qty to Add</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Reason</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($products as $index => $product)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-800">{{ $product->name }}</p>
                                    <p class="font-mono text-xs text-slate-400">{{ $product->code }}</p>
                                    <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $product->id }}">
                                </td>
                                <td class="px-4 py-3 text-right text-slate-500">{{ $product->stock }}</td>
                                <td class="px-4 py-3">
                                    <input type="number" name="items[{{ $index }}][quantity]" min="1"
                                           value="{{ old('items.'.$index.'.quantity') }}" required
                                           class="w-24 border border-slate-300 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" name="items[{{ $index }}][reason]" maxlength="255"
                                           value="{{ old('items.'.$index.'.reason') }}"
                                           placeholder="e.g. Physical count correction"
                                           class="w-full border border-slate-300 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit"
                        class="px-5 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition shadow-sm"
                        style="background-color:#363E48">
                    Add to Stock
                </button>
                <a href="{{ route('owner.inventory.index') }}"
                   class="px-5 py-2 text-sm font-medium text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50 transition">
                    Cancel
                </a>
            </div>
        </form>
        @endif
    </div>
@endsection
