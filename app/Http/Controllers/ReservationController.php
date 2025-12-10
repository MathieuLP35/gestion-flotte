<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationStatusChanged;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ReservationController extends Controller
{

    use AuthorizesRequests;

    
    public function index()
    {
        $user = Auth::user();
        $reservations = Reservation::with('vehicle')
            ->where('user_id', $user->id)
            ->orderBy('date_debut', 'desc')
            ->get();

        return Inertia::render('Reservations/Index', [
            'reservations' => $reservations
        ]);
    }

    public function dashboard()
    {
        $userId = Auth::id();
        
        // 1. Les trajets où je suis CONDUCTEUR
        $reservationsAsDriver = Reservation::with(['vehicle', 'passengers.user'])
            ->where('user_id', $userId)
            ->orderBy('date_debut', 'desc')
            ->get();

        // 2. Les trajets où je suis PASSAGER
        // On passe par le modèle Passenger pour récupérer les infos
        $reservationsAsPassenger = \App\Models\Passenger::with(['reservation.driver', 'reservation.vehicle'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return inertia('Dashboard', [
            'reservationsAsDriver' => $reservationsAsDriver,
            'reservationsAsPassenger' => $reservationsAsPassenger,
            'geocoding' => [
                'nominatimEnabled' => config('geocoding.nominatim_enabled'),
            ],
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
            'destination' => 'required',
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
            'destination' => $request->destination,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'statut' => 'en attente',
            'covoiturage' => false,
        ]);

        // Optionnel: envoyer notification par mail
        Mail::to(Auth::user()->email)->queue(new ReservationStatusChanged($reservation));

        return redirect()->route('dashboard')->with('success', 'Réservation créée en attente de validation');
    }

    public function edit(Reservation $reservation)
    {

        $this->authorize('update', $reservation);

        // NOUVEAU : On charge les passagers et leurs utilisateurs
        $reservation->load(['vehicle', 'driver', 'passengers.user']);

        return inertia('Reservations/Edit', [
            'reservation' => $reservation,
            'vehicles' => Vehicle::all(['id', 'modele', 'immatriculation']), // On envoie les véhicules pour le <select>
        ]);
    }

    public function update(Request $request, Reservation $reservation)
    {
        $this->authorize('update', $reservation);

        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'destination' => 'required',
            'date_debut' => 'required|date|after:now',
            'date_fin' => 'required|date|after:date_debut',
            'statut' => 'required|in:en attente,validé,annulé',
        ]);

        $reservation->update($request->only(['vehicle_id', 'destination', 'date_debut', 'date_fin', 'statut', 'covoiturage']));

        // Envoyer mail si changement statut
        Mail::to($reservation->user->email)->send(new ReservationStatusChanged($reservation));

        return redirect()->route('reservations.index')->with('success', 'Réservation mise à jour');
    }

    public function destroy(Reservation $reservation)
    {
        $this->authorize('delete', $reservation);

        $reservation->delete();

        return redirect()->route('dashboard')->with('success', 'Réservation supprimée');
    }

    public function show(Reservation $reservation)
    {

        $this->authorize('view', $reservation);

        // On charge TOUT ce dont la page aura besoin
        $reservation->load([
            'vehicle',          // Le véhicule
            'driver',           // Le conducteur
            'passengers.user',  // Les passagers (avec leurs noms)
            'messages.user'     // Les messages (avec leurs expéditeurs)
        ]);

        return inertia('Reservations/Show', [
            'reservation' => $reservation
        ]);
    }

    public function checkCarpool(Request $request)
    {
        $request->validate([
            'date_debut' => 'required|date',
            'destination' => 'required|string|max:255',
        ]);

        $dateDebut = Carbon::parse($request->date_debut);
        $destination = strtolower(trim($request->destination));

        // On cherche des trajets validés, qui partent +/- 1 heure
        // autour de la date demandée, et qui vont au même endroit.
        $reservations = Reservation::with(['driver', 'vehicle', 'passengers'])
            ->where('statut', 'validé')
            ->whereRaw('LOWER(destination) = ?', [$destination])
            ->whereDate('date_debut', $dateDebut)
            ->where('user_id', '!=', Auth::id()) // On ne veut pas ses propres trajets
            ->get();

        // On filtre pour ne garder que ceux qui ont des places libres
        $availableCarpools = $reservations->filter(function ($reservation) {
            // S'il n'y a pas de véhicule ou de places, on ignore
            if (!$reservation->vehicle || !$reservation->vehicle->nbr_places) {
                return false;
            }

            // +1 pour le conducteur
            $currentOccupants = $reservation->passengers->where('statut', 'confirme')->count() + 1; 

            return $reservation->vehicle->nbr_places > $currentOccupants;
        });

        return response()->json([
            'carpool_available' => $availableCarpools->count() > 0,
            'reservations' => $availableCarpools->values(), // Retourne les trajets en JSON
        ]);
    }
}
