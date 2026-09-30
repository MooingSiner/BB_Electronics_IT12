@if(session('success') || session('error'))
    <div x-data="{ show: true }"
         x-show="show"
         x-init="setTimeout(() => show = false, 4000)"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-4 left-4 z-[100] max-w-sm flex items-start gap-3 rounded-xl shadow-lg border px-4 py-3
                {{ session('error') ? 'bg-red-50 border-red-200' : 'bg-white border-slate-200' }}">
        @if(session('error'))
            <span class="mt-0.5 flex-shrink-0 w-5 h-5 rounded-full bg-red-100 flex items-center justify-center">
                <svg class="w-3 h-3 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </span>
            <p class="text-sm text-red-700 flex-1">{{ session('error') }}</p>
        @else
            <span class="mt-0.5 flex-shrink-0 w-5 h-5 rounded-full flex items-center justify-center" style="background-color:#E0CD66">
                <svg class="w-3 h-3" style="color:#363E48" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </span>
            <p class="text-sm text-slate-700 flex-1">{{ session('success') }}</p>
        @endif
        <button type="button" @click="show = false" class="flex-shrink-0 text-slate-400 hover:text-slate-600 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
@endif
