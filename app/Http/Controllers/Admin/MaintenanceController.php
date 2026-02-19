<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class MaintenanceController extends Controller
{
    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'km_alert_threshold' => 'required|integer|min:0',
            'date_dernier_entretien' => 'nullable|date',
        ]);

        $vehicle = Vehicle::findOrFail($request->vehicle_id);

        if (! Auth::user()?->can('agences.view_all') && $vehicle->agence_id !== Auth::user()?->agence_id) {
            abort(403);
        }

        Maintenance::create($request->all());

        return back()->with('success', 'Seuil de maintenance ajouté');
    }

    /**
     * @param Maintenance $maintenance
     * @return Response
     */
    public function edit(Maintenance $maintenance): Response
    {
        if (!Auth::user()->can('agences.view_all') && $maintenance->vehicle->agence_id !== Auth::user()->agence_id) {
            abort(403);
        }

        $vehicles = Auth::user()?->can('agences.view_all')
            ? Vehicle::orderBy('modele')->get()
            : Vehicle::where('agence_id', Auth::user()?->agence_id)->get();

        return Inertia::render('Admin/Maintenances/Edit', ['maintenance' => $maintenance, 'vehicles' => $vehicles]);
    }

    /**
     * @param Request $request
     * @param Maintenance $maintenance
     * @return RedirectResponse
     */
    public function update(Request $request, Maintenance $maintenance): RedirectResponse
    {
        if (! Auth::user()?->can('agences.view_all') && $maintenance->vehicle->agence_id !== Auth::user()?->agence_id) {
            abort(403);
        }

        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'km_alert_threshold' => 'required|integer|min:0',
            'date_dernier_entretien' => 'nullable|date',
        ]);

        $maintenance->update($request->all());

        return redirect()->route('admin.vehicles.edit', $maintenance->vehicle_id)->with('success', 'Seuil de maintenance mis à jour');
    }

    /**
     * @param Maintenance $maintenance
     * @return RedirectResponse
     */
    public function destroy(Maintenance $maintenance): RedirectResponse
    {
        if (! Auth::user()?->can('agences.view_all') && $maintenance->vehicle->agence_id !== Auth::user()?->agence_id) {
            abort(403);
        }

        $maintenance->delete();

        return back()->with('success', 'Seuil de maintenance supprimé');
    }
}
