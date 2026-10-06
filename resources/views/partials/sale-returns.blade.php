@if($txn->return_lines->isNotEmpty())
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="flex items-center gap-2 px-6 py-4 border-b border-slate-100">
            <h2 class="text-sm font-semibold text-[#363E48]">Returns on this sale</h2>
            @include('partials.return-badge', ['label' => $txn->return_label])
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-6 py-2 text-left font-semibold">Product</th>
                        <th class="px-6 py-2 text-left font-semibold">Qty</th>
                        <th class="px-6 py-2 text-left font-semibold">Condition</th>
                        <th class="px-6 py-2 text-left font-semibold">Resolution</th>
                        <th class="px-6 py-2 text-left font-semibold">Status</th>
                        <th class="px-6 py-2 text-right font-semibold">Refund</th>
                        <th class="px-6 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($txn->return_lines as $line)
                        <tr>
                            <td class="px-6 py-3 text-slate-700">{{ $line->product }}</td>
                            <td class="px-6 py-3 text-slate-600">{{ $line->quantity }}</td>
                            <td class="px-6 py-3 text-slate-600">{{ $line->condition }}</td>
                            <td class="px-6 py-3 text-slate-600">{{ $line->resolution }}</td>
                            <td class="px-6 py-3 text-slate-600">{{ $line->status }}</td>
                            <td class="px-6 py-3 text-right text-slate-700">{{ $line->refund !== null ? '₱'.number_format($line->refund, 2) : '—' }}</td>
                            <td class="px-6 py-3 text-right">
                                <a href="{{ route($routePrefix.'.returns.show', $line->id) }}" class="text-xs font-medium text-[#363E48] underline">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
