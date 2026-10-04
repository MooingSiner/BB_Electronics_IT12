@if($paginator instanceof \Illuminate\Pagination\LengthAwarePaginator && $paginator->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">
        {{ $paginator->withQueryString()->links() }}
    </div>
@endif
