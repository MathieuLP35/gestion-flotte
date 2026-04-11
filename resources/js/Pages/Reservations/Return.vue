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
    <Head :title="$t('res.return_title')" />

    <AuthenticatedLayout>
        <div class="py-10 bg-gray-50 min-h-screen">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ $t('res.return_title') }}</h1>
                        <p class="mt-2 text-sm text-gray-600">{{ $t('res.return_subtitle') }}</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
                    
                    <!-- Informations de la réservation -->
                    <div class="p-6 bg-sparkotto-purple text-white relative">
                        <!-- Decorative SVG -->
                        <div class="absolute right-0 top-0 opacity-10">
                            <svg class="h-40 w-40" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 22h20L12 2zm0 3.8l7.2 14.2H4.8L12 5.8z"/></svg>
                        </div>
                        
                        <h3 class="text-xs font-bold text-purple-200 uppercase tracking-widest mb-4">{{ $t('res.trip_info') }}</h3>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <span class="block text-xs font-medium text-purple-200 mb-1">{{ $t('res.vehicle') }}</span>
                                <p class="text-sm font-bold">{{ reservation.vehicle.modele }}</p>
                            </div>
                            <div>
                                <span class="block text-xs font-medium text-purple-200 mb-1">{{ $t('res.km_initial') }}</span>
                                <p class="text-sm font-bold">{{ kmInitial }} km</p>
                            </div>
                            <div class="col-span-2">
                                <span class="block text-xs font-medium text-purple-200 mb-1">{{ $t('res.itinerary') }}</span>
                                <p class="text-sm font-bold truncate">{{ reservation.depart }} → {{ reservation.destination }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Formulaire de retour -->
                    <div class="p-8">
                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Kilométrage final -->
                                <div>
                                    <label for="km_final" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">
                                        {{ $t('res.km_final') }} *
                                    </label>
                                    <input
                                        id="km_final"
                                        v-model="form.km_final"
                                        type="number"
                                        :min="kmInitial"
                                        required
                                        class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-900 focus:bg-white focus:ring-2 focus:ring-sparkotto-purple focus:border-sparkotto-purple transition"
                                        :class="{ 'border-red-500': form.errors.km_final }"
                                    />
                                    <p v-if="form.errors.km_final" class="mt-1 text-xs text-red-600">{{ form.errors.km_final }}</p>
                                    <p class="mt-2 text-xs font-medium text-amber-600 bg-amber-50 rounded px-2 py-1 inline-block">
                                        {{ $t('res.km_must_be') }} {{ kmInitial }} km
                                    </p>
                                </div>

                                <!-- État du véhicule -->
                                <div>
                                    <label for="etat_vehicule" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">
                                        {{ $t('res.vehicle_state') }} *
                                    </label>
                                    <select
                                        id="etat_vehicule"
                                        v-model="form.etat_vehicule"
                                        required
                                        class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-900 focus:bg-white focus:ring-2 focus:ring-sparkotto-purple focus:border-sparkotto-purple transition"
                                        :class="{ 'border-red-500': form.errors.etat_vehicule }"
                                    >
                                        <option value="" disabled>{{ $t('res.select_state') }}</option>
                                        <option v-for="etat in etatsVehicule" :key="etat.value" :value="etat.value">
                                            {{ etat.label }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.etat_vehicule" class="mt-1 text-xs text-red-600">{{ form.errors.etat_vehicule }}</p>
                                    <div v-if="form.etat_vehicule === 'mauvais'" class="mt-2 text-xs text-red-700 bg-red-50 p-2 rounded-lg flex items-start">
                                        <svg class="h-4 w-4 mr-1.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                        {{ $t('res.state_warning') }}
                                    </div>
                                </div>
                            </div>

                            <!-- Emplacement de retour -->
                            <div>
                                <label for="emplacement_retour" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">
                                    {{ $t('res.return_location') }} *
                                </label>
                                <input
                                    id="emplacement_retour"
                                    v-model="form.emplacement_retour"
                                    type="text"
                                    required
                                    :placeholder="$t('res.return_location_placeholder')"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-900 focus:bg-white focus:ring-2 focus:ring-sparkotto-purple focus:border-sparkotto-purple transition"
                                    :class="{ 'border-red-500': form.errors.emplacement_retour }"
                                />
                                <p v-if="form.errors.emplacement_retour" class="mt-1 text-xs text-red-600">{{ form.errors.emplacement_retour }}</p>
                            </div>

                            <!-- Notes de retour -->
                            <div>
                                <label for="notes_retour" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">
                                    {{ $t('res.return_notes') }}
                                </label>
                                <textarea
                                    id="notes_retour"
                                    v-model="form.notes_retour"
                                    rows="3"
                                    :placeholder="$t('res.return_notes_placeholder')"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-900 focus:bg-white focus:ring-2 focus:ring-sparkotto-purple focus:border-sparkotto-purple transition"
                                    :class="{ 'border-red-500': form.errors.notes_retour }"
                                ></textarea>
                                <p v-if="form.errors.notes_retour" class="mt-1 text-xs text-red-600">{{ form.errors.notes_retour }}</p>
                            </div>

                            <!-- Boutons d'action -->
                            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-100">
                                <Link
                                    :href="route('reservations.show', reservation.id)"
                                    class="px-5 py-2.5 rounded-xl font-bold text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition"
                                >
                                    {{ $t('action.cancel') }}
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-6 py-2.5 bg-sparkotto-purple hover:bg-sparkotto-purple-hover text-white text-sm font-bold rounded-xl shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-sparkotto-purple focus:ring-offset-2 flex items-center"
                                >
                                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    {{ form.processing ? $t('res.submitting') : $t('res.submit_return') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
