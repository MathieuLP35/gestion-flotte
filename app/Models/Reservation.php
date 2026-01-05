<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = ['vehicle_id', 'user_id', 'depart', 'destination', 'date_debut', 'date_fin', 'statut', 'covoiturage','depart_latitude', 'depart_longitude', 'destination_latitude', 'destination_longitude',];

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

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    public static function searchCarpoolings(string $departure, string $destination, string $departureDate, ?string $arrivalDate = null)
    {
        $query = self::where('covoiturage', 1)
            ->where('depart', $departure)
            ->where('destination', $destination);

        if ($arrivalDate) {
            $query->where('date_debut', '<=', $arrivalDate)
            ->where('date_fin', '>=', $departureDate);
        } else {
            $query->where('date_debut', '>=', $departureDate);
        }

        return $query->with('driver', 'passengers', 'vehicle')->get();
    }
}
