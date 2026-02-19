<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import RolePermissionCheckboxes from '@/Components/RolePermissionCheckboxes.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    role: Object,
    permissions: Array,
});

const form = useForm({
    name: props.role.data.name, 
    permissions: [...(props.role.data.permissions || [])], 
});

const submit = () => {
    if (props.role?.data?.id) {
        form.put(route('admin.roles.update', props.role.data.id));
    } else {
        console.error("ID du rôle introuvable dans props.role.data");
    }
};

</script>

<template>
    <Head :title="'Modifier : ' + form.name" />

    <div class="p-4 sm:p-6 md:p-8 max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Modifier le rôle</h1>
        <form @submit.prevent="submit" class="space-y-6">
            <pre>{{ role }}</pre>
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <label for="name" class="block text-sm font-medium text-gray-700">Nom du rôle</label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    required
                    class="mt-1 block w-full max-w-md rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                />
                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-gray-800">Permissions</h2>
                <RolePermissionCheckboxes
                    v-model="form.permissions"
                    :permissions="permissions"
                />
            </div>

            <div class="flex items-center justify-between gap-4 border-t border-gray-200 pt-4">
                <Link
                    :href="route('admin.roles.index')"
                    class="text-sm font-medium text-gray-600 hover:text-gray-900"
                >
                    Annuler
                </Link>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50"
                >
                    {{ form.processing ? 'Mise à jour…' : 'Mettre à jour' }}
                </button>
            </div>
        </form>
    </div>
</template>
