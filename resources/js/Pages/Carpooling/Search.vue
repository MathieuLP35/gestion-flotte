<script setup>
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    carpoolings: Array,
});

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
    <AuthenticatedLayout>
        <div class="min-h-screen bg-white p-8">
            <div class="max-w-7xl mx-auto">
                <div class="mb-12">
                    <h1 class="text-5xl font-bold text-gray-900 mb-3">Covoiturages disponibles</h1>
                    <p class="text-gray-600 text-lg">Trouvez et rejoignez un covoiturage facilement</p>
                </div>
                
                <div v-if="carpoolings.length === 0" class="text-center py-16">
                    <p class="text-gray-500 text-xl">Aucun covoiturage disponible</p>
                </div>
                
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="carpooling in carpoolings" :key="carpooling.id" class="bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow overflow-hidden border border-gray-200">
                        <div class="bg-indigo-600 p-6 text-white">
                            <h2 class="text-xl font-bold">Départ → {{ carpooling.destination }}</h2>
                        </div>
                        
                        <div class="p-6 space-y-4">
                            <div class="flex justify-between text-sm">
                                <span class="font-semibold text-gray-700">Départ</span>
                                <span class="text-gray-600">{{ carpooling.date_debut }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="font-semibold text-gray-700">Arrivée</span>
                                <span class="text-gray-600">{{ carpooling.date_fin }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="font-semibold text-gray-700">Conducteur</span>
                                <span class="text-gray-600">{{ carpooling.driver.name }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="font-semibold text-gray-700">Places</span>
                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full font-bold text-xs">
                                    {{ carpooling.vehicle.nbr_places - carpooling.passengers.length - 1 }}
                                </span>
                            </div>
                        </div>
                        
                        <button 
                            @click="joinCarpooling(carpooling.id)"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 transition-colors"
                        >
                            Rejoindre
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>