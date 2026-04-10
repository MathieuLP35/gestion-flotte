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
 * @property Agence|null $agence
 * @property Collection<int, Maintenance> $maintenances
 * @property Collection<int, VehicleKey> $keys
 * @property Collection<int, Reservation> $reservations
 */
class Vehicle extends Model
{
    protected $with = ['latestRevision'];

    protected $fillable = [
        'agence_id', 'modele', 'immatriculation', 'kilometrage', 'km_initial', 'emplacement', 'nbr_places', 'en_maintenance', 'energie',
        'purchase_price', 'purchase_date', 'insurance_monthly', 'maintenance_monthly', 'last_service_km', 'service_interval_km', 'service_interval_months',
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<Maintenance, $this>
     */
    public function latestRevision(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Maintenance::class)->where('type', 'revision')->latest('date');
    }

    public function getMaintenanceStatusAttribute(): string
    {
        $lastRevision = $this->latestRevision;

        $currentKm = $this->kilometrage ?? 0;
        $intervalKm = $this->service_interval_km ?? 20000;
        $intervalMonths = $this->service_interval_months ?? 12;

        $lastKm = $lastRevision ? $lastRevision->kilometrage : ($this->km_initial ?? 0);
        $lastDate = $lastRevision ? \Carbon\Carbon::parse($lastRevision->date) : \Carbon\Carbon::parse($this->purchase_date ?? $this->created_at);

        $nextServiceKm = $lastKm + $intervalKm;
        $nextServiceDate = $lastDate->copy()->addMonths($intervalMonths);

        if ($currentKm >= $nextServiceKm || now() >= $nextServiceDate) {
            return 'overdue';
        }

        if ($currentKm >= $nextServiceKm - 2000 || now() >= $nextServiceDate->copy()->subMonth()) {
            return 'warning';
        }

        return 'ok';
    }

    public function getKmUntilNextServiceAttribute(): int
    {
        $lastRevision = $this->latestRevision;

        $intervalKm = $this->service_interval_km ?? 20000;
        $lastKm = $lastRevision ? $lastRevision->kilometrage : ($this->km_initial ?? 0);
        $nextServiceKm = $lastKm + $intervalKm;

        return max(0, $nextServiceKm - ($this->kilometrage ?? 0));
    }

    public function getNextServiceDateAttribute(): ?string
    {
        $lastRevision = $this->latestRevision;

        $intervalMonths = $this->service_interval_months ?? 12;
        $lastDate = $lastRevision ? \Carbon\Carbon::parse($lastRevision->date) : \Carbon\Carbon::parse($this->purchase_date ?? $this->created_at);
        $nextServiceDate = $lastDate->copy()->addMonths($intervalMonths);

        return $nextServiceDate->format('Y-m-d');
    }
}
