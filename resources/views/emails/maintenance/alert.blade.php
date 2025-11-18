@component('mail::message')
# Alerte Entretien

Le véhicule **{{ $vehicle->modele }} ({{ $vehicle->immatriculation }})** a atteint ou dépassé les **{{ $maintenance->km_alert_threshold }} km**

Merci de planifier son entretien rapidement.

Cordialement,  
{{ config('app.name') }}
@endcomponent
