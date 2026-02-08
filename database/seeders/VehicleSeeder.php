<?php

namespace Database\Seeders;

use App\Models\Agence;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    /**
     * Crée des véhicules de démonstration pour les agences.
     */
    public function run(): void
    {
        $vehiclesByAgence = [
            'Agence Paris Centre' => [
                ['modele' => 'Peugeot 308', 'immatriculation' => 'AB-123-CD', 'km_initial' => 24500, 'emplacement' => 'Parking A - Place 12', 'nbr_places' => 5, 'energie' => 'essence'],
                ['modele' => 'Renault Zoé', 'immatriculation' => 'EF-456-GH', 'km_initial' => 12000, 'emplacement' => 'Parking A - Place 08', 'nbr_places' => 5, 'energie' => 'electrique'],
                ['modele' => 'Toyota Corolla Hybrid', 'immatriculation' => 'IJ-789-KL', 'km_initial' => 32000, 'emplacement' => 'Parking B - Place 03', 'nbr_places' => 5, 'energie' => 'hybride'],
                ['modele' => 'Citroën C3', 'immatriculation' => 'MN-012-PQ', 'km_initial' => 18500, 'emplacement' => 'Parking A - Place 15', 'nbr_places' => 5, 'energie' => 'diesel'],
                ['modele' => 'Peugeot e-208', 'immatriculation' => 'RS-345-TU', 'km_initial' => 8000, 'emplacement' => 'Parking B - Place 07', 'nbr_places' => 5, 'energie' => 'electrique'],
            ],
            'Agence Rennes' => [
                ['modele' => 'Renault Clio', 'immatriculation' => 'VW-678-XY', 'km_initial' => 41000, 'emplacement' => 'Box 1', 'nbr_places' => 5, 'energie' => 'essence'],
                ['modele' => 'Nissan Leaf', 'immatriculation' => 'ZA-901-BC', 'km_initial' => 22000, 'emplacement' => 'Box 2', 'nbr_places' => 5, 'energie' => 'electrique'],
                ['modele' => 'Volkswagen Golf', 'immatriculation' => 'DE-234-FG', 'km_initial' => 56000, 'emplacement' => 'Box 3', 'nbr_places' => 5, 'energie' => 'diesel'],
                ['modele' => 'Hyundai Kona Electric', 'immatriculation' => 'HI-567-JK', 'km_initial' => 15000, 'emplacement' => 'Box 4', 'nbr_places' => 5, 'energie' => 'electrique'],
            ],
            'Agence Lyon' => [
                ['modele' => 'Peugeot 3008', 'immatriculation' => 'LM-890-NO', 'km_initial' => 28000, 'emplacement' => 'Emplacement 1', 'nbr_places' => 5, 'energie' => 'hybride'],
                ['modele' => 'Renault Mégane', 'immatriculation' => 'PR-123-ST', 'km_initial' => 35000, 'emplacement' => 'Emplacement 2', 'nbr_places' => 5, 'energie' => 'essence'],
                ['modele' => 'Tesla Model 3', 'immatriculation' => 'UV-456-WX', 'km_initial' => 12000, 'emplacement' => 'Emplacement 3', 'nbr_places' => 5, 'energie' => 'electrique'],
            ],
            'Agence Marseille' => [
                ['modele' => 'Fiat 500', 'immatriculation' => 'YZ-789-AB', 'km_initial' => 19000, 'emplacement' => 'A1', 'nbr_places' => 4, 'energie' => 'essence'],
                ['modele' => 'Renault Kangoo', 'immatriculation' => 'CD-012-EF', 'km_initial' => 67000, 'emplacement' => 'A2', 'nbr_places' => 5, 'energie' => 'diesel'],
            ],
        ];

        foreach ($vehiclesByAgence as $agenceNom => $vehicles) {
            $agence = Agence::where('nom', $agenceNom)->first();
            if (! $agence) {
                continue;
            }

            foreach ($vehicles as $data) {
                Vehicle::firstOrCreate(
                    ['immatriculation' => $data['immatriculation']],
                    array_merge($data, [
                        'agence_id' => $agence->id,
                        'en_maintenance' => false,
                    ])
                );
            }
        }
    }
}
