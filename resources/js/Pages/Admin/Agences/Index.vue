<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    agences: Array,
});

const page = usePage();
const success = computed(() => page.props.flash?.success);
const error = computed(() => page.props.flash?.error);

const deleteAgence = (id, nom) => {
    if (confirm(`Supprimer l'agence « ${nom} » ?`)) {
        router.delete(route('admin.agences.destroy', id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Gestion des agences" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">Gestion des agences</h1>
                    <Link
                        :href="route('admin.agences.create')"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-md text-sm transition"
                    >
                        Créer une agence
                    </Link>
                </div>

                <div v-if="success" class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
                    {{ success }}
                </div>
                <div v-if="error" class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                    {{ error }}
                </div>

                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="w-full text-sm text-left text-gray-700">
                        <thead class="bg-gray-50 text-gray-700 uppercase">
                            <tr>
                                <th class="p-3 font-medium">Nom</th>
                                <th class="p-3 font-medium">Adresse</th>
                                <th class="p-3 font-medium text-center">Véhicules</th>
                                <th class="p-3 font-medium text-center">Utilisateurs</th>
                                <th class="p-3 font-medium text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="a in agences" :key="a.id" class="border-t border-gray-200">
                                <td class="p-3 font-medium">{{ a.nom }}</td>
                                <td class="p-3 text-gray-600">{{ a.adresse || '—' }}</td>
                                <td class="p-3 text-center">{{ a.vehicles_count }}</td>
                                <td class="p-3 text-center">{{ a.users_count }}</td>
                                <td class="p-3 text-right whitespace-nowrap">
                                    <Link
                                        :href="route('admin.agences.edit', a.id)"
                                        class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded text-xs mr-2 transition"
                                    >
                                        Modifier
                                    </Link>
                                    <button
                                        type="button"
                                        :disabled="a.vehicles_count > 0 || a.users_count > 0"
                                        :title="(a.vehicles_count > 0 || a.users_count > 0) ? 'Agence avec véhicules ou utilisateurs' : 'Supprimer'"
                                        @click="deleteAgence(a.id, a.nom)"
                                        class="px-3 py-1.5 bg-red-600 hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded text-xs transition"
                                    >
                                        Supprimer
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p v-if="agences.length === 0" class="mt-6 text-center text-gray-500">
                    Aucune agence.
                </p>
            </div>
        </div>
    </div>
</template>
