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
        $query = self::where('covoiturage', 1)
            ->where('statut', 'validé')
            ->where('depart', $departure)
            ->where('destination', $destination);

        if ($arrivalDate) {
            $query->where('date_debut', '<=', $arrivalDate)
                ->where('date_fin', '>=', $departureDate);
        } else {
            $query->where('date_debut', '>=', $departureDate);
        }

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
}
