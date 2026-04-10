<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $vehicle_id
 * @property string|null $date
 * @property int|null $kilometrage
 * @property string|null $type
 * @property float|null $cost
 * @property string|null $notes
 * @property Vehicle $vehicle
 */
class Maintenance extends Model
{
    protected $fillable = ['vehicle_id', 'date', 'kilometrage', 'type', 'cost', 'notes'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Vehicle, $this>
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
