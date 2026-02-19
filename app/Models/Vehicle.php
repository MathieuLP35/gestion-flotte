<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $agence_id
 * @property string $modele
 * @property string $immatriculation
 * @property Agence $agence
 * @property Collection<int, Maintenance> $maintenances
 * @property Collection<int, VehicleKey> $keys
 * @property Collection<int, Reservation> $reservations
 */
class Vehicle extends Model
{
    protected $fillable = [
        'agence_id', 'modele', 'immatriculation', 'km_initial', 'emplacement', 'nbr_places', 'en_maintenance', 'energie',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Agence, $this>
     */
    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Maintenance, $this>
     */
    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<VehicleKey, $this>
     */
    public function keys(): HasMany
    {
        return $this->hasMany(VehicleKey::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Reservation, $this>
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Calcule la distance à vol d'oiseau entre deux points GPS (formule de Haversine)
     *
     * @param  float  $lat1  Latitude du point 1
     * @param  float  $lon1  Longitude du point 1
     * @param  float  $lat2  Latitude du point 2
     * @param  float  $lon2  Longitude du point 2
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
     * @param  int  $agenceId  ID de l'agence
     * @param  float  $distance  Distance en kilomètres
     * @param  string|null  $dateDebut  Date de début de la réservation
     * @param  string|null  $dateFin  Date de fin de la réservation
     */
    public static function suggestBestVehicle(int $agenceId, float $distance, ?string $dateDebut = null, ?string $dateFin = null): ?Vehicle
    {
        $settings = VehicleSuggestionSetting::get();
        $petitTrajetSeuil = $settings->petit_trajet_seuil_km;
        $prioritePetit = $settings->priorite_petit_trajet ?: ['electrique', 'hybride'];
        $prioriteLong = $settings->priorite_long_trajet ?: ['hybride', 'essence', 'diesel'];

        // Récupérer les véhicules disponibles de l'agence
        $query = self::where('agence_id', $agenceId)
            ->where('en_maintenance', false);

        // Vérifier la disponibilité si des dates sont fournies
        if ($dateDebut && $dateFin) {
            $query->whereDoesntHave('reservations', function ($q) use ($dateDebut, $dateFin): void {
                $q->where('statut', 'validé')
                    ->where(function ($query) use ($dateDebut, $dateFin): void {
                        $query->whereBetween('date_debut', [$dateDebut, $dateFin])
                            ->orWhereBetween('date_fin', [$dateDebut, $dateFin])
                            ->orWhere(function ($q) use ($dateDebut, $dateFin): void {
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

        $priorite = ($distance <= $petitTrajetSeuil) ? $prioritePetit : $prioriteLong;

        foreach ($priorite as $energie) {
            $vehicle = $vehicles->where('energie', $energie)->first();
            if ($vehicle) {
                return $vehicle;
            }
        }

        return $vehicles->first();
    }
}
