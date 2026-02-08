<script setup>
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';

defineOptions({ layout: AdminLayout });

const page = usePage();

const props = defineProps({
    user: Object,
    agences: Array,
    roles: Array,
});

const roleId = ref(props.user?.role_id ?? props.user?.roles?.[0]?.id ?? null);

const editUser = (id) => {
    router.put(route('admin.users.update', id), {
        name: props.user.name,
        email: props.user.email,
        agence_id: props.user.agence_id,
        role_id: roleId.value,
    });
};

</script>

<template>
    <Head :title="'Modifier Utilisateur - ' + user.name" />

    <div class="p-4 sm:p-6 md:p-8 max-w-2xl mx-auto">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h1 class="text-2xl font-bold mb-6">Modification de l'utilisateur</h1>

            <form @submit.prevent="editUser(user.id)">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                        Nom
                    </label>
                    <input
                        v-model="user.name"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        id="name"
                        type="text"
                        placeholder="Nom de l'utilisateur"
                    />
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                        Email
                    </label>
                    <input
                        v-model="user.email"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        id="email"
                        type="email"
                        placeholder="Email de l'utilisateur"
                    />
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="agence">
                        Agence
                    </label>
                    <select
                        v-model="user.agence_id"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        id="agence"
                    >
                        <option :value="null">Aucune agence</option>
                        <option v-for="agence in agences" :key="agence.id" :value="agence.id">
                            {{ agence.nom }}
                        </option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="role">
                        Rôle
                    </label>
                    <select
                        v-model="roleId"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        id="role"
                    >
                        <option :value="null">Aucun rôle</option>
                        <option v-for="role in (roles || [])" :key="role.id" :value="role.id">
                            {{ role.name }}
                        </option>
                    </select>
                </div>
                <div class="flex justify-end mt-6">
                    <button
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-md shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150"
                        type="submit"
                    >
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
