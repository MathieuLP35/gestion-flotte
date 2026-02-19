<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleKey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class KeyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): void
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): void
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'emplacement_clef' => 'required|string|max:255',
        ]);

        $vehicle = Vehicle::find($validated['vehicle_id']);

        if (! $vehicle) {
            return redirect()->back()->with('error', 'Véhicule non trouvé.');
        }

        if (! Auth::user()?->can('agences.view_all') && $vehicle->agence_id !== Auth::user()?->agence_id) {
            abort(403);
        }

        VehicleKey::create([
            'vehicle_id' => $validated['vehicle_id'],
            'emplacement_clef' => $validated['emplacement_clef'],
        ]);

        return redirect()->route('admin.vehicles.edit', $vehicle->id)
            ->with('success', 'Clé ajoutée avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(VehicleKey $vehicleKey): void
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param VehicleKey $key
     * @return Response
     */
    public function edit(VehicleKey $key): Response
    {
        $key->load('vehicle');

        if (! Auth::user()?->can('agences.view_all') && $key->vehicle->agence_id !== Auth::user()?->agence_id) {
            abort(403);
        }

        return Inertia::render('Admin/Keys/Edit', [
            'vehicleKey' => $key,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param VehicleKey $key
     * @return RedirectResponse
     */
    public function update(Request $request, VehicleKey $key): RedirectResponse
    {
        if (! Auth::user()?->can('agences.view_all') && $key->vehicle->agence_id !== Auth::user()?->agence_id) {
            abort(403);
        }

        $validated = $request->validate([
            'emplacement_clef' => 'required|string|max:255',
        ]);

        $key->update([
            'emplacement_clef' => $validated['emplacement_clef'],
        ]);

        return redirect()->route('admin.vehicles.edit', $key->vehicle_id)
            ->with('success', 'Clé mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param VehicleKey $key
     * @return RedirectResponse
     */
    public function destroy(VehicleKey $key): RedirectResponse
    {
        $key->load('vehicle');

        if (! Auth::user()?->can('agences.view_all') && $key->vehicle->agence_id !== Auth::user()?->agence_id) {
            abort(403);
        }

        $vehicleId = $key->vehicle_id;
        $key->delete();

        return redirect()->route('admin.vehicles.edit', $vehicleId)
            ->with('success', 'Clé supprimée avec succès.');
    }
}
