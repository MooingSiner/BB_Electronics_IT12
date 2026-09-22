@extends('layouts.owner')

@section('title', 'User Management')
@php $activeNav = 'users'; @endphp

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">User Management</h1>
            <p class="text-sm text-slate-500 mt-1">Manage staff accounts and roles. Only the owner can add or change roles.</p>
        </div>
        <button id="addUserBtn"
                class="shrink-0 inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-white rounded-lg transition-opacity hover:opacity-90"
                style="background-color:#363E48">
            + Add User
        </button>
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

    {{-- Filter Bar --}}
    <div class="bg-white rounded-xl border shadow-sm p-4">
        <form method="GET" action="{{ route('owner.users.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-40">
                <label class="block text-xs font-medium text-slate-600 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Name or username..."
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Role</label>
                <select name="role" class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                    <option value="">All Roles</option>
                    <option value="owner" {{ request('role') === 'owner' ? 'selected' : '' }}>Owner / Manager</option>
                    <option value="cashier" {{ request('role') === 'cashier' ? 'selected' : '' }}>Cashier</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
                <select name="status" class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white rounded-lg transition-opacity hover:opacity-90"
                    style="background-color:#363E48">
                Filter
            </button>
            @if(request()->hasAny(['search', 'role', 'status']))
            <a href="{{ route('owner.users.index') }}"
               class="px-4 py-2 text-sm text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                Clear
            </a>
            @endif
        </form>
    </div>

    {{-- Users Table --}}
    <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">User ID</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Full Name</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Username</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Role</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 font-medium text-slate-800">{{ $user->id }}</td>
                        <td class="px-6 py-4 text-slate-700">{{ $user->name }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $user->username }}</td>
                        <td class="px-6 py-4">
                            @if(in_array($user->role, ['owner', 'Owner', 'manager', 'Manager', 'Owner / Manager']))
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Owner / Manager</span>
                            @elseif(in_array($user->role, ['cashier', 'Cashier', 'store_attendant', 'Cashier / Store Attendant']))
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">Cashier</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $user->role }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($user->status === 'Active' || $user->status === 'active')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('owner.users.edit', $user->id) }}"
                                   class="text-sm font-medium hover:underline"
                                   style="color:#363E48">Edit</a>
                                <form method="POST" action="{{ route('owner.users.toggleStatus', $user->id) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="text-sm font-medium text-slate-500 hover:text-slate-700 hover:underline transition-colors"
                                            onclick="return confirm('Are you sure you want to {{ ($user->status === 'Active' || $user->status === 'active') ? 'deactivate' : 'activate' }} this user?')">
                                        {{ ($user->status === 'Active' || $user->status === 'active') ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            <p class="text-sm">No users found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer --}}
        <div class="px-6 py-3 bg-slate-50 border-t text-xs text-slate-500">
            {{ $activeCount ?? 3 }} active &middot; {{ $inactiveCount ?? 1 }} inactive &middot; {{ $totalCount ?? 4 }} total
        </div>
    </div>

</div>
@endsection

@push('modals')
<div id="addUserModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-slate-800">Add User</h3>
            <button onclick="document.getElementById('addUserModal').classList.add('hidden')"
                    class="text-slate-400 hover:text-slate-600 text-lg leading-none">&#x2715;</button>
        </div>
        <form method="POST" action="{{ route('owner.users.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Full Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" required
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"
                       placeholder="e.g. Maria Santos">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Username <span class="text-red-500">*</span>
                </label>
                <input type="text" name="username" required
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"
                       placeholder="e.g. msantos">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Role</label>
                <select name="role"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                    <option value="owner">Owner / Manager</option>
                    <option value="cashier">Cashier / Store Attendant</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                <select name="status"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
            <hr class="border-slate-200">
            <p class="text-xs text-slate-500">Set an initial password for this user.</p>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Password <span class="text-red-500">*</span>
                </label>
                <input type="password" name="password" required
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Confirm Password</label>
                <input type="password" name="password_confirmation"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
            </div>
            <div class="flex gap-2 justify-end pt-2">
                <button type="button" onclick="document.getElementById('addUserModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 text-sm text-white rounded-lg transition-opacity hover:opacity-90"
                        style="background-color:#363E48">
                    Create User
                </button>
            </div>
        </form>
    </div>
</div>
@endpush

@push('scripts')
<script>
    document.getElementById('addUserBtn').addEventListener('click', function() {
        document.getElementById('addUserModal').classList.remove('hidden');
    });

    document.getElementById('addUserModal').addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.getElementById('addUserModal').classList.add('hidden');
        }
    });
</script>
@endpush
