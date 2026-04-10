<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
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
    return /^\/admin\/vehicles(\/|$)/.test(url) || /\/admin\/settings\/vehicle-suggestion/.test(url) || /^\/admin\/maintenances(\/|$)/.test(url);
});
</script>

<template>
    <div class="h-screen bg-gray-50 flex flex-col overflow-hidden">
        <div class="flex flex-col lg:flex-row flex-1 min-h-0 relative overflow-hidden">
            <!-- Sidebar (Desktop) -->
            <aside 
                class="hidden lg:flex w-72 bg-slate-900 border-r border-slate-800 flex-col h-full z-50 transition-all duration-300 shadow-xl"
            >
                <!-- Logo Section -->
                <div class="p-6 border-b border-slate-800 flex items-center justify-between">
                    <Link :href="route('admin.dashboard')" class="flex items-center gap-3">
                        <ApplicationLogo class="w-10 h-10 fill-current text-indigo-400" />
                        <div>
                            <span class="text-white font-bold text-lg block leading-tight">SparkOtto</span>
                            <span class="text-slate-500 text-xs font-medium uppercase tracking-wider">Administration</span>
                        </div>
                    </Link>
                </div>

                <!-- Main Navigation -->
                <nav class="flex-1 overflow-y-auto p-4 space-y-6">
                    <!-- Section DASHBOARDS -->
                    <div>
                        <h3 class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Statistiques</h3>
                        <div class="space-y-1">
                            <Link 
                                v-if="canViewDashboard"
                                :href="route('admin.dashboard')" 
                                :class="route().current('admin.dashboard') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all group"
                            >
                                <span class="text-lg opacity-80 group-hover:opacity-100">📊</span>
                                Tableau de bord
                            </Link>
                            <Link 
                                v-if="canViewDashboard"
                                :href="route('admin.mobility-report')" 
                                :class="route().current('admin.mobility-report') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all group"
                            >
                                <span class="text-lg opacity-80 group-hover:opacity-100">🌿</span>
                                Rapport RSE
                            </Link>
                        </div>
                    </div>

                    <!-- Section FLOTTE -->
                    <div v-if="showVehiclesMenu || canViewAgences">
                        <h3 class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Gestion Flotte</h3>
                        <div class="space-y-1">
                            <Link 
                                v-if="canViewVehicles"
                                :href="route('admin.vehicles.availability')" 
                                :class="route().current('admin.vehicles.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all group"
                            >
                                <span class="text-lg opacity-80 group-hover:opacity-100">🚗</span>
                                Véhicules
                            </Link>

                            <Link 
                                v-if="canViewAgences"
                                :href="route('admin.agences.index')" 
                                :class="route().current('admin.agences.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all group"
                            >
                                <span class="text-lg opacity-80 group-hover:opacity-100">📍</span>
                                Agences
                            </Link>
                            
                            <Link 
                                v-if="canViewVehicleSuggestion"
                                :href="route('admin.settings.vehicleSuggestion.edit')" 
                                :class="route().current('admin.settings.vehicleSuggestion.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all group"
                            >
                                <span class="text-lg opacity-80 group-hover:opacity-100">✨</span>
                                Suggestion
                            </Link>

                            <Link 
                                v-if="canViewVehicles"
                                :href="route('admin.maintenances.index')" 
                                :class="route().current('admin.maintenances.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all group"
                            >
                                <span class="text-lg opacity-80 group-hover:opacity-100">🔧</span>
                                Entretien
                            </Link>
                        </div>
                    </div>

                    <!-- Section ORGANISATION -->
                    <div v-if="canViewUsers || canViewRoles || canViewDomains">
                        <h3 class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Utilisateurs & Accès</h3>
                        <div class="space-y-1">
                            <Link 
                                v-if="canViewUsers"
                                :href="route('admin.users.index')" 
                                :class="route().current('admin.users.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all group"
                            >
                                <span class="text-lg opacity-80 group-hover:opacity-100">👥</span>
                                Utilisateurs
                            </Link>
                            
                            <Link 
                                v-if="canViewRoles"
                                :href="route('admin.roles.index')" 
                                :class="route().current('admin.roles.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all group"
                            >
                                <span class="text-lg opacity-80 group-hover:opacity-100">🛡️</span>
                                Rôles & Droits
                            </Link>
                            
                            <Link 
                                v-if="canViewDomains"
                                :href="route('admin.domains.index')" 
                                :class="route().current('admin.domains.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all group"
                            >
                                <span class="text-lg opacity-80 group-hover:opacity-100">🌐</span>
                                Domaines
                            </Link>
                        </div>
                    </div>
                </nav>

                <!-- Sidebar Footer -->
                <div class="p-4 bg-slate-950 border-t border-slate-800 space-y-3">
                    <Link 
                        :href="route('dashboard')"
                        class="flex items-center justify-center gap-2 w-full px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-lg transition-all border border-slate-700 shadow-sm"
                    >
                        <span>🚀</span>
                        VUE CONDUCTEUR
                    </Link>
                </div>
            </aside>

            <!-- Mobile Navbar Header -->
            <nav class="lg:hidden bg-slate-900 border-b border-slate-800 p-4 shrink-0 z-50 flex items-center justify-between">
                <div class="flex items-center gap-3 overflow-hidden">
                    <button 
                        @click="showingNavigationDropdown = !showingNavigationDropdown" 
                        class="p-1 -ml-1 text-slate-400 hover:text-white transition-colors"
                    >
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <div class="text-sm font-bold text-white truncate max-w-[180px]">
                        <slot name="header">SparkOtto</slot>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <Link :href="route('dashboard')" class="text-indigo-400">
                            <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center font-bold text-indigo-500 border border-indigo-500/20 text-xs">⚡</div>
                    </Link>
                </div>
            </nav>

            <!-- Responsive Sidebar (Mobile Drawer) -->
            <transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="-translate-x-full"
                enter-to-class="translate-x-0"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="translate-x-0"
                leave-to-class="-translate-x-full"
            >
                <div v-if="showingNavigationDropdown" class="lg:hidden fixed inset-0 z-[55] flex">
                    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showingNavigationDropdown = false"></div>
                    <aside class="relative flex flex-col w-72 bg-slate-900 h-full shadow-2xl border-r border-slate-800">
                        <div class="p-6 border-b border-slate-800 flex items-center justify-between">
                            <Link :href="route('admin.dashboard')" class="flex items-center gap-2">
                                <ApplicationLogo class="w-8 h-8 fill-current text-indigo-400" />
                                <span class="text-white font-bold">SparkOtto</span>
                            </Link>
                            <button @click="showingNavigationDropdown = false" class="text-slate-400 hover:text-white">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        
                        <nav class="flex-1 overflow-y-auto p-4 space-y-6">
                            <!-- Stats -->
                            <div>
                                <h3 class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Statistiques</h3>
                                <div class="space-y-1">
                                    <Link v-if="canViewDashboard" :href="route('admin.dashboard')" class="flex items-center gap-3 px-3 py-2 text-slate-300 text-sm hover:text-white hover:bg-slate-800 rounded-lg">📊 Dashboard</Link>
                                    <Link v-if="canViewDashboard" :href="route('admin.mobility-report')" class="flex items-center gap-3 px-3 py-2 text-slate-300 text-sm hover:text-white hover:bg-slate-800 rounded-lg">🌿 Rapport RSE</Link>
                                </div>
                            </div>
                            <!-- Flotte -->
                            <div v-if="showVehiclesMenu || canViewAgences">
                                <h3 class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Gestion Flotte</h3>
                                <div class="space-y-1">
                                    <Link v-if="canViewVehicles" :href="route('admin.vehicles.availability')" class="flex items-center gap-3 px-3 py-2 text-slate-300 text-sm hover:text-white hover:bg-slate-800 rounded-lg">🚗 Véhicules</Link>
                                    <Link v-if="canViewVehicles" :href="route('admin.maintenances.index')" class="flex items-center gap-3 px-3 py-2 text-slate-300 text-sm hover:text-white hover:bg-slate-800 rounded-lg">🔧 Entretien</Link>
                                    <Link v-if="canViewAgences" :href="route('admin.agences.index')" class="flex items-center gap-3 px-3 py-2 text-slate-300 text-sm hover:text-white hover:bg-slate-800 rounded-lg">📍 Agences</Link>
                                    <Link v-if="canViewVehicleSuggestion" :href="route('admin.settings.vehicleSuggestion.edit')" class="flex items-center gap-3 px-3 py-2 text-slate-300 text-sm hover:text-white hover:bg-slate-800 rounded-lg">✨ Suggestion</Link>
                                </div>
                            </div>
                            <!-- Admin -->
                            <div v-if="canViewUsers || canViewRoles || canViewDomains">
                                <h3 class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Utilisateurs</h3>
                                <div class="space-y-1">
                                    <Link v-if="canViewUsers" :href="route('admin.users.index')" class="flex items-center gap-3 px-3 py-2 text-slate-300 text-sm hover:text-white hover:bg-slate-800 rounded-lg">👥 Utilisateurs</Link>
                                    <Link v-if="canViewRoles" :href="route('admin.roles.index')" class="flex items-center gap-3 px-3 py-2 text-slate-300 text-sm hover:text-white hover:bg-slate-800 rounded-lg">🛡️ Rôles</Link>
                                    <Link v-if="canViewDomains" :href="route('admin.domains.index')" class="flex items-center gap-3 px-3 py-2 text-slate-300 text-sm hover:text-white hover:bg-slate-800 rounded-lg">🌐 Domaines</Link>
                                </div>
                            </div>
                        </nav>

                        <div class="p-4 bg-slate-950 border-t border-slate-800 space-y-2">
                            <Link :href="route('dashboard')" class="flex items-center justify-center gap-2 w-full px-4 py-2 bg-slate-800 text-slate-200 text-xs font-bold rounded-lg border border-slate-700">🚀 VUE CONDUCTEUR</Link>
                        </div>
                    </aside>
                </div>
            </transition>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col min-w-0 bg-gray-50 h-full overflow-hidden">
                <!-- Top Header Bar (Desktop Only) -->
                <header class="hidden lg:flex h-16 bg-white border-b border-gray-200 px-4 lg:px-8 items-center justify-between shrink-0">
                    <div>
                        <h2 class="text-base font-semibold text-gray-800 leading-tight">
                            <slot name="header" />
                        </h2>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="hidden sm:flex flex-col text-right" v-if="$page.props.auth && $page.props.auth.user">
                             <span class="text-sm font-bold text-gray-900 leading-none">{{ $page.props.auth.user.name }}</span>
                             <span class="text-[10px] text-gray-500 font-medium uppercase tracking-tighter mt-1">Administrateur</span>
                        </div>

                        <Dropdown align="right" width="48" v-if="$page.props.auth && $page.props.auth.user">
                            <template #trigger>
                                <button class="w-10 h-10 rounded-full bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm hover:bg-indigo-100 transition-colors">
                                    {{ $page.props.auth.user.name.charAt(0) }}
                                </button>
                            </template>
                            <template #content>
                                <DropdownLink :href="route('profile.edit')">Profil</DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">Déconnexion</DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 overflow-y-auto p-4 lg:p-8 relative">
                    <div class="max-w-7xl mx-auto">
                        <slot />
                    </div>
                </main>

                <!-- Footer -->
                <footer class="bg-white border-t border-gray-200 py-4 px-4 lg:px-8 shrink-0">
                    <div class="flex justify-between items-center text-[10px] text-gray-400 font-medium tracking-tight">
                        <span>© 2025 SPARKOTTO FLEET MANAGEMENT</span>
                        <div class="flex gap-4">
                            <Link href="/terms" class="hover:text-gray-600 transition-colors">CONDITIONS</Link>
                            <Link href="/privacy" class="hover:text-gray-600 transition-colors">PRIVACY</Link>
                            <span v-if="page.props.appVersion" class="bg-gray-100 text-gray-500 px-1.5 rounded">v{{ page.props.appVersion }}</span>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    </div>
</template>
