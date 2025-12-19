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
                                <p class="text-sm text-gray-500">
                                    Véhicule: {{ carpooling.vehicle.modele }} ({{ carpooling.vehicle.immatriculation }})
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
