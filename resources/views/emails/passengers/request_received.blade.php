@component('mail::message')
# Nouvelle demande de covoiturage

Bonjour {{ $passenger->reservation->driver->name }},

**{{ $passenger->user->name }}** souhaite rejoindre votre trajet en covoiturage :

**Trajet :** {{ $passenger->reservation->depart }} → {{ $passenger->reservation->destination }}

**Départ :** {{ $passenger->reservation->date_debut->format('d/m/Y à H:i') }}

**Véhicule :** {{ $passenger->reservation->vehicle->modele }} ({{ $passenger->reservation->vehicle->immatriculation }})

Vous pouvez accepter ou refuser cette demande depuis la page de la réservation.

@component('mail::button', ['url' => route('reservations.show', $passenger->reservation_id)])
Gérer la réservation
@endcomponent

Merci,<br>
{{ config('app.name') }}
@endcomponent
