<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    protected $fillable = [ 'vehicle_id', 'km_alert_threshold', 'date_dernier_entretien' ];

    public function vehicle() {
        return $this->belongsTo(Vehicle::class);
    }
}
