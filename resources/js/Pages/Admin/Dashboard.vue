<script setup>
// 1. On importe notre NOUVEAU layout
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

defineOptions({
  layout: AdminLayout // 2. On dit à Inertia de l'utiliser
});

// 3. On récupère les statistiques depuis les props de la page
defineProps({
  stats: Object
});

</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h1 class="text-3xl font-bold mb-4">
                    Bienvenue dans le Panel d'Administration
                </h1>
                <p class="text-gray-600 mb-6">
                    Utilisez le menu de navigation pour gérer le site.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-blue-100 p-4 rounded-lg shadow">
                        <h2 class="text-xl font-semibold mb-2">Utilisateurs</h2>
                        <p class="text-3xl font-bold">{{ stats.users_count }}</p>
                    </div>
                    <div class="bg-green-100 p-4 rounded-lg shadow">
                        <h2 class="text-xl font-semibold mb-2">Véhicules</h2>
                        <p class="text-3xl font-bold">{{ stats.vehicles_count }}</p>
                    </div>
                    <div class="bg-yellow-100 p-4 rounded-lg shadow">
                        <h2 class="text-xl font-semibold mb-2">Nombre de trajets totals</h2>
                        <p class="text-3xl font-bold">{{ stats.reservations_count }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>