<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationStatusChanged;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ReservationController extends Controller
{

    use AuthorizesRequests;

    
    public function index()
    {
        $user = Auth::user();
        $reservations = Reservation::with('vehicle')
            ->where('user_id', $user->id)
            ->orWhereHas('vehicle', fn($q) => $q->where('agence_id', $user->agence_id))
            ->orderBy('date_debut', 'desc')
            ->get();

        return Inertia::render('Reservations/Index', [
            'reservations' => $reservations
        ]);
    }

    public function create()
    {
        $vehicles = Vehicle::where('agence_id', Auth::user()->agence_id)
            ->where('en_maintenance', false)
            ->get();

        return Inertia::render('Reservations/Create', ['vehicles' => $vehicles]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'date_debut' => 'required|date|after:now',
            'date_fin' => 'required|date|after:date_debut',
        ]);

        $vehicle = Vehicle::findOrFail($request->vehicle_id);

        if ($vehicle->agence_id !== Auth::user()->agence_id || $vehicle->en_maintenance) {
            abort(403, 'Véhicule indisponible');
        }

        // Vérifier conflit sur réservation
        $conflict = Reservation::where('vehicle_id', $vehicle->id)
            ->where('statut', 'validé')
            ->where(function ($query) use ($request) {
                $query->whereBetween('date_debut', [$request->date_debut, $request->date_fin])
                    ->orWhereBetween('date_fin', [$request->date_debut, $request->date_fin])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('date_debut', '<=', $request->date_debut)
                            ->where('date_fin', '>=', $request->date_fin);
                    });
            })->exists();

        if ($conflict) {
            return back()->withErrors(['date_debut' => 'Le véhicule est déjà réservé à cette période']);
        }

        $reservation = Reservation::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => Auth::id(),
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'statut' => 'en attente',
            'covoiturage' => false,
        ]);

        // Optionnel: envoyer notification par mail
        Mail::to(Auth::user()->email)->send(new ReservationStatusChanged($reservation));

        return redirect()->route('reservations.index')->with('success', 'Réservation créée en attente de validation');
    }

    public function edit(Reservation $reservation)
    {

        $vehicle = Vehicle::where('agence_id', Auth::user()->agence_id)->get();

        return Inertia::render('Reservations/Edit', [
            'reservation' => $reservation,
            'vehicles' => $vehicle,
        ]);
    }

    public function update(Request $request, Reservation $reservation)
    {

        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'date_debut' => 'required|date|after:now',
            'date_fin' => 'required|date|after:date_debut',
            'statut' => 'required|in:en attente,validé,annulé',
        ]);

        $reservation->update($request->only(['vehicle_id', 'date_debut', 'date_fin', 'statut', 'covoiturage']));

        // Envoyer mail si changement statut
        Mail::to($reservation->user->email)->send(new ReservationStatusChanged($reservation));

        return redirect()->route('reservations.index')->with('success', 'Réservation mise à jour');
    }

    public function destroy(Reservation $reservation)
    {
        $this->authorize('delete', $reservation);
        $reservation->delete();

        return redirect()->route('reservations.index')->with('success', 'Réservation supprimée');
    }
}
