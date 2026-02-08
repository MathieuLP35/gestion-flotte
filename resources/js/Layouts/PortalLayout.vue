<!-- resources/js/Layouts/LandingLayout.vue -->
<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const mobileMenuOpen = ref(false);
const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth?.user);
</script>

<template>
    <div class="min-h-screen flex flex-col bg-gray-50">
        <!-- Header -->
        <header class="bg-white shadow-sm py-4">
            <div class="mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center">
                        <Link href="/">
                            <ApplicationLogo class="block h-20 w-auto fill-current text-gray-600" />
                        </Link>
                    </div>

                    <!-- Navigation desktop (tablette + PC) -->
                    <div class="hidden sm:flex items-center space-x-8">
                        <template v-if="isAuthenticated">
                            <Link
                                :href="route('dashboard')"
                                class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition"
                            >
                                Dashboard
                            </Link>
                            <span class="text-sm font-medium text-gray-700">{{ page.props.auth.user.name }}</span>
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition"
                            >
                                Déconnexion
                            </Link>
                        </template>
                        <template v-else>
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
                        </template>
                    </div>

                    <!-- Bouton menu burger (mobile uniquement) -->
                    <div class="flex items-center sm:hidden">
                        <button
                            type="button"
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-gray-500"
                            :aria-expanded="mobileMenuOpen"
                            aria-label="Ouvrir le menu"
                            @click="mobileMenuOpen = !mobileMenuOpen"
                        >
                            <span class="sr-only">Ouvrir le menu</span>
                            <!-- Icône hamburger -->
                            <svg
                                v-if="!mobileMenuOpen"
                                class="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                aria-hidden="true"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                            <!-- Icône fermer -->
                            <svg
                                v-else
                                class="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                aria-hidden="true"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Menu mobile (ouvert au clic sur le burger) -->
                <div
                    v-show="mobileMenuOpen"
                    class="sm:hidden border-t border-gray-200 pt-4 pb-3"
                >
                    <div class="flex flex-col gap-1">
                        <template v-if="isAuthenticated">
                            <Link
                                :href="route('dashboard')"
                                class="block px-3 py-2 rounded-md text-base font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-800"
                                @click="mobileMenuOpen = false"
                            >
                                Dashboard
                            </Link>
                            <span class="block px-3 py-2 text-base font-medium text-gray-800">{{ page.props.auth.user.name }}</span>
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="block px-3 py-2 rounded-md text-base font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-800 text-left w-full"
                                @click="mobileMenuOpen = false"
                            >
                                Déconnexion
                            </Link>
                        </template>
                        <template v-else>
                            <Link
                                href="/login"
                                class="block px-3 py-2 rounded-md text-base font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-800"
                                @click="mobileMenuOpen = false"
                            >
                                Se connecter
                            </Link>
                            <Link
                                href="/register"
                                class="block px-3 py-2 rounded-md text-base font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-800"
                                @click="mobileMenuOpen = false"
                            >
                                S'inscrire
                            </Link>
                        </template>
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