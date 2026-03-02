<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property int|null $agence_id
 * @property Agence|null $agence
 * @property Collection<int, Reservation> $reservationsAsDriver
 * @property Collection<int, Passenger> $reservationsAsPassenger
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name', 'email', 'password', 'agence_id', 'locked_until',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * (Les relations avec pivot sont exclues via UserResource, pas via $hidden.)
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'locked_until' => 'datetime',
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Agence, $this>
     */
    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Reservation, $this>
     */
    // Les trajets où cet utilisateur est le CONDUCTEUR
    public function reservationsAsDriver(): HasMany
    {
        return $this->hasMany(Reservation::class , 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Passenger, $this>
     */
    // Les trajets où cet utilisateur est PASSAGER
    public function reservationsAsPassenger(): HasMany
    {
        return $this->hasMany(Passenger::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Message, $this>
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * RSE : Total de CO2 économisé par cet utilisateur
     */
    public function getTotalCo2SavedAttribute(): float
    {
        $driverCo2 = $this->reservationsAsDriver
            ->where('statut', 'terminé')
            ->sum('co2_saved');

        $passengerCo2 = $this->reservationsAsPassenger
            ->where('statut', 'confirme')
            ->sum(function ($passenger) {
            if ($passenger->reservation->statut === 'terminé') {
                $distance = $passenger->reservation->distance_km;
                $actualCo2 = ($passenger->reservation->vehicle && $passenger->reservation->vehicle->energie === 'electrique') ? 0 : (($passenger->reservation->vehicle && $passenger->reservation->vehicle->energie === 'hybride') ? 70 : 130);
                $sharedEmissions = ($distance * $actualCo2) / max(1, 1 + $passenger->reservation->passengers->where('statut', 'confirme')->count());

                $saved = ($distance * 130) - $sharedEmissions;

                return max(0, $saved / 1000);
            }

            return 0;
        });

        return round($driverCo2 + $passengerCo2, 1);
    }

    /**
     * Nombre de covoiturages effectués (comme conducteur avec passagers ou comme passager)
     */
    public function getCarpoolsCountAttribute(): int
    {
        $asDriver = $this->reservationsAsDriver
            ->where('statut', 'terminé')
            ->filter(function ($resa) {
            return $resa->passengers->where('statut', 'confirme')->count() > 0;
        })->count();

        $asPassenger = $this->reservationsAsPassenger
            ->where('statut', 'confirme')
            ->filter(function ($pass) {
            return $pass->reservation->statut === 'terminé';
        })->count();

        return $asDriver + $asPassenger;
    }
}