{{-- resources/views/emails/reservation/status_changed.blade.php --}}

@component('mail::message')

@if ($reservation->statut === 'en attente')
# Confirmation de votre réservation

Bonjour {{ $reservation->driver->name }},

Votre réservation pour le véhicule **{{ $reservation->vehicle->modele }}** ({{ $reservation->vehicle->immatriculation }}) a bien été enregistrée.

**Détails du trajet :**

* **Départ :** {{ $reservation->depart }}

* **Destination :** {{ $reservation->destination }}

{{-- CORRECTION ICI --}}
* **Début :** {{ $reservation->date_debut->format('d/m/Y à H:i') }}

{{-- CORRECTION ICI --}}
* **Fin :** {{ $reservation->date_fin->format('d/m/Y à H:i') }}

* **État de la réservation :** {{ $reservation->statut }}

Vous pouvez voir et gérer cette réservation en cliquant sur le bouton ci-dessous.

@component('mail::button', ['url' => route('reservations.edit', $reservation->id)])
Voir ma réservation
@endcomponent

@else
# Mise à jour de votre réservation

Bonjour {{ $reservation->driver->name }},

Votre réservation pour le véhicule **{{ $reservation->vehicle->modele }}** ({{ $reservation->vehicle->immatriculation }}) a bien été mise à jour.

**Détails du trajet :**
* **Destination :** {{ $reservation->destination }}

{{-- CORRECTION ICI --}}
* **Début :** {{ $reservation->date_debut->format('d/m/Y à H:i') }}

{{-- CORRECTION ICI --}}
* **Fin :** {{ $reservation->date_fin->format('d/m/Y à H:i') }}

* **État de la réservation :** {{ $reservation->statut }}

Vous pouvez voir et gérer cette réservation en cliquant sur le bouton ci-dessous.

@component('mail::button', ['url' => route('reservations.edit', $reservation->id)])
Voir ma réservation
@endcomponent

@endif

Merci,<br>
L'équipe {{ config('app.name') }}
@endcomponent