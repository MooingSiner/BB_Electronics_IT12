<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>B&B Electronics — @yield('title', 'Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-bg { background-color: #363E48; }
        .accent-bg  { background-color: #E0CD66; }
        .accent-text { color: #363E48; }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-100 flex h-screen overflow-hidden">

{{-- ── Sidebar ─────────────────────────────────────────────────────────────── --}}
<aside class="w-60 flex-shrink-0 flex flex-col h-full sidebar-bg">

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

            @php
                $nav = $activeNav ?? '';
            @endphp

            @foreach ([
                ['id' => 'dashboard',         'label' => 'Dashboard',           'route' => 'owner.dashboard',        'icon' => 'dashboard'],
                ['id' => 'sales',             'label' => 'Sales Transactions',   'route' => 'owner.sales.index',      'icon' => 'cart'],
                ['id' => 'inventory',         'label' => 'Inventory',            'route' => 'owner.inventory.index',  'icon' => 'box'],
                ['id' => 'supplier-orders',   'label' => 'Supplier Orders',      'route' => 'owner.suppliers.index',  'icon' => 'truck'],
                ['id' => 'returns',           'label' => 'Returns & Warranties', 'route' => 'owner.returns.index',    'icon' => 'return'],
                ['id' => 'reports',           'label' => 'Reports',              'route' => 'owner.reports.index',    'icon' => 'chart'],
                ['id' => 'users',             'label' => 'User Management',      'route' => 'owner.users.index',      'icon' => 'users'],
            ] as $item)
                @php $isActive = $nav === $item['id']; @endphp
                <li>
                    <a href="{{ route($item['route']) }}"
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
                    {{ strtoupper(substr(auth()->user()->name ?? 'MS', 0, 1)) }}{{ strtoupper(substr(strstr(auth()->user()->name ?? ' S', ' '), 1, 1)) }}
                </span>
            </div>
            <div class="min-w-0">
                <div class="text-white text-xs font-medium truncate">{{ auth()->user()->name ?? 'Maria Santos' }}</div>
                <div class="text-white/40 text-xs truncate">Owner / Manager</div>
            </div>
        </div>
        <a href="{{ route('owner.profile') }}"
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
        <div class="text-sm text-slate-500">@yield('breadcrumb')</div>
        <div class="flex items-center gap-3">
            <button class="relative p-1.5 text-slate-400 hover:text-slate-600 rounded-md hover:bg-slate-100">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span class="absolute top-1 right-1 w-2 h-2 rounded-full" style="background-color:#E0CD66"></span>
            </button>
            <div class="flex items-center gap-2 px-2 py-1.5 rounded-md hover:bg-slate-100 cursor-pointer">
                <div class="w-7 h-7 rounded-full flex items-center justify-center" style="background-color:#363E48">
                    <span class="text-white text-xs font-semibold">
                        {{ strtoupper(substr(auth()->user()->name ?? 'MS', 0, 1)) }}{{ strtoupper(substr(strstr(auth()->user()->name ?? ' S', ' '), 1, 1)) }}
                    </span>
                </div>
                <div class="hidden sm:block text-left">
                    <div class="text-xs font-medium text-slate-700">{{ auth()->user()->name ?? 'Maria Santos' }}</div>
                    <div class="text-xs text-slate-400">Owner / Manager</div>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </div>
    </header>

    {{-- Page content --}}
    <main class="flex-1 overflow-y-auto p-5 lg:p-6">
        @yield('content')
    </main>
</div>

{{-- ── Modals (stacked from child views) ───────────────────────────────────── --}}
@stack('modals')

@stack('scripts')
</body>
</html>
