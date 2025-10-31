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
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Mes Trajets
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 md:p-8 text-gray-900">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">
                            Mes Trajets (Conducteur)
                        </h2>

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

                        <ul v-else class="space-y-4">
                            <li v-for="pass in reservationsAsPassenger" :key="pass.id" class="p-4 border border-gray-200 rounded-lg flex flex-col sm:flex-row sm:items-center sm:justify-between">
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
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>