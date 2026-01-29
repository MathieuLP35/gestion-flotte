<?php

namespace App\Policies;

use App\Models\Passenger;
use App\Models\User;

class PassengerPolicy
{
    /**
     * Règle : Qui a le droit de MODIFIER un passager (accepter/refuser) ?
     */
    public function update(User $user, Passenger $passenger): bool
    {
        // Seul le CONDUCTEUR du trajet concerné a le droit
        return $user->id === $passenger->reservation->user_id;
    }

    /**
     * Règle : Qui a le droit de SUPPRIMER un passager (le retirer / annuler) ?
     */
    public function delete(User $user, Passenger $passenger): bool
    {
        // 1. Le passager LUI-MÊME a le droit (pour annuler sa place)
        if ($user->id === $passenger->user_id) {
            return true;
        }

        // 2. Le CONDUCTEUR du trajet a le droit (pour le retirer)
        return $user->id === $passenger->reservation->user_id;
    }
}
