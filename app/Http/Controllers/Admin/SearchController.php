<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\Maintenance;
use App\Models\Agence;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->get('q');

        if (empty($q) || strlen($q) < 2) {
            return response()->json([
                'vehicles' => [],
                'users' => [],
                'maintenances' => [],
                'agencies' => [],
            ]);
        }

        // Recherche Véhicules
        $vehicles = Vehicle::where('modele', 'like', "%{$q}%")
            ->orWhere('immatriculation', 'like', "%{$q}%")
            ->take(5)
            ->get()
            ->map(function ($v) {
                return [
                    'id' => $v->id,
                    'title' => $v->modele,
                    'subtitle' => $v->immatriculation,
                    'url' => route('admin.vehicles.edit', $v->id),
                    'icon' => '🚗'
                ];
            });

        // Recherche Utilisateurs
        $users = User::where('name', 'like', "%{$q}%")
            ->orWhere('email', 'like', "%{$q}%")
            ->take(5)
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'title' => $u->name,
                    'subtitle' => $u->email,
                    'url' => route('admin.users.edit', $u->id),
                    'icon' => '👥'
                ];
            });

        // Recherche Maintenances
        $maintenances = Maintenance::with('vehicle')
            ->where('type', 'like', "%{$q}%")
            ->orWhereHas('vehicle', function($query) use ($q) {
                $query->where('immatriculation', 'like', "%{$q}%");
            })
            ->take(5)
            ->latest('date')
            ->get()
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'title' => ucfirst($m->type),
                    'subtitle' => ($m->vehicle ? $m->vehicle->immatriculation . ' - ' : '') . date('d/m/Y', strtotime($m->date)),
                    'url' => route('admin.maintenances.show', $m->vehicle_id),
                    'icon' => '🔧'
                ];
            });

        // Recherche Agences
        $agencies = Agence::where('name', 'like', "%{$q}%")
            ->take(5)
            ->get()
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'title' => $a->name,
                    'subtitle' => 'Agence géographique',
                    'url' => route('admin.agences.edit', $a->id),
                    'icon' => '📍'
                ];
            });

        return response()->json([
            'vehicles' => $vehicles,
            'users' => $users,
            'maintenances' => $maintenances,
            'agencies' => $agencies,
        ]);
    }
}
