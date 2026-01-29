<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleKey extends Model
{
    protected $fillable = ['vehicle_id', 'emplacement_clef'];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
