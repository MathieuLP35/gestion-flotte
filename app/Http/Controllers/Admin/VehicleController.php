<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleKey;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class VehicleController extends Controller
{
    use AuthorizesRequests;
    // Redirection vers la page disponibilités (remplace l'ancienne liste)
    public function index()
    {
        $this->authorize('vehicles.view');
        return redirect()->route('admin.vehicles.availability');
    }

    // Formulaire de création
    public function create()
    {
        $this->authorize('vehicles.create');
        return Inertia::render('Admin/Vehicles/Create');
    }

    public function show(Vehicle $vehicle)
    {
        $this->authorize('vehicles.view');
        if (!Auth::user()->can('agences.view_all') && $vehicle->agence_id !== Auth::user()->agence_id) {
            abort(403);
        }

        return Inertia::render('Admin/Vehicles/Show', [
            'vehicle' => $vehicle
        ]);
    }

    /**
     * Affiche le calendrier de disponibilités d'un véhicule
     */
    public function calendar(Vehicle $vehicle)
    {
        $this->authorize('vehicles.view');
        if (!Auth::user()->can('agences.view_all') && $vehicle->agence_id !== Auth::user()->agence_id) {
            abort(403);
        }

        // Charger les réservations du véhicule avec les informations nécessaires
        $reservations = $vehicle->reservations()
            ->with(['driver', 'passengers'])
            ->orderBy('date_debut', 'asc')
            ->get();

        return Inertia::render('Admin/Vehicles/Calendar', [
            'vehicle' => $vehicle,
            'reservations' => $reservations
        ]);
    }

    /**
     * Affiche la page de disponibilités avec tous les véhicules
     */
    public function availability(Request $request)
    {
        $this->authorize('vehicles.view');
        $query = Vehicle::query()->orderBy('modele', 'asc');
        if (!Auth::user()->can('agences.view_all')) {
            $query->where('agence_id', Auth::user()->agence_id);
        }
        $vehicles = $query->get();

        $selectedVehicleId = $request->get('vehicle_id');
        $selectedVehicle = null;
        $reservations = collect();

        // Si aucun véhicule n'est sélectionné, prendre le premier par défaut
        if (!$selectedVehicleId && $vehicles->isNotEmpty()) {
            $selectedVehicleId = $vehicles->first()->id;
        }

        if ($selectedVehicleId) {
            $selectedVehicle = $vehicles->firstWhere('id', $selectedVehicleId);
            if ($selectedVehicle) {
                $reservations = $selectedVehicle->reservations()
                    ->with(['driver', 'passengers'])
                    ->orderBy('date_debut', 'asc')
                    ->get();
            }
        }

        return Inertia::render('Admin/Vehicles/Availability', [
            'vehicles' => $vehicles,
            'selectedVehicle' => $selectedVehicle,
            'reservations' => $reservations,
        ]);
    }

    // Enregistre un nouveau véhicule
    public function store(Request $request)
    {
        $this->authorize('vehicles.create');
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

        return redirect()->route('admin.vehicles.availability')->with('success', 'Véhicule et clés créés avec succès');
    }

    // Formulaire d’édition d’un véhicule
    public function edit(Vehicle $vehicle)
    {
        $this->authorize('vehicles.edit');
        if (!Auth::user()->can('agences.view_all') && $vehicle->agence_id !== Auth::user()->agence_id) {
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
        $this->authorize('vehicles.edit');
        if (!Auth::user()->can('agences.view_all') && $vehicle->agence_id !== Auth::user()->agence_id) {
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

        return redirect()->route('admin.vehicles.availability')->with('success', 'Véhicule mis à jour');
    }

    // Supprime un véhicule
    public function destroy(Vehicle $vehicle)
    {
        $this->authorize('vehicles.delete');
        if (!Auth::user()->can('agences.view_all') && $vehicle->agence_id !== Auth::user()->agence_id) {
            abort(403);
        }

        $vehicle->delete();

        return redirect()->route('admin.vehicles.availability')->with('success', 'Véhicule supprimé');
    }
}
