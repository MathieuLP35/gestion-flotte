<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $hasGlobalView = $user?->can('agences.view_all');
        $agenceId = $hasGlobalView ? null : $user?->agence_id;

        $baseReservations = Reservation::query();
        $baseVehicles = Vehicle::query();
        $baseUsers = User::query();

        if ($hasGlobalView) {
            // Vision globale : aucun filtre agence
        } elseif ($agenceId !== null) {
            $baseReservations->whereHas('vehicle', fn ($q) => $q->where('agence_id', $agenceId));
            $baseVehicles->where('agence_id', $agenceId);
            $baseUsers->where('agence_id', $agenceId);
        } else {
            // Ni vision globale ni agence rattachée : aucune donnée
            $baseReservations->whereRaw('1=0');
            $baseVehicles->whereRaw('1=0');
            $baseUsers->whereRaw('1=0');
        }

        $debutMois = now()->startOfMonth();
        $finMois = now()->endOfMonth();
        $debutMoisPrec = now()->subMonth()->startOfMonth();
        $finMoisPrec = now()->subMonth()->endOfMonth();

        $reservationsCeMois = (clone $baseReservations)
            ->whereBetween('date_debut', [$debutMois, $finMois])
            ->count();
        $reservationsMoisPrec = (clone $baseReservations)
            ->whereBetween('date_debut', [$debutMoisPrec, $finMoisPrec])
            ->count();

        $byStatus = (clone $baseReservations)
            ->select('statut', DB::raw('count(*) as total'))
            ->groupBy('statut')
            ->pluck('total', 'statut')
            ->toArray();

        $recentReservations = (clone $baseReservations)
            ->with(['vehicle:id,modele,immatriculation', 'driver:id,name'])
            ->orderByDesc('date_debut')
            ->limit(8)
            ->get(['id', 'depart', 'destination', 'date_debut', 'statut', 'vehicle_id', 'user_id']);

        $agencesCount = $hasGlobalView ? Agence::count() : ($agenceId ? 1 : 0);

        // Graphique : réservations par jour (30 derniers jours)
        $dateExpr = DB::getDriverName() === 'sqlite' ? 'date(date_debut)' : 'DATE(date_debut)';
        $chart30j = (clone $baseReservations)
            ->where('date_debut', '>=', now()->subDays(30)->startOfDay())
            ->select(DB::raw("{$dateExpr} as d"), DB::raw('COUNT(*) as c'))
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('c', 'd')
            ->toArray();
        $labels30j = [];
        $data30j = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = now()->subDays($i)->format('Y-m-d');
            $labels30j[] = now()->subDays($i)->locale('fr')->isoFormat('D MMM');
            $data30j[] = $chart30j[$d] ?? 0;
        }

        // Graphique : réservations par mois (12 derniers mois)
        $dateFormat = match (DB::getDriverName()) {
            'sqlite' => "strftime('%Y-%m', date_debut)",
            'pgsql'  => "to_char(date_debut, 'YYYY-MM')",
            default  => "DATE_FORMAT(date_debut, '%Y-%m')", // MySQL / MariaDB
        };
        $chart12m = (clone $baseReservations)
            ->where('date_debut', '>=', now()->subMonths(11)->startOfMonth())
            ->select(DB::raw("{$dateFormat} as m"), DB::raw('COUNT(*) as c'))
            ->groupBy('m')
            ->orderBy('m')
            ->pluck('c', 'm')
            ->toArray();
        $labels12m = [];
        $data12m = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = now()->subMonths($i)->format('Y-m');
            $labels12m[] = now()->subMonths($i)->locale('fr')->isoFormat('MMM YYYY');
            $data12m[] = $chart12m[$m] ?? 0;
        }

        // Réservations avec coordonnées pour la carte (départ et/ou destination)
        $mapReservations = (clone $baseReservations)
            ->where(function ($q) {
                $q->whereNotNull('depart_latitude')->whereNotNull('depart_longitude')
                    ->orWhere(function ($q2) {
                        $q2->whereNotNull('destination_latitude')->whereNotNull('destination_longitude');
                    });
            })
            ->orderByDesc('date_debut')
            ->limit(50)
            ->get(['id', 'depart', 'destination', 'statut', 'depart_latitude', 'depart_longitude', 'destination_latitude', 'destination_longitude']);

        return inertia('Admin/Dashboard', [
            'agence' => $agenceId ? Agence::find($agenceId)?->only('nom') : null,
            'stats' => [
                'users_count' => $baseUsers->count(),
                'vehicles_count' => $baseVehicles->count(),
                'reservations_count' => $baseReservations->count(),
                'agences_count' => $agencesCount,
                'vehicles_in_maintenance_count' => (clone $baseVehicles)->where('en_maintenance', true)->count(),
                'reservations_en_attente_count' => (clone $baseReservations)->where('statut', 'en attente')->count(),
                'reservations_en_cours_count' => (clone $baseReservations)->whereIn('statut', ['en cours', 'à retourner'])->count(),
                'reservations_ce_mois' => $reservationsCeMois,
                'reservations_mois_precedent' => $reservationsMoisPrec,
                'by_status' => $byStatus,
            ],
            'recent_reservations' => $recentReservations,
            'chart_30j' => ['labels' => $labels30j, 'data' => $data30j],
            'chart_12m' => ['labels' => $labels12m, 'data' => $data12m],
            'map_reservations' => $mapReservations,
        ]);
    }
}