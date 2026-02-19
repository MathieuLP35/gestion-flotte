<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use App\Models\Agence;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('users.view');

        $query = User::with('agence', 'roles');
        if (! Auth::user()->can('agences.view_all')) {
            $query->where('agence_id', Auth::user()->agence_id);
        }

        $users = $query->get();

        return inertia('Admin/Users/Index', [
            'users' => $users->map(fn (User $u) => (new UserResource($u))->resolve())->values()->all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $this->authorize('users.view');
        if (! Auth::user()->can('agences.view_all') && $user->agence_id !== Auth::user()->agence_id) {
            abort(403);
        }
        $user->load(['agence', 'roles']);

        return inertia('Admin/Users/Show', [
            'user' => (new UserResource($user))->resolve(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $this->authorize('users.edit');
        if (! Auth::user()->can('agences.view_all') && $user->agence_id !== Auth::user()->agence_id) {
            abort(403);
        }

        $user->load(['roles']);

        return Inertia::render('Admin/Users/Edit', [
            'user' => (new UserResource($user))->resolve(),
            'agences' => Agence::get(['id', 'nom']),
            'roles' => Role::orderBy('name')->get(['id', 'name'])->map(fn ($r) => (new RoleResource($r))->resolve())->values()->all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('users.edit');
        if (! Auth::user()->can('agences.view_all') && $user->agence_id !== Auth::user()->agence_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'agence_id' => 'nullable|exists:agences,id',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'agence_id' => $validated['agence_id'],
        ]);

        if ($request->has('role_id')) {
            $user->syncRoles($request->role_id ? [Role::findOrFail($request->role_id)->name] : []);
        }

        return redirect()->route('admin.users.index')->with('success', 'Utilisateur mis à jour.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('users.delete');
        if (! Auth::user()->can('agences.view_all') && $user->agence_id !== Auth::user()->agence_id) {
            abort(403);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Utilisateur supprimé.');
    }
}
