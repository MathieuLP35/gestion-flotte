<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\PassengerRemovedFromTrip;
use App\Mail\PassengerRequestReceived;
use App\Mail\PassengerStatusUpdated;
use Illuminate\Http\Request;
use App\Models\Passenger;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class PassengerController extends Controller
{

    use AuthorizesRequests;


    public function store(Request $request)
    {
        $request->validate([
            'reservation_id' => 'required|exists:reservations,id'
        ]);

        // Vérifier qu'il n'est pas déjà passager de ce vol
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
        Mail::to($passenger->reservation->driver->email)->queue(new PassengerRequestReceived($passenger));

        return redirect()->route('dashboard')->with('success', 'Votre demande de covoiturage a été envoyée.');
    }

    public function update(Request $request, Passenger $passenger)
    {

        $this->authorize('update', $passenger);

        $request->validate([
            'statut' => 'required|in:confirme,refuse'
        ]);

        $passenger->update(['statut' => $request->statut]);

        // Email au passager
        Mail::to($passenger->user->email)->send(new PassengerStatusUpdated($passenger));

        return back()->with('success', 'Statut du passager mis à jour.');
    }

    // Retirer un passager (ou annuler sa propre place)
    public function destroy(Passenger $passenger)
    {
        $this->authorize('delete', $passenger);

        if (Auth::id() !== $passenger->user_id) {
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