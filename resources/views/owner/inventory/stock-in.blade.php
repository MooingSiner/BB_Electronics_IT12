@extends('layouts.owner')

@section('title', 'Stock In — ' . $item->name)

@php $activeNav = 'inventory'; @endphp

@section('content')
    <a href="{{ route('owner.inventory.index') }}"
       class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700 mb-4 transition">
        ← Back to Inventory
    </a>

    <div class="max-w-md mx-auto">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold mb-1" style="color:#363E48">Stock In</h1>
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

        <form method="POST" action="{{ route('owner.inventory.stockin.store', $item->id) }}">
            @csrf
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-5">
                <div>
                    <label for="quantity" class="block text-sm font-medium text-slate-700 mb-1">
                        Quantity to Add <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="quantity" name="quantity" min="1" value="{{ old('quantity') }}" required
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30 @error('quantity') border-red-400 @enderror">
                    @error('quantity')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="reason" class="block text-sm font-medium text-slate-700 mb-1">Reason</label>
                    <input type="text" id="reason" name="reason" value="{{ old('reason') }}" placeholder="e.g. Physical count correction, found stock"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30 @error('reason') border-red-400 @enderror">
                    @error('reason')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center gap-3 mt-5">
                <button type="submit"
                        class="flex-1 px-5 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition shadow-sm"
                        style="background-color:#363E48">
                    Add to Stock
                </button>
                <a href="{{ route('owner.inventory.show', $item->id) }}"
                   class="flex-1 text-center px-5 py-2 text-sm font-medium text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
