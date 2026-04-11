<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import useGeocoding from '@/Composables/useGeocoding';
import useDate from '@/Composables/useDate';
import useReservation from '@/Composables/useReservation';
import { getCurrentInstance } from 'vue';

const props = defineProps({
    reservationsAsDriver: Array,
    reservationsAsPassenger: Array,
});

const { formatDate } = useDate();

// Traduction des statuts (valeurs venant de la BDD)
const { proxy } = getCurrentInstance();
const statusLabel = (statut) => {
    const t = proxy.$t.bind(proxy);
    const map = {
        'en attente': t('status.en_attente'),
        'validé': t('status.valide'),
        'en cours': t('status.en_cours'),
        'à retourner': t('status.a_retourner'),
        'annulé': t('status.annule'),
        'terminé': t('status.termine'),
        'confirme': t('status.confirme'),
        'en_attente': t('status.en_attente_psg'),
        'refuse': t('status.refuse'),
    };
    return map[statut] ?? statut;
};
const { cancelPassenger, deleteReservation } = useReservation();
const { suggestionsDeparture, suggestionsDestination, isLoadingDeparture, isLoadingDestination, fetchSuggestions } = useGeocoding();

const startTrip = (reservationId) => {
    router.post(route('reservations.start', reservationId), {}, {
        preserveScroll: true,
        onSuccess: () => {},
        onError: (errors) => {
            console.error('Erreur lors du lancement du trajet:', errors);
        }
    });
};

const endTrip = (reservationId) => {
    router.post(route('reservations.end', reservationId), {}, {
        preserveScroll: true,
        onSuccess: () => {},
        onError: (errors) => {
            console.error('Erreur lors de la fin du trajet:', errors);
        }
    });
};

const handleReturnVehicle = (reservation) => {
    if (reservation.statut === 'en cours') {
        router.post(route('reservations.end', reservation.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                router.visit(route('reservations.return.form', reservation.id));
            },
            onError: (errors) => {
                console.error('Erreur lors de la fin du trajet:', errors);
            }
        });
    } else {
        router.visit(route('reservations.return.form', reservation.id));
    }
};

const form = useForm({
    departure: '',
    destination: '',
    departureSelected: null,
    destinationSelected: null,
    departureDate: '',
    arrivalDate: '',
});

const searchCarpooling = () => {
    form.clearErrors();
    let hasError = false;

    if (!form.departureSelected) {
        form.setError('departure', 'Veuillez choisir une adresse ciblée.');
        hasError = true;
    }
    if (!form.destinationSelected) {
        form.setError('destination', 'Veuillez choisir une adresse ciblée.');
        hasError = true;
    }

    if (hasError) return;

    form.post(route('carpooling.search'), {
        preserveState: true,
    });
};
</script>

<template>
    <Head :title="$t('dashboard.title')" />

    <AuthenticatedLayout>
        <div class="py-10 bg-gray-50 min-h-screen">
            
            <!-- EN-TETE ET RECHERCHE (Clarté et Action Immédiate) -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-10">
                <div class="bg-white p-8 rounded-2xl shadow-soft border border-gray-100">
                    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
                        <div>
                            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ $t('dashboard.search_heading') }}</h1>
                            <p class="mt-2 text-sm text-gray-600">{{ $t('dashboard.search_desc') }}</p>
                        </div>
                        <div class="mt-4 md:mt-0">
                            <Link :href="route('reservations.create')" class="inline-flex items-center justify-center px-5 py-3 bg-sparkotto-purple hover:bg-sparkotto-purple-hover text-white text-sm font-semibold rounded-xl shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-sparkotto-purple focus:ring-offset-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                {{ $t('dashboard.propose_trip') }}
                            </Link>
                        </div>
                    </div>
                    
                    <div v-if="form.errors.departure || form.errors.destination" class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">{{ $t('dashboard.precision_required') }}</h3>
                                <ul class="mt-1 list-disc pl-5 text-sm text-red-700">
                                    <li v-if="form.errors.departure">{{ form.errors.departure }}</li>
                                    <li v-if="form.errors.destination">{{ form.errors.destination }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Moteur de réservation type "Trainline/Uber" -->
                    <form @submit.prevent="searchCarpooling" class="flex flex-col lg:flex-row gap-4 items-end bg-gray-50 p-4 rounded-xl border border-gray-100">
                        <div class="relative w-full lg:w-1/3">
                            <label for="departure" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">{{ $t('res.departure') }}</label>
                            <input
                                type="text"
                                id="departure"
                                v-model="form.departure"
                                @input="form.departureSelected = null; fetchSuggestions(form.departure, 'departure')"
                                placeholder="Adresse, Ville, CP..."
                                class="w-full bg-white border border-gray-200 rounded-lg px-4 py-3 text-gray-900 shadow-sm focus:ring-2 focus:ring-sparkotto-purple focus:border-sparkotto-purple transition"
                                required
                            />
                            <ul v-if="suggestionsDeparture.length > 0" class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto z-20 mt-1">
                                <li
                                    v-for="suggestion in suggestionsDeparture"
                                    :key="suggestion.label"
                                    @click="form.departureSelected = suggestion; form.departure = suggestion.label; suggestionsDeparture = []"
                                    class="px-4 py-3 hover:bg-gray-50 cursor-pointer text-sm flex items-center border-b border-gray-50 last:border-0"
                                >
                                    <span class="truncate">{{ suggestion.label }}</span>
                                </li>
                            </ul>
                        </div>

                        <div class="relative w-full lg:w-1/3">
                            <label for="destination" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">{{ $t('res.destination') }}</label>
                            <input
                                type="text"
                                id="destination"
                                v-model="form.destination"
                                @input="form.destinationSelected = null; fetchSuggestions(form.destination, 'destination')"
                                placeholder="Adresse, Ville, CP..."
                                class="w-full bg-white border border-gray-200 rounded-lg px-4 py-3 text-gray-900 shadow-sm focus:ring-2 focus:ring-sparkotto-purple focus:border-sparkotto-purple transition"
                                required
                            />
                            <ul v-if="suggestionsDestination.length > 0" class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto z-20 mt-1">
                                <li
                                    v-for="suggestion in suggestionsDestination"
                                    :key="suggestion.label"
                                    @click="form.destinationSelected = suggestion; form.destination = suggestion.label; suggestionsDestination = []"
                                    class="px-4 py-3 hover:bg-gray-50 cursor-pointer text-sm border-b border-gray-50 last:border-0"
                                >
                                    <span class="truncate">{{ suggestion.label }}</span>
                                </li>
                            </ul>
                        </div>

                        <div class="w-full lg:w-1/6">
                            <label for="departureDate" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">{{ $t('dashboard.aller') }}</label>
                            <input type="date" id="departureDate" v-model="form.departureDate" class="w-full bg-white border border-gray-200 rounded-lg px-4 py-3 text-gray-900 shadow-sm focus:ring-2 focus:ring-sparkotto-purple focus:border-sparkotto-purple transition" required />
                        </div>
                        
                        <div class="w-full lg:w-1/6">
                            <label for="arrivalDate" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">{{ $t('dashboard.retour_optional') }}</label>
                            <input type="date" id="arrivalDate" v-model="form.arrivalDate" class="w-full bg-white border border-gray-200 rounded-lg px-4 py-3 text-gray-900 shadow-sm focus:ring-2 focus:ring-sparkotto-purple focus:border-sparkotto-purple transition" />
                        </div>

                        <div class="w-full lg:w-auto mt-4 lg:mt-0">
                            <button type="submit" class="w-full lg:w-auto h-[46px] px-8 bg-gray-900 border border-transparent rounded-lg font-bold text-white hover:bg-gray-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 transition-all">
                                {{ $t('dashboard.search_btn') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- SECTIONS MES TRAJETS -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- COLONNE: MES TRAJETS (CONDUCTEUR) -->
                <div>
                    <h2 class="text-xl font-bold text-gray-900 flex items-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-sparkotto-purple" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4" />
                        </svg>
                        {{ $t('dashboard.driver_section') }}
                    </h2>

                    <div v-if="reservationsAsDriver.length === 0" class="bg-white border-2 border-dashed border-gray-200 rounded-2xl p-8 text-center text-gray-500">
                        {{ $t('dashboard.no_driver_trips') }}
                    </div>

                    <div v-else class="space-y-4">
                        <!-- CARTES DE TRAJETS -->
                        <div v-for="resa in reservationsAsDriver" :key="resa.id" class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden transform transition duration-200 hover:shadow-soft">
                            <div class="p-5">
                                <!-- En-tête Statut -->
                                <div class="flex justify-between items-start mb-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                            :class="{
                                            'bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-600/20': resa.statut === 'en attente',
                                            'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20': resa.statut === 'validé',
                                            'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20': resa.statut === 'en cours',
                                            'bg-orange-50 text-orange-700 ring-1 ring-inset ring-orange-600/20': resa.statut === 'à retourner',
                                            'bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20': resa.statut === 'annulé',
                                            'bg-gray-100 text-gray-700 ring-1 ring-inset ring-gray-600/20': resa.statut === 'terminé',
                                            'bg-gray-100 text-gray-600': !['en attente', 'validé', 'en cours', 'à retourner', 'annulé', 'terminé'].includes(resa.statut)
                                            }">
                                        {{ statusLabel(resa.statut) }}
                                    </span>
                                    
                                    <div class="flex space-x-1">
                                        <Link :href="route('reservations.edit', resa.id)" class="text-gray-400 hover:text-sparkotto-purple transition" :title="$t('action.edit')">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" /></svg>
                                        </Link>
                                        <button @click="deleteReservation(resa.id)" class="text-gray-400 hover:text-red-600 transition" :title="$t('dashboard.cancel_tooltip')">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 01-7.5 0" /></svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Informations Trajet -->
                                <div class="mb-4">
                                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-sparkotto-purple transition">
                                        {{ resa.depart }} <span class="text-gray-400">→</span> {{ resa.destination }}
                                    </h3>
                                    <div class="mt-2 text-sm text-gray-600 space-y-1">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            {{ $t('dashboard.trip_departure') }} <span class="font-medium text-gray-900 ml-1">{{ formatDate(resa.date_debut) }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                            {{ $t('dashboard.trip_return') }} <span class="font-medium text-gray-900 ml-1">{{ formatDate(resa.date_fin) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Véhicule et Passagers (Format compact) -->
                                <div class="bg-gray-50 rounded-lg p-3 text-sm flex justify-between items-center">
                                    <div class="flex items-center text-gray-700 font-medium">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                        </svg>
                                        {{ resa.vehicle.modele }} ({{ resa.vehicle.immatriculation }})
                                    </div>
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                          <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                                        </svg>
                                        <span class="text-gray-700 font-medium">{{ resa.passengers.length }}/{{ resa.vehicle.nbr_places - 1 - resa.places_reservees_materiel }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Barre d'actions exclusives -->
                            <div v-if="resa.statut === 'validé' || (resa.statut === 'en cours' || resa.statut === 'à retourner') && !resa.date_retour" class="bg-gray-50 px-5 py-3 border-t border-gray-100 flex justify-end">
                                <button
                                    v-if="resa.statut === 'validé'"
                                    @click="startTrip(resa.id)"
                                    class="inline-flex items-center px-4 py-2 bg-sparkotto-purple text-white text-sm font-semibold rounded-lg shadow-sm hover:bg-sparkotto-purple-hover focus:outline-none transition-colors"
                                >
                                    {{ $t('dashboard.start_trip') }}
                                </button>
                                <button
                                    v-if="(resa.statut === 'en cours' || resa.statut === 'à retourner') && !resa.date_retour"
                                    @click="handleReturnVehicle(resa)"
                                    class="inline-flex items-center px-4 py-2 bg-gray-900 text-white text-sm font-semibold rounded-lg shadow-sm hover:bg-gray-800 focus:outline-none transition-colors"
                                >
                                    {{ $t('dashboard.return_vehicle') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- COLONNE: MES TRAJETS (PASSAGER) -->
                <div>
                    <h2 class="text-xl font-bold text-gray-900 flex items-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-sparkotto-yellow" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        {{ $t('dashboard.passenger_section') }}
                    </h2>

                    <div v-if="reservationsAsPassenger.length === 0" class="bg-white border-2 border-dashed border-gray-200 rounded-2xl p-8 text-center text-gray-500">
                        {{ $t('dashboard.no_passenger_trips') }}
                    </div>

                    <div v-else class="space-y-4">
                        <div v-for="pass in reservationsAsPassenger" :key="pass.id" class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden transform transition duration-200 hover:shadow-soft">
                            <Link :href="route('reservations.show', pass.reservation.id)" class="block p-5">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="text-lg font-bold text-gray-900 mb-1">
                                        {{ pass.reservation.depart }} <span class="text-gray-400">→</span> {{ pass.reservation.destination }}
                                    </h3>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                            :class="{
                                            'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20': pass.statut === 'confirme',
                                            'bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-600/20': pass.statut === 'en_attente',
                                            'bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20': pass.statut === 'refuse'
                                            }">
                                        {{ statusLabel(pass.statut) }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mb-3">{{ $t('dashboard.driver_label') }} <span class="font-medium text-gray-900">{{ pass.reservation.driver.name }}</span></p>
                                
                                <div class="mt-2 text-sm text-gray-600 flex flex-wrap gap-4">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        {{ formatDate(pass.reservation.date_debut) }}
                                    </div>
                                </div>
                            </Link>
                            <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 text-right">
                                <button @click.prevent="cancelPassenger(pass.id)" class="text-sm font-semibold text-red-600 hover:text-red-800 transition">
                                    {{ $t('dashboard.cancel_seat') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>