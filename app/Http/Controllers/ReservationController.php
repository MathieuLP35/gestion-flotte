<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationStatusChanged;
use App\Models\Passenger;
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
        $reservationsAsPassenger = Passenger::with(['reservation.driver', 'reservation.vehicle'])
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
            'departure' => 'required',
            'destination' => 'required',
            'date_debut' => 'required|date|after:now',
            'date_fin' => 'required|date|after:date_debut',
            'is_carpool' => 'sometimes|boolean',
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
            'depart' => $request->departure,
            'destination' => $request->destination,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'statut' => config('reservation.default_status', 'en attente'),
            'covoiturage' => $request->has('is_carpool') ? $request->is_carpool : false,
        ]);

        Mail::to(Auth::user()->email)->queue(new ReservationStatusChanged($reservation));

        return redirect()->route('dashboard')->with('success', 'Réservation créée en attente de validation');
    }

    public function edit(Reservation $reservation)
    {

        $this->authorize('update', $reservation);

        // On charge les passagers et leurs utilisateurs
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
            'departure' => 'required',
            'destination' => 'required',
            'date_debut' => 'required|date|after:now',
            'date_fin' => 'required|date|after:date_debut',
            'statut' => 'required|in:en attente,validé,annulé,en cours,à retourner,terminé',
        ]);

        $reservation->update($request->only(['vehicle_id', 'departure', 'destination', 'date_debut', 'date_fin', 'statut']));

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
            'date_fin' => 'sometimes|date|after:date_debut',
            'departure' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
        ]);

        $dateDebut = Carbon::parse($request->date_debut);
        $departure = strtolower(trim($request->departure));
        $destination = strtolower(trim($request->destination));

        // On cherche des trajets validés, qui partent +/- 1 heure
        // autour de la date demandée, et qui vont au même endroit.
        $reservations = Reservation::with(['driver', 'vehicle', 'passengers'])
            ->where('statut', 'validé')
            ->whereRaw('LOWER(destination) = ?', [$destination])
            ->whereRaw('LOWER(depart) = ?', [$departure])
            ->whereDate('date_debut', $dateDebut->toDateString())
            ->where('date_fin', '>=', $dateDebut)
            ->where('user_id', '!=', Auth::id())
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

    /**
     * Affiche le formulaire de retour du véhicule
     */
    public function showReturnForm(Reservation $reservation)
    {
        $this->authorize('view', $reservation);

        // Vérifier que l'utilisateur est le conducteur
        if ($reservation->user_id !== Auth::id()) {
            abort(403, 'Vous n\'êtes pas autorisé à effectuer le retour de ce véhicule');
        }

        // Vérifier que la réservation est validée, en cours ou à retourner
        if (!in_array($reservation->statut, ['validé', 'en cours', 'à retourner'])) {
            return redirect()->route('reservations.show', $reservation)
                ->with('error', 'Seules les réservations validées, en cours ou à retourner peuvent être retournées');
        }

        // Vérifier que le véhicule n'a pas déjà été retourné
        if ($reservation->date_retour !== null) {
            return redirect()->route('reservations.show', $reservation)
                ->with('error', 'Ce véhicule a déjà été retourné');
        }

        $reservation->load(['vehicle', 'driver']);

        return Inertia::render('Reservations/Return', [
            'reservation' => $reservation
        ]);
    }

    /**
     * Traite le retour du véhicule
     */
    public function returnVehicle(Request $request, Reservation $reservation)
    {
        $this->authorize('update', $reservation);

        // Vérifier que l'utilisateur est le conducteur
        if ($reservation->user_id !== Auth::id()) {
            abort(403, 'Vous n\'êtes pas autorisé à effectuer le retour de ce véhicule');
        }

        // Vérifier que la réservation est validée, en cours ou à retourner
        if (!in_array($reservation->statut, ['validé', 'en cours', 'à retourner'])) {
            return back()->withErrors(['error' => 'Seules les réservations validées, en cours ou à retourner peuvent être retournées']);
        }

        // Vérifier que le véhicule n'a pas déjà été retourné
        if ($reservation->date_retour !== null) {
            return back()->withErrors(['error' => 'Ce véhicule a déjà été retourné']);
        }

        $request->validate([
            'km_final' => 'required|integer|min:' . $reservation->vehicle->km_initial,
            'emplacement_retour' => 'required|string|max:255',
            'etat_vehicule' => 'required|in:excellent,bon,moyen,mauvais',
            'notes_retour' => 'nullable|string|max:1000',
        ]);

        // Mettre à jour la réservation avec les informations de retour
        $reservation->update([
            'date_retour' => now(),
            'km_final' => $request->km_final,
            'emplacement_retour' => $request->emplacement_retour,
            'etat_vehicule' => $request->etat_vehicule,
            'notes_retour' => $request->notes_retour,
            'statut' => 'terminé',
        ]);

        // Mettre à jour l'emplacement du véhicule
        $reservation->vehicle->update([
            'emplacement' => $request->emplacement_retour,
        ]);

        // Si l'état du véhicule est mauvais, le mettre en maintenance
        if ($request->etat_vehicule === 'mauvais') {
            $reservation->vehicle->update([
                'en_maintenance' => true,
            ]);
        }

        return redirect()->route('reservations.show', $reservation)
            ->with('success', 'Véhicule retourné avec succès');
    }

    /**
     * Lance le trajet (change le statut de "validé" à "en cours")
     */
    public function startTrip(Reservation $reservation)
    {
        $this->authorize('update', $reservation);

        // Vérifier que l'utilisateur est le conducteur
        if ($reservation->user_id !== Auth::id()) {
            abort(403, 'Vous n\'êtes pas autorisé à lancer ce trajet');
        }

        // Vérifier que la réservation est validée
        if ($reservation->statut !== 'validé') {
            return back()->withErrors(['error' => 'Seules les réservations validées peuvent être lancées']);
        }

        // Mettre à jour le statut
        $reservation->update([
            'statut' => 'en cours',
        ]);

        // Envoyer un email de notification
        Mail::to($reservation->user->email)->send(new ReservationStatusChanged($reservation));

        return back()->with('success', 'Trajet lancé avec succès');
    }

    /**
     * Termine le trajet (change le statut de "en cours" à "à retourner")
     */
    public function endTrip(Reservation $reservation)
    {
        $this->authorize('update', $reservation);

        // Vérifier que l'utilisateur est le conducteur
        if ($reservation->user_id !== Auth::id()) {
            abort(403, 'Vous n\'êtes pas autorisé à terminer ce trajet');
        }

        // Vérifier que la réservation est en cours
        if ($reservation->statut !== 'en cours') {
            return back()->withErrors(['error' => 'Seuls les trajets en cours peuvent être terminés']);
        }

        // Mettre à jour le statut
        $reservation->update([
            'statut' => 'à retourner',
        ]);

        // Envoyer un email de notification
        Mail::to($reservation->user->email)->send(new ReservationStatusChanged($reservation));

        return back()->with('success', 'Trajet terminé. Vous pouvez maintenant retourner le véhicule.');
    }
}
