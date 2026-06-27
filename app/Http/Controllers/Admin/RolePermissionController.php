<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    /** Show roles with their permissions */
    public function index()
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);

        $roles       = Role::with('permissions')->whereNotIn('slug', ['guest'])->orderBy('name')->get();
        $permissions = Permission::orderBy('slug')->get()->groupBy(function ($p) {
            // Group by resource prefix e.g. "hero-slides" from "hero-slides.view"
            return explode('.', $p->slug)[0];
        });

        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    /** Update permissions for a role */
    public function update(Request $request, Role $role)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);

        // Super-admin role always keeps all permissions
        if ($role->isSuperAdmin()) {
            return back()->with('error', 'Super Admin permissions cannot be modified.');
        }

        $permissionIds = $request->input('permissions', []);

        $role->permissions()->sync($permissionIds);

        return back()->with('success', "Permissions for \"{$role->name}\" updated successfully.");
    }

    /** Create a new role */
    public function store(Request $request)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', 'unique:roles,slug'],
        ]);

        Role::create([
            'name'      => $data['name'],
            'slug'      => $data['slug'],
            'is_system' => false,
        ]);

        return back()->with('success', "Role \"{$data['name']}\" created.");
    }

    /** Delete a non-system role */
    public function destroy(Role $role)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);
        abort_unless(! $role->is_system, 403, 'System roles cannot be deleted.');

        $role->delete();

        return back()->with('success', "Role \"{$role->name}\" deleted.");
    }
}
