<script setup>
import { usePage,  Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';


const { vehicleKey } = usePage().props;


const editKey = (id) => {
    router.put(route('admin.keys.update', id), {
        emplacement_clef: vehicleKey.emplacement_clef,
    });
};
</script>

<template>
    <Head title="Edition d'une clé" />

    <AdminLayout>
        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 flex justify-center gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 md:p-8">
                        <h1 class="text-3xl font-bold text-gray-800 mb-6">
                            Édition de la clé pour le véhicule : {{ vehicleKey.vehicle.modele }} ({{ vehicleKey.vehicle.immatriculation }})
                        </h1>
                        <form @submit.prevent="editKey(vehicleKey.id)">
                            <div class="mb-4">
                                <label for="emplacement_clef" class="block text-gray-700 font-bold mb-2">Emplacement de la clef :</label>
                                <textarea id="emplacement_clef" v-model="vehicleKey.emplacement_clef" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                            </div>
                            <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                                <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-md shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150">
                                    Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
