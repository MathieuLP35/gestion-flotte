<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center p-4 sm:p-6">
        <!-- Logo -->
        <div class="mb-8">
            <Link href="/">
                <ApplicationLogo class="w-[8rem] h-[8rem] fill-current text-gray-600" />
            </Link>
        </div>

        <!-- Contenu principal -->
        <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md">
            <slot />
        </div>

        <!-- Footer -->
        <div class="mt-6 text-center text-sm text-gray-500">
            © 2025 SparkOtto. Tous droits réservés.
        </div>
    </div>
</template>