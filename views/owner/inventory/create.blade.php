@extends('layouts.owner')

@section('title', 'Add Product')

@php $activeNav = 'inventory'; @endphp

@section('content')
    {{-- Back Link --}}
    <a href="{{ route('owner.inventory.index') }}"
       class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700 mb-4 transition">
        ← Back to Inventory
    </a>

    {{-- Page Header --}}
    <h1 class="text-2xl font-bold mb-6" style="color:#363E48">Add Product</h1>

    {{-- Success Alert --}}
    @if(session('success'))
        <div class="mb-4 flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                      clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
            <p class="font-semibold mb-1">Please fix the following errors:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form --}}
    <form method="POST" action="{{ route('owner.inventory.store') }}" class="max-w-xl">
        @csrf

        <div class="bg-white rounded-xl shadow border border-slate-200 p-6 space-y-5">

            {{-- Product Name --}}
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1">
                    Product Name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name') }}"
                       required autocomplete="off"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30 @error('name') border-red-400 @enderror">
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Category --}}
            <div>
                <label for="category" class="block text-sm font-medium text-slate-700 mb-1">
                    Category <span class="text-red-500">*</span>
                </label>
                <select id="category" name="category" required
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30 @error('category') border-red-400 @enderror">
                    <option value="">Select a category</option>
                    @foreach(['Lighting', 'Components', 'Switches', 'Wiring', 'Adapters', 'Connectors', 'Batteries'] as $cat)
                        <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
                @error('category')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div>
                <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                <textarea id="description" name="description" rows="3"
                          class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30 resize-none @error('description') border-red-400 @enderror">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Unit Price & Cost Price --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="unit_price" class="block text-sm font-medium text-slate-700 mb-1">
                        Unit Price (₱) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="unit_price" name="unit_price"
                           value="{{ old('unit_price') }}" step="0.01" min="0" required
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30 @error('unit_price') border-red-400 @enderror">
                    @error('unit_price')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="cost_price" class="block text-sm font-medium text-slate-700 mb-1">Cost Price (₱)</label>
                    <input type="number" id="cost_price" name="cost_price"
                           value="{{ old('cost_price') }}" step="0.01" min="0"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30 @error('cost_price') border-red-400 @enderror">
                    <p class="mt-1 text-xs text-slate-400">Supplier purchase price.</p>
                    @error('cost_price')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Initial Quantity & Reorder Level --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="initial_qty" class="block text-sm font-medium text-slate-700 mb-1">
                        Initial Quantity <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="initial_qty" name="initial_qty"
                           value="{{ old('initial_qty') }}" min="0" required
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30 @error('initial_qty') border-red-400 @enderror">
                    @error('initial_qty')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="reorder_level" class="block text-sm font-medium text-slate-700 mb-1">
                        Reorder Level <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="reorder_level" name="reorder_level"
                           value="{{ old('reorder_level') }}" min="0" required
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30 @error('reorder_level') border-red-400 @enderror">
                    <p class="mt-1 text-xs text-slate-400">Alert at this level.</p>
                    @error('reorder_level')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Warranty Period --}}
            <div>
                <label for="warranty_period" class="block text-sm font-medium text-slate-700 mb-1">Warranty Period</label>
                <input type="text" id="warranty_period" name="warranty_period"
                       value="{{ old('warranty_period') }}" placeholder="e.g. 1 year"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30 @error('warranty_period') border-red-400 @enderror">
                <p class="mt-1 text-xs text-slate-400">Leave blank if no warranty.</p>
                @error('warranty_period')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

        </div>

        {{-- Form Buttons --}}
        <div class="flex items-center gap-3 mt-5">
            <button type="submit"
                    class="px-5 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition shadow-sm"
                    style="background-color:#363E48">
                Add Product
            </button>
            <a href="{{ route('owner.inventory.index') }}"
               class="px-5 py-2 text-sm font-medium text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50 transition">
                Cancel
            </a>
        </div>
    </form>
@endsection
