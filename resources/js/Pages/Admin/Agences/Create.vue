<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineOptions({ layout: AdminLayout });

const form = useForm({ nom: '', adresse: '' });

const submit = () => {
    form.post(route('admin.agences.store'));
};
</script>

<template>
    <Head title="Créer une agence" />

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h1 class="text-2xl font-bold mb-6">Créer une agence</h1>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label for="nom" class="block text-sm font-semibold text-gray-900 mb-2">Nom</label>
                        <input
                            id="nom"
                            v-model="form.nom"
                            type="text"
                            required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Nom de l'agence"
                        />
                        <p v-if="form.errors.nom" class="mt-1 text-sm text-red-600">{{ form.errors.nom }}</p>
                    </div>
                    <div>
                        <label for="adresse" class="block text-sm font-semibold text-gray-900 mb-2">Adresse</label>
                        <input
                            id="adresse"
                            v-model="form.adresse"
                            type="text"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Adresse (optionnel)"
                        />
                        <p v-if="form.errors.adresse" class="mt-1 text-sm text-red-600">{{ form.errors.adresse }}</p>
                    </div>
                    <div class="flex justify-end gap-2">
                        <Link
                            :href="route('admin.agences.index')"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                        >
                            Annuler
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-semibold rounded-md"
                        >
                            Créer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
