<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleKey;
use Illuminate\Http\Request;

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
    public function edit(VehicleKey $vehicleKey)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VehicleKey $vehicleKey)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($vehicleKey)
    {
        $key = VehicleKey::find($vehicleKey);
        if ($key) {
            $vehicleId = $key->vehicle_id;
            $key->delete();
            return redirect()->route('admin.vehicles.edit', $vehicleId)
                             ->with('success', 'Clé supprimée avec succès.');
        }
        return redirect()->back()->with('error', 'Clé non trouvée.');
    }
}
