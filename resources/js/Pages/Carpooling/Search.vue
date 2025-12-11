<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import useDate from '@/Composables/useDate';

const props = defineProps({
    carpoolings: Array,
});

const { formatDate } = useDate();

function joinCarpooling(carpoolingId) {
    Inertia.post(route('carpooling.join', { id: carpoolingId }),
    {}, {
        onSuccess: () => {
            alert('Vous avez rejoint le covoiturage avec succès !');
        }
    });
}


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
                                <Link :href="route('reservations.edit', carpooling.id)" class="inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Rejoindre
                                </Link>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </AuthenticatedLayout>
</template>