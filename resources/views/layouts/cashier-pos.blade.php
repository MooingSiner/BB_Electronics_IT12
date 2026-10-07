<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>B&B Electronics — {{ $title ?? 'Point of Sale' }}</title>
    @vite('resources/css/app.css')
    <style>
        @view-transition { navigation: auto; }
        html { background-color: #f1f5f9; }
        body { font-family: 'Inter', sans-serif; }
        .sidebar-bg { background-color: #363E48; }
        .accent-bg  { background-color: #E0CD66; }
        .accent-text { color: #363E48; }
    </style>
    @livewireStyles
</head>
<body class="bg-slate-100 flex h-screen overflow-hidden" x-data="{ sidebarCollapsed: true }">

<div x-show="!sidebarCollapsed" style="display:none" @click="sidebarCollapsed = true" class="lg:hidden fixed inset-0 z-40 bg-black/40"></div>

{{-- ── Sidebar ─────────────────────────────────────────────────────────────── --}}
<aside class="flex-shrink-0 flex flex-col h-full sidebar-bg transition-all duration-200 max-lg:fixed max-lg:inset-y-0 max-lg:left-0 max-lg:z-50"
       :class="sidebarCollapsed ? 'w-16 max-lg:-translate-x-full' : 'w-60'">

    {{-- Logo --}}
    <div class="px-5 py-5 border-b border-white/10 flex items-center gap-2.5" :class="sidebarCollapsed ? 'justify-center px-0' : ''">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center accent-bg flex-shrink-0">
            <svg class="w-4 h-4 accent-text" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
        </div>
        <div x-show="!sidebarCollapsed" x-cloak>
            <div class="text-white font-semibold text-sm leading-tight">B&B Electronics</div>
            <div class="text-white/40 text-xs leading-tight">Sales &amp; Inventory</div>
        </div>
    </div>

    {{-- Nav items --}}
    <div class="flex-1 px-3 py-4 overflow-y-auto">
        <p x-show="!sidebarCollapsed" x-cloak class="text-white/30 text-xs uppercase tracking-wider font-medium px-2 mb-2">Menu</p>
        <ul class="space-y-0.5">

            @php $nav = 'sales'; @endphp

            @foreach ([
                ['id' => 'dashboard', 'label' => 'Dashboard',           'route' => 'cashier.dashboard',       'icon' => 'dashboard'],
                ['id' => 'sales',     'label' => 'Sales Transactions',   'route' => 'cashier.sales.index',     'icon' => 'cart'],
                ['id' => 'inventory', 'label' => 'Inventory',            'route' => 'cashier.inventory.index', 'icon' => 'box'],
                ['id' => 'returns',   'label' => 'Returns & Warranties', 'route' => 'cashier.returns.index',   'icon' => 'return'],
            ] as $item)
                @php $isActive = $nav === $item['id']; @endphp
                <li>
                    <a href="{{ route($item['route']) }}"
                       title="{{ $item['label'] }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-md text-sm transition-colors
                              {{ $isActive ? 'font-medium' : 'text-white/60 hover:text-white hover:bg-white/10' }}"
                       :class="sidebarCollapsed ? 'justify-center' : ''"
                       @if($isActive) style="background-color:#E0CD66;color:#363E48" @endif>
                        @include('layouts._nav-icon', ['icon' => $item['icon']])
                        <span x-show="!sidebarCollapsed" x-cloak>{{ $item['label'] }}</span>
                    </a>
                </li>
            @endforeach

        </ul>
    </div>

    {{-- Bottom --}}
    <div class="px-3 py-4 border-t border-white/10 space-y-0.5">
        <div class="flex items-center gap-3 px-3 py-2 mb-2" :class="sidebarCollapsed ? 'justify-center' : ''">
            @include('partials.avatar')
            <div class="min-w-0" x-show="!sidebarCollapsed" x-cloak>
                <div class="text-white text-xs font-medium truncate">{{ auth()->user()->full_name }}</div>
                <div class="text-white/40 text-xs truncate">Cashier / Store Attendant</div>
            </div>
        </div>
        <button type="button" @click="$dispatch('open-help')" title="Help"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-md text-sm text-white/60 hover:text-white hover:bg-white/10"
                :class="sidebarCollapsed ? 'justify-center' : ''">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.5 9.5a2.5 2.5 0 114 2c-.8.6-1.5 1-1.5 2M12 17h.01"/></svg>
            <span x-show="!sidebarCollapsed" x-cloak>Help</span>
        </button>
        <a href="{{ route('cashier.profile') }}" title="Account Settings"
           class="flex items-center gap-3 px-3 py-2 rounded-md text-sm text-white/60 hover:text-white hover:bg-white/10"
           :class="sidebarCollapsed ? 'justify-center' : ''">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/>
            </svg>
            <span x-show="!sidebarCollapsed" x-cloak>Account Settings</span>
        </a>
        <form data-confirm="Log out of your account?" method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" title="Logout"
                    class="w-full flex items-center gap-3 px-3 py-2 rounded-md text-sm text-white/60 hover:text-red-400 hover:bg-white/10 transition-colors"
                    :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span x-show="!sidebarCollapsed" x-cloak>Logout</span>
            </button>
        </form>
    </div>
</aside>

{{-- ── Main area ────────────────────────────────────────────────────────────── --}}
<div class="flex-1 flex flex-col min-w-0 overflow-hidden">

    {{-- Header --}}
    <header class="bg-white border-b border-slate-200 px-5 h-14 flex items-center justify-between flex-shrink-0">
        <button type="button" @click="sidebarCollapsed = !sidebarCollapsed" title="Toggle sidebar"
                class="p-2 -ml-2 rounded-md text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
    </header>

    {{-- Page content --}}
    <main class="flex-1 overflow-hidden">
        {{ $slot }}
    </main>
</div>

@include('layouts._toast')

@livewireScripts
<script>
    // If this page is restored from the browser's back/forward cache, its Livewire
    // component snapshot is stale (e.g. after completing a sale and navigating back).
    // Force a fresh load instead of letting Livewire hydrate against old state.
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
</script>
@include('partials.confirm-dialog')
@include('partials.help-guide', ['helpRole' => 'cashier'])
</body>
</html>
