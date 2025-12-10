{{-- resources/views/emails/passengers/status-updated.blade.php (Corrigé) --}}

@component('mail::message')

{{-- 
  La variable $passenger est automatiquement passée par la classe Mailable.
--}}
@if ($passenger->statut === 'confirme')

# Votre demande de covoiturage a été confirmée !

Bonjour {{ $passenger->user->name }},

Bonne nouvelle ! Le conducteur a accepté votre demande pour rejoindre le trajet suivant :

**Départ :** {{ $passenger->reservation->depart }}

**Destination :** {{ $passenger->reservation->destination }}

**Conducteur :** {{ $passenger->reservation->driver->name }}

{{-- CORRECTION ICI : Remplacé toLocaleString() par format() --}}
**Départ le :** {{ $passenger->reservation->date_debut->format('d/m/Y à H:i') }}

Vous pouvez retrouver ce trajet dans votre tableau de bord.

@component('mail::button', ['url' => route('reservations.index')])
Voir mes trajets
@endcomponent

@elseif ($passenger->statut === 'refuse')

# Mise à jour de votre demande de covoiturage

Bonjour {{ $passenger->user->name }},

Malheureusement, le conducteur n'a pas pu accepter votre demande pour le trajet suivant :

**Départ :** {{ $passenger->reservation->depart }}

**Destination :** {{ $passenger->reservation->destination }}

**Conducteur :** {{ $passenger->reservation->driver->name }}

{{-- CORRECTION ICI : Remplacé toLocaleString() par format() --}}
**Départ le :** {{ $passenger->reservation->date_debut->format('d/m/Y à H:i') }}

Vous pouvez toujours rechercher d'autres trajets ou réserver un véhicule.

@component('mail::button', ['url' => route('reservations.create')])
Rechercher un autre trajet
@endcomponent

@endif


Merci,
L'équipe {{ config('app.name') }}
@endcomponent