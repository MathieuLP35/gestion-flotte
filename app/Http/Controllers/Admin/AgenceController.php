<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AgenceController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $this->authorize('agences.view');

        $agences = Agence::withCount(['vehicles', 'users'])
            ->orderBy('nom')
            ->get();

        return Inertia::render('Admin/Agences/Index', [
            'agences' => $agences,
        ]);
    }

    public function create()
    {
        $this->authorize('agences.view');
        return Inertia::render('Admin/Agences/Create');
    }

    public function store(Request $request)
    {
        $this->authorize('agences.view');
        $request->validate([
            'nom' => 'required|string|max:255',
            'adresse' => 'nullable|string|max:500',
        ]);

        Agence::create($request->only(['nom', 'adresse']));

        return redirect()->route('admin.agences.index')->with('success', 'Agence créée.');
    }

    public function edit(Agence $agence)
    {
        $this->authorize('agences.view');
        return Inertia::render('Admin/Agences/Edit', [
            'agence' => $agence,
        ]);
    }

    public function update(Request $request, Agence $agence)
    {
        $this->authorize('agences.view');
        $request->validate([
            'nom' => 'required|string|max:255',
            'adresse' => 'nullable|string|max:500',
        ]);

        $agence->update($request->only(['nom', 'adresse']));

        return redirect()->route('admin.agences.index')->with('success', 'Agence mise à jour.');
    }

    public function destroy(Agence $agence)
    {
        $this->authorize('agences.view');
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
