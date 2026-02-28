<?php

namespace App\Http\Controllers;

use App\Mail\ReservationStatusChanged;
use App\Models\Passenger;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller gérant les réservations de véhicules.
 */
class ReservationController extends Controller
{
    use AuthorizesRequests;

    /**
     * Affiche la liste des réservations de l'utilisateur connecté.
     */
    public function index(): Response
    {
        /** @var User $user */
        $user = Auth::user();

        $reservations = Reservation::with('vehicle')
            ->where('user_id', $user->id)
            ->orderBy('date_debut', 'desc')
            ->get();

        return Inertia::render('Reservations/Index', [
            'reservations' => $reservations,
        ]);
    }

    /**
     * Affiche le tableau de bord avec les trajets conducteur et passager.
     */
    public function dashboard(): Response
    {
        $userId = Auth::id();

        $reservationsAsDriver = Reservation::with(['vehicle', 'passengers.user'])
            ->where('user_id', $userId)
            ->orderBy('date_debut', 'desc')
            ->get();

        $reservationsAsPassenger = Passenger::with(['reservation.driver', 'reservation.vehicle'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Dashboard', [
            'reservationsAsDriver' => $reservationsAsDriver,
            'reservationsAsPassenger' => $reservationsAsPassenger,
            'geocoding' => [
                'nominatimEnabled' => config('geocoding.nominatim_enabled'),
            ],
        ]);
    }

    public function create(): Response
    {
        /** @var User $user */
        $user = Auth::user();

        $vehicles = Vehicle::where('agence_id', $user->agence_id)
            ->where('en_maintenance', false)
            ->get();

        return Inertia::render('Reservations/Create', [
            'vehicles' => $vehicles,
        ]);
    }

    /**
     * API pour suggérer un véhicule basé sur la distance.
     */
    public function suggestVehicle(Request $request): JsonResponse
    {
        $request->validate([
            'depart_lat' => 'required|numeric',
            'depart_lng' => 'required|numeric',
            'dest_lat' => 'required|numeric',
            'dest_lng' => 'required|numeric',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
        ]);

        $distance = Vehicle::calculateDistance(
            (float)$request->depart_lat,
            (float)$request->depart_lng,
            (float)$request->dest_lat,
            (float)$request->dest_lng
        );

        $dateDebut = $request->filled('date_debut')
            ?Carbon::parse((string)$request->date_debut)->format('Y-m-d H:i:s')
            : null;
        $dateFin = $request->filled('date_fin')
            ?Carbon::parse((string)$request->date_fin)->format('Y-m-d H:i:s')
            : null;

        /** @var User $user */
        $user = Auth::user();

        $suggestedVehicle = Vehicle::suggestBestVehicle(
            $user->agence_id,
            $distance,
            $dateDebut,
            $dateFin
        );

        return response()->json([
            'suggestedVehicle' => $suggestedVehicle,
            'distance' => $distance,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'departure' => 'required',
            'destination' => 'required',
            'date_debut' => 'required|date|after:now',
            'date_fin' => 'required|date|after:date_debut',
            'is_carpool' => 'sometimes|boolean',
            'places_reservees_materiel' => 'sometimes|integer|min:0',
            'departureSelected' => 'sometimes|array',
            'destinationSelected' => 'sometimes|array',
        ]);

        /** @var Vehicle $vehicle */
        $vehicle = Vehicle::findOrFail($request->vehicle_id);
        /** @var User $user */
        $user = Auth::user();

        if ($vehicle->agence_id !== $user->agence_id || $vehicle->en_maintenance) {
            abort(403, 'Véhicule indisponible');
        }

        $conflict = Reservation::where('vehicle_id', $vehicle->id)
            ->where('statut', 'validé')
            ->where(function ($query) use ($request): void {
            $query->whereBetween('date_debut', [$request->date_debut, $request->date_fin])
                ->orWhereBetween('date_fin', [$request->date_debut, $request->date_fin])
                ->orWhere(function ($q) use ($request): void {
                $q->where('date_debut', '<=', $request->date_debut)
                    ->where('date_fin', '>=', $request->date_fin);
            }
            );
        })->exists();

        if ($conflict) {
            return back()->withErrors(['date_debut' => 'Le véhicule est déjà réservé à cette période']);
        }

        $reservation = Reservation::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'depart' => $request->departure,
            'destination' => $request->destination,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'statut' => config('reservation.default_status', 'en attente'),
            'covoiturage' => $request->has('is_carpool') ? $request->is_carpool : false,
            'places_reservees_materiel' => $request->has('places_reservees_materiel') ? $request->places_reservees_materiel : 0,
            'depart_latitude' => ($request->departureSelected ?? [])['lat'] ?? null,
            'depart_longitude' => ($request->departureSelected ?? [])['lng'] ?? null,
            'destination_latitude' => ($request->destinationSelected ?? [])['lat'] ?? null,
            'destination_longitude' => ($request->destinationSelected ?? [])['lng'] ?? null,
        ]);

        Mail::to($user->email)->queue(new ReservationStatusChanged($reservation));

        return redirect()->route('dashboard')->with('success', 'Réservation créée en attente de validation');
    }

    public function edit(Reservation $reservation): Response
    {
        $this->authorize('update', $reservation);
        $reservation->load(['vehicle', 'driver', 'passengers.user']);

        return Inertia::render('Reservations/Edit', [
            'reservation' => $reservation,
            'vehicles' => Vehicle::all(['id', 'modele', 'immatriculation']),
        ]);
    }

    public function update(Request $request, Reservation $reservation): RedirectResponse
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

        $oldStatut = $reservation->statut;
        $reservation->update($request->only(['vehicle_id', 'departure', 'destination', 'date_debut', 'date_fin', 'statut']));

        if ($oldStatut !== $reservation->statut && $reservation->user) {
            Mail::to($reservation->user->email)->send(new ReservationStatusChanged($reservation));
        }

        return redirect()->route('reservations.index')->with('success', 'Réservation mise à jour');
    }

    public function destroy(Reservation $reservation): RedirectResponse
    {
        $this->authorize('delete', $reservation);
        $reservation->delete();

        return redirect()->route('dashboard')->with('success', 'Réservation supprimée');
    }

    public function show(Reservation $reservation): Response
    {
        $this->authorize('view', $reservation);
        $reservation->load(['vehicle', 'driver', 'passengers.user', 'messages.user']);

        return Inertia::render('Reservations/Show', [
            'reservation' => $reservation,
        ]);
    }

    public function checkCarpool(Request $request): JsonResponse
    {
        $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|sometimes|date|after_or_equal:date_debut',
            'departure' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
        ]);

        $dateDebut = Carbon::parse((string)$request->date_debut);
        $departure = strtolower(trim((string)$request->departure));
        $destination = strtolower(trim((string)$request->destination));

        $reservations = Reservation::with(['driver', 'vehicle', 'passengers'])
            ->where('statut', 'validé')
            ->whereRaw('LOWER(destination) = ?', [$destination])
            ->whereRaw('LOWER(depart) = ?', [$departure])
            ->whereDate('date_debut', $dateDebut->toDateString())
            ->where('date_fin', '>=', $dateDebut)
            ->where('user_id', '!=', Auth::id())
            ->get();

        $availableCarpools = $reservations->filter(function (Reservation $reservation): bool {
            if (!$reservation->vehicle || !$reservation->vehicle->nbr_places) {
                return false;
            }
            $currentOccupants = $reservation->passengers->where('statut', 'confirme')->count() + 1 + $reservation->places_reservees_materiel;

            return $reservation->vehicle->nbr_places > $currentOccupants;
        });

        return response()->json([
            'carpool_available' => $availableCarpools->count() > 0,
            'reservations' => $availableCarpools->values(),
        ]);
    }

    /**
     * @return Response|RedirectResponse
     */
    public function showReturnForm(Reservation $reservation)
    {
        $this->authorize('view', $reservation);

        if ($reservation->user_id !== Auth::id()) {
            abort(403);
        }

        if (!in_array($reservation->statut, ['validé', 'en cours', 'à retourner'])) {
            return redirect()->route('reservations.show', $reservation)->with('error', 'Statut invalide');
        }

        $reservation->load(['vehicle', 'driver']);

        return Inertia::render('Reservations/Return', [
            'reservation' => $reservation,
        ]);
    }

    public function returnVehicle(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->authorize('update', $reservation);

        if (!$reservation->vehicle) {
            abort(404);
        }

        $vehicleKmInitial = $reservation->vehicle->km_initial;

        $request->validate([
            'km_final' => 'required|integer|min:' . $vehicleKmInitial,
            'emplacement_retour' => 'required|string|max:255',
            'etat_vehicule' => 'required|in:excellent,bon,moyen,mauvais',
            'notes_retour' => 'nullable|string|max:1000',
        ]);

        $reservation->update([
            'date_retour' => now(),
            'km_final' => $request->km_final,
            'emplacement_retour' => $request->emplacement_retour,
            'etat_vehicule' => $request->etat_vehicule,
            'notes_retour' => $request->notes_retour,
            'statut' => 'terminé',
        ]);

        $reservation->vehicle->update([
            'emplacement' => $request->emplacement_retour,
            'km_initial' => $request->km_final,
            'en_maintenance' => $request->etat_vehicule === 'mauvais',
        ]);

        if ($reservation->user) {
            Mail::to($reservation->user->email)->send(new ReservationStatusChanged($reservation));
        }

        return redirect()->route('reservations.show', $reservation)->with('success', 'Retour effectué');
    }

    public function startTrip(Reservation $reservation): RedirectResponse
    {
        $this->authorize('update', $reservation);
        $reservation->update(['statut' => 'en cours']);

        if ($reservation->user) {
            Mail::to($reservation->user->email)->send(new ReservationStatusChanged($reservation));
        }

        return back()->with('success', 'Trajet lancé');
    }

    public function endTrip(Reservation $reservation): RedirectResponse
    {
        $this->authorize('update', $reservation);
        $reservation->update(['statut' => 'à retourner']);

        if ($reservation->user) {
            Mail::to($reservation->user->email)->send(new ReservationStatusChanged($reservation));
        }

        return back()->with('success', 'Trajet terminé');
    }
}