<?php

namespace Database\Seeders;

use App\Models\Passenger;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    /**
     * Crée des réservations et trajets covoiturage pour la démo.
     */
    public function run(): void
    {
        // Recherche par nom pour rester valide avec DEMO_MAIL_RECIPIENT (emails en +tag)
        $thomas = User::where('name', 'Thomas Dubois')->first();
        $lea = User::where('name', 'Léa Petit')->first();
        $julien = User::where('name', 'Julien Moreau')->first();
        $camille = User::where('name', 'Camille Bernard')->first();
        $lucas = User::where('name', 'Lucas Simon')->first();

        if (! $thomas || ! $lea) {
            return;
        }

        $vehicles = Vehicle::with('agence')->get();
        $vehiclesParis = $vehicles->where('agence.nom', 'Agence Paris Centre')->values();
        $vehiclesRennes = $vehicles->where('agence.nom', 'Agence Rennes')->values();
        $vehiclesLyon = $vehicles->where('agence.nom', 'Agence Lyon')->values();

        $now = Carbon::now();

        $reservations = [
            // Thomas (Paris) - trajet validé avec covoiturage
            [
                'driver_name' => 'Thomas Dubois',
                'vehicle_index' => 0,
                'agence_name' => 'Agence Paris Centre',
                'depart' => 'Paris Gare de Lyon',
                'destination' => 'Lyon Part-Dieu',
                'depart_lat' => 48.8443,
                'depart_lng' => 2.3734,
                'dest_lat' => 45.7606,
                'dest_lng' => 4.8604,
                'date_debut' => $now->copy()->addDays(2)->setTime(8, 0),
                'date_fin' => $now->copy()->addDays(2)->setTime(12, 30),
                'statut' => 'validé',
                'covoiturage' => true,
                'passenger_names' => ['Léa Petit', 'Julien Moreau'],
            ],
            // Léa (Rennes) - trajet en attente
            [
                'driver_name' => 'Léa Petit',
                'vehicle_index' => 0,
                'agence_name' => 'Agence Rennes',
                'depart' => 'Rennes Centre',
                'destination' => 'Saint-Malo',
                'depart_lat' => 48.1173,
                'depart_lng' => -1.6778,
                'dest_lat' => 48.6493,
                'dest_lng' => -2.0257,
                'date_debut' => $now->copy()->addDays(5)->setTime(9, 0),
                'date_fin' => $now->copy()->addDays(5)->setTime(10, 30),
                'statut' => 'en attente',
                'covoiturage' => false,
                'passenger_names' => [],
            ],
            // Julien (Rennes) - trajet terminé (avec retour)
            [
                'driver_name' => 'Julien Moreau',
                'vehicle_index' => 1,
                'agence_name' => 'Agence Rennes',
                'depart' => 'Rennes',
                'destination' => 'Nantes',
                'depart_lat' => 48.1173,
                'depart_lng' => -1.6778,
                'dest_lat' => 47.2184,
                'dest_lng' => -1.5536,
                'date_debut' => $now->copy()->subDays(3)->setTime(14, 0),
                'date_fin' => $now->copy()->subDays(3)->setTime(16, 0),
                'date_retour' => $now->copy()->subDays(3)->setTime(18, 30),
                'statut' => 'terminé',
                'covoiturage' => true,
                'km_final' => 22150,
                'emplacement_retour' => 'Box 2',
                'etat_vehicule' => 'bon',
                'notes_retour' => 'Trajet sans incident.',
                'passenger_names' => ['Lucas Simon'],
            ],
            // Camille (Lyon) - trajet validé covoiturage
            [
                'driver_name' => 'Camille Bernard',
                'vehicle_index' => 0,
                'agence_name' => 'Agence Lyon',
                'depart' => 'Lyon Part-Dieu',
                'destination' => 'Grenoble',
                'depart_lat' => 45.7606,
                'depart_lng' => 4.8604,
                'dest_lat' => 45.1885,
                'dest_lng' => 5.7245,
                'date_debut' => $now->copy()->addDays(7)->setTime(7, 30),
                'date_fin' => $now->copy()->addDays(7)->setTime(9, 0),
                'statut' => 'validé',
                'covoiturage' => true,
                'passenger_names' => ['Thomas Dubois'],
            ],
            // Lucas (Lyon) - trajet en cours
            [
                'driver_name' => 'Lucas Simon',
                'vehicle_index' => 1,
                'agence_name' => 'Agence Lyon',
                'depart' => 'Lyon',
                'destination' => 'Annecy',
                'depart_lat' => 45.7606,
                'depart_lng' => 4.8604,
                'dest_lat' => 45.8992,
                'dest_lng' => 6.1294,
                'date_debut' => $now->copy()->subHour(),
                'date_fin' => $now->copy()->addHours(2),
                'statut' => 'en cours',
                'covoiturage' => false,
                'passenger_names' => [],
            ],
            // Thomas - second trajet (à retourner)
            [
                'driver_name' => 'Thomas Dubois',
                'vehicle_index' => 1,
                'agence_name' => 'Agence Paris Centre',
                'depart' => 'Paris',
                'destination' => 'Versailles',
                'depart_lat' => 48.8566,
                'depart_lng' => 2.3522,
                'dest_lat' => 48.8049,
                'dest_lng' => 2.1204,
                'date_debut' => $now->copy()->subDays(1)->setTime(9, 0),
                'date_fin' => $now->copy()->subDays(1)->setTime(17, 0),
                'statut' => 'à retourner',
                'covoiturage' => false,
                'passenger_names' => [],
            ],
            // Léa - covoiturage futur
            [
                'driver_name' => 'Léa Petit',
                'vehicle_index' => 2,
                'agence_name' => 'Agence Rennes',
                'depart' => 'Rennes',
                'destination' => 'Brest',
                'depart_lat' => 48.1173,
                'depart_lng' => -1.6778,
                'dest_lat' => 48.3905,
                'dest_lng' => -4.4860,
                'date_debut' => $now->copy()->addDays(10)->setTime(8, 0),
                'date_fin' => $now->copy()->addDays(10)->setTime(11, 0),
                'statut' => 'validé',
                'covoiturage' => true,
                'passenger_names' => ['Camille Bernard'],
            ],
        ];

        foreach ($reservations as $r) {
            $driver = User::where('name', $r['driver_name'])->first();
            $vehicle = $this->getVehicleByAgenceAndIndex($r['agence_name'], $r['vehicle_index']);
            if (! $driver || ! $vehicle) {
                continue;
            }

            $reservation = Reservation::firstOrCreate(
                [
                    'vehicle_id' => $vehicle->id,
                    'user_id' => $driver->id,
                    'date_debut' => $r['date_debut'],
                ],
                [
                    'depart' => $r['depart'],
                    'destination' => $r['destination'],
                    'depart_latitude' => $r['depart_lat'],
                    'depart_longitude' => $r['depart_lng'],
                    'destination_latitude' => $r['dest_lat'],
                    'destination_longitude' => $r['dest_lng'],
                    'date_fin' => $r['date_fin'],
                    'statut' => $r['statut'],
                    'covoiturage' => $r['covoiturage'],
                    'date_retour' => $r['date_retour'] ?? null,
                    'km_final' => $r['km_final'] ?? null,
                    'emplacement_retour' => $r['emplacement_retour'] ?? null,
                    'etat_vehicule' => $r['etat_vehicule'] ?? null,
                    'notes_retour' => $r['notes_retour'] ?? null,
                ]
            );

            foreach ($r['passenger_names'] as $passengerName) {
                $passengerUser = User::where('name', $passengerName)->first();
                if ($passengerUser && $passengerUser->id !== $driver->id) {
                    Passenger::firstOrCreate(
                        [
                            'reservation_id' => $reservation->id,
                            'user_id' => $passengerUser->id,
                        ],
                        ['statut' => 'confirme']
                    );
                }
            }
        }
    }

    private function getVehicleByAgenceAndIndex(string $agenceName, int $index): ?Vehicle
    {
        return Vehicle::whereHas('agence', fn ($q) => $q->where('nom', $agenceName))
            ->orderBy('id')
            ->skip($index)
            ->first();
    }
}
