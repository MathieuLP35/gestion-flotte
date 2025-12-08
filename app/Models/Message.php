<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['reservation_id', 'user_id', 'body'];

    // Un message appartient à un utilisateur (expéditeur)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Un message appartient à une réservation
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}