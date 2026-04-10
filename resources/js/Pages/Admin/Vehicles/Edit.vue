<script setup>
import { usePage, useForm, Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import useDate from '@/Composables/useDate';
import { ref } from 'vue';

// 1. Récupérer les props
const { vehicle } = usePage().props;

// 2. Créer une référence mutable
const vehicleRef = ref(vehicle);

// 3. Utiliser useDate
const { formatDate } = useDate();

// 4. Formulaire pour le véhicule
const form = useForm({
    modele: vehicle.modele || '',
    immatriculation: vehicle.immatriculation || '',
    km_initial: vehicle.km_initial || 0,
    emplacement: vehicle.emplacement || '',
    nbr_places: vehicle.nbr_places || 1,
    energie: vehicle.energie || 'essence',
    en_maintenance: vehicle.en_maintenance === 1,
    last_service_km: vehicle.last_service_km || 0,
    service_interval_km: vehicle.service_interval_km || 20000,
});

function submitVehicle() {
    form.put(route('admin.vehicles.update', vehicle.id));
}

// Formulaire pour ajouter une clé
const formCle = useForm({
    vehicle_id: vehicle.id,
    emplacement_clef: '',
});

function submitCle() {
    formCle.post(route('admin.keys.store'), {
        preserveScroll: true,
        onSuccess: (response) => {
            formCle.reset('emplacement_clef');
            // 👇 Met à jour le state avec les nouvelles données
            vehicleRef.value = response.props.vehicle;
        },
    });
}

function deleteKey(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette clé ?')) {
        router.delete(route('admin.keys.destroy', id), {
            preserveScroll: true,
            onSuccess: (response) => {
                // 👇 Met à jour le state avec les nouvelles données
                vehicleRef.value = response.props.vehicle;
            },
        });
    }
}
</script>

<template>
    <Head title="Edition de véhicule" />

    <AdminLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
                <!-- FORMULAIRE PRINCIPAL -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg h-full">
                    <div class="p-4 sm:p-6 md:p-8 h-full flex flex-col">
                        <h1 class="text-3xl font-bold text-gray-800 mb-6">
                            Modifier le véhicule
                        </h1>

                        <form @submit.prevent="submitVehicle" class="space-y-6">
                            <div>
                                <label for="modele" class="block text-sm font-semibold text-gray-900 mb-2">Modèle</label>
                                <input v-model="form.modele" type="text" id="modele" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" required />
                                <div v-if="form.errors.modele" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.modele }}
                                </div>
                            </div>

                            <div>
                                <label for="immatriculation" class="block text-sm font-semibold text-gray-900 mb-2">Immatriculation</label>
                                <input v-model="form.immatriculation" type="text" id="immatriculation" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" required />
                                <div v-if="form.errors.immatriculation" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.immatriculation }}
                                </div>
                            </div>

                            <div>
                                <label for="km_initial" class="block text-sm font-semibold text-gray-900 mb-2">Kilométrage initial</label>
                                <input v-model.number="form.km_initial" type="number" min="0" id="km_initial" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" required />
                                <div v-if="form.errors.km_initial" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.km_initial }}
                                </div>
                            </div>

                            <div>
                                <label for="emplacement" class="block text-sm font-semibold text-gray-900 mb-2">Emplacement</label>
                                <input v-model="form.emplacement" type="text" id="emplacement" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" required />
                                <div v-if="form.errors.emplacement" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.emplacement }}
                                </div>
                            </div>

                            <div>
                                <label for="nbr_places" class="block text-sm font-semibold text-gray-900 mb-2">Nombre de places</label>
                                <input v-model.number="form.nbr_places" type="number" min="1" id="nbr_places" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" required />
                                <div v-if="form.errors.nbr_places" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.nbr_places }}
                                </div>
                            </div>

                            <div>
                                <label for="energie" class="block text-sm font-semibold text-gray-900 mb-2">Type d'énergie</label>
                                <select v-model="form.energie" id="energie" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" required>
                                    <option value="essence">Essence</option>
                                    <option value="diesel">Diesel</option>
                                    <option value="hybride">Hybride</option>
                                    <option value="electrique">Électrique</option>
                                </select>
                                <div v-if="form.errors.energie" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.energie }}
                                </div>
                            </div>

                            <div class="flex items-center">
                                <input v-model="form.en_maintenance" type="checkbox" id="maintenance" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" />
                                <label for="maintenance" class="ml-3 block text-sm font-semibold text-gray-900 mb-2">En maintenance</label>
                            </div>
                            <div v-if="form.errors.en_maintenance" class="mt-2 text-sm text-red-600">
                                {{ form.errors.en_maintenance }}
                            </div>
                        </form>
                    </div>
                </div>

                <!-- RIGHT COLUMN -->
                <div class="space-y-6">
                    <!-- BLOCK GESTION DES CLES -->
                    <div class="overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-4 sm:p-6 md:p-8 bg-white">
                            <h2 class="text-3xl font-bold text-gray-800 mb-4">Gestion des clés</h2>
                            <form @submit.prevent="submitCle" class="space-y-4 mb-6">
                                <div>
                                    <label for="emplacement_cle" class="block text-sm font-semibold text-gray-900 mb-2">Emplacement de la clé</label>
                                    <input v-model="formCle.emplacement_clef" type="text" id="emplacement_cle" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" />
                                    <div v-if="formCle.errors.emplacement_clef" class="mt-2 text-sm text-red-600">
                                        {{ formCle.errors.emplacement_clef }}
                                    </div>
                                </div>
                                <div class="flex items-center justify-end">
                                    <button type="submit" :disabled="formCle.processing" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-md text-sm transition ease-in-out duration-150" :class="{ 'opacity-50 cursor-not-allowed': formCle.processing }">
                                        {{ formCle.processing ? 'Ajout...' : 'Ajouter une clé' }}
                                    </button>
                                </div>
                            </form>

                            <div v-if="vehicleRef.keys && vehicleRef.keys.length" class="mt-4 pt-4 border-t border-gray-200">
                                <div class="overflow-auto" :style="{ maxHeight: vehicleRef.keys.length > 4 ? '400px' : 'auto' }">
                                    <table class="w-full text-sm text-left text-gray-500 whitespace-nowrap">
                                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 text-center sticky top-0">
                                            <tr>
                                                <th scope="col" class="py-3 px-6">#</th>
                                                <th scope="col" class="py-3 px-6">Emplacement</th>
                                                <th scope="col" class="py-3 px-6">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="key in vehicleRef.keys" :key="key.id" class="bg-white border-b hover:bg-gray-50 text-center">
                                                <td class="py-4 px-6">Clé {{ key.id }}</td>
                                                <td class="py-4 px-6">{{ key.emplacement_clef || '-' }}</td>
                                                <td class="py-4 px-6">
                                                    <div class="flex flex-col sm:flex-row items-center justify-center gap-2">
                                                        <Link :href="route('admin.keys.edit', key.id)" class="px-3 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-md text-xs transition ease-in-out duration-150">Modifier</Link>
                                                        <button @click="deleteKey(key.id)" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-md text-xs transition ease-in-out duration-150">Supprimer</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div v-else class="text-gray-600 text-sm">Aucune clé enregistrée pour ce véhicule.</div>
                        </div>
                    </div>

                    <!-- BLOCK VERS NOUVEAU MODULE MAINTENANCE -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-4 sm:p-6 md:p-8">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-2xl font-bold text-gray-800">Historique d'entretien</h2>
                                    <p class="text-gray-500 text-sm mt-1">Consulter et ajouter des révisions pour ce véhicule.</p>
                                </div>
                                <Link :href="route('admin.maintenances.show', vehicle.id) + '?from_vehicle=1'" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-md shadow-sm transition whitespace-nowrap text-center">
                                    Aller au Module
                                </Link>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-4 sm:p-6 md:p-8">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Paramètres d'entretien</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Dernier entretien (Km)</label>
                                    <input v-model.number="form.last_service_km" type="number" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Intervalle révision (Km)</label>
                                    <input v-model.number="form.service_interval_km" type="number" step="1000" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" />
                                </div>
                            </div>

                            <div class="flex items-center justify-end pt-4 border-t border-gray-200 mt-auto">
                                <button type="submit" :disabled="form.processing" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-md shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150" :class="{ 'opacity-50 cursor-not-allowed': form.processing }">
                                    {{ form.processing ? 'Mise à jour...' : 'Mettre à jour' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
