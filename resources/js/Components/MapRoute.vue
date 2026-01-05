<script setup>
import { onMounted, ref } from 'vue';
import L from 'leaflet';
import 'leaflet-routing-machine';

// Importation du CSS de Leaflet
import 'leaflet/dist/leaflet.css';
import 'leaflet-routing-machine/dist/leaflet-routing-machine.css';

const props = defineProps({
    startCoords: Array, // [lat, lng]
    endCoords: Array    // [lat, lng]
});

const mapContainer = ref(null);
let map = null;

function openGPS() {
    const lat = props.endCoords[0];
    const lng = props.endCoords[1];
    const label = "Destination"; // Nom affiché sur le marqueur GPS
    
    // Détection de l'appareil
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    const isAndroid = /Android/.test(navigator.userAgent);

    if (isIOS) {
        // Sur iOS : 'maps://' ouvre Apple Maps, qui propose souvent Google Maps/Waze si installés
        // Format : maps://?q=LAT,LNG
        window.location.href = `maps://?q=${label}&ll=${lat},${lng}`;
    } 
    else if (isAndroid) {
        // Sur Android : 'geo:' déclenche le "Intent Chooser" du système
        // Cela affiche la liste : Google Maps, Waze, Citymapper, etc.
        window.location.href = `geo:0,0?q=${lat},${lng}(${label})`;
    } 
    else {
        // Sur PC / Desktop : On ouvre Google Maps dans un nouvel onglet
        const url = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;
        window.open(url, '_blank');
    }
}

onMounted(() => {

    // 1. Initialisation de la carte
    map = L.map(mapContainer.value).setView(props.startCoords, 13);

    // 2. Ajout des tuiles OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // 3. Configuration du trajet
    const routingControl = L.Routing.control({
        waypoints: [
            L.latLng(props.startCoords[0], props.startCoords[1]),
            L.latLng(props.endCoords[0], props.endCoords[1])
        ],
        lineOptions: {
            styles: [{ color: '#3b82f6', weight: 5 }] // Couleur bleue type Tailwind
        },
        router: L.Routing.osrmv1({
            serviceUrl: `https://router.project-osrm.org/route/v1`,
            language: 'fr',
            profile: 'driving' // Assure le trajet le plus court en termes de km
        }),
        show: true, // Affiche le panneau d'instructions
        addWaypoints: false, // Empêche le conducteur de modifier le trajet
        routeWhileDragging: false,
        createMarker: function() { return null; }, 
        formatter: new L.Routing.Formatter({
            language: 'fr',
            units: 'metric',
        })
    }).addTo(map);

    

    // Ajout des icônes pour le départ et l'arrivée
    const startIcon = L.divIcon({
        html: `<div class="flex flex-col items-center">
            <div class="w-8 h-8 bg-green-500 rounded-full border-2 border-white shadow-lg flex items-center justify-center">
                <span class="text-white font-bold text-sm">D</span>
            </div>
            <div class="w-0 h-0 border-l-4 border-r-4 border-t-4 border-l-transparent border-r-transparent border-t-green-500"></div>
        </div>`,
        iconSize: [32, 40],
        iconAnchor: [16, 40],
        className: 'custom-marker'
    });

    const endIcon = L.divIcon({
        html: `<div class="flex flex-col items-center">
            <div class="w-8 h-8 bg-red-500 rounded-full border-2 border-white shadow-lg flex items-center justify-center">
                <span class="text-white font-bold text-sm">A</span>
            </div>
            <div class="w-0 h-0 border-l-4 border-r-4 border-t-4 border-l-transparent border-r-transparent border-t-red-500"></div>
        </div>`,
        iconSize: [32, 40],
        iconAnchor: [16, 40],
        className: 'custom-marker'
    });

    L.marker(props.startCoords, { icon: startIcon }).addTo(map).bindPopup('Départ');
    L.marker(props.endCoords, { icon: endIcon }).addTo(map).bindPopup('Arrivée');

    // Stylisation du panneau d'instructions
    setTimeout(() => {
        const container = document.querySelector('.leaflet-routing-container');
        if (container) {
            container.style.backgroundColor = '#ffffff';
            container.style.borderRadius = '8px';
            container.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
            container.style.padding = '16px';
            container.style.fontFamily = 'system-ui, -apple-system, sans-serif';
        }
        
        const alternatives = document.querySelector('.leaflet-routing-alternatives-container');
        if (alternatives) {
            alternatives.style.marginTop = '12px';
        }
    }, 100);

    // Correction bug d'affichage des icônes par défaut de Leaflet avec Vite/Webpack
    delete L.Icon.Default.prototype._getIconUrl;
    L.Icon.Default.mergeOptions({
        iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
        iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
    });
});

</script>

<template>
    <div class="rounded-lg shadow-md overflow-hidden border border-gray-200">
        <div ref="mapContainer" class="h-[500px] w-full"></div>
    </div>
    <button 
        @click="openGPS"
        class="relative mt-4 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
    >
        📍 Ouvrir dans GPS
    </button>
</template>

<style>
/* Optionnel : masquer le panneau d'instructions si vous voulez juste la carte */
/* .leaflet-routing-container { display: none; } */
</style>