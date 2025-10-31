@component('mail::message')
# Mise à jour de votre réservation

Votre réservation pour le véhicule **{{ $reservation->vehicle->modele }} ({{ $reservation->vehicle->immatriculation }})** 
du **{{ $reservation->date_debut }}** au **{{ $reservation->date_fin }}** est maintenant **{{ $reservation->statut }}**.

Merci,  
{{ config('app.name') }}
@endcomponent
