<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    permissions: Array // C'est le "dictionnaire" de toutes les permissions
});

// 3. Récupérer les traductions du fichier JSON
const translations = computed(() => usePage().props.translations || {});

// 4. Créer notre helper de traduction
function translate(key) {
    return translations.value[key] || key; // Renvoie la traduction, ou la clé si non trouvée
}

const form = useForm({
    name: '',
    permissions: [] // C'est ici qu'on stockera les permissions cochées
});

const submit = () => {
    form.post(route('admin.roles.store'));
};
</script>

<template>
    <Head title="Créer un Rôle" />

    <div class="p-4 sm:p-6 md:p-8 max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Créer un nouveau Rôle</h1>

        <form @submit.prevent="submit" class="bg-white shadow-md rounded-lg p-6">
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Nom du Rôle</label>
                <input type="text" v-model="form.name" id="name"
                       class="mt-1 block w-full border-gray-300 rounded-md" required>
                <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Permissions</label>
                <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    <div v-for="permission in permissions" :key="permission" class="flex items-center">
                        <input type="checkbox" :id="permission" :value="permission" v-model="form.permissions"
                               class="rounded border-gray-300 text-indigo-600 shadow-sm">
                        <label :for="permission" class="ml-2 text-sm text-gray-600">{{ translate('permissions.' + permission) }}</label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" :disabled="form.processing"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</template>