<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

// 1. Récupérer les props envoyées par le contrôleur
const props = defineProps({
    reservationsAsDriver: Array,
    reservationsAsPassenger: Array,
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
    return new Date(dateString).toLocaleString('fr-FR', {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
};

</script>

<template>
    <Head title="Mes Trajets" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto mb-6 bg-white p-6 rounded-lg shadow-sm">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Recherche de Covoiturage</h2>
                <form @submit.prevent="searchCarpooling" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                    <div>
                        <label for="departure" class="block text-sm font-semibold text-gray-900 mb-2">Départ</label>
                        <input type="text" id="departure" v-model="departure" placeholder="Ex: Paris" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" required />
                    </div>
                    <div>
                        <label for="destination" class="block text-sm font-semibold text-gray-900 mb-2">Destination</label>
                        <input type="text" id="destination" v-model="destination" placeholder="Ex: Lyon" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" required />
                    </div>
                    <div>
                        <label for="departureDate" class="block text-sm font-semibold text-gray-900 mb-2">Date de départ</label>
                        <input type="date" id="departureDate" v-model="departureDate" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" required />
                    </div>
                    <div>
                        <label for="arrivalDate" class="block text-sm font-semibold text-gray-900 mb-2">Date de retour</label>
                        <input type="date" id="arrivalDate" v-model="arrivalDate" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" required />
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
                                        <h3 class="text-lg font-semibold text-indigo-700">
                                            {{ resa.destination }}
                                        </h3>
                                        <p class="text-sm text-gray-600">
                                            {{ formatDate(resa.date_debut) }}
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
                                    <div class="mt-4 sm:mt-0 sm:ml-4 sm:flex-shrink-0">
                                        <Link :href="route('reservations.edit', resa.id)" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Gérer le trajet
                                        </Link>
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