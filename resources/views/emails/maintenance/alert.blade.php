@component('mail::message')
# Alerte Entretien

Le véhicule **{{ $vehicle->modele }} ({{ $vehicle->immatriculation }})** a atteint ou dépassé le kilométrage de maintenance prévu.

Merci de planifier son entretien rapidement.

Cordialement,  
{{ config('app.name') }}
@endcomponent
