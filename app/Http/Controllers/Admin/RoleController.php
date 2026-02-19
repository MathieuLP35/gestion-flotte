<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    use AuthorizesRequests;

    public function index(): Response
    {
        $this->authorize('roles.view');

        $roles = Role::with('permissions')->get();

        return inertia('Admin/Roles/Index', [
            'roles' => $roles->map(fn (Role $r) => (new RoleResource($r))->resolve())->values()->all(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('roles.create');

        return inertia('Admin/Roles/Create', [
            'permissions' => Permission::all()->pluck('name'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('roles.create');

        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array',
        ]);

        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        return redirect()->route('admin.roles.index')->with('success', 'Rôle créé.');
    }

    public function edit(Role $role): Response
    {
        $this->authorize('roles.edit');

        return inertia('Admin/Roles/Edit', [
            'role' => new RoleResource($role->load('permissions')),
            'permissions' => Permission::all()->pluck('name')->values()->all(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('roles.edit');

        $request->validate([
            'name' => 'required|string|unique:roles,name,'.$role->id,
            'permissions' => 'array',
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        return redirect()->route('admin.roles.index')->with('success', 'Rôle mis à jour.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('roles.delete');

        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Rôle supprimé.');
    }
}
