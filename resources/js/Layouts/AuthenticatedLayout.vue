<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div>
        <div class="min-h-screen flex flex-col bg-gray-50">
            <nav class="border-b border-gray-200 bg-white shadow-sm">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo class="block py-2 w-[5rem] h-[5.5rem] fill-current text-gray-800" />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    {{ $t('nav.dashboard') }}
                                </NavLink>
                                <NavLink
                                    v-if="$page.props.auth?.roles && $page.props.auth?.permissions?.includes('admin.view')"
                                    :href="route('admin.dashboard')"
                                    :active="route().current('admin.*')"
                                    class="inline-flex items-center gap-1"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                    </svg>
                                    {{ $t('nav.admin') }}
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center gap-3">
                            <!-- Language Switcher -->
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

                            <!-- Profile Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button" class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none">
                                                {{ $page.props.auth.user.name }}
                                                <svg class="-me-0.5 ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>
                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')">{{ $t('nav.profile') }}</DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">{{ $t('nav.logout') }}</DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden">
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            {{ $t('nav.dashboard') }}
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="$page.props.auth?.roles?.includes('Super Admin')" :href="route('admin.dashboard')" :active="route().current('admin.*')">
                            {{ $t('nav.admin') }}
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings -->
                    <div class="border-t border-gray-200 pb-1 pt-4">
                        <div class="px-4">
                            <div class="text-base font-medium text-gray-800">{{ $page.props.auth.user.name }}</div>
                            <div class="text-sm font-medium text-gray-500">{{ $page.props.auth.user.email }}</div>
                        </div>
                        <!-- Language switcher mobile -->
                        <div class="px-4 py-3">
                            <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-1 w-fit">
                                <Link :href="route('language.update')" method="post" as="button" :data="{ language: 'fr' }"
                                    class="px-2.5 py-1 rounded-md text-xs font-bold transition"
                                    :class="$page.props.locale === 'fr' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500'"
                                >FR</Link>
                                <Link :href="route('language.update')" method="post" as="button" :data="{ language: 'en' }"
                                    class="px-2.5 py-1 rounded-md text-xs font-bold transition"
                                    :class="$page.props.locale === 'en' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500'"
                                >EN</Link>
                            </div>
                        </div>
                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">{{ $t('nav.profile') }}</ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button">{{ $t('nav.logout') }}</ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white shadow" v-if="$slots.header">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main><slot /></main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 mt-auto">
                <div class="mx-auto px-4 sm:px-6 lg:px-8 py-6 max-w-7xl">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            © {{ new Date().getFullYear() }} SparkOtto. {{ $t('nav.copyright') }}
                        </div>
                        <div class="space-x-4">
                            <Link href="/terms" class="text-sm text-gray-500 hover:text-gray-700">{{ $t('nav.terms') }}</Link>
                            <Link href="/privacy" class="text-sm text-gray-500 hover:text-gray-700">{{ $t('nav.privacy') }}</Link>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</template>
