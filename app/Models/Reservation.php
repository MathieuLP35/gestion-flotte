<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = ['vehicle_id', 'user_id', 'destination', 'date_debut', 'date_fin', 'statut', 'covoiturage'];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
    ];

    public function vehicle() {
        return $this->belongsTo(Vehicle::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function passengers()
    {
        return $this->hasMany(Passenger::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
