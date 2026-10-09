{{-- Cancel Order: asks for a reason first, then the usual confirmation before anything is cancelled. --}}
<div x-data="{ open: false, reason: '' }" class="inline-block">
    <button type="button" @click="open = true"
            class="px-4 py-2 text-sm font-medium rounded-lg border border-red-200 text-red-600 bg-red-50 hover:bg-red-100 transition">
        Cancel Order
    </button>

    <div x-show="open" x-cloak @keydown.escape.window="open = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true">
        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl" @click.outside="open = false">
            <h3 class="text-base font-semibold text-slate-800">Cancel this order</h3>
            <p class="mt-1 text-sm text-slate-500">Nothing has been received yet, so stock is not affected. Choose why the order is being cancelled.</p>

            <form method="POST" action="{{ $action }}" class="mt-4 space-y-3"
                  data-confirm="Cancel this order? Nothing has been received yet, so stock is not affected. This cannot be undone.">
                @csrf
                <div>
                    <label for="cancelReason" class="block text-sm font-medium text-slate-700 mb-1">Reason <span class="text-red-500">*</span></label>
                    <select id="cancelReason" name="reason" required x-model="reason"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30">
                        <option value="">Choose a reason…</option>
                        @foreach(\App\Support\CancelReasons::options() as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-show="reason === '{{ \App\Support\CancelReasons::OTHER }}'" x-cloak>
                    <input type="text" name="note" maxlength="60" placeholder="Type the reason"
                           :required="reason === '{{ \App\Support\CancelReasons::OTHER }}'"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="open = false"
                            class="px-4 py-2 text-sm border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition">Keep order</button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white rounded-lg bg-red-600 hover:bg-red-700 transition">Cancel order</button>
                </div>
            </form>
        </div>
    </div>
</div>
