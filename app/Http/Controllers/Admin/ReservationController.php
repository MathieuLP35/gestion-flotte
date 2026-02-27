<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ReservationStatusChanged;
use App\Models\Reservation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ReservationController extends Controller
{
    use AuthorizesRequests;

    public function updateStatus(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->authorize('reservations.validate');

        $request->validate([
            'statut' => 'required|in:validé,annulé',
        ]);

        $oldStatut = $reservation->statut;

        $reservation->update([
            'statut' => $request->statut,
        ]);

        if ($oldStatut !== $reservation->statut && $reservation->user) {
            Mail::to($reservation->user->email)->send(new ReservationStatusChanged($reservation));
        }

        return back()->with('success', 'Statut de la réservation mis à jour.');
    }
}
