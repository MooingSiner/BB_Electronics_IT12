@extends('layouts.owner')

@section('title', 'Account Settings')
@php $activeNav = ''; @endphp

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Account Settings</h1>
    </div>

    {{-- Main Grid --}}
    <div class="grid grid-cols-3 gap-6 items-start">

        {{-- LEFT: Profile Card --}}
        <div class="bg-white rounded-xl border shadow-sm p-6 text-center">
            {{-- Avatar --}}
            @include('partials.avatar', ['size' => 'w-24 h-24 text-2xl', 'class' => 'mx-auto'])
            <form method="POST" action="{{ route('owner.profile.photo') }}" enctype="multipart/form-data" class="mt-3"
                  x-data @change="$el.requestSubmit()">
                @csrf
                <input type="file" name="photo" id="photo" accept="image/png,image/jpeg,image/webp" class="hidden">
                <label for="photo" class="inline-block cursor-pointer px-3 py-1.5 text-xs font-medium text-slate-700 border border-slate-300 rounded-lg hover:bg-slate-50">Upload picture</label>
            </form>
            @if (auth()->user()->profile_photo)
                <form method="POST" action="{{ route('owner.profile.photo.remove') }}" class="mt-1" data-confirm="Remove your profile picture?">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs text-red-500 hover:underline">Remove picture</button>
                </form>
            @endif
            @error('photo')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
            <p class="font-semibold text-slate-800 mt-3">{{ auth()->user()->full_name }}</p>
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
                               value="{{ old('name', auth()->user()->full_name) }}"
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
