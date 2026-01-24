@component('mail::message')
# Retrait d’un trajet covoiturage

Bonjour {{ $removedUserName }},

Vous avez été **retiré** du trajet covoiturage suivant :

**Trajet :** {{ $reservation->depart }} → {{ $reservation->destination }}

**Départ :** {{ $reservation->date_debut->format('d/m/Y à H:i') }}

**Conducteur :** {{ $reservation->driver->name }}

Si vous souhaitez effectuer ce trajet, vous pouvez chercher un autre covoiturage ou réserver un véhicule.

@component('mail::button', ['url' => route('reservations.create')])
Rechercher un trajet
@endcomponent

Merci,<br>
{{ config('app.name') }}
@endcomponent
