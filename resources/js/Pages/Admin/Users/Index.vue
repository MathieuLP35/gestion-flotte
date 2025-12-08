<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3'; 
import { computed } from 'vue';

defineOptions({ layout: AdminLayout });

const page = usePage();

const props = defineProps({
    users: Array
});

// 3. Récupérer les traductions du fichier JSON
const translations = computed(() => usePage().props.translations || {});

// 4. Créer notre helper de traduction
function translate(key) {
    return translations.value[key] || key; // Renvoie la traduction, ou la clé si non trouvée
}

// 2. Ajouter la fonction de suppression
const deleteUser = (userId) => {
    if (confirm("Êtes-vous sûr de vouloir supprimer ce rôle ? Cette action est irréversible.")) {
        // Envoie une requête DELETE au AdminUserController@destroy
        router.delete(route('admin.users.destroy', userId), {
            preserveScroll: true // Ne remonte pas en haut de la page
        });
    }
};
</script>

<template>
    <Head title="Gérer les Utilisateurs" />

    <div class="p-4 sm:p-6 md:p-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Gestion des Utilisateurs</h1>
            
            <Link 
                v-if="page.props.auth.permissions.includes('users.create')"
                :href="route('admin.users.create')" 
                class="px-4 py-2 bg-indigo-600 text-white rounded-md">
                Créer un utilisateur
            </Link>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left p-3 font-medium">Nom</th>
                        <th class="text-left p-3 font-medium">Email</th>
                        <th class="text-left p-3 font-medium">Agence</th>
                        <th class="text-left p-3 font-medium">Rôles</th>
                        <th class="text-right p-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in users" :key="user.id" class="border-b">
                        <td class="p-3 font-medium">{{ user.name }}</td>
                        <td class="p-3 font-medium"> {{ user.email }}</td>
                        <td class="p-3 font-medium"> {{ user.agence.nom }}</td>
                        <td>{{ user.roles.map(role => role.name).join(', ') }}</td>
                        <td class="p-3 text-right whitespace-nowrap">
                            <Link 
                                v-if="page.props.auth.permissions.includes('users.edit')"
                                :href="route('admin.users.edit', user.id)" 
                                class="text-indigo-600 hover:text-indigo-900 mr-4">
                                Modifier
                            </Link>
                            
                            <button 
                                v-if="page.props.auth.permissions.includes('users.delete')"
                                @click="deleteUser(user.id)"
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