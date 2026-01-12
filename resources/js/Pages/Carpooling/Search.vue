<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import useDate from '@/Composables/useDate';
import { router } from '@inertiajs/vue3'

const props = defineProps({
    carpoolings: Array,
});

const { formatDate } = useDate();

// Fonction pour rejoindre un covoiturage (devient passager)
const joinCarpool = (reservationId) => {
    router.post(route('passengers.store'), {
        reservation_id: reservationId
    }, {
        onError: (errors) => {
            console.error(errors);
        }
    });
};


</script>
<template>
    <Head title="Covoiturage" />

    <AuthenticatedLayout>
        <div class="p-8">
            <div class="max-w-7xl mx-auto bg-white p-8 rounded-lg">
                <div class="mb-12 text-center">
                    <h1 class="text-5xl font-bold text-gray-900 mb-3">Covoiturages disponibles</h1>
                    <p class="text-gray-600 text-lg">Trouvez et rejoignez un covoiturage facilement</p>
                </div>

                <div v-if="carpoolings.length === 0" class="text-center py-16">
                    <p class="text-gray-500 text-xl">Aucun covoiturage disponible</p>
                </div>

                <ul v-else class="space-y-6">
                    <li v-for="carpooling in carpoolings" :key="carpooling.id" class="p-4 border border-gray-200 rounded-lg shadow-sm">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex-grow">
                                <h3 class="text-lg font-semibold text-indigo-700 mb-2">
                                    {{ carpooling.depart }} → {{ carpooling.destination }}
                                </h3>
                                <p class="text-sm text-gray-600">
                                    Départ: {{ formatDate(carpooling.date_debut) }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    Retour : {{ formatDate(carpooling.date_fin) }}
                                </p>
                                <p class="text-sm text-gray-500 flex items-center gap-2">
                                    <span>Véhicule: {{ carpooling.vehicle.modele }} ({{ carpooling.vehicle.immatriculation }})</span>
                                    <span v-if="carpooling.vehicle.energie === 'electrique' || carpooling.vehicle.energie === 'hybride'" 
                                          class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold"
                                          :class="{
                                            'bg-green-100 text-green-800': carpooling.vehicle.energie === 'electrique',
                                            'bg-emerald-100 text-emerald-800': carpooling.vehicle.energie === 'hybride'
                                          }"
                                          title="Trajet écologique">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                                        </svg>
                                        <span v-if="carpooling.vehicle.energie === 'electrique'">Électrique</span>
                                        <span v-else>Hybride</span>
                                    </span>
                                </p>
                            </div>
                            <div class="mt-4 sm:mt-0 sm:ml-4 sm:flex-shrink-0 space-x-2">
                                <button @click="joinCarpool(carpooling.id)" class="mt-3 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-md shadow-md transition ease-in-out duration-150">
                                    Rejoindre ce trajet
                                </button>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
