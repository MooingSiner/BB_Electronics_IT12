@extends('layouts.owner')

@section('title', 'Stock Out — ' . $item->name)

@php $activeNav = 'inventory'; @endphp

@section('content')
    <a href="{{ route('owner.inventory.index') }}"
       class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700 mb-4 transition">
        ← Back to Inventory
    </a>

    <div class="max-w-md mx-auto">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-1" style="color:#363E48">Stock Out</h1>
            <p class="text-sm text-slate-500">{{ $item->name }} &middot; currently {{ $item->stock }} in stock</p>
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

        <form method="POST" action="{{ route('owner.inventory.stockout.store', $item->id) }}">
            @csrf
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-5">
                <div>
                    <label for="quantity" class="block text-sm font-medium text-slate-700 mb-1">
                        Quantity to Remove <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="quantity" name="quantity" min="1" max="{{ $item->stock }}" value="{{ old('quantity') }}" required
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400/30 @error('quantity') border-red-400 @enderror">
                    <p class="mt-1 text-xs text-slate-400">Can't exceed the {{ $item->stock }} unit(s) currently in stock.</p>
                    @error('quantity')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="date_adjusted" class="block text-sm font-medium text-slate-700 mb-1">
                        Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="date_adjusted" name="date_adjusted" value="{{ old('date_adjusted', date('Y-m-d')) }}"
                           max="{{ date('Y-m-d') }}" required
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400/30 @error('date_adjusted') border-red-400 @enderror">
                    <p class="mt-1 text-xs text-slate-400">Backdate this if the stock was actually lost or corrected earlier.</p>
                    @error('date_adjusted')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="reason" class="block text-sm font-medium text-slate-700 mb-1">
                        Reason <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="reason" name="reason" value="{{ old('reason') }}" placeholder="e.g. Physical count correction, shrinkage, breakage"
                           required
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400/30 @error('reason') border-red-400 @enderror">
                    @error('reason')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center gap-3 mt-5">
                <button type="submit"
                        class="flex-1 px-5 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition shadow-sm bg-red-600">
                    Remove from Stock
                </button>
                <a href="{{ route('owner.inventory.show', $item->id) }}"
                   class="flex-1 text-center px-5 py-2 text-sm font-medium text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
