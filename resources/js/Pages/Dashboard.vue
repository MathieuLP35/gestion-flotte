<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

// 1. Récupérer les props envoyées par le contrôleur
const props = defineProps({
    reservationsAsDriver: Array,
    reservationsAsPassenger: Array,
    geocoding: {
        type: Object,
        default: () => ({
            nominatimEnabled: false,
        }),
    },
});

// 2. Fonction pour annuler sa propre place de passager
const cancelPassenger = (passengerId) => {
    if (confirm("Voulez-vous vraiment annuler votre place sur ce trajet ?")) {
        router.delete(route('passengers.destroy', passengerId), {
            preserveScroll: true, // Pour ne pas remonter en haut de la page
            onSuccess: () => {
                // Vous pourriez afficher un "toast" de succès ici
            },
        });
    }
};

// Fonction simple pour formater la date
const formatDate = (dateString) => {
    const date = new Date(dateString);

    // Format UTC (date)
    const utcFormatter = new Intl.DateTimeFormat('fr-FR', {
        dateStyle: 'medium',
        timeZone: 'UTC',
    });

    // Format local (heure)
    const localFormatter = new Intl.DateTimeFormat('fr-FR', {
        timeStyle: 'short',
        timeZone: 'UTC',
    });

    const datePart = utcFormatter.format(date);
    const timePart = localFormatter.format(date);

    return `${datePart} à ${timePart}`;
};

function deleteReservation(id) {
  if (confirm('Êtes-vous sûr de vouloir supprimer cette réservation ?')) {
    router.delete(route('reservations.destroy', id), {
      preserveScroll: true, // Garde la position du scroll
    });
  }
}

const form = useForm({
    departure: '',        // saisie libre
    destination: '',      // saisie libre
    departureSelected: null,   // sélectionnée
    destinationSelected: null, // sélectionnée
    departureDate: '',
    arrivalDate: '',
});

const searchCarpooling = () => {
    if (!form.departureSelected) {
        return;
    }
    if (!form.destinationSelected) {
        return;
    }

    form.post(route('carpooling.search'), {
        preserveState: true,
        onSuccess: () => {
            // Réinitialiser les sélections si tu veux
            // form.departureSelected = null;
            // form.destinationSelected = null;
        },
    });
};

const suggestionsDeparture = ref([]);
const suggestionsDestination = ref([]);
const isLoadingDeparture = ref(false);
const isLoadingDestination = ref(false);
const cache = {};

const fetchSuggestions = async (query, type) => {
    if (!query || query.length < 4) {
        if (type === 'departure') suggestionsDeparture.value = [];
        if (type === 'destination') suggestionsDestination.value = [];
        return;
    }

    const cacheKey = `${query}_${type}`;
    if (cache[cacheKey] && Date.now() - cache[cacheKey].timestamp < 5 * 60 * 1000) { // 5 min
        if (type === 'departure') suggestionsDeparture.value = cache[cacheKey].data;
        if (type === 'destination') suggestionsDestination.value = cache[cacheKey].data;
        return;
    }


    try {
        if (type === 'departure') isLoadingDeparture.value = true;
        if (type === 'destination') isLoadingDestination.value = true;

        let suggestions = [];

        // 🔍 1. Essayer d'abord avec API Adresse.gouv.fr
        const adresseResponse = await fetch(`https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(query)}&limit=3`);
        const adresseData = await adresseResponse.json();

        suggestions = adresseData.features.map(f => ({
            label: f.properties.label,
            citycode: f.properties.citycode,
            city: f.properties.city,
            postcode: f.properties.postcode,
            street: f.properties.street || '',
            type: f.properties.type,
            lat: f.geometry.coordinates[1],
            lng: f.geometry.coordinates[0],
            source: 'adresse_gouv',
        }));

        // 🔍 2. Si peu de résultats (moins de 2) ET Nominatim activé → essayer avec Nominatim
        if (suggestions.length < 2 && props.geocoding.nominatimEnabled) {
            const nominatimUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=3&countrycodes=FR&addressdetails=1`;

            const nominatimResponse = await fetch(nominatimUrl);
            const nominatimData = await nominatimResponse.json();

            const nominatimSuggestions = nominatimData.map(f => ({
                label: `${f.address.office ? f.address.office + ' ' : ''}, ${f.address.house_number ? f.address.house_number + ' ' : ''}${f.address.road ? f.address.road + ', ' : ''}${f.address.postcode ? f.address.postcode + ' ' : ''}${f.address.city || f.address.town || f.address.village || ''}`.trim().replace(/^,|,$/g, ''),
                city: f.address?.city || f.address?.town || f.address?.village || '',
                postcode: f.address?.postcode || '',
                street: f.address?.road || '',
                type: f.type || '',
                lat: parseFloat(f.lat),
                lng: parseFloat(f.lon),
                source: 'nominatim',
            }));

            suggestions = [...suggestions, ...nominatimSuggestions];
        }

        if (type === 'departure') {
            suggestionsDeparture.value = suggestions;
        } else {
            suggestionsDestination.value = suggestions;
        }

        // Enregistrer dans le cache
        cache[cacheKey] = {
            data: suggestions,
            timestamp: Date.now(),
        };

        // Sauvegarder dans localStorage (optionnel)
        localStorage.setItem('geocodingCache', JSON.stringify(cache));
    } catch (error) {
        console.error('Erreur lors de la recherche d’adresses :', error);
        if (type === 'departure') suggestionsDeparture.value = [];
        if (type === 'destination') suggestionsDestination.value = [];
    } finally {
        if (type === 'departure') isLoadingDeparture.value = false;
        if (type === 'destination') isLoadingDestination.value = false;
    }
};

</script>

<template>
    <Head title="Mes Trajets" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto mb-6 bg-white p-6 rounded-lg shadow-sm">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Recherche de Covoiturage</h2>
                <form @submit.prevent="searchCarpooling" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                    <div class="relative">
                        <label for="departure" class="block text-sm font-semibold text-gray-900 mb-2">Départ</label>
                        <input
                            type="text"
                            id="departure"
                            v-model="form.departure"
                            @input="fetchSuggestions(form.departure, 'departure')"
                            placeholder="Ex: Rennes, Bruz, 35000..."
                            class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                            required
                        />
                        <div v-if="isLoadingDeparture" class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-b-xl shadow-md p-2 text-sm text-gray-500">
                            Recherche en cours...
                        </div>
                        <ul v-if="suggestionsDeparture.length > 0" class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-b-xl shadow-md max-h-60 overflow-y-auto z-10">
                            <li
                                v-for="suggestion in suggestionsDeparture"
                                :key="suggestion.label"
                                @click="form.departureSelected = suggestion; form.departure = suggestion.label; suggestionsDeparture = []"
                                class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm flex items-center justify-between"
                            >
                                <span>{{ suggestion.label }}</span>
                                <svg v-if="suggestion.source === 'nominatim'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.995 1.995 0 01-2.828 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.995 1.995 0 01-2.828 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                </svg>
                            </li>
                        </ul>
                        <div v-if="form.errors.departure" class="mt-2 text-sm text-red-600">
                            {{ form.errors.departure }}
                        </div>
                    </div>
                    <div class="relative">
                        <label for="destination" class="block text-sm font-semibold text-gray-900 mb-2">Destination</label>
                        <input
                            type="text"
                            id="destination"
                            v-model="form.destination"
                            @input="fetchSuggestions(form.destination, 'destination')"
                            placeholder="Ex: Pontivy, Nantes, 56000..."
                            class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                            required
                        />
                        <div v-if="isLoadingDestination" class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-b-xl shadow-md p-2 text-sm text-gray-500">
                            Recherche en cours...
                        </div>
                        <ul v-if="suggestionsDestination.length > 0" class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-b-xl shadow-md max-h-60 overflow-y-auto z-10">
                            <li
                                v-for="suggestion in suggestionsDestination"
                                :key="suggestion.label"
                                @click="form.destinationSelected = suggestion; form.destination = suggestion.label; suggestionsDestination = []"
                                class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm"
                            >
                                <span>{{ suggestion.label }}</span>
                                <svg v-if="suggestion.source === 'nominatim'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.995 1.995 0 01-2.828 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.995 1.995 0 01-2.828 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                </svg>
                            </li>
                        </ul>
                        <div v-if="form.errors.destination" class="mt-2 text-sm text-red-600">
                            {{ form.errors.destination }}
                        </div>
                    </div>
                    <div>
                        <label for="departureDate" class="block text-sm font-semibold text-gray-900 mb-2">Date de départ</label>
                        <input type="date" id="departureDate" v-model="form.departureDate" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" required />
                        <div v-if="form.errors.departureDate" class="mt-2 text-sm text-red-600">
                            {{ form.errors.departureDate }}
                        </div>
                    </div>
                    <div>
                        <label for="arrivalDate" class="block text-sm font-semibold text-gray-900 mb-2">Date de retour</label>
                        <input type="date" id="arrivalDate" v-model="form.arrivalDate" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" />
                        <div v-if="form.errors.arrivalDate" class="mt-2 text-sm text-red-600">
                            {{ form.errors.arrivalDate }}
                        </div>
                    </div>
                    <div>
                        <button type="submit" class="w-full px-6 py-3 bg-indigo-600 border border-transparent rounded-xl font-bold text-white uppercase hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-300 transition">Rechercher</button>
                    </div>
                </form>
            </div>
            <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 md:p-8 text-gray-900">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-gray-800">
                                Mes Trajets (Conducteur)
                            </h2>
                            <Link :href="route('reservations.create')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-md shadow-md transition ease-in-out duration-150">
                                Nouveau trajet
                            </Link>
                        </div>

                        <div v-if="reservationsAsDriver.length === 0" class="text-center text-gray-500 p-4">
                            Vous n'avez encore aucun trajet planifié en tant que conducteur.
                        </div>

                        <ul v-else class="space-y-6">
                            <li v-for="resa in reservationsAsDriver" :key="resa.id" class="p-4 border border-gray-200 rounded-lg shadow-sm">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex-grow">
                                        <h3 class="text-lg font-semibold text-indigo-700 mb-2">
                                            {{ resa.depart }} → {{ resa.destination }} 
                                        </h3>
                                        <p class="text-sm text-gray-600">
                                            Départ: {{ formatDate(resa.date_debut) }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            Retour : {{ formatDate(resa.date_fin) }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            Véhicule: {{ resa.vehicle.modele }} ({{ resa.vehicle.immatriculation }})
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            Statut:
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                    :class="{
                                                    'bg-yellow-100 text-yellow-800': resa.statut === 'en attente',
                                                    'bg-green-100 text-green-800': resa.statut === 'validé',
                                                    'bg-red-100 text-red-800': resa.statut === 'annulé',
                                                    'bg-gray-100 text-gray-800': !['en attente', 'validé', 'annulé'].includes(resa.statut)
                                                    }">
                                                {{ resa.statut }}
                                            </span>
                                        </p>
                                    </div>
                                    <div class="mt-4 sm:mt-0 sm:ml-4 sm:flex-shrink-0 space-x-2">
                                        <Link :href="route('reservations.edit', resa.id)" class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                        </Link>
                                        <button @click="deleteReservation(resa.id)" class="inline-flex items-center px-3 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 1-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="mt-4 border-t border-gray-200 pt-4">
                                    <h4 class="text-sm font-medium text-gray-700">
                                        Passagers ({{ resa.passengers.length }}/{{ resa.vehicle.nbr_places - 1 }})
                                    </h4>
                                    <div v-if="resa.passengers.length === 0" class="text-sm text-gray-500 mt-2">
                                        Aucun passager pour ce trajet.
                                    </div>
                                    <div v-else class="flex flex-wrap gap-2 mt-2">
                                        <span v-for="p in resa.passengers" :key="p.id" class="px-2.5 py-0.5 rounded-full text-xs font-medium"
                                              :class="{ 
                                                'bg-green-100 text-green-800': p.statut === 'confirme', 
                                                'bg-yellow-100 text-yellow-800': p.statut === 'en_attente', 
                                                'bg-red-100 text-red-800': p.statut === 'refuse' 
                                              }">
                                            {{ p.user.name }} ({{ p.statut }})
                                        </span>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 md:p-8 text-gray-900">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">
                            Mes Trajets (Passager)
                        </h2>

                        <div v-if="reservationsAsPassenger.length === 0" class="text-center text-gray-500 p-4">
                            Vous n'êtes encore passager d'aucun trajet.
                        </div>

                        <ul v-else class="space-y-6">
                            <Link v-for="pass in reservationsAsPassenger" :key="pass.id" :href="route('reservations.show', pass.reservation.id)" class="p-4 border border-gray-200 rounded-lg flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-indigo-700">
                                        {{ pass.reservation.destination }}
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        Conducteur: {{ pass.reservation.driver.name }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Départ: {{ formatDate(pass.reservation.date_debut) }}
                                    </p>
                                    <div class="mt-2">
                                        <span class="text-sm font-medium">Statut: </span>
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium"
                                              :class="{ 
                                                'bg-green-100 text-green-800': pass.statut === 'confirme', 
                                                'bg-yellow-100 text-yellow-800': pass.statut === 'en_attente',
                                                'bg-red-100 text-red-800': pass.statut === 'refuse' 
                                              }">
                                            {{ pass.statut }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="mt-4 sm:mt-0 sm:ml-4 sm:flex-shrink-0">
                                    <button @click="cancelPassenger(pass.id)" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Annuler ma place
                                    </button>
                                </div>
                            </Link>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>