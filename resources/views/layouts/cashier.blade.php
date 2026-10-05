<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>B&B Electronics — @yield('title', 'Dashboard')</title>
    @vite('resources/css/app.css')
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-bg { background-color: #363E48; }
        .accent-bg  { background-color: #E0CD66; }
        .accent-text { color: #363E48; }
        .tip { position: relative; }
        .tip::after {
            content: attr(data-tip);
            position: absolute; bottom: calc(100% + 8px); left: 50%; transform: translateX(-50%);
            padding: 7px 14px; border-radius: 10px; background: #363E48; color: #fff;
            font-size: 14px; font-weight: 500; line-height: 1.2; white-space: nowrap;
            box-shadow: 0 6px 16px rgba(0,0,0,.22);
            opacity: 0; visibility: hidden; pointer-events: none; z-index: 40;
        }
        .tip:hover::after, .tip:focus-visible::after { opacity: 1; visibility: visible; }
    </style>
    @stack('styles')
    @livewireStyles
</head>
<body x-data="{ menuOpen: false }" @keydown.escape.window="menuOpen = false" class="bg-slate-100 flex h-screen overflow-hidden">

<div x-show="menuOpen" style="display:none" @click="menuOpen = false" class="lg:hidden fixed inset-0 z-40 bg-black/40"></div>

{{-- ── Sidebar ─────────────────────────────────────────────────────────────── --}}
<aside :class="menuOpen ? 'translate-x-0' : '-translate-x-full'"
       class="fixed inset-y-0 left-0 z-50 w-60 flex-shrink-0 flex flex-col h-full sidebar-bg transition-transform duration-200 lg:static lg:z-auto lg:translate-x-0">

    {{-- Logo --}}
    <div class="px-5 py-5 border-b border-white/10 flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center accent-bg">
            <svg class="w-4 h-4 accent-text" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
        </div>
        <div>
            <div class="text-white font-semibold text-sm leading-tight">B&B Electronics</div>
            <div class="text-white/40 text-xs leading-tight">Sales &amp; Inventory</div>
        </div>
    </div>

    {{-- Nav items --}}
    <div class="flex-1 px-3 py-4 overflow-y-auto">
        <p class="text-white/30 text-xs uppercase tracking-wider font-medium px-2 mb-2">Menu</p>
        <ul class="space-y-0.5">

            @php $nav = $activeNav ?? ''; @endphp

            @foreach ([
                ['id' => 'dashboard', 'label' => 'Dashboard',           'route' => 'cashier.dashboard',       'icon' => 'dashboard'],
                ['id' => 'sales',     'label' => 'Sales Transactions',   'route' => 'cashier.sales.index',     'icon' => 'cart'],
                ['id' => 'inventory', 'label' => 'Inventory',            'route' => 'cashier.inventory.index', 'icon' => 'box'],
                ['id' => 'returns',   'label' => 'Returns & Warranties', 'route' => 'cashier.returns.index',   'icon' => 'return'],
            ] as $item)
                @php $isActive = $nav === $item['id']; @endphp
                <li>
                    <a href="{{ route($item['route']) }}" @click="menuOpen = false"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-md text-sm transition-colors
                              {{ $isActive ? 'font-medium' : 'text-white/60 hover:text-white hover:bg-white/10' }}"
                       @if($isActive) style="background-color:#E0CD66;color:#363E48" @endif>
                        @include('layouts._nav-icon', ['icon' => $item['icon']])
                        {{ $item['label'] }}
                    </a>
                </li>
            @endforeach

        </ul>
    </div>

    {{-- Bottom --}}
    <div class="px-3 py-4 border-t border-white/10 space-y-0.5">
        <div class="flex items-center gap-3 px-3 py-2 mb-2">
            <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 accent-bg">
                <span class="text-xs font-bold accent-text">
                    {{ strtoupper(substr(auth()->user()->full_name, 0, 1)) }}{{ strtoupper(substr(strstr(auth()->user()->full_name, ' '), 1, 1)) }}
                </span>
            </div>
            <div class="min-w-0">
                <div class="text-white text-xs font-medium truncate">{{ auth()->user()->full_name }}</div>
                <div class="text-white/40 text-xs truncate">Cashier / Store Attendant</div>
            </div>
        </div>
        <a href="{{ route('cashier.profile') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-md text-sm text-white/60 hover:text-white hover:bg-white/10">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/>
            </svg>
            Account Settings
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full flex items-center gap-3 px-3 py-2 rounded-md text-sm text-white/60 hover:text-red-400 hover:bg-white/10 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Logout
            </button>
        </form>
    </div>
</aside>

{{-- ── Main area ────────────────────────────────────────────────────────────── --}}
<div class="flex-1 flex flex-col min-w-0 overflow-hidden">

    {{-- Header --}}
    <header class="bg-white border-b border-slate-200 px-5 h-14 flex items-center justify-between flex-shrink-0">
        <div class="flex items-center gap-2 min-w-0">
            <button type="button" @click="menuOpen = !menuOpen" aria-label="Toggle menu"
                    class="lg:hidden p-2 -ml-2 rounded-md text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <div class="text-sm text-slate-500 truncate">@yield('breadcrumb')</div>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 px-2 py-1.5 rounded-md hover:bg-slate-100 cursor-pointer">
                <div class="w-7 h-7 rounded-full flex items-center justify-center" style="background-color:#363E48">
                    <span class="text-white text-xs font-semibold">
                        {{ strtoupper(substr(auth()->user()->full_name, 0, 1)) }}{{ strtoupper(substr(strstr(auth()->user()->full_name, ' '), 1, 1)) }}
                    </span>
                </div>
                <div class="hidden sm:block text-left">
                    <div class="text-xs font-medium text-slate-700">{{ auth()->user()->full_name }}</div>
                    <div class="text-xs text-slate-400">Cashier / Store Attendant</div>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </div>
    </header>

    {{-- Page content --}}
    <main class="@yield('main-class', 'flex-1 overflow-y-auto p-5 lg:p-6')">
        @yield('content')
    </main>
</div>

@stack('modals')

@include('layouts._toast')

@stack('scripts')
@livewireScripts
@include('partials.confirm-dialog')
</body>
</html>
