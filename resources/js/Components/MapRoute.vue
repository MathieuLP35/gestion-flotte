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

const routeDistance = ref('');
const routeTime = ref('');

const mapContainer = ref(null);
let map = null;

const openGPS = () => {
    const lat = props.endCoords[0];
    const lng = props.endCoords[1];
    const label = "Destination";
    
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    const isAndroid = /Android/.test(navigator.userAgent);

    if (isIOS) {
        window.location.href = `maps://?q=${label}&ll=${lat},${lng}`;
    } 
    else if (isAndroid) {
        window.location.href = `geo:0,0?q=${lat},${lng}(${label})`;
    } 
    else {
        const url = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;
        window.open(url, '_blank');
    }
};

onMounted(() => {

    // 1. Initialisation de la carte
    map = L.map(mapContainer.value).setView(props.startCoords, 13);

    // 2. Ajout des tuiles OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

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
            profile: 'driving'
        }),
        show: false, // On masque la liste d'instructions textuelles
        addWaypoints: false,
        routeWhileDragging: false,
        fitSelectedRoutes: false, // On le fait manuellement pour dézoomer
        createMarker: function() { return null; }
    }).addTo(map);

    routingControl.on('routesfound', function(e) {
        const routes = e.routes;
        const summary = routes[0].summary;
        
        routeDistance.value = (summary.totalDistance / 1000).toFixed(1) + ' km';
        
        // OSRM est par défaut environ 20% plus lent que la réalité (sans trafic). 
        // On applique un coefficient réseau routier pour équilibrer.
        const realisticTimeSeconds = summary.totalTime * 0.82; 
        const totalMinutes = Math.round(realisticTimeSeconds / 60);

        if (totalMinutes >= 60) {
            const h = Math.floor(totalMinutes / 60);
            const m = totalMinutes % 60;
            routeTime.value = `${h}h ${(m < 10 ? '0' : '')}${m}min`;
        } else {
            routeTime.value = `${totalMinutes} min`;
        }

        // On ajuste la carte à l'itinéraire avec une marge "padding" pour bien dézoomer
        map.fitBounds(L.latLngBounds(routes[0].coordinates), { padding: [50, 50] });
    });

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
    <div class="flex flex-col h-full w-full">
        <!-- Wrapper de la carte avec hauteur fixe claire pour simplifier Leaflet -->
        <div class="w-full h-[400px] relative z-0">
            <div ref="mapContainer" class="w-full h-full"></div>
        </div>
        
        <!-- Bouton GPS & Résumé -->
        <div class="p-4 bg-gray-50 border-t border-gray-100 flex flex-col gap-3">
            <div class="text-sm font-semibold text-gray-700 text-center">
                <span v-if="routeDistance && routeTime" class="flex items-center justify-center gap-2">
                    <span class="flex items-center"><svg class="w-4 h-4 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-1.1 0-2 .9-2 2v8c0 1.1.9 2 2 2h2m10 0a2 2 0 10-4 0m4 0a2 2 0 11-4 0m-10 0a2 2 0 10-4 0m4 0a2 2 0 11-4 0m8-8H4"/></svg> {{ routeDistance }}</span><span class="text-gray-300 mx-1">•</span><span class="flex items-center"><svg class="w-4 h-4 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> {{ routeTime }}</span>
                </span>
                <span v-else class="text-gray-400 italic font-normal">Calcul itinéraire...</span>
            </div>
            
            <button 
                @click="openGPS"
                class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-sparkotto-purple text-white text-sm font-bold rounded-xl shadow-sm hover:bg-sparkotto-purple-hover focus:outline-none transition-colors"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Ouvrir dans le GPS</span>
            </button>
        </div>
    </div>
</template>

<style>
/* Style personnalisé pour limiter la taille de la boîte d'instructions */
.leaflet-routing-container {
    max-height: 250px !important;
    max-width: 250px !important;
    overflow-y: auto !important;
    font-size: 11px !important;
    margin-top: 10px !important;
    margin-right: 10px !important;
    background-color: rgba(255, 255, 255, 0.95) !important;
    backdrop-filter: blur(4px);
    border-radius: 8px !important;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1) !important;
}

/* Cache l'élément si class -hide est appliquée via JS plus tard (optionnel) */
.leaflet-routing-container-hide {
    display: none !important;
}
</style>