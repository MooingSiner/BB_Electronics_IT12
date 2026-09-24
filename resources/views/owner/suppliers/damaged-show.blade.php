@extends('layouts.owner')

@section('title', 'Damaged Product Detail')

@php $activeNav = 'supplier-orders'; @endphp

@section('content')
    {{-- Back Link --}}
    <a href="{{ route('owner.suppliers.damaged') }}"
       class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700 mb-4 transition">
        ← Back to Damaged Products
    </a>

    {{-- Page Header --}}
    <h1 class="text-2xl font-bold mb-6" style="color:#363E48">{{ $item->id }}</h1>

    <div class="max-w-xl bg-white rounded-xl shadow border border-slate-200 p-6">
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between">
                <dt class="text-slate-500">Supplier</dt>
                <dd class="text-slate-700 font-medium">{{ $item->supplier }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Product</dt>
                <dd class="text-slate-700">{{ $item->product_name }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Quantity Damaged</dt>
                <dd class="text-red-600 font-semibold">{{ $item->qty_damaged }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Date</dt>
                <dd class="text-slate-700">{{ $item->date?->format('M d, Y') }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Description</dt>
                <dd class="text-slate-700 text-right max-w-xs">{{ $item->description }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Resolution</dt>
                <dd class="text-slate-700">{{ $item->resolution }}</dd>
            </div>
            <div class="flex justify-between items-center">
                <dt class="text-slate-500">Status</dt>
                <dd>
                    @if($item->status === 'Reported')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Reported</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Resolved</span>
                    @endif
                </dd>
            </div>
        </dl>
    </div>
@endsection
