<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name', 'email', 'password', 'agence_id', 'locked_until'
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

    public function agence() {
        return $this->belongsTo(Agence::class);
    }

    // Les trajets où cet utilisateur est le CONDUCTEUR
    public function reservationsAsDriver()
    {
        return $this->hasMany(Reservation::class, 'user_id');
    }

    // Les trajets où cet utilisateur est PASSAGER
    public function reservationsAsPassenger()
    {
        return $this->hasMany(Passenger::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
