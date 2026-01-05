<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    domains: Array,
    can: Object // Reçoit { create: bool, delete: bool, edit: bool }
});

const form = useForm({ name: '' });

const submit = () => {
    if (props.can.create) {
        form.post(route('admin.domains.store'), {
            onSuccess: () => form.reset(),
        });
    }
};

function deleteDomain(id) {
    if (props.can.delete && confirm('Êtes-vous sûr de vouloir supprimer ce domaine ?')) {
        router.delete(route('admin.domains.destroy', id), {
            preserveScroll: true,
        });
    }
}
</script>
<template>
    <Head title="Gestion des domaines" />

    <AdminLayout>
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold">Gestion des domaines</h1>
                    </div>

                    <div v-if="can.create" class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <form @submit.prevent="submit" class="flex gap-2">
                            <input 
                                v-model="form.name" 
                                type="text" 
                                placeholder="gmail.com" 
                                class="flex-1 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <button 
                                type="submit" 
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-md text-sm transition ease-in-out duration-150">
                                Ajouter
                            </button>
                        </form>
                    </div>

                    <div class="overflow-x-auto relative shadow-md sm:rounded-lg border border-gray-200">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="py-3 px-6">Domaine</th>
                                    <th scope="col" class="py-3 px-6 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="domain in domains" :key="domain.id" class="border-b">
                                    <td class="p-3 font-medium">{{ domain.name }}</td>
                                    <td class="py-4 px-6 text-center whitespace-nowrap">
                                        <button 
                                            v-if="can.delete" 
                                            @click="deleteDomain(domain.id)"
                                            class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-md text-xs transition ease-in-out duration-150">
                                            Supprimer
                                        </button>
                                        
                                        <span v-else class="text-gray-400 text-xs italic">Lecture seule</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="domains.length === 0" class="mt-6 text-center text-gray-500">
                        Aucun domaine trouvé.
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>