@if(! empty($label))
    @php
        $tone = match ($label) {
            'Return pending' => 'bg-amber-100 text-amber-700',
            'Partly returned' => 'bg-teal-100 text-teal-700',
            default => 'bg-slate-200 text-slate-700',
        };
    @endphp
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $tone }}">{{ $label }}</span>
@endif
