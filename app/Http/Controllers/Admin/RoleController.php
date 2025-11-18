<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{

    use AuthorizesRequests;

    /**
     * Affiche la liste des rôles.
     */
    public function index()
    {
        $this->authorize('roles.view');

        return inertia('Admin/Roles/Index', [
            'roles' => Role::with('permissions')->get()
        ]);
    }

    /**
     * Affiche le formulaire de création de rôle.
     */
    public function create()
    {

        $this->authorize('roles.create');

        return inertia('Admin/Roles/Create', [
            'permissions' => Permission::all()->pluck('name')
        ]);
    }

    /**
     * Enregistre le nouveau rôle.
     */
    public function store(Request $request)
    {

        $this->authorize('roles.create');

        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array'
        ]);

        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        return redirect()->route('admin.roles.index')->with('success', 'Rôle créé.');
    }

    /**
     * Affiche le formulaire d'édition.
     */
    public function edit(Role $role)
    {

        $this->authorize('roles.edit');

        return inertia('Admin/Roles/Edit', [
            'role' => $role,
            'permissions' => Permission::all()->pluck('name'),
            'rolePermissions' => $role->permissions->pluck('name'),
        ]);
    }

    /**
     * Met à jour le rôle.
     */
    public function update(Request $request, Role $role)
    {

        $this->authorize('roles.edit');

        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'array'
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        return redirect()->route('admin.roles.index')->with('success', 'Rôle mis à jour.');
    }

    /**
     * Supprime le rôle.
     */
    public function destroy(Role $role)
    {

        $this->authorize('roles.delete');

        $role->delete();
        return redirect()->route('admin.roles.index')->with('success', 'Rôle supprimé.');
    }
}