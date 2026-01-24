<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const props = defineProps({
    reservations: { type: Array, default: () => [] },
});

const mapContainer = ref(null);
let map = null;
let markersLayer = null;

const statusLabels = {
    'en attente': 'En attente',
    'validé': 'Validé',
    'en cours': 'En cours',
    'à retourner': 'À retourner',
    'terminé': 'Terminé',
    'annulé': 'Annulé',
};

function buildMarkers() {
    if (!map) return;
    if (markersLayer) {
        map.removeLayer(markersLayer);
    }
    markersLayer = L.layerGroup().addTo(map);

    const bounds = [];
    const depIcon = L.divIcon({
        html: '<div class="w-6 h-6 bg-emerald-500 rounded-full border-2 border-white shadow"></div>',
        iconSize: [24, 24],
        iconAnchor: [12, 12],
        className: 'bg-transparent border-0',
    });
    const arrIcon = L.divIcon({
        html: '<div class="w-6 h-6 bg-red-500 rounded-full border-2 border-white shadow"></div>',
        iconSize: [24, 24],
        iconAnchor: [12, 12],
        className: 'bg-transparent border-0',
    });

    for (const r of props.reservations || []) {
        const reservationUrl = typeof route === 'function' ? route('reservations.show', r.id) : `/reservations/${r.id}`;
        const popup = `<div class="text-sm min-w-[180px]">
            <p class="font-semibold">${r.depart || '—'} → ${r.destination || '—'}</p>
            <p class="text-gray-600">${statusLabels[r.statut] || r.statut}</p>
            <a href="${reservationUrl}" class="text-indigo-600 hover:underline mt-1 inline-block">Voir la réservation</a>
        </div>`;

        if (r.depart_latitude != null && r.depart_longitude != null) {
            const latlng = [parseFloat(r.depart_latitude), parseFloat(r.depart_longitude)];
            L.marker(latlng, { icon: depIcon }).addTo(markersLayer).bindPopup(popup);
            bounds.push(latlng);
        }
        if (r.destination_latitude != null && r.destination_longitude != null) {
            const latlng = [parseFloat(r.destination_latitude), parseFloat(r.destination_longitude)];
            L.marker(latlng, { icon: arrIcon }).addTo(markersLayer).bindPopup(popup);
            bounds.push(latlng);
        }
    }

    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [24, 24], maxZoom: 12 });
    } else {
        map.setView([46.8, 2.5], 6);
    }
}

onMounted(() => {
    if (!mapContainer.value) return;
    map = L.map(mapContainer.value).setView([46.8, 2.5], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
    }).addTo(map);
    delete L.Icon.Default.prototype._getIconUrl;
    L.Icon.Default.mergeOptions({
        iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
        iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
    });
    buildMarkers();
});

onUnmounted(() => {
    if (map) {
        map.remove();
        map = null;
    }
});

watch(() => props.reservations, () => {
    buildMarkers();
}, { deep: true });
</script>

<template>
    <div class="rounded-xl border border-gray-200 overflow-hidden bg-white">
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">Carte des trajets</h3>
            <span class="text-xs text-gray-500">● Départ &nbsp; ● Destination</span>
        </div>
        <div ref="mapContainer" class="h-[320px] w-full"></div>
        <p v-if="!reservations?.length" class="px-4 pb-3 text-center text-xs text-gray-400">Aucun trajet avec coordonnées GPS</p>
    </div>
</template>
