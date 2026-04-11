<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $vehicle_id
 * @property int $user_id
 * @property string $statut
 * @property Vehicle|null $vehicle
 * @property User|null $user
 * @property User|null $driver
 * @property Collection<int, Passenger> $passengers
 */
class Reservation extends Model
{
    protected $fillable = [
        'vehicle_id', 'user_id', 'depart', 'destination',
        'date_debut', 'date_fin', 'statut', 'covoiturage', 'places_reservees_materiel',
        'depart_latitude', 'depart_longitude', 'destination_latitude', 'destination_longitude',
        'date_retour', 'km_final', 'emplacement_retour', 'etat_vehicule', 'notes_retour',
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
        'date_retour' => 'datetime',
    ];

    protected $appends = [
        'distance_km', 'co2_saved',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Vehicle, $this>
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Passenger, $this>
     */
    public function passengers(): HasMany
    {
        return $this->hasMany(Passenger::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, $this>
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Message, $this>
     */
    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Reservation>
     */
    public static function searchCarpoolings(string $departure, string $destination, string $departureDate, ?string $arrivalDate = null)
    {
        $departure = strtolower(trim($departure));
        $destination = strtolower(trim($destination));

        $startDate = \Carbon\Carbon::parse($departureDate)->toDateString();
        $endDate = $arrivalDate ? \Carbon\Carbon::parse($arrivalDate)->toDateString() : $startDate;

        $query = self::where('covoiturage', 1)
            ->where('statut', 'validé')
            ->where('user_id', '!=', auth()->id())
            ->whereRaw('LOWER(depart) LIKE ?', ['%' . $departure . '%'])
            ->whereRaw('LOWER(destination) LIKE ?', ['%' . $destination . '%'])
            ->whereDate('date_debut', '<=', $endDate)
            ->whereDate('date_fin', '>=', $startDate);

        return $query->with('driver', 'passengers', 'vehicle')->get();
    }

    /**
     * Vérifie si le véhicule peut être retourné
     */
    public function canBeReturned(): bool
    {
        return in_array($this->statut, ['validé', 'en cours', 'à retourner']) && $this->date_retour === null;
    }

    /**
     * Vérifie si le véhicule a été retourné
     */
    public function isReturned(): bool
    {
        return $this->date_retour !== null;
    }

    /**
     * Calcule la distance en KM du trajet si les coordonnées sont renseignées.
     */
    public function getDistanceKmAttribute(): float
    {
        if ($this->depart_latitude && $this->destination_latitude) {
            return Vehicle::calculateDistance(
                (float) $this->depart_latitude, (float) $this->depart_longitude,
                (float) $this->destination_latitude, (float) $this->destination_longitude
            );
        }

        return 0;
    }

    /**
     * Calcule le CO2 économisé en Kg
     * Compare le fait de faire le trajet avec ce véhicule + passagers vs. si chacun prenait une voiture standard thermique individuelle.
     */
    public function getCo2SavedAttribute(): float
    {
        $distance = $this->distance_km;
        if ($distance <= 0) {
            return 0;
        }

        // Nombre de personnes dans la voiture (conducteur + passagers confirmés)
        $passengersLoaded = $this->relationLoaded('passengers') ? $this->passengers->where('statut', 'confirme')->count() : $this->passengers()->where('statut', 'confirme')->count();
        $peopleCount = 1 + $passengersLoaded;

        // Bilan carbone de base : 130g/km pour une voiture thermique moyenne solo
        $baselineCo2 = $peopleCount * $distance * 130; // en grammes

        // Bilan carbone réel du véhicule utilisé
        $vehicleCo2PerKm = 130; // Essence/Diesel par défaut
        if ($this->relationLoaded('vehicle') && $this->vehicle) {
            if ($this->vehicle->energie === 'electrique') {
                $vehicleCo2PerKm = 0; // On considère 0g à l'usage pour simplifier
            } elseif ($this->vehicle->energie === 'hybride') {
                $vehicleCo2PerKm = 70;
            }
        }

        $actualCo2 = $distance * $vehicleCo2PerKm;
        $saved = $baselineCo2 - $actualCo2;

        return round(max(0, $saved / 1000), 1); // en kg de CO2
    }
}
