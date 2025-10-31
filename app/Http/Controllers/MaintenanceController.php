<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MaintenanceController extends Controller
{
    public function index()
    {
        $maintenances = Maintenance::with('vehicle')
            ->whereHas('vehicle', function ($q) {
                $q->where('agence_id', Auth::user()->agence_id);
            })->get();

        return Inertia::render('Maintenances/Index', ['maintenances' => $maintenances]);
    }

    public function create()
    {
        $vehicles = Vehicle::where('agence_id', Auth::user()->agence_id)->get();

        return Inertia::render('Maintenances/Create', ['vehicles' => $vehicles]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'km_alert_threshold' => 'required|integer|min:0',
            'date_dernier_entretien' => 'nullable|date',
        ]);

        $vehicle = Vehicle::findOrFail($request->vehicle_id);

        if ($vehicle->agence_id !== Auth::user()->agence_id) {
            abort(403);
        }

        Maintenance::create($request->all());

        return redirect()->route('maintenances.index')->with('success', 'Seuil de maintenance créé');
    }

    public function edit(Maintenance $maintenance)
    {
        if ($maintenance->vehicle->agence_id !== Auth::user()->agence_id) {
            abort(403);
        }

        $vehicles = Vehicle::where('agence_id', Auth::user()->agence_id)->get();

        return Inertia::render('Maintenances/Edit', ['maintenance' => $maintenance, 'vehicles' => $vehicles]);
    }

    public function update(Request $request, Maintenance $maintenance)
    {
        if ($maintenance->vehicle->agence_id !== Auth::user()->agence_id) {
            abort(403);
        }

        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'km_alert_threshold' => 'required|integer|min:0',
            'date_dernier_entretien' => 'nullable|date',
        ]);

        $maintenance->update($request->all());

        return redirect()->route('maintenances.index')->with('success', 'Seuil de maintenance mis à jour');
    }

    public function destroy(Maintenance $maintenance)
    {
        if ($maintenance->vehicle->agence_id !== Auth::user()->agence_id) {
            abort(403);
        }

        $maintenance->delete();

        return redirect()->route('maintenances.index')->with('success', 'Seuil de maintenance supprimé');
    }
}
