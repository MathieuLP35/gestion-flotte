<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $vehicle_id
 * @property string $emplacement_clef
 * @property Vehicle $vehicle
 */
class VehicleKey extends Model
{
    protected $fillable = ['vehicle_id', 'emplacement_clef'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Vehicle, $this>
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
