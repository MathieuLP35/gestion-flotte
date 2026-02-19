<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AgenceController extends Controller
{
    use AuthorizesRequests;

    public function index(): Response
    {
        $this->authorize('agences.view');

        $agences = Agence::withCount(['vehicles', 'users'])
            ->when(! Auth::user()?->can('agences.view_all'), fn ($q) => $q->where('id', Auth::user()?->agence_id))
            ->orderBy('nom')
            ->get();

        return Inertia::render('Admin/Agences/Index', [
            'agences' => $agences,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('agences.view');

        return Inertia::render('Admin/Agences/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('agences.view');
        $request->validate([
            'nom' => 'required|string|max:255',
            'adresse' => 'nullable|string|max:500',
        ]);

        Agence::create($request->only(['nom', 'adresse']));

        return redirect()->route('admin.agences.index')->with('success', 'Agence créée.');
    }

    public function edit(Agence $agence): Response
    {
        $this->authorize('agences.view');
        if (! Auth::user()?->can('agences.view_all') && $agence->id !== Auth::user()?->agence_id) {
            abort(403);
        }

        return Inertia::render('Admin/Agences/Edit', [
            'agence' => $agence,
        ]);
    }

    public function update(Request $request, Agence $agence): RedirectResponse
    {
        $this->authorize('agences.view');
        if (! Auth::user()?->can('agences.view_all') && $agence->id !== Auth::user()?->agence_id) {
            abort(403);
        }
        $request->validate([
            'nom' => 'required|string|max:255',
            'adresse' => 'nullable|string|max:500',
        ]);

        $agence->update($request->only(['nom', 'adresse']));

        return redirect()->route('admin.agences.index')->with('success', 'Agence mise à jour.');
    }

    public function destroy(Agence $agence): RedirectResponse
    {
        $this->authorize('agences.view');
        if (! Auth::user()?->can('agences.view_all') && $agence->id !== Auth::user()?->agence_id) {
            abort(403);
        }
        if ($agence->vehicles()->exists()) {
            return back()->with('error', 'Impossible de supprimer une agence qui possède des véhicules.');
        }
        if ($agence->users()->exists()) {
            return back()->with('error', 'Impossible de supprimer une agence qui possède des utilisateurs.');
        }

        $agence->delete();

        return redirect()->route('admin.agences.index')->with('success', 'Agence supprimée.');
    }
}
