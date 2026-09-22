@extends('layouts.cashier')

@section('title', 'Account Settings')

@php $activeNav = ''; @endphp

@section('content')
<div class="p-6 space-y-6">

    {{-- Page Header --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Account Settings</h1>
        <p class="text-sm text-slate-500 mt-1">Manage your personal information and account security.</p>
    </div>

    {{-- Success Alert --}}
    @if(session('success'))
    <div class="flex items-center gap-3 bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-sm text-green-700">
        <svg class="w-5 h-5 flex-shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Error Alert --}}
    @if($errors->any())
    <div class="flex items-start gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
        <svg class="w-5 h-5 flex-shrink-0 text-red-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <ul class="space-y-0.5 list-disc list-inside">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT: Profile Card --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 text-center">
                {{-- Avatar --}}
                <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4 text-white text-2xl font-bold"
                     style="background-color:#363E48;">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}{{ strtoupper(substr(explode(' ', auth()->user()->name ?? 'U ')[1] ?? 'U', 0, 1)) }}
                </div>
                <h2 class="text-lg font-bold text-slate-800">{{ auth()->user()->name }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">Cashier / Store Attendant</p>
                <div class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium" style="background-color:rgba(224,205,102,0.2); color:#363E48;">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    {{ auth()->user()->username ?? auth()->user()->email }}
                </div>
                <div class="mt-4 pt-4 border-t border-slate-100 space-y-2 text-sm text-left">
                    <div class="flex items-center gap-2 text-slate-500">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span class="truncate">{{ auth()->user()->email }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-slate-500">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>Joined {{ auth()->user()->created_at ? \Carbon\Carbon::parse(auth()->user()->created_at)->format('M Y') : '—' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT: Forms --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Personal Information Card --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <h3 class="font-semibold text-slate-800 mb-5">Personal Information</h3>
                <form method="POST" action="{{ route('cashier.profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    {{-- Full Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">Full Name</label>
                        <input type="text"
                               id="name"
                               name="name"
                               value="{{ old('name', auth()->user()->name) }}"
                               required
                               class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 transition-shadow @error('name') border-red-300 @enderror"
                               style="--tw-ring-color:#363E48;">
                        @error('name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Username --}}
                    <div>
                        <label for="username" class="block text-sm font-medium text-slate-700 mb-1.5">Username</label>
                        <input type="text"
                               id="username"
                               name="username"
                               value="{{ old('username', auth()->user()->username ?? '') }}"
                               class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 transition-shadow @error('username') border-red-300 @enderror"
                               style="--tw-ring-color:#363E48;">
                        @error('username')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Role (read-only) --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Role</label>
                        <div class="flex items-center gap-2.5 w-full px-3.5 py-2.5 border border-slate-100 bg-slate-50 rounded-xl text-sm text-slate-500 cursor-not-allowed">
                            <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Cashier / Store Attendant
                        </div>
                        <p class="mt-1 text-xs text-slate-400">You cannot change your own role.</p>
                    </div>

                    <div class="pt-1">
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white shadow-sm hover:opacity-90 transition-opacity"
                                style="background-color:#363E48;">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            {{-- Change Password Card --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <h3 class="font-semibold text-slate-800 mb-5">Change Password</h3>
                <form method="POST" action="{{ route('cashier.profile.password') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    {{-- Current Password --}}
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-slate-700 mb-1.5">Current Password</label>
                        <input type="password"
                               id="current_password"
                               name="current_password"
                               autocomplete="current-password"
                               required
                               class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 transition-shadow @error('current_password') border-red-300 @enderror"
                               style="--tw-ring-color:#363E48;">
                        @error('current_password')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- New Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">New Password</label>
                        <input type="password"
                               id="password"
                               name="password"
                               autocomplete="new-password"
                               required
                               class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 transition-shadow @error('password') border-red-300 @enderror"
                               style="--tw-ring-color:#363E48;">
                        @error('password')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Confirm New Password --}}
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1.5">Confirm New Password</label>
                        <input type="password"
                               id="password_confirmation"
                               name="password_confirmation"
                               autocomplete="new-password"
                               required
                               class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 transition-shadow"
                               style="--tw-ring-color:#363E48;">
                    </div>

                    <div class="pt-1">
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white shadow-sm hover:opacity-90 transition-opacity"
                                style="background-color:#363E48;">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

</div>
@endsection
