<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Vehicle;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class MobilityReportController extends Controller
{
    /**
     * @return array<string, mixed>
     */
    private function getReportData(string|int $year, string|int $month): array
    {
        $startDate = null;
        $endDate = null;
        $prevStartDate = null;
        $prevEndDate = null;
        $periodLabel = 'Toutes périodes';

        if ($year !== 'all') {
            $year = (int) $year;
            if ($month !== 'all') {
                $month = (int) $month;
                $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
                $endDate = $startDate->copy()->endOfMonth();

                $prevStartDate = $startDate->copy()->subMonth();
                $prevEndDate = $prevStartDate->copy()->endOfMonth();

                // Capitalize first letter of month like "Janvier 2024"
                \Carbon\Carbon::setLocale('fr');
                $periodLabel = ucfirst($startDate->translatedFormat('F Y'));
            } else {
                $startDate = \Carbon\Carbon::createFromDate($year, 1, 1)->startOfYear();
                $endDate = $startDate->copy()->endOfYear();

                $prevStartDate = $startDate->copy()->subYear();
                $prevEndDate = $prevStartDate->copy()->endOfYear();

                $periodLabel = 'Année '.$year;
            }
        }

        $driverFilter = function ($q) use ($startDate, $endDate) {
            if ($startDate) {
                $q->whereDate('date_debut', '>=', $startDate);
            }
            if ($endDate) {
                $q->whereDate('date_debut', '<=', $endDate);
            }
        };
        $passengerFilter = function ($q) use ($startDate, $endDate) {
            if ($startDate || $endDate) {
                $q->whereHas('reservation', function ($rq) use ($startDate, $endDate) {
                    if ($startDate) {
                        $rq->whereDate('date_debut', '>=', $startDate);
                    }
                    if ($endDate) {
                        $rq->whereDate('date_debut', '<=', $endDate);
                    }
                }
                );
            }
        };

        // Current period stats
        $users = User::with([
            'reservationsAsDriver' => $driverFilter, 'reservationsAsDriver.vehicle', 'reservationsAsDriver.passengers',
            'reservationsAsPassenger' => $passengerFilter, 'reservationsAsPassenger.reservation.vehicle', 'reservationsAsPassenger.reservation.passengers',
            'agence',
        ])->get();

        $totalCo2Saved = $users->sum('total_co2_saved');

        $carpoolsQuery = Reservation::where('statut', 'terminé')->whereHas('passengers', function ($q) {
            $q->where('statut', 'confirme');
        });
        if ($startDate) {
            $carpoolsQuery->whereDate('date_debut', '>=', $startDate);
        }
        if ($endDate) {
            $carpoolsQuery->whereDate('date_debut', '<=', $endDate);
        }
        $totalCarpools = $carpoolsQuery->count();

        // Prev period stats for Deltas
        $prevTotalCo2Saved = 0;
        $prevTotalCarpools = 0;
        if ($year !== 'all') {
            $prevDriverFilter = function ($q) use ($prevStartDate, $prevEndDate) {
                if ($prevStartDate) {
                    $q->whereDate('date_debut', '>=', $prevStartDate);
                }
                if ($prevEndDate) {
                    $q->whereDate('date_debut', '<=', $prevEndDate);
                }
            };
            $prevPassengerFilter = function ($q) use ($prevStartDate, $prevEndDate) {
                if ($prevStartDate || $prevEndDate) {
                    $q->whereHas('reservation', function ($rq) use ($prevStartDate, $prevEndDate) {
                        if ($prevStartDate) {
                            $rq->whereDate('date_debut', '>=', $prevStartDate);
                        }
                        if ($prevEndDate) {
                            $rq->whereDate('date_debut', '<=', $prevEndDate);
                        }
                    }
                    );
                }
            };
            $prevUsers = User::with([
                'reservationsAsDriver' => $prevDriverFilter, 'reservationsAsDriver.vehicle', 'reservationsAsDriver.passengers',
                'reservationsAsPassenger' => $prevPassengerFilter, 'reservationsAsPassenger.reservation.vehicle', 'reservationsAsPassenger.reservation.passengers',
            ])->get();
            $prevTotalCo2Saved = $prevUsers->sum('total_co2_saved');

            $prevCarpoolsQuery = Reservation::where('statut', 'terminé')->whereHas('passengers', function ($q) {
                $q->where('statut', 'confirme');
            });
            if ($prevStartDate) {
                $prevCarpoolsQuery->whereDate('date_debut', '>=', $prevStartDate);
            }
            if ($prevEndDate) {
                $prevCarpoolsQuery->whereDate('date_debut', '<=', $prevEndDate);
            }
            $prevTotalCarpools = $prevCarpoolsQuery->count();
        }

        $deltaCo2 = $prevTotalCo2Saved > 0 ? round((($totalCo2Saved - $prevTotalCo2Saved) / $prevTotalCo2Saved) * 100, 1) : ($totalCo2Saved > 0 ? 100 : 0);
        $deltaCarpools = $prevTotalCarpools > 0 ? round((($totalCarpools - $prevTotalCarpools) / $prevTotalCarpools) * 100, 1) : ($totalCarpools > 0 ? 100 : 0);

        $agencesStats = $users->groupBy('agence.nom')->map(function ($usrs, $n) {
            return ['name' => $n ?: 'Sans Agence', 'co2_saved' => round($usrs->sum('total_co2_saved'), 1), 'carpools_count' => $usrs->sum('carpools_count')];
        })->sortByDesc('co2_saved')->values();

        $vehicleEnergyStats = Vehicle::select('energie', DB::raw('count(*) as total'))->groupBy('energie')->get();

        $logoBase64 = '';
        if (file_exists(public_path('images/logo.png'))) {
            $logoBase64 = 'data:image/png;base64,'.base64_encode(file_get_contents(public_path('images/logo.png')));
        }

        return [
            'year' => $year,
            'month' => $month,
            'periodLabel' => $periodLabel,
            'logoBase64' => $logoBase64,
            'stats' => [
                'total_co2_saved' => round($totalCo2Saved, 1),
                'total_carpools' => $totalCarpools,
                'delta_co2' => $deltaCo2,
                'delta_carpools' => $deltaCarpools,
            ],
            'agencesStats' => $agencesStats,
            'vehicleEnergyStats' => $vehicleEnergyStats,
        ];
    }

    public function index(Request $request): Response
    {
        $periodsInput = $request->input('periods');
        if (is_string($periodsInput)) {
            $periodsInput = json_decode($periodsInput, true);
        }
        if (empty($periodsInput) || ! is_array($periodsInput)) {
            $periodsInput = [['year' => now()->year, 'month' => 'all']];
        }

        $reports = [];
        foreach ($periodsInput as $p) {
            $reports[] = $this->getReportData($p['year'] ?? now()->year, $p['month'] ?? 'all');
        }

        return Inertia::render('Admin/MobilityReport', [
            'reports' => $reports,
        ]);
    }

    public function export(Request $request, string $type = 'technical'): \Illuminate\Http\Response
    {
        $periodsInput = $request->input('periods');
        if (is_string($periodsInput)) {
            $periodsInput = json_decode($periodsInput, true);
        }
        if (empty($periodsInput) || ! is_array($periodsInput)) {
            $periodsInput = [['year' => now()->year, 'month' => 'all']];
        }

        $reports = [];
        foreach ($periodsInput as $p) {
            $reports[] = $this->getReportData($p['year'] ?? now()->year, $p['month'] ?? 'all');
        }

        $view = $type === 'communication' ? 'pdf.mobility-report-comm' : 'pdf.mobility-report-tech';

        $pdf = Pdf::loadView($view, ['reports' => $reports]);

        return $pdf->download('rapport-mobilite-'.$type.'-'.now()->format('Y-m-d').'.pdf');
    }
}
