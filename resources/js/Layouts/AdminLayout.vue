<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import useAdminPermissions from '@/Composables/useAdminPermissions';

const showingNavigationDropdown = ref(false);
const page = usePage();
const {
    canViewDashboard,
    canViewAgences,
    canViewRoles,
    canViewVehicles,
    canViewVehicleSuggestion,
    canViewUsers,
    canViewDomains,
    showVehiclesMenu,
} = useAdminPermissions();

const vehiclesMenuActive = computed(() => {
    const url = page.url || '';
    return /^\/admin\/vehicles(\/|$)/.test(url) || /\/admin\/settings\/vehicle-suggestion/.test(url);
});
</script>

<template>
    <div>
        <div class="min-h-screen flex flex-col bg-gray-50">
            <nav class="bg-white border-b border-gray-100">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between">
                        <div class="flex">
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('admin.dashboard')">
                                    <ApplicationLogo class="block py-2 w-[5rem] h-[5.5rem] fill-current text-gray-800" />
                                </Link>
                            </div>

                            <div class="hidden items-center space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')" class="text-gray-600 hover:text-gray-900">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                                    </svg>
                                    Utilisateur
                                </NavLink>
                                <NavLink v-if="canViewDashboard" :href="route('admin.dashboard')" :active="route().current('admin.dashboard')">
                                    Tableau de bord
                                </NavLink>
                                <NavLink v-if="canViewAgences" :href="route('admin.agences.index')" :active="route().current('admin.agences.*')">
                                    Agences
                                </NavLink>
                                <NavLink v-if="canViewRoles" :href="route('admin.roles.index')" :active="route().current('admin.roles.*')">
                                    Rôles
                                </NavLink>
                                <div v-if="showVehiclesMenu" class="inline-flex items-center">
                                    <Dropdown align="left" width="48">
                                        <template #trigger>
                                            <button
                                                type="button"
                                                :class="vehiclesMenuActive
                                                    ? 'inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out'
                                                    : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out'"
                                            >
                                                Véhicules
                                                <svg class="ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </template>
                                        <template #content>
                                            <DropdownLink v-if="canViewVehicles" :href="route('admin.vehicles.availability')">
                                                Gestion
                                            </DropdownLink>
                                            <DropdownLink
                                                v-if="canViewVehicleSuggestion"
                                                :href="route('admin.settings.vehicleSuggestion.edit')"
                                            >
                                                Suggestion véhicule
                                            </DropdownLink>
                                        </template>
                                    </Dropdown>
                                </div>
                                <NavLink v-if="canViewUsers" :href="route('admin.users.index')" :active="route().current('admin.users.*')">
                                    Utilisateurs
                                </NavLink>
                                <NavLink v-if="canViewDomains" :href="route('admin.domains.index')" :active="route().current('admin.domains.*')">
                                    Domaines
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
                                            Profil
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Déconnexion
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex': !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex': showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Utilisateur
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="canViewDashboard" :href="route('admin.dashboard')" :active="route().current('admin.dashboard')">
                            Tableau de bord
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="canViewAgences" :href="route('admin.agences.index')" :active="route().current('admin.agences.*')">
                            Agences
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="canViewRoles" :href="route('admin.roles.index')" :active="route().current('admin.roles.*')">
                            Rôles
                        </ResponsiveNavLink>
                        <div v-if="showVehiclesMenu" class="pt-1">
                            <div class="px-4 py-1 text-xs font-semibold text-gray-400 uppercase tracking">Véhicules</div>
                            <ResponsiveNavLink v-if="canViewVehicles" :href="route('admin.vehicles.availability')" :active="route().current('admin.vehicles.availability') || route().current('admin.vehicles.create') || route().current('admin.vehicles.edit') || route().current('admin.vehicles.calendar')">
                                Véhicules
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                v-if="canViewVehicleSuggestion"
                                :href="route('admin.settings.vehicleSuggestion.edit')"
                                :active="route().current('admin.settings.vehicleSuggestion.*')"
                            >
                                Suggestion véhicule
                            </ResponsiveNavLink>
                        </div>
                        <ResponsiveNavLink v-if="canViewUsers" :href="route('admin.users.index')" :active="route().current('admin.users.*')">
                            Utilisateurs
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="canViewDomains" :href="route('admin.domains.index')" :active="route().current('admin.domains.*')">
                            Domaines
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="border-t border-gray-200 pb-1 pt-4">
                        <div class="px-4">
                            <div class="text-base font-medium text-gray-800">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profil
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Déconnexion
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <header class="bg-white shadow" v-if="$slots.header">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main>
                <slot />
            </main>
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
    </div>
</template>
