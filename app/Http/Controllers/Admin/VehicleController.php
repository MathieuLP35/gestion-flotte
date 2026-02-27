<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleKey;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class VehicleController extends Controller
{
    use AuthorizesRequests;

    public function index(): RedirectResponse
    {
        $this->authorize('vehicles.view');

        return redirect()->route('admin.vehicles.availability');
    }

    public function create(): Response
    {
        $this->authorize('vehicles.create');

        return Inertia::render('Admin/Vehicles/Create');
    }

    public function show(Vehicle $vehicle): Response
    {
        $this->authorize('vehicles.view');

        if (! Auth::user()?->can('agences.view_all') && $vehicle->agence_id !== Auth::user()?->agence_id) {
            abort(403);
        }

        return Inertia::render('Admin/Vehicles/Show', [
            'vehicle' => $vehicle,
        ]);
    }

    public function calendar(Vehicle $vehicle): Response
    {
        $this->authorize('vehicles.view');

        if (! Auth::user()?->can('agences.view_all') && $vehicle->agence_id !== Auth::user()?->agence_id) {
            abort(403);
        }

        $reservations = $vehicle->reservations()
            ->with(['driver', 'passengers'])
            ->orderBy('date_debut', 'asc')
            ->get();

        return Inertia::render('Admin/Vehicles/Calendar', [
            'vehicle' => $vehicle,
            'reservations' => $reservations,
        ]);
    }

    public function availability(Request $request): Response
    {
        $this->authorize('vehicles.view');

        $query = Vehicle::query()
            ->withCount(['reservations as pending_reservations_count' => function ($query) {
                $query->where('statut', 'en attente');
            }])
            ->orderBy('modele', 'asc');

        if (! Auth::user()?->can('agences.view_all')) {
            $query->where('agence_id', Auth::user()?->agence_id);
        }
        $vehicles = $query->get();

        $selectedVehicleId = $request->get('vehicle_id');
        $selectedVehicle = null;
        $reservations = collect();

        if (! $selectedVehicleId && $vehicles->isNotEmpty()) {
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

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('vehicles.create');

        $validated = $request->validate([
            'modele' => 'required|string|max:255',
            'immatriculation' => 'required|string|unique:vehicles',
            'km_initial' => 'required|integer|min:0',
            'emplacement' => 'required|string|max:255',
            'nbr_places' => 'required|integer|min:1',
            'nbr_cles' => 'required|integer|min:1|max:5',
            'energie' => 'required|in:essence,diesel,hybride,electrique',
        ]);

        $validated['agence_id'] = Auth::user()?->agence_id;
        $validated['en_maintenance'] = false;

        $vehicle = Vehicle::create($validated);

        for ($i = 1; $i <= (int) $request->nbr_cles; $i++) {
            VehicleKey::create([
                'vehicle_id' => $vehicle->id,
                'emplacement_clef' => "{$validated['immatriculation']} (Clé {$i})",
            ]);
        }

        return redirect()->route('admin.vehicles.availability')->with('success', 'Véhicule créé');
    }

    public function edit(Vehicle $vehicle): Response
    {
        $this->authorize('vehicles.edit');

        if (! Auth::user()?->can('agences.view_all') && $vehicle->agence_id !== Auth::user()?->agence_id) {
            abort(403);
        }

        $vehicle->load(['maintenances', 'keys']);

        return Inertia::render('Admin/Vehicles/Edit', [
            'vehicle' => $vehicle,
        ]);
    }

    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $this->authorize('vehicles.edit');

        if (! Auth::user()?->can('agences.view_all') && $vehicle->agence_id !== Auth::user()?->agence_id) {
            abort(403);
        }

        $validated = $request->validate([
            'modele' => 'required|string|max:255',
            'immatriculation' => 'required|string|unique:vehicles,immatriculation,'.$vehicle->id,
            'km_initial' => 'required|integer|min:0',
            'emplacement' => 'required|string|max:255',
            'nbr_places' => 'required|integer|min:1',
            'energie' => 'required|in:essence,diesel,hybride,electrique',
            'en_maintenance' => 'sometimes|boolean',
        ]);

        $vehicle->update($validated);

        return redirect()->route('admin.vehicles.availability')->with('success', 'Mis à jour');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $this->authorize('vehicles.delete');

        if (! Auth::user()?->can('agences.view_all') && $vehicle->agence_id !== Auth::user()?->agence_id) {
            abort(403);
        }

        $vehicle->delete();

        return redirect()->route('admin.vehicles.availability')->with('success', 'Supprimé');
    }
}
