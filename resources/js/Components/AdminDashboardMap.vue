<script setup>
import { onMounted, onUnmounted, ref, watch, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

import 'leaflet.markercluster';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';

const props = defineProps({
    reservations: { type: Array, default: () => [] },
});

const mapContainer = ref(null);
const fullScreenContainer = ref(null);
const isFullScreen = ref(false);
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

function toggleFullScreen() {
    isFullScreen.value = !isFullScreen.value;
    // On attend que le DOM se mette à jour avant d'initialiser ou redimensionner
    nextTick(() => {
        if (isFullScreen.value) {
            initMap(fullScreenContainer.value);
        } else {
            initMap(mapContainer.value);
        }
    });
}

function initMap(container) {
    // Si une carte existe déjà, on la détruit proprement
    if (map) {
        map.remove();
        map = null;
    }

    if (!container) return;

    map = L.map(container).setView([46.8, 2.5], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
    }).addTo(map);

    buildMarkers();
}

function buildMarkers() {
    if (!map) return;

    if (markersLayer) {
        map.removeLayer(markersLayer);
    }

    markersLayer = L.markerClusterGroup({
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: false,
        zoomToBoundsOnClick: true,
        maxClusterRadius: 50,
        iconCreateFunction: function (cluster) {
            const childMarkers = cluster.getAllChildMarkers();
            let startCount = 0;
            let endCount = 0;

            childMarkers.forEach(marker => {
                if (marker.options.type === 'start') startCount++;
                if (marker.options.type === 'end') endCount++;
            });

            let clusterClass = '';
            let htmlContent = '';

            if (startCount > 0 && endCount > 0) {
                clusterClass = 'cluster-mixed';
                htmlContent = `<div class="flex items-center justify-center space-x-1 px-2">
                    <span class="text-emerald-300">●</span><span>${startCount}</span>
                    <span class="text-gray-400">|</span>
                    <span class="text-red-400">●</span><span>${endCount}</span>
                </div>`;
            } else if (startCount > 0) {
                clusterClass = 'cluster-start';
                htmlContent = `<div><span>${startCount}</span></div>`;
            } else {
                clusterClass = 'cluster-end';
                htmlContent = `<div><span>${endCount}</span></div>`;
            }

            return L.divIcon({
                html: htmlContent,
                className: `marker-cluster ${clusterClass}`,
                iconSize: startCount > 0 && endCount > 0 ? L.point(70, 40) : L.point(40, 40),
            });
        }
    });

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
            const marker = L.marker(latlng, { icon: depIcon, type: 'start' }).bindPopup(popup);
            markersLayer.addLayer(marker);
            bounds.push(latlng);
        }

        if (r.destination_latitude != null && r.destination_longitude != null) {
            const latlng = [parseFloat(r.destination_latitude), parseFloat(r.destination_longitude)];
            const marker = L.marker(latlng, { icon: arrIcon, type: 'end' }).bindPopup(popup);
            markersLayer.addLayer(marker);
            bounds.push(latlng);
        }
    }

    map.addLayer(markersLayer);

    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [40, 40], maxZoom: 12 });
    }
}

onMounted(() => {
    initMap(mapContainer.value);
});

onUnmounted(() => {
    if (map) map.remove();
});

watch(() => props.reservations, () => {
    buildMarkers();
}, { deep: true });
</script>

<template>
    <div class="rounded-xl border border-gray-200 overflow-hidden bg-white shadow-sm">
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between bg-gray-50/50">
            <h3 class="text-sm font-semibold text-gray-900">Carte des trajets</h3>

            <div class="flex items-center space-x-4">
                <div class="hidden sm:flex items-center space-x-3">
                    <div class="flex items-center space-x-1">
                        <div class="w-2.5 h-2.5 bg-emerald-500 rounded-full border border-white"></div>
                        <span class="text-[10px] text-gray-500 uppercase tracking-wider font-medium">Départ</span>
                    </div>
                    <div class="flex items-center space-x-1">
                        <div class="w-2.5 h-2.5 bg-red-500 rounded-full border border-white"></div>
                        <span class="text-[10px] text-gray-500 uppercase tracking-wider font-medium">Arrivée</span>
                    </div>
                </div>

                <button
                    @click="toggleFullScreen"
                    class="p-1.5 hover:bg-gray-200 rounded-md transition-colors text-gray-600"
                    title="Agrandir la carte"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/>
                    </svg>
                </button>
            </div>
        </div>

        <div ref="mapContainer" class="h-[320px] w-full z-0"></div>

        <p v-if="!reservations?.length" class="px-4 py-3 text-center text-xs text-gray-400">
            Aucun trajet avec coordonnées GPS
        </p>

        <Teleport to="body">
            <div v-if="isFullScreen" class="fixed inset-0 z-[9999] bg-white flex flex-col">
                <div class="p-4 border-b flex justify-between items-center bg-white">
                    <h3 class="font-bold text-lg">Vue détaillée des trajets</h3>
                    <button
                        @click="toggleFullScreen"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition-colors"
                    >
                        Fermer
                    </button>
                </div>
                <div ref="fullScreenContainer" class="flex-grow w-full"></div>
            </div>
        </Teleport>
    </div>
</template>

<style>
/* Style des clusters (on garde ta logique) */
.marker-cluster {
    background-clip: padding-box;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.marker-cluster div {
    width: 100%;
    height: 30px;
    margin: 5px;
    text-align: center;
    border-radius: 15px;
    font: 12px "Helvetica Neue", Arial, Helvetica, sans-serif;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.cluster-start { background-color: rgba(16, 185, 129, 0.4); }
.cluster-start div { background-color: rgba(16, 185, 129, 0.9); }

.cluster-end { background-color: rgba(239, 68, 68, 0.4); }
.cluster-end div { background-color: rgba(239, 68, 68, 0.9); }

.cluster-mixed {
    background-color: rgba(245, 158, 11, 0.4);
    min-width: 65px !important;
}
.cluster-mixed div {
    background-color: rgba(245, 158, 11, 0.9);
    width: calc(100% - 10px);
}

.text-emerald-300 { color: #6ee7b7; }
.text-red-400 { color: #f87171; }
.text-gray-400 { color: #9ca3af; }
</style>
