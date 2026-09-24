@extends('layouts.owner')

@section('title', 'Return Detail')
@php $activeNav = 'returns'; @endphp

@section('content')
<div class="space-y-6 max-w-2xl">

    {{-- Back Link --}}
    <a href="{{ route('owner.returns.index') }}"
       class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700">
        ← Back to Returns & Warranties
    </a>

    {{-- Page Header --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Return #{{ $return->id }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ $return->product_name }} ({{ $return->product_code }})</p>
        </div>
        @if($return->status === 'Pending')
        <form method="POST" action="{{ route('owner.returns.resolve', $return->id) }}">
            @csrf
            @method('PATCH')
            <button type="submit"
                    class="shrink-0 px-4 py-2 text-sm font-medium text-white rounded-lg transition-opacity hover:opacity-90"
                    style="background-color:#363E48"
                    onclick="return confirm('Mark this return as resolved?')">
                Mark Resolved
            </button>
        </form>
        @endif
    </div>

    {{-- Success Alert --}}
    @if(session('success'))
    <div class="flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Return Information --}}
    <div class="bg-white rounded-xl border shadow-sm p-5">
        <h2 class="font-semibold text-slate-800 mb-4">Return Information</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between">
                <dt class="text-slate-500">Transaction</dt>
                <dd class="text-slate-800 font-medium">
                    @if($return->transaction_sale_id)
                        <a href="{{ route('owner.sales.show', $return->transaction_sale_id) }}" class="hover:underline" style="color:#363E48">{{ $return->transaction_id }}</a>
                    @else
                        {{ $return->transaction_id }}
                    @endif
                </dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Quantity</dt>
                <dd class="text-slate-700">{{ $return->qty }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Reason</dt>
                <dd class="text-slate-700">{{ $return->reason }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Condition</dt>
                <dd class="text-slate-700">{{ $return->condition }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Resolution</dt>
                <dd class="text-slate-700">{{ $return->resolution }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Date</dt>
                <dd class="text-slate-700">{{ $return->created_at?->format('M d, Y g:i A') }}</dd>
            </div>
            <div class="flex justify-between items-center">
                <dt class="text-slate-500">Status</dt>
                <dd>
                    @if($return->status === 'Pending')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Pending</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>
                    @endif
                </dd>
            </div>
        </dl>
    </div>

</div>
@endsection
