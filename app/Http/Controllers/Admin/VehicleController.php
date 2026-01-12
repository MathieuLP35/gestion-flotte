<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class VehicleController extends Controller
{
    // Liste des véhicules de l'agence de l'utilisateur connecté
    public function index()
    {
        $vehicles = Vehicle::where('agence_id', Auth::user()->agence_id)->get();

        return Inertia::render('Admin/Vehicles/Index', [
            'vehicles' => $vehicles
        ]);
    }

    // Formulaire de création
    public function create()
    {
        return Inertia::render('Admin/Vehicles/Create');
    }

    public function show(Vehicle $vehicle)
    {

        return Inertia::render('Admin/Vehicles/Show', [
            'vehicle' => $vehicle
        ]);
    }

    // Enregistre un nouveau véhicule
    public function store(Request $request)
    {
        // 1. Validation (on ajoute nbr_cles)
        $validated = $request->validate([
            'modele' => 'required|string|max:255',
            'immatriculation' => 'required|string|unique:vehicles',
            'km_initial' => 'required|integer|min:0',
            'emplacement' => 'required|string|max:255',
            'nbr_places' => 'required|integer|min:1',
            'nbr_cles' => 'required|integer|min:1|max:5',
            'energie' => 'required|in:essence,diesel,hybride,electrique',
        ]);

        $validated['agence_id'] = Auth::user()->agence_id;
        $validated['en_maintenance'] = false;

        // 2. On stocke le véhicule dans une variable pour récupérer son ID
        $vehicle = Vehicle::create($validated);

        // 3. Boucle pour créer les clés correspondantes
        for ($i = 1; $i <= $request->nbr_cles; $i++) {
            VehicleKey::create([
                'vehicle_id' => $vehicle->id,
                'emplacement_clef' => "{$validated['immatriculation']} (Clé {$i})", // Valeur par défaut
            ]);
        }

        return redirect()->route('admin.vehicles.index')->with('success', 'Véhicule et clés créés avec succès');
    }

    // Formulaire d’édition d’un véhicule
    public function edit(Vehicle $vehicle)
    {
        // Vérifier que le véhicule appartient à l'agence de l'utilisateur, sinon 403
        if ($vehicle->agence_id !== Auth::user()->agence_id) {
            abort(403);
        }

        $vehicle->load('maintenances');
        $vehicle->load('keys');

        return Inertia::render('Admin/Vehicles/Edit', [
            'vehicle' => $vehicle,
        ]);
    }

    // Met à jour un véhicule
    public function update(Request $request, Vehicle $vehicle)
    {
        if ($vehicle->agence_id !== Auth::user()->agence_id) {
            abort(403);
        }

        $validated = $request->validate([
            'modele' => 'required|string|max:255',
            'immatriculation' => 'required|string|unique:vehicles,immatriculation,' . $vehicle->id,
            'km_initial' => 'required|integer|min:0',
            'emplacement' => 'required|string|max:255',
            'nbr_places' => 'required|integer|min:1',
            'energie' => 'required|in:essence,diesel,hybride,electrique',
            'en_maintenance' => 'sometimes|boolean',
        ]);

        $vehicle->update($validated);

        return redirect()->route('admin.vehicles.index')->with('success', 'Véhicule mis à jour');
    }

    // Supprime un véhicule
    public function destroy(Vehicle $vehicle)
    {
        if ($vehicle->agence_id !== Auth::user()->agence_id) {
            abort(403);
        }

        $vehicle->delete();

        return redirect()->route('admin.vehicles.index')->with('success', 'Véhicule supprimé');
    }
}
