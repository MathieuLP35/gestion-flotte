<!-- resources/js/Layouts/PortalLayout.vue -->
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

                    <!-- Navigation desktop -->
                    <div class="hidden sm:flex items-center space-x-6">
                        <!-- Language switcher -->
                        <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-1">
                            <Link
                                :href="route('language.update')" method="post" as="button" :data="{ language: 'fr' }"
                                class="px-2.5 py-1 rounded-md text-xs font-bold transition"
                                :class="$page.props.locale === 'fr' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                                :title="$t('lang.switch_to_fr')"
                            >FR</Link>
                            <Link
                                :href="route('language.update')" method="post" as="button" :data="{ language: 'en' }"
                                class="px-2.5 py-1 rounded-md text-xs font-bold transition"
                                :class="$page.props.locale === 'en' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                                :title="$t('lang.switch_to_en')"
                            >EN</Link>
                        </div>

                        <template v-if="isAuthenticated">
                            <Link :href="route('dashboard')" class="text-sm font-medium text-gray-500 hover:text-gray-700 transition">
                                {{ $t('nav.dashboard') }}
                            </Link>
                            <span class="text-sm font-medium text-gray-700">{{ page.props.auth.user.name }}</span>
                            <Link :href="route('logout')" method="post" as="button" class="text-sm font-medium text-gray-500 hover:text-gray-700 transition">
                                {{ $t('nav.logout') }}
                            </Link>
                        </template>
                        <template v-else>
                            <Link href="/login" class="text-sm font-medium text-gray-500 hover:text-gray-700 transition">
                                {{ $t('welcome.cta_login') }}
                            </Link>
                            <Link href="/register" class="text-sm font-medium text-gray-500 hover:text-gray-700 transition">
                                {{ $t('welcome.cta_register') }}
                            </Link>
                        </template>
                    </div>

                    <!-- Mobile burger -->
                    <div class="flex items-center gap-3 sm:hidden">
                        <!-- Language switcher mobile -->
                        <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-0.5">
                            <Link :href="route('language.update')" method="post" as="button" :data="{ language: 'fr' }"
                                class="px-2 py-1 rounded text-xs font-bold transition"
                                :class="$page.props.locale === 'fr' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500'"
                            >FR</Link>
                            <Link :href="route('language.update')" method="post" as="button" :data="{ language: 'en' }"
                                class="px-2 py-1 rounded text-xs font-bold transition"
                                :class="$page.props.locale === 'en' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500'"
                            >EN</Link>
                        </div>
                        <button
                            type="button"
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none"
                            @click="mobileMenuOpen = !mobileMenuOpen"
                        >
                            <svg v-if="!mobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                            <svg v-else class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile menu -->
                <div v-show="mobileMenuOpen" class="sm:hidden border-t border-gray-200 pt-4 pb-3">
                    <div class="flex flex-col gap-1">
                        <template v-if="isAuthenticated">
                            <Link :href="route('dashboard')" class="block px-3 py-2 rounded-md text-base font-medium text-gray-600 hover:bg-gray-100" @click="mobileMenuOpen = false">
                                {{ $t('nav.dashboard') }}
                            </Link>
                            <span class="block px-3 py-2 text-base font-medium text-gray-800">{{ page.props.auth.user.name }}</span>
                            <Link :href="route('logout')" method="post" as="button" class="block px-3 py-2 rounded-md text-base font-medium text-gray-600 hover:bg-gray-100 text-left w-full" @click="mobileMenuOpen = false">
                                {{ $t('nav.logout') }}
                            </Link>
                        </template>
                        <template v-else>
                            <Link href="/login" class="block px-3 py-2 rounded-md text-base font-medium text-gray-600 hover:bg-gray-100" @click="mobileMenuOpen = false">
                                {{ $t('welcome.cta_login') }}
                            </Link>
                            <Link href="/register" class="block px-3 py-2 rounded-md text-base font-medium text-gray-600 hover:bg-gray-100" @click="mobileMenuOpen = false">
                                {{ $t('welcome.cta_register') }}
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
                        © {{ new Date().getFullYear() }} SparkOtto. {{ $t('nav.copyright') }}
                    </div>
                    <div class="space-x-4">
                        <Link href="/terms" class="text-sm text-gray-500 hover:text-gray-700">
                            {{ $t('nav.terms') }}
                        </Link>
                        <Link href="/privacy" class="text-sm text-gray-500 hover:text-gray-700">
                            {{ $t('nav.privacy') }}
                        </Link>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>