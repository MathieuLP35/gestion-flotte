<!-- resources/js/Layouts/LandingLayout.vue -->
<script setup>
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>

<template>
    <div class="min-h-screen flex flex-col bg-gray-50">
        <!-- Header -->
        <header class="bg-white shadow-sm py-4">
            <div class="mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">
                <div class="flex justify-between h-16">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center">
                        <Link href="/">
                            <ApplicationLogo class="block h-20 w-auto fill-current text-gray-600" />
                        </Link>
                    </div>

                    <!-- Navigation -->
                    <div class="hidden sm:flex items-center space-x-8">
                        <Link
                            href="/login"
                            class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition"
                        >
                            Se connecter
                        </Link>
                        <Link
                            href="/register"
                            class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition"
                        >
                            S'inscrire
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-grow py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <slot />
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-auto">
            <div class="mx-auto px-4 sm:px-6 lg:px-8 py-6 max-w-7xl">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        © 2025 SparkOtto. Tous droits réservés.
                    </div>
                    <div class="space-x-4">
                        <Link
                            href="/terms"
                            class="text-sm text-gray-500 hover:text-gray-700"
                        >
                            Conditions d'utilisation
                        </Link>
                        <Link
                            href="/privacy"
                            class="text-sm text-gray-500 hover:text-gray-700"
                        >
                            Politique de confidentialité
                        </Link>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>