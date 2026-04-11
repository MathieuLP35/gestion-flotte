<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import useDate from '@/Composables/useDate';
import { Head, router } from '@inertiajs/vue3'

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
    <Head title="Covoiturages trouvés" />

    <AuthenticatedLayout>
        <div class="py-10 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="mb-10 lg:flex lg:items-center lg:justify-between">
                    <div>
                        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Covoiturages trouvés</h1>
                        <p class="mt-2 text-sm text-gray-600">Consultez les trajets disponibles correspondants à votre recherche.</p>
                    </div>
                </div>

                <div v-if="carpoolings.length === 0" class="bg-white border-2 border-dashed border-gray-200 rounded-2xl p-12 text-center text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900">Aucun covoiturage disponible</h3>
                    <p class="mt-1">Modifiez vos critères de recherche depuis votre tableau de bord.</p>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="carpooling in carpoolings" :key="carpooling.id" class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden transform transition duration-200 hover:shadow-soft flex flex-col">
                        <div class="p-6 flex-grow">
                            <!-- Conducteur et Statut -->
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <div class="h-10 w-10 flex-shrink-0 bg-sparkotto-purple text-white rounded-full flex items-center justify-center font-bold text-sm">
                                        {{ carpooling.driver.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ carpooling.driver.name }}</p>
                                        <p class="text-xs text-gray-500">Conducteur</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Trajet -->
                            <div class="mb-5">
                                <h3 class="text-lg font-bold text-gray-900">
                                    {{ carpooling.depart }} <span class="text-gray-400">→</span> {{ carpooling.destination }}
                                </h3>
                                <div class="mt-3 space-y-2">
                                    <div class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        <span>Départ : <span class="font-medium text-gray-900">{{ formatDate(carpooling.date_debut) }}</span></span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                        <span>Retour : <span class="font-medium text-gray-900">{{ formatDate(carpooling.date_fin) }}</span></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Véhicule -->
                            <div class="bg-gray-50 rounded-lg p-3 text-sm flex justify-between items-center mb-2">
                                <div class="flex items-center text-gray-700 font-medium">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                    {{ carpooling.vehicle.modele }}
                                </div>
                                
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
                            </div>
                        </div>

                        <!-- Action de rejoindre -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 justify-center flex">
                            <button @click="joinCarpool(carpooling.id)" class="w-full justify-center inline-flex items-center px-4 py-2 bg-sparkotto-purple text-white text-sm font-semibold rounded-lg shadow-sm hover:bg-sparkotto-purple-hover focus:outline-none transition-colors">
                                Demander à rejoindre
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
