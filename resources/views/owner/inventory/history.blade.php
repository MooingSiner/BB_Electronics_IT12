@extends('layouts.owner')

@section('title', 'Stock History — ' . $item->name)

@php $activeNav = 'inventory'; @endphp

@section('content')
    <a href="{{ route('owner.inventory.show', $item->id) }}"
       class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700 mb-4 transition">
        ← Back to Product
    </a>

    <h1 class="text-2xl font-bold mb-1" style="color:#363E48">Stock History</h1>
    <p class="text-sm text-slate-500 mb-6">{{ $item->name }}</p>

    <div class="bg-white rounded-xl shadow border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Date</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">Type</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Quantity</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Reason</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">By</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($movements as $movement)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3 text-slate-500 whitespace-nowrap">
                            {{ $movement->date ? \Carbon\Carbon::parse($movement->date)->format('M d, Y') : '—' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($movement->type === 'In')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">In</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Out</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-700">{{ $movement->quantity }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $movement->reason }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $movement->by }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-slate-400 text-sm">No stock movements recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
