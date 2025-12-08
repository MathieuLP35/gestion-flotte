<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineOptions({ layout: AdminLayout });

// On reçoit les props du RoleController@edit
const props = defineProps({
    role: Object,
    permissions: Array,
    rolePermissions: Array // Les permissions que ce rôle a déjà
});

// 3. Récupérer les traductions du fichier JSON
const translations = computed(() => usePage().props.translations || {});

// 4. Créer notre helper de traduction
function translate(key) {
    return translations.value[key] || key; // Renvoie la traduction, ou la clé si non trouvée
}

// On initialise le formulaire avec les données existantes
const form = useForm({
    name: props.role.name,
    permissions: props.rolePermissions // On pré-coche les cases !
});

// La soumission se fait sur la route 'update'
const submit = () => {
    form.put(route('admin.roles.update', props.role.id));
};
</script>

<template>
    <Head :title="'Modifier Rôle: ' + form.name" />

    <div class="p-4 sm:p-6 md:p-8 max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Modifier le Rôle</h1>

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

            <div class="flex items-center justify-between">
                <Link :href="route('admin.roles.index')" 
                      class="text-sm text-gray-600 hover:text-gray-900">
                    Annuler
                </Link>
                <button type="submit" :disabled="form.processing"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md">
                    Mettre à jour
                </button>
            </div>
        </form>
    </div>
</template>