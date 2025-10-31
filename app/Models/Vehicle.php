<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $fillable = [
        'agence_id', 'modele', 'immatriculation', 'km_initial', 'emplacement', 'nbr_places', 'en_maintenance'
    ];

    public function agence() {
        return $this->belongsTo(Agence::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }
}
