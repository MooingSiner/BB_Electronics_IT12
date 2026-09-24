@extends('layouts.owner')

@section('title', 'Edit User')

@php $activeNav = 'users'; @endphp

@section('content')
    {{-- Back Link --}}
    <a href="{{ route('owner.users.index') }}"
       class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700 mb-4 transition">
        ← Back to User Management
    </a>

    <div class="max-w-xl mx-auto">

    {{-- Page Header --}}
    <h1 class="text-2xl font-bold mb-6 text-center" style="color:#363E48">Edit User</h1>

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
            <p class="font-semibold mb-1">Please fix the following errors:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form --}}
    <form method="POST" action="{{ route('owner.users.update', $item->user_id) }}">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl shadow border border-slate-200 p-6 space-y-5">

            {{-- Full Name --}}
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1">
                    Full Name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $item->full_name) }}"
                       required autocomplete="off"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30 @error('name') border-red-400 @enderror">
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Username --}}
            <div>
                <label for="username" class="block text-sm font-medium text-slate-700 mb-1">
                    Username <span class="text-red-500">*</span>
                </label>
                <input type="text" id="username" name="username" value="{{ old('username', $item->username) }}"
                       required autocomplete="off"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30 @error('username') border-red-400 @enderror">
                @error('username')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Role --}}
            <div>
                <label for="role" class="block text-sm font-medium text-slate-700 mb-1">Role</label>
                <select id="role" name="role"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30 @error('role') border-red-400 @enderror">
                    <option value="owner" {{ old('role', $item->role === \App\Enums\UserRole::OwnerManager ? 'owner' : 'cashier') === 'owner' ? 'selected' : '' }}>Owner / Manager</option>
                    <option value="cashier" {{ old('role', $item->role === \App\Enums\UserRole::OwnerManager ? 'owner' : 'cashier') === 'cashier' ? 'selected' : '' }}>Cashier / Store Attendant</option>
                </select>
                @error('role')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Status --}}
            <div>
                <label for="status" class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                <select id="status" name="status"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30 @error('status') border-red-400 @enderror">
                    <option value="Active" {{ old('status', $item->status === \App\Enums\UserStatus::Active ? 'Active' : 'Inactive') === 'Active' ? 'selected' : '' }}>Active</option>
                    <option value="Inactive" {{ old('status', $item->status === \App\Enums\UserStatus::Active ? 'Active' : 'Inactive') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <hr class="border-slate-200">
            <p class="text-xs text-slate-500">Leave blank to keep the current password.</p>

            {{-- Password --}}
            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">New Password</label>
                <input type="password" id="password" name="password" autocomplete="new-password"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30 @error('password') border-red-400 @enderror">
                @error('password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirm Password --}}
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Confirm New Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#363E48]/30">
            </div>

        </div>

        {{-- Form Buttons --}}
        <div class="flex items-center gap-3 mt-5">
            <button type="submit"
                    class="flex-1 px-5 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition shadow-sm"
                    style="background-color:#363E48">
                Save Changes
            </button>
            <a href="{{ route('owner.users.index') }}"
               class="flex-1 text-center px-5 py-2 text-sm font-medium text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50 transition">
                Cancel
            </a>
        </div>
    </form>
    </div>
@endsection
