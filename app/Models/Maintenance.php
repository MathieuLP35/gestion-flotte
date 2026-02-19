<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $vehicle_id
 * @property Vehicle $vehicle
 */
class Maintenance extends Model
{
    protected $fillable = ['vehicle_id', 'km_alert_threshold', 'date_dernier_entretien'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Vehicle, $this>
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
