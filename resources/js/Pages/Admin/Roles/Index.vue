<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3'; 
import { computed } from 'vue';

defineOptions({ layout: AdminLayout });

const page = usePage();

const props = defineProps({
    roles: Array
});

// 3. Récupérer les traductions du fichier JSON
const translations = computed(() => usePage().props.translations || {});

// 4. Créer notre helper de traduction
function translate(key) {
    return translations.value[key] || key; // Renvoie la traduction, ou la clé si non trouvée
}

// 2. Ajouter la fonction de suppression
const deleteRole = (roleId) => {
    if (confirm("Êtes-vous sûr de vouloir supprimer ce rôle ? Cette action est irréversible.")) {
        // Envoie une requête DELETE au RoleController@destroy
        router.delete(route('admin.roles.destroy', roleId), {
            preserveScroll: true // Ne remonte pas en haut de la page
        });
    }
};
</script>

<template>
    <Head title="Gérer les Rôles" />

    <div class="p-4 sm:p-6 md:p-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Gestion des Rôles</h1>
            
            <Link 
                v-if="page.props.auth.permissions.includes('roles.create')"
                :href="route('admin.roles.create')" 
                class="px-4 py-2 bg-indigo-600 text-white rounded-md">
                Créer un Rôle
            </Link>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left p-3 font-medium">Nom du Rôle</th>
                        <th class="text-left p-3 font-medium">Permissions</th>
                        <th class="text-right p-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="role in roles" :key="role.id" class="border-b">
                        <td class="p-3 font-medium">{{ role.name }}</td>
                        <td class="p-3">
                            <span v-for="perm in role.permissions.slice(0, 5)" :key="perm.id"
                                  class="text-xs bg-gray-200 text-gray-700 rounded-full px-2 py-0.5 mr-1">
                                {{ translate('permissions.' + perm.name) }}
                            </span>
                            <span v-if="role.permissions.length > 5" class="text-xs">
                                + {{ role.permissions.length - 5 }} autres
                            </span>
                        </td>
                        
                        <td class="p-3 text-right whitespace-nowrap">
                            <Link 
                                v-if="page.props.auth.permissions.includes('roles.edit')"
                                :href="route('admin.roles.edit', role.id)" 
                                class="text-indigo-600 hover:text-indigo-900 mr-4">
                                Modifier
                            </Link>
                            
                            <button 
                                v-if="role.name !== 'Super Admin' && page.props.auth.permissions.includes('roles.delete')"
                                @click="deleteRole(role.id)"
                                class="text-red-600 hover:text-red-900">
                                Supprimer
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>