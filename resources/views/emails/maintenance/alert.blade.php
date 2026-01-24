@component('mail::message')
# Alerte entretien véhicule

Le véhicule **{{ $vehicle->modele }}** ({{ $vehicle->immatriculation }}) a atteint ou dépassé le seuil d’entretien.

**Kilométrage actuel :** {{ number_format($vehicle->km_initial, 0, ',', ' ') }} km  
**Seuil d’alerte :** {{ number_format($maintenance->km_alert_threshold, 0, ',', ' ') }} km

Merci de planifier son entretien et de mettre à jour la fiche véhicule ou les maintenances.

@component('mail::button', ['url' => route('admin.vehicles.edit', $vehicle->id)])
Voir le véhicule
@endcomponent

Cordialement,  
{{ config('app.name') }}
@endcomponent
