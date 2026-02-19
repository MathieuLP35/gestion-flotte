<?php

namespace App\Http\Controllers;

use App\Mail\PassengerRemovedFromTrip;
use App\Mail\PassengerRequestReceived;
use App\Mail\PassengerStatusUpdated;
use App\Models\Passenger;
use App\Models\Reservation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class PassengerController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
        ]);

        $reservation = Reservation::with(['vehicle', 'passengers'])->findOrFail($request->reservation_id);

        if ($reservation->user_id === Auth::id()) {
            return back()->with('error', 'Vous êtes le conducteur de ce trajet.');
        }

        if (! $reservation->covoiturage) {
            return back()->with('error', 'Ce trajet n\'accepte pas les passagers.');
        }

        if ($reservation->statut !== 'validé') {
            return back()->with('error', 'Les demandes ne sont possibles que pour les trajets validés.');
        }

        $occupants = 1 + $reservation->passengers->whereIn('statut', ['confirme', 'en_attente'])->count();
        if ($reservation->vehicle && $reservation->vehicle->nbr_places && $occupants >= $reservation->vehicle->nbr_places) {
            return back()->with('error', 'Ce trajet est complet.');
        }

        $existing = Passenger::where('reservation_id', $request->reservation_id)
            ->where('user_id', Auth::id())
            ->exists();

        if ($existing) {
            return back()->with('error', 'Vous êtes déjà passager sur ce trajet.');
        }

        $passenger = Passenger::create([
            'reservation_id' => $request->reservation_id,
            'user_id' => Auth::id(),
            'statut' => 'en_attente',
        ]);

        $passenger->load('reservation.driver');

        // Suppression du check superflu (Ligne 65 du rapport)
        Mail::to($passenger->reservation->driver->email)->queue(new PassengerRequestReceived($passenger));

        return redirect()->route('dashboard')->with('success', 'Votre demande de covoiturage a été envoyée.');
    }

    public function update(Request $request, Passenger $passenger): RedirectResponse
    {
        $this->authorize('update', $passenger);

        $request->validate([
            'statut' => 'required|in:confirme,refuse',
        ]);

        $passenger->update(['statut' => (string) $request->statut]);

        // Suppression du check superflu (Ligne 87 du rapport)
        Mail::to($passenger->user->email)->send(new PassengerStatusUpdated($passenger));

        return back()->with('success', 'Statut du passager mis à jour.');
    }

    public function destroy(Passenger $passenger): RedirectResponse
    {
        $this->authorize('delete', $passenger);

        if (Auth::id() !== $passenger->user_id) {
            // Suppression des checks superflus (Ligne 106 du rapport)
            $email = $passenger->user->email;
            $userName = $passenger->user->name;
            $reservation = $passenger->reservation;

            $passenger->delete();
            Mail::to($email)->send(new PassengerRemovedFromTrip($reservation, $userName));
        } else {
            $passenger->delete();
        }

        return back()->with('success', 'Passager retiré du trajet.');
    }
}
