@extends('layouts.owner')

@section('title', 'Account Settings')
@php $activeNav = ''; @endphp

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Account Settings</h1>
    </div>

    {{-- Success Alert --}}
    @if(session('success'))
    <div class="flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Main Grid --}}
    <div class="grid grid-cols-3 gap-6 items-start">

        {{-- LEFT: Profile Card --}}
        <div class="bg-white rounded-xl border shadow-sm p-6 text-center">
            {{-- Avatar --}}
            <div class="w-20 h-20 mx-auto rounded-full flex items-center justify-center" style="background-color:#363E48">
                <span class="font-bold text-xl" style="color:#E0CD66">
                    @php
                        $name = auth()->user()->name ?? 'Maria Santos';
                        $parts = explode(' ', trim($name));
                        $initials = strtoupper(substr($parts[0], 0, 1));
                        if (count($parts) > 1) {
                            $initials .= strtoupper(substr($parts[count($parts) - 1], 0, 1));
                        }
                        echo $initials;
                    @endphp
                </span>
            </div>
            <p class="font-semibold text-slate-800 mt-3">{{ auth()->user()->name ?? 'Maria Santos' }}</p>
            <p class="text-sm text-slate-500 mt-0.5">Owner / Manager</p>
            <p class="text-xs text-slate-400 mt-0.5">{{ auth()->user()->username ?? 'msantos' }}</p>
        </div>

        {{-- RIGHT: Forms --}}
        <div class="col-span-2 space-y-5">

            {{-- Personal Information --}}
            <div class="bg-white rounded-xl border shadow-sm p-6">
                <h2 class="font-semibold text-slate-800 mb-4">Personal Information</h2>
                <form method="POST" action="{{ route('owner.profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                        <input type="text" id="name" name="name"
                               value="{{ old('name', auth()->user()->name) }}"
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400 @error('name') border-red-400 @enderror">
                        @error('name')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="username" class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                        <input type="text" id="username" name="username"
                               value="{{ old('username', auth()->user()->username) }}"
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400 @error('username') border-red-400 @enderror">
                        @error('username')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Role</label>
                        <div class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-600">
                            Owner / Manager
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Role can only be changed by a system administrator.</p>
                    </div>
                    <div>
                        <button type="submit"
                                class="px-5 py-2 text-sm font-medium text-white rounded-lg transition-opacity hover:opacity-90"
                                style="background-color:#363E48">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            {{-- Change Password --}}
            <div class="bg-white rounded-xl border shadow-sm p-6">
                <h2 class="font-semibold text-slate-800 mb-4">Change Password</h2>
                <form method="POST" action="{{ route('owner.profile.password') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-slate-700 mb-1">Current Password</label>
                        <input type="password" id="current_password" name="current_password"
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400 @error('current_password') border-red-400 @enderror">
                        @error('current_password')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1">New Password</label>
                        <input type="password" id="password" name="password"
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400 @error('password') border-red-400 @enderror">
                        @error('password')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Confirm New Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                    </div>
                    <div>
                        <button type="submit"
                                class="px-5 py-2 text-sm font-medium text-white rounded-lg transition-opacity hover:opacity-90"
                                style="background-color:#363E48">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

</div>
@endsection
