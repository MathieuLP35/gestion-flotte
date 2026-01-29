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

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    // Les trajets où cet utilisateur est le CONDUCTEUR
    public function reservationsAsDriver(): HasMany
    {
        return $this->hasMany(Reservation::class, 'user_id');
    }

    // Les trajets où cet utilisateur est PASSAGER
    public function reservationsAsPassenger(): HasMany
    {
        return $this->hasMany(Passenger::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
