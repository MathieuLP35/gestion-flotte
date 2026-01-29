<?php

namespace Database\Seeders;

use App\Models\Agence;
use Illuminate\Database\Seeder;

class AgenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $agences = [
            [
                'nom' => 'Agence Paris Centre',
                'adresse' => '15 Avenue des Champs-Élysées, 75008 Paris',
            ],
            [
                'nom' => 'Agence Rennes',
                'adresse' => '12 Place de la République, 35000 Rennes',
            ],
            [
                'nom' => 'Agence Lyon',
                'adresse' => '45 Rue de la République, 69002 Lyon',
            ],
            [
                'nom' => 'Agence Marseille',
                'adresse' => '8 Boulevard de la Canebière, 13001 Marseille',
            ],
            [
                'nom' => 'Agence Bordeaux',
                'adresse' => '22 Cours de l\'Intendance, 33000 Bordeaux',
            ],
            [
                'nom' => 'Agence Toulouse',
                'adresse' => '18 Place du Capitole, 31000 Toulouse',
            ],
            [
                'nom' => 'Agence Nantes',
                'adresse' => '30 Rue Crébillon, 44000 Nantes',
            ],
            [
                'nom' => 'Agence Strasbourg',
                'adresse' => '10 Place Kléber, 67000 Strasbourg',
            ],
        ];

        foreach ($agences as $agence) {
            Agence::create($agence);
        }
    }
}
