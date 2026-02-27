<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    reservation: Object,
});

const form = useForm({
    km_final: '',
    emplacement_retour: '',
    etat_vehicule: '',
    notes_retour: '',
});

const etatsVehicule = [
    { value: 'excellent', label: 'Excellent' },
    { value: 'bon', label: 'Bon' },
    { value: 'moyen', label: 'Moyen' },
    { value: 'mauvais', label: 'Mauvais' },
];

const kmInitial = computed(() => props.reservation.vehicle?.km_initial || 0);

const submit = () => {
    form.post(route('reservations.return', props.reservation.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Retour du véhicule" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Retour du véhicule
                </h2>
                <Link 
                    :href="route('reservations.show', reservation.id)"
                    class="text-gray-600 hover:text-gray-900"
                >
                    ← Retour aux détails
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Informations de la réservation -->
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">Informations de la réservation</h3>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-gray-600">Véhicule:</span>
                                    <p class="text-gray-900">{{ reservation.vehicle.modele }} ({{ reservation.vehicle.immatriculation }})</p>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Kilométrage initial:</span>
                                    <p class="text-gray-900">{{ kmInitial }} km</p>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Départ:</span>
                                    <p class="text-gray-900">{{ reservation.depart }}</p>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Destination:</span>
                                    <p class="text-gray-900">{{ reservation.destination }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Formulaire de retour -->
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Kilométrage final -->
                            <div>
                                <label for="km_final" class="block text-sm font-semibold text-gray-900 mb-2">
                                    Kilométrage final <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="km_final"
                                    v-model="form.km_final"
                                    type="number"
                                    :min="kmInitial"
                                    required
                                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                    :class="{ 'border-red-500': form.errors.km_final }"
                                />
                                <p v-if="form.errors.km_final" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.km_final }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    Le kilométrage final doit être supérieur ou égal à {{ kmInitial }} km
                                </p>
                            </div>

                            <!-- Emplacement de retour -->
                            <div>
                                <label for="emplacement_retour" class="block text-sm font-semibold text-gray-900 mb-2">
                                    Emplacement de retour <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="emplacement_retour"
                                    v-model="form.emplacement_retour"
                                    type="text"
                                    required
                                    placeholder="Ex: Agence Paris, Parking central..."
                                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                    :class="{ 'border-red-500': form.errors.emplacement_retour }"
                                />
                                <p v-if="form.errors.emplacement_retour" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.emplacement_retour }}
                                </p>
                            </div>

                            <!-- État du véhicule -->
                            <div>
                                <label for="etat_vehicule" class="block text-sm font-semibold text-gray-900 mb-2">
                                    État du véhicule <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="etat_vehicule"
                                    v-model="form.etat_vehicule"
                                    required
                                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                    :class="{ 'border-red-500': form.errors.etat_vehicule }"
                                >
                                    <option value="">Sélectionnez un état</option>
                                    <option v-for="etat in etatsVehicule" :key="etat.value" :value="etat.value">
                                        {{ etat.label }}
                                    </option>
                                </select>
                                <p v-if="form.errors.etat_vehicule" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.etat_vehicule }}
                                </p>
                                <p v-if="form.etat_vehicule === 'mauvais'" class="mt-2 text-sm text-amber-600 bg-amber-50 p-2 rounded">
                                    ⚠️ Le véhicule sera automatiquement mis en maintenance si l'état est "Mauvais"
                                </p>
                            </div>

                            <!-- Notes de retour -->
                            <div>
                                <label for="notes_retour" class="block text-sm font-semibold text-gray-900 mb-2">
                                    Notes de retour (optionnel)
                                </label>
                                <textarea
                                    id="notes_retour"
                                    v-model="form.notes_retour"
                                    rows="4"
                                    placeholder="Ajoutez des observations sur l'état du véhicule, des incidents, etc."
                                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                    :class="{ 'border-red-500': form.errors.notes_retour }"
                                ></textarea>
                                <p v-if="form.errors.notes_retour" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.notes_retour }}
                                </p>
                            </div>

                            <!-- Boutons d'action -->
                            <div class="flex items-center justify-end space-x-4 pt-4 border-t">
                                <Link
                                    :href="route('reservations.show', reservation.id)"
                                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    Annuler
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                                >
                                    <span v-if="form.processing">Enregistrement...</span>
                                    <span v-else>Enregistrer le retour</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

