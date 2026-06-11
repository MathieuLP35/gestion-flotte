<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $vehicle_id
 * @property string $type
 * @property string $date
 * @property float $amount
 * @property int|null $kilometrage
 * @property float|null $quantity
 * @property string|null $notes
 * @property Vehicle $vehicle
 */
class VehicleCost extends Model
{
    protected $fillable = [
        'vehicle_id',
        'type',
        'date',
        'amount',
        'kilometrage',
        'quantity',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'kilometrage' => 'integer',
        'quantity' => 'decimal:2',
    ];

    /**
     * @return BelongsTo<Vehicle, $this>
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'fuel' => 'Carburant / Recharge',
            'toll' => 'Péage',
            'parking' => 'Parking',
            'cleaning' => 'Nettoyage',
            default => 'Autre',
        };
    }
}
