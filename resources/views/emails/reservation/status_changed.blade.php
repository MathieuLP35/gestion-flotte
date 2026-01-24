@component('mail::message')

@switch($reservation->statut)
    @case('en attente')
# Réservation enregistrée

Bonjour {{ $reservation->driver->name }},

Votre réservation pour le véhicule **{{ $reservation->vehicle->modele }}** ({{ $reservation->vehicle->immatriculation }}) a bien été enregistrée et est **en attente de validation**.

**Détails du trajet :**
* **Départ :** {{ $reservation->depart }}
* **Destination :** {{ $reservation->destination }}
* **Début :** {{ $reservation->date_debut->format('d/m/Y à H:i') }}
* **Fin :** {{ $reservation->date_fin->format('d/m/Y à H:i') }}

@component('mail::button', ['url' => route('reservations.show', $reservation->id)])
Voir ma réservation
@endcomponent
        @break

    @case('validé')
# Votre réservation est validée

Bonjour {{ $reservation->driver->name }},

Votre réservation pour le véhicule **{{ $reservation->vehicle->modele }}** ({{ $reservation->vehicle->immatriculation }}) a été **validée**.

**Détails du trajet :**
* **Départ :** {{ $reservation->depart }}
* **Destination :** {{ $reservation->destination }}
* **Début :** {{ $reservation->date_debut->format('d/m/Y à H:i') }}
* **Fin :** {{ $reservation->date_fin->format('d/m/Y à H:i') }}

Vous pouvez lancer le trajet à l’heure du départ.

@component('mail::button', ['url' => route('reservations.show', $reservation->id)])
Voir ma réservation
@endcomponent
        @break

    @case('annulé')
# Votre réservation a été annulée

Bonjour {{ $reservation->driver->name }},

Votre réservation pour le véhicule **{{ $reservation->vehicle->modele }}** ({{ $reservation->vehicle->immatriculation }}) du **{{ $reservation->date_debut->format('d/m/Y') }}** a été **annulée**.

Vous pouvez créer une nouvelle réservation si besoin.

@component('mail::button', ['url' => route('reservations.create')])
Nouvelle réservation
@endcomponent
        @break

    @case('en cours')
# Votre trajet a démarré

Bonjour {{ $reservation->driver->name }},

Le trajet **{{ $reservation->depart }} → {{ $reservation->destination }}** avec le véhicule **{{ $reservation->vehicle->modele }}** est **en cours**.

Pensez à marquer le trajet comme terminé et à effectuer le retour du véhicule dès votre arrivée.

@component('mail::button', ['url' => route('reservations.show', $reservation->id)])
Voir ma réservation
@endcomponent
        @break

    @case('à retourner')
# Pensez à retourner le véhicule

Bonjour {{ $reservation->driver->name }},

Le trajet **{{ $reservation->depart }} → {{ $reservation->destination }}** est terminé. Il reste à **retourner le véhicule** et à renseigner le kilométrage, l’emplacement et l’état.

@component('mail::button', ['url' => route('reservations.return.form', $reservation->id)])
Effectuer le retour
@endcomponent
        @break

    @case('terminé')
# Véhicule retourné – réservation terminée

Bonjour {{ $reservation->driver->name }},

Votre réservation pour le véhicule **{{ $reservation->vehicle->modele }}** ({{ $reservation->vehicle->immatriculation }}) est **terminée**.

**Récapitulatif du retour :**
* **Date de retour :** {{ $reservation->date_retour?->format('d/m/Y à H:i') }}
* **Kilométrage final :** {{ $reservation->km_final }} km
* **Emplacement :** {{ $reservation->emplacement_retour }}
* **État du véhicule :** {{ $reservation->etat_vehicule }}

Merci d’avoir respecté les règles de retour.

@component('mail::button', ['url' => route('dashboard')])
Tableau de bord
@endcomponent
        @break

    @default
# Mise à jour de votre réservation

Bonjour {{ $reservation->driver->name }},

Votre réservation pour le véhicule **{{ $reservation->vehicle->modele }}** ({{ $reservation->vehicle->immatriculation }}) a été mise à jour.

**État actuel :** {{ $reservation->statut }}

* **Départ :** {{ $reservation->depart }}
* **Destination :** {{ $reservation->destination }}
* **Début :** {{ $reservation->date_debut->format('d/m/Y à H:i') }}
* **Fin :** {{ $reservation->date_fin->format('d/m/Y à H:i') }}

@component('mail::button', ['url' => route('reservations.show', $reservation->id)])
Voir ma réservation
@endcomponent
@endswitch

Merci,<br>
{{ config('app.name') }}
@endcomponent
