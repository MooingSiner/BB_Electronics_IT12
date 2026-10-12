@extends('layouts.owner')

@section('title', 'Shop Settings')

@php $activeNav = 'settings'; @endphp

@section('content')
    <div class="max-w-xl mx-auto">

    <h1 class="text-2xl font-bold mb-1 text-center" style="color:#363E48">Shop Settings</h1>
    <p class="text-sm text-slate-500 mb-6 text-center">Settings that apply to every sale in the point of sale.</p>

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

    <form data-confirm="Save the VAT rate? It will apply to new sales only." method="POST" action="{{ route('owner.settings.update') }}">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl shadow border border-slate-200 p-6 space-y-5">
            <div>
                <label for="vat_rate" class="block text-sm font-medium text-slate-700 mb-1">
                    VAT rate (%) <span class="text-red-500">*</span>
                </label>
                <input type="number" id="vat_rate" name="vat_rate" value="{{ old('vat_rate', rtrim(rtrim(number_format($vatRate, 2, '.', ''), '0'), '.')) }}"
                       min="0" max="100" step="0.01" required inputmode="decimal"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30 @error('vat_rate') border-red-400 @enderror">
                <p class="mt-1 text-xs text-slate-500">
                    VAT is added to the amount left after any discount, and it shows as its own line on the cart, the receipt, and the sale details.
                    Use 0 if the shop does not charge VAT. A change applies to new sales only; past sales keep the rate they were made with.
                </p>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-5">
            <a href="{{ route('owner.dashboard') }}"
               class="px-4 py-2 text-sm border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition">Cancel</a>
            <button type="submit"
                    class="px-5 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition"
                    style="background-color:#363E48">Save</button>
        </div>
    </form>

    </div>
@endsection
