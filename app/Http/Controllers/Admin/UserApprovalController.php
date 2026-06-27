<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class UserApprovalController extends Controller
{
    /** List all pending registrations — super-admin only */
    public function index()
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);

        $pending = User::with('role')
            ->where('status', 'pending')
            ->latest()
            ->get();

        $roles = Role::whereNotIn('slug', ['guest'])->orderBy('name')->get();

        return view('admin.approvals.index', compact('pending', 'roles'));
    }

    /** Approve a pending user and optionally set their role */
    public function approve(Request $request, User $user)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);

        $data = $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $user->update([
            'status'      => 'active',
            'role_id'     => $data['role_id'],
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', "Access granted to {$user->name}.");
    }

    /** Reject (delete) a pending registration */
    public function reject(User $user)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);
        abort_unless($user->status === 'pending', 422);

        $name = $user->name;
        $user->delete();

        return back()->with('success', "Registration for {$name} has been rejected and removed.");
    }

    /** Suspend an active admin */
    public function suspend(User $user)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);
        abort_unless(! $user->isSuperAdmin(), 403, 'Cannot suspend a super admin.');

        $user->update(['status' => 'suspended']);

        return back()->with('success', "{$user->name}'s account has been suspended.");
    }

    /** Reactivate a suspended admin */
    public function reactivate(User $user)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);

        $user->update(['status' => 'active']);

        return back()->with('success', "{$user->name}'s account has been reactivated.");
    }
}
