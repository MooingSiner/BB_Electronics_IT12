@extends('layouts.owner')

@section('title', 'Edit Product')

@php $activeNav = 'inventory'; @endphp

@section('content')
    {{-- Back Link --}}
    <a href="{{ route('owner.inventory.index') }}"
       class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700 mb-4 transition">
        ← Back to Inventory
    </a>

    <div class="max-w-xl mx-auto">

    {{-- Page Header --}}
    <h1 class="text-2xl font-bold mb-6 text-center" style="color:#363E48">Edit Product</h1>

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
    <form method="POST" action="{{ route('owner.inventory.update', $item->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl shadow border border-slate-200 p-6 space-y-5">

            {{-- Product Name --}}
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1">
                    Product Name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $item->name) }}"
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
                <input type="text" id="category" name="category" list="category-options" value="{{ old('category', $item->category) }}"
                       required autocomplete="off"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30 @error('category') border-red-400 @enderror">
                <datalist id="category-options">
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}"></option>
                    @endforeach
                </datalist>
                @error('category')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div>
                <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                <textarea id="description" name="description" rows="3"
                          class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30 resize-none @error('description') border-red-400 @enderror">{{ old('description', $item->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Image --}}
            <div>
                <label for="image_file" class="block text-sm font-medium text-slate-700 mb-1">Upload Image</label>
                <input type="file" id="image_file" name="image_file" accept="image/*"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30 file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-slate-100 file:text-slate-600 hover:file:bg-slate-200 @error('image_file') border-red-400 @enderror">
                <p class="mt-1 text-xs text-slate-400">JPG, PNG, or GIF up to 4MB. Replaces the current photo below.</p>
                @error('image_file')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror

                <div class="flex items-center gap-2 my-3">
                    <div class="flex-1 h-px bg-slate-200"></div>
                    <span class="text-xs text-slate-400">or</span>
                    <div class="flex-1 h-px bg-slate-200"></div>
                </div>

                <label for="image_url" class="block text-sm font-medium text-slate-700 mb-1">Image URL</label>
                <input type="url" id="image_url" name="image_url" value="{{ old('image_url', $item->image_url) }}"
                       placeholder="https://example.com/product-photo.jpg"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30 @error('image_url') border-red-400 @enderror">
                <p class="mt-1 text-xs text-slate-400">Link to a hosted photo instead. Uploading a file above takes priority.</p>
                @error('image_url')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
                @if($item->image_url)
                <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="mt-2 w-24 h-24 object-cover rounded-lg border border-slate-200">
                @endif
            </div>

            {{-- Barcode --}}
            <div>
                <label for="barcode" class="block text-sm font-medium text-slate-700 mb-1">Barcode</label>
                <input type="text" id="barcode" name="barcode" value="{{ old('barcode', $item->barcode) }}" maxlength="50"
                       placeholder="Click here, then scan the product's barcode"
                       @keydown.enter.prevent
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-[#363E48]/30 @error('barcode') border-red-400 @enderror">
                <p class="mt-1 text-xs text-slate-400">Optional. Leave blank to use the product code. The cashier can scan this at the POS.</p>
                @error('barcode')
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
                           value="{{ old('unit_price', $item->unit_price) }}" step="0.01" min="0" required
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30 @error('unit_price') border-red-400 @enderror">
                    @error('unit_price')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="cost_price" class="block text-sm font-medium text-slate-700 mb-1">Capital Price (₱)</label>
                    <input type="number" id="cost_price" name="cost_price"
                           value="{{ old('cost_price', $item->cost_price) }}" step="0.01" min="0"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30 @error('cost_price') border-red-400 @enderror">
                    @error('cost_price')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Reorder Level --}}
            <div>
                <label for="reorder_level" class="block text-sm font-medium text-slate-700 mb-1">
                    Reorder Level <span class="text-red-500">*</span>
                </label>
                <input type="number" id="reorder_level" name="reorder_level"
                       value="{{ old('reorder_level', $item->reorder_level) }}" min="0" required
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30 @error('reorder_level') border-red-400 @enderror">
                <p class="mt-1 text-xs text-slate-400">To change the actual quantity on hand, use Stock In instead.</p>
                @error('reorder_level')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Warranty Period --}}
            <div>
                <label for="warranty_period" class="block text-sm font-medium text-slate-700 mb-1">Warranty Period</label>
                <input type="text" id="warranty_period" name="warranty_period"
                       value="{{ old('warranty_period', $item->warranty_period) }}" placeholder="e.g. 1 year"
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
                    class="flex-1 px-5 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition shadow-sm"
                    style="background-color:#363E48">
                Save Changes
            </button>
            <a href="{{ route('owner.inventory.show', $item->id) }}"
               class="flex-1 text-center px-5 py-2 text-sm font-medium text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50 transition">
                Cancel
            </a>
        </div>
    </form>
    </div>
@endsection
