@component('mail::message')
# Alerte entretien véhicule

Le véhicule **{{ $vehicle->modele }}** ({{ $vehicle->immatriculation }}) nécessite un entretien.

**Kilométrage actuel :** {{ number_format($vehicle->kilometrage, 0, ',', ' ') }} km  
**Intervalle révision :** {{ number_format($vehicle->service_interval_km, 0, ',', ' ') }} km
**Distance avant révision :** {{ number_format($vehicle->km_until_next_service, 0, ',', ' ') }} km

Merci de planifier son entretien et de mettre à jour son historique.

@component('mail::button', ['url' => route('admin.maintenances.show', $vehicle->id)])
Historique d'entretien
@endcomponent

Cordialement,  
{{ config('app.name') }}
@endcomponent
