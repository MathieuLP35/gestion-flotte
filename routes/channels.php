<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('reservation.{id}', function ($user, $id) {
    // Seuls l'admin ou les personnes liées à la réservation peuvent écouter
    return true; // À affiner selon votre logique de droits (ex: $user->id === Reservation::find($id)->user_id)
});
