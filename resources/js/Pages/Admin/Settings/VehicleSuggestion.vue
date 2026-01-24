<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    setting: Object,
    energies: Array,
});

const page = usePage();
const success = computed(() => page.props.flash?.success);

function pad(arr, len = 4) {
    const a = Array.isArray(arr) ? [...arr] : [];
    while (a.length < len) a.push('');
    return a.slice(0, len);
}

const form = useForm({
    petit_trajet_seuil_km: props.setting?.petit_trajet_seuil_km ?? 100,
    priorite_petit_trajet: pad(props.setting?.priorite_petit_trajet),
    priorite_long_trajet: pad(props.setting?.priorite_long_trajet),
});

const submit = () => {
    form.transform((data) => ({
        petit_trajet_seuil_km: data.petit_trajet_seuil_km,
        priorite_petit_trajet: data.priorite_petit_trajet.filter(Boolean),
        priorite_long_trajet: data.priorite_long_trajet.filter(Boolean),
    })).put(route('admin.settings.vehicleSuggestion.update'));
};

const labels = { electrique: 'Électrique', hybride: 'Hybride', essence: 'Essence', diesel: 'Diesel' };
</script>

<template>
    <Head title="Suggestion de véhicule" />

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Paramètres de suggestion de véhicule</h1>
                <p class="text-sm text-gray-500 mb-6">
                    Définit comment un véhicule est proposé selon la distance (départ → destination) lors de la création d’une réservation.
                </p>

                <div v-if="success" class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
                    {{ success }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label for="seuil" class="block text-sm font-medium text-gray-700">Seuil « petit trajet » (km)</label>
                        <input
                            id="seuil"
                            v-model.number="form.petit_trajet_seuil_km"
                            type="number"
                            min="1"
                            max="2000"
                            class="mt-1 block w-full max-w-xs border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            Si la distance ≤ ce seuil, on applique la priorité « petit trajet », sinon la priorité « long trajet ».
                        </p>
                        <p v-if="form.errors.petit_trajet_seuil_km" class="mt-1 text-sm text-red-600">{{ form.errors.petit_trajet_seuil_km }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Priorité pour les petits trajets</label>
                        <p class="text-xs text-gray-500 mb-2">Ordre de préférence des types d’énergie (du plus prioritaire au moins). Laissez un rang vide pour l’ignorer.</p>
                        <div class="flex flex-wrap gap-2">
                            <select
                                v-for="(_, i) in form.priorite_petit_trajet"
                                :key="'p'+i"
                                v-model="form.priorite_petit_trajet[i]"
                                class="border border-gray-300 rounded-md shadow-sm py-1.5 px-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="">—</option>
                                <option v-for="e in energies" :key="e" :value="e">{{ labels[e] || e }}</option>
                            </select>
                        </div>
                        <p v-if="form.errors.priorite_petit_trajet" class="mt-1 text-sm text-red-600">{{ form.errors.priorite_petit_trajet }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Priorité pour les trajets longs</label>
                        <p class="text-xs text-gray-500 mb-2">Même principe pour les trajets au‑dessus du seuil.</p>
                        <div class="flex flex-wrap gap-2">
                            <select
                                v-for="(_, i) in form.priorite_long_trajet"
                                :key="'l'+i"
                                v-model="form.priorite_long_trajet[i]"
                                class="border border-gray-300 rounded-md shadow-sm py-1.5 px-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="">—</option>
                                <option v-for="e in energies" :key="e" :value="e">{{ labels[e] || e }}</option>
                            </select>
                        </div>
                        <p v-if="form.errors.priorite_long_trajet" class="mt-1 text-sm text-red-600">{{ form.errors.priorite_long_trajet }}</p>
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-semibold rounded-md text-sm transition"
                        >
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
