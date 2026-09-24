<?php

namespace App\Http\Controllers\Owner;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(fn ($q) => $q
                    ->where('full_name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%"));
            })
            ->when($request->filled('role'), fn ($query) => $query->where(
                'role',
                $request->string('role') === 'owner' ? UserRole::OwnerManager : UserRole::CashierAttendant
            ))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderBy('full_name')
            ->get()
            ->map(fn (User $user) => (object) [
                'id' => $user->user_id,
                'name' => $user->full_name,
                'username' => $user->username,
                'role' => $user->role === UserRole::OwnerManager ? 'Owner / Manager' : 'Cashier / Store Attendant',
                'status' => $user->status === UserStatus::Active ? 'Active' : 'Inactive',
            ]);

        $activeCount = $users->where('status', 'Active')->count();
        $inactiveCount = $users->where('status', 'Inactive')->count();
        $totalCount = $users->count();

        return view('owner.users.index', compact('users', 'activeCount', 'inactiveCount', 'totalCount'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:user,username'],
            'role' => ['required', 'in:owner,cashier'],
            'status' => ['required', 'in:Active,Inactive'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        User::create([
            'full_name' => $validated['name'],
            'username' => $validated['username'],
            'password_hash' => Hash::make($validated['password']),
            'role' => $validated['role'] === 'owner' ? UserRole::OwnerManager : UserRole::CashierAttendant,
            'status' => $validated['status'] === 'Active' ? UserStatus::Active : UserStatus::Inactive,
        ]);

        return redirect()->route('owner.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user): RedirectResponse
    {
        return back()->with('status', 'Editing users is not implemented yet.');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        $user->update([
            'status' => $user->status === UserStatus::Active ? UserStatus::Inactive : UserStatus::Active,
        ]);

        return back()->with('success', 'User status updated.');
    }
}
