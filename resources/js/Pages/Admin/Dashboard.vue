<script setup>
// 1. On importe notre NOUVEAU layout
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

defineOptions({
  layout: AdminLayout // 2. On dit à Inertia de l'utiliser
});
</script>

<template>
    <Head title="Admin Dashboard" />
    <div class="p-4">
        <h1 class="text-2xl font-bold">
            Bienvenue dans le Panel d'Administration
        </h1>
        <p>C'est ici que vous gérerez le site.</p>
    </div>
</template>