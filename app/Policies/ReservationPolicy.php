<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    /**
     * Règle : Qui a le droit de VOIR un trajet (et son chat) ?
     */
    public function view(User $user, Reservation $reservation): bool
    {
        // 1. Le conducteur a le droit
        if ($user->id === $reservation->user_id) {
            return true;
        }

        // 2. Un passager confirmé ou en attente a le droit
        return $reservation->passengers()
            ->where('user_id', $user->id)
            ->whereIn('statut', ['confirme', 'en_attente'])
            ->exists();
    }

    /**
     * Règle : Qui a le droit de MODIFIER un trajet ?
     */
    public function update(User $user, Reservation $reservation): bool
    {
        // Seul le conducteur a le droit
        return $user->id === $reservation->user_id;
    }

    /**
     * Règle : Qui a le droit de SUPPRIMER un trajet ?
     */
    public function delete(User $user, Reservation $reservation): bool
    {
        // Seul le conducteur a le droit
        return $user->id === $reservation->user_id;
    }
}
