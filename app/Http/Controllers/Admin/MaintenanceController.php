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
    public function index(): Response
    {
        $query = Vehicle::query();

        if (! Auth::user()->can('agences.view_all')) {
            $query->where('agence_id', Auth::user()->agence_id);
        }

        $vehicles = $query->with('agence:id,nom')->get()->map(function ($v) {
            return [
                'id' => $v->id,
                'modele' => $v->modele,
                'immatriculation' => $v->immatriculation,
                'agence' => $v->agence?->nom,
                'kilometrage' => $v->kilometrage,
                'status' => $v->maintenance_status,
                'km_until_next' => $v->km_until_next_service,
                'date_next' => $v->next_service_date,
            ];
        });

        // Tri : overdue en premier, puis warning, puis ok
        $vehicles = $vehicles->sortBy(function ($v) {
            if ($v['status'] === 'overdue') {
                return 1;
            }
            if ($v['status'] === 'warning') {
                return 2;
            }

            return 3;
        })->values();

        return Inertia::render('Admin/Maintenances/Index', [
            'vehicles' => $vehicles,
        ]);
    }

    public function show(int $id): Response
    {
        $vehicle = Vehicle::with(['maintenances' => function ($q) {
            $q->orderBy('date', 'desc');
        }])->findOrFail($id);

        if (! Auth::user()->can('agences.view_all') && $vehicle->agence_id !== Auth::user()->agence_id) {
            abort(403);
        }

        return Inertia::render('Admin/Maintenances/Show', [
            'vehicle' => [
                'id' => $vehicle->id,
                'modele' => $vehicle->modele,
                'immatriculation' => $vehicle->immatriculation,
                'kilometrage' => $vehicle->kilometrage,
                'service_interval_km' => $vehicle->service_interval_km,
                'service_interval_months' => $vehicle->service_interval_months,
                'status' => $vehicle->maintenance_status,
                'km_until_next' => $vehicle->km_until_next_service,
                'date_next' => $vehicle->next_service_date,
            ],
            'maintenances' => $vehicle->maintenances,
        ]);
    }

    public function create(Request $request): Response
    {
        $vehicles = Auth::user()?->can('agences.view_all')
            ? Vehicle::orderBy('modele')->get()
            : Vehicle::where('agence_id', Auth::user()?->agence_id)->get();

        return Inertia::render('Admin/Maintenances/Create', [
            'vehicles' => $vehicles,
            'default_vehicle_id' => $request->query('vehicle_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'date' => 'required|date',
            'kilometrage' => 'required|integer|min:0',
            'type' => 'required|string',
            'cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $vehicle = Vehicle::findOrFail($request->vehicle_id);

        if (! Auth::user()?->can('agences.view_all') && $vehicle->agence_id !== Auth::user()?->agence_id) {
            abort(403);
        }

        $data = $request->all();

        Maintenance::create($data);

        if ($vehicle->kilometrage < $request->kilometrage) {
            $vehicle->update(['kilometrage' => $request->kilometrage]);
        }

        return redirect()->route('admin.maintenances.show', $vehicle->id)->with('success', 'Intervention ajoutée avec succès.');
    }

    public function edit(Maintenance $maintenance): Response
    {
        if (! Auth::user()->can('agences.view_all') && $maintenance->vehicle->agence_id !== Auth::user()->agence_id) {
            abort(403);
        }

        $vehicles = Auth::user()?->can('agences.view_all')
            ? Vehicle::orderBy('modele')->get()
            : Vehicle::where('agence_id', Auth::user()?->agence_id)->get();

        return Inertia::render('Admin/Maintenances/Edit', ['maintenance' => $maintenance, 'vehicles' => $vehicles]);
    }

    public function update(Request $request, Maintenance $maintenance): RedirectResponse
    {
        if (! Auth::user()?->can('agences.view_all') && $maintenance->vehicle->agence_id !== Auth::user()?->agence_id) {
            abort(403);
        }

        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'date' => 'required|date',
            'kilometrage' => 'required|integer|min:0',
            'type' => 'required|string',
            'cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $maintenance->update($request->all());

        return redirect()->route('admin.maintenances.show', $maintenance->vehicle_id)->with('success', 'Intervention mise à jour.');
    }

    public function destroy(Maintenance $maintenance): RedirectResponse
    {
        $vehicleId = $maintenance->vehicle_id;

        if (! Auth::user()?->can('agences.view_all') && $maintenance->vehicle->agence_id !== Auth::user()?->agence_id) {
            abort(403);
        }

        $maintenance->delete();

        return redirect()->route('admin.maintenances.show', $vehicleId)->with('success', 'Intervention supprimée.');
    }
}
