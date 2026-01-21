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

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">Gestion des Utilisateurs</h1>
                </div>

                <div class="overflow-x-auto relative shadow-md sm:rounded-lg border border-gray-200">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="text-left p-3 font-medium">Nom</th>
                                <th class="text-left p-3 font-medium">Email</th>
                                <th class="text-left p-3 font-medium">Agence</th>
                                <th class="text-left p-3 font-medium">Rôles</th>
                                <th class="text-center p-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users" :key="user.id" class="border-b">
                                <td class="p-3 font-medium">{{ user.name }}</td>
                                <td class="p-3 font-medium">{{ user.email }}</td>
                                <td class="p-3 font-medium">{{ user.agence ? user.agence.nom : 'Aucune agence' }}</td>
                                <td>{{ user.roles.map(role => role.name).join(', ') }}</td>
                                <td class="py-4 px-6 text-center whitespace-nowrap">
                                    <Link
                                        v-if="page.props.auth.permissions.includes('users.edit')"
                                        :href="route('admin.users.edit', user.id)"
                                        class="px-3 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-md text-xs mr-2 transition ease-in-out duration-150">
                                        Modifier
                                    </Link>

                                    <button
                                        v-if="page.props.auth.permissions.includes('users.delete')"
                                        @click="deleteUser(user.id)"
                                        class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-md text-xs transition ease-in-out duration-150">
                                        Supprimer
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="users.length === 0" class="mt-6 text-center text-gray-500">
                    Aucun utilisateur trouvé.
                </div>
            </div>
        </div>
    </div>
</template>
