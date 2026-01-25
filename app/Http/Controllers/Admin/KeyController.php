<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class KeyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'emplacement_clef' => 'required|string|max:255',
        ]);

        $vehicle = Vehicle::find($validated['vehicle_id']);

        if (!$vehicle) {
            return redirect()->back()->with('error', 'Véhicule non trouvé.');
        }

        if (!Auth::user()->can('agences.view_all') && $vehicle->agence_id !== Auth::user()->agence_id) {
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
    public function show(VehicleKey $vehicleKey)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VehicleKey $key)
    {
        $key->load('vehicle');

        if (!Auth::user()->can('agences.view_all') && $key->vehicle->agence_id !== Auth::user()->agence_id) {
            abort(403);
        }

        return Inertia::render('Admin/Keys/Edit', [
            'vehicleKey' => $key,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VehicleKey $key)
    {
        if (!Auth::user()->can('agences.view_all') && $key->vehicle->agence_id !== Auth::user()->agence_id) {
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
     */
    public function destroy($vehicleKey)
    {
        $key = VehicleKey::find($vehicleKey);
        if ($key) {
            $key->load('vehicle');
            if (!Auth::user()->can('agences.view_all') && $key->vehicle->agence_id !== Auth::user()->agence_id) {
                abort(403);
            }
            $vehicleId = $key->vehicle_id;
            $key->delete();
            return redirect()->route('admin.vehicles.edit', $vehicleId)
                             ->with('success', 'Clé supprimée avec succès.');
        }
        return redirect()->back()->with('error', 'Clé non trouvée.');
    }
}
