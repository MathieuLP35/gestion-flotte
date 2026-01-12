<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $fillable = [
        'agence_id', 'modele', 'immatriculation', 'km_initial', 'emplacement', 'nbr_places', 'en_maintenance', 'energie'
    ];

    public function agence() {
        return $this->belongsTo(Agence::class);
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }

    public function keys()
    {
        return $this->hasMany(VehicleKey::class);
    }
  
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Calcule la distance à vol d'oiseau entre deux points GPS (formule de Haversine)
     * 
     * @param float $lat1 Latitude du point 1
     * @param float $lon1 Longitude du point 1
     * @param float $lat2 Latitude du point 2
     * @param float $lon2 Longitude du point 2
     * @return float Distance en kilomètres
     */
    public static function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // Rayon de la Terre en kilomètres

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Suggère le véhicule le plus adapté basé sur la distance et le type d'énergie
     * 
     * @param int $agenceId ID de l'agence
     * @param float $distance Distance en kilomètres
     * @param string|null $dateDebut Date de début de la réservation
     * @param string|null $dateFin Date de fin de la réservation
     * @return Vehicle|null
     */
    public static function suggestBestVehicle(int $agenceId, float $distance, ?string $dateDebut = null, ?string $dateFin = null): ?Vehicle
    {
        // Seuil pour considérer un trajet comme "petit" (en km)
        $petitTrajetSeuil = 100;

        // Récupérer les véhicules disponibles de l'agence
        $query = self::where('agence_id', $agenceId)
            ->where('en_maintenance', false);

        // Vérifier la disponibilité si des dates sont fournies
        if ($dateDebut && $dateFin) {
            $query->whereDoesntHave('reservations', function ($q) use ($dateDebut, $dateFin) {
                $q->where('statut', 'validé')
                  ->where(function ($query) use ($dateDebut, $dateFin) {
                      $query->whereBetween('date_debut', [$dateDebut, $dateFin])
                            ->orWhereBetween('date_fin', [$dateDebut, $dateFin])
                            ->orWhere(function ($q) use ($dateDebut, $dateFin) {
                                $q->where('date_debut', '<=', $dateDebut)
                                  ->where('date_fin', '>=', $dateFin);
                            });
                  });
            });
        }

        $vehicles = $query->get();

        if ($vehicles->isEmpty()) {
            return null;
        }

        // Pour les petits trajets, privilégier électrique puis hybride
        if ($distance <= $petitTrajetSeuil) {
            // Chercher d'abord un véhicule électrique
            $electrique = $vehicles->where('energie', 'electrique')->first();
            if ($electrique) {
                return $electrique;
            }

            // Sinon, chercher un véhicule hybride
            $hybride = $vehicles->where('energie', 'hybride')->first();
            if ($hybride) {
                return $hybride;
            }
        }

        // Pour les trajets plus longs ou si pas d'électrique/hybride disponible
        // Privilégier hybride, puis essence, puis diesel
        $priorite = ['hybride', 'essence', 'diesel'];
        
        foreach ($priorite as $energie) {
            $vehicle = $vehicles->where('energie', $energie)->first();
            if ($vehicle) {
                return $vehicle;
            }
        }

        // Si aucun véhicule ne correspond, retourner le premier disponible
        return $vehicles->first();
    }
}
