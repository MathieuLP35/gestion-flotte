<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import useAdminPermissions from '@/Composables/useAdminPermissions';
import CommandPalette from '@/Components/CommandPalette.vue';

const showingNavigationDropdown = ref(false);
const isSearchOpen = ref(false);
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

const toggleSearch = (e) => {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        isSearchOpen.value = !isSearchOpen.value;
    }
};

onMounted(() => window.addEventListener('keydown', toggleSearch));
onUnmounted(() => window.removeEventListener('keydown', toggleSearch));

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
                            <span class="text-slate-500 text-xs font-medium uppercase tracking-wider">{{ $t('nav.admin') }}</span>
                        </div>
                    </Link>
                </div>

                <!-- Main Navigation -->
                <nav class="flex-1 overflow-y-auto p-4 space-y-6">
                    <!-- Section DASHBOARDS -->
                    <div>
                        <h3 class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">{{ $t('admin.statistics') }}</h3>
                        <div class="space-y-1">
                            <Link 
                                v-if="canViewDashboard"
                                :href="route('admin.dashboard')" 
                                :class="route().current('admin.dashboard') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all group"
                            >
                                <span class="text-lg opacity-80 group-hover:opacity-100"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg></span>
                                {{ $t('nav.dashboard') }}
                            </Link>
                            <Link 
                                v-if="canViewDashboard"
                                :href="route('admin.mobility-report')" 
                                :class="route().current('admin.mobility-report') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all group"
                            >
                                <span class="text-lg opacity-80 group-hover:opacity-100"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg></span>
                                {{ $t('admin.csr_report') }}
                            </Link>
                        </div>
                    </div>

                    <!-- Section FLOTTE -->
                    <div v-if="showVehiclesMenu || canViewAgences">
                        <h3 class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">{{ $t('admin.fleet_mgmt') }}</h3>
                        <div class="space-y-1">
                            <Link 
                                v-if="canViewVehicles"
                                :href="route('admin.vehicles.availability')" 
                                :class="route().current('admin.vehicles.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all group"
                            >
                                <span class="text-lg opacity-80 group-hover:opacity-100"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-1.1 0-2 .9-2 2v8c0 1.1.9 2 2 2h2m10 0a2 2 0 10-4 0m4 0a2 2 0 11-4 0m-10 0a2 2 0 10-4 0m4 0a2 2 0 11-4 0m8-8H4"/></svg></span>
                                {{ $t('admin.vehicles') }}
                            </Link>

                            <Link 
                                v-if="canViewAgences"
                                :href="route('admin.agences.index')" 
                                :class="route().current('admin.agences.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all group"
                            >
                                <span class="text-lg opacity-80 group-hover:opacity-100"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg></span>
                                {{ $t('admin.agences') }}
                            </Link>
                            
                            <Link 
                                v-if="canViewVehicleSuggestion"
                                :href="route('admin.settings.vehicleSuggestion.edit')" 
                                :class="route().current('admin.settings.vehicleSuggestion.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all group"
                            >
                                <span class="text-lg opacity-80 group-hover:opacity-100"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg></span>
                                {{ $t('admin.suggestion') }}
                            </Link>

                            <Link 
                                v-if="canViewVehicles"
                                :href="route('admin.maintenances.index')" 
                                :class="route().current('admin.maintenances.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all group"
                            >
                                <span class="text-lg opacity-80 group-hover:opacity-100"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg></span>
                                {{ $t('admin.maintenance') }}
                            </Link>
                        </div>
                    </div>

                    <!-- Section ORGANISATION -->
                    <div v-if="canViewUsers || canViewRoles || canViewDomains">
                        <h3 class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">{{ $t('admin.users_access') }}</h3>
                        <div class="space-y-1">
                            <Link 
                                v-if="canViewUsers"
                                :href="route('admin.users.index')" 
                                :class="route().current('admin.users.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all group"
                            >
                                <span class="text-lg opacity-80 group-hover:opacity-100"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg></span>
                                {{ $t('admin.users') }}
                            </Link>
                            
                            <Link 
                                v-if="canViewRoles"
                                :href="route('admin.roles.index')" 
                                :class="route().current('admin.roles.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all group"
                            >
                                <span class="text-lg opacity-80 group-hover:opacity-100"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg></span>
                                {{ $t('admin.roles') }}
                            </Link>
                            
                            <Link 
                                v-if="canViewDomains"
                                :href="route('admin.domains.index')" 
                                :class="route().current('admin.domains.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all group"
                            >
                                <span class="text-lg opacity-80 group-hover:opacity-100"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg></span>
                                {{ $t('admin.domains') }}
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
                        <span><svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg></span>
                        {{ $t('admin.driver_view') }}
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
                            <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center font-bold text-indigo-500 border border-indigo-500/20 text-xs"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg></div>
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
                                    <Link v-if="canViewDashboard" :href="route('admin.dashboard')" class="flex items-center gap-3 px-3 py-2 text-slate-300 text-sm hover:text-white hover:bg-slate-800 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg> Dashboard</Link>
                                    <Link v-if="canViewDashboard" :href="route('admin.mobility-report')" class="flex items-center gap-3 px-3 py-2 text-slate-300 text-sm hover:text-white hover:bg-slate-800 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg> Rapport RSE</Link>
                                </div>
                            </div>
                            <!-- Flotte -->
                            <div v-if="showVehiclesMenu || canViewAgences">
                                <h3 class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Gestion Flotte</h3>
                                <div class="space-y-1">
                                    <Link v-if="canViewVehicles" :href="route('admin.vehicles.availability')" class="flex items-center gap-3 px-3 py-2 text-slate-300 text-sm hover:text-white hover:bg-slate-800 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-1.1 0-2 .9-2 2v8c0 1.1.9 2 2 2h2m10 0a2 2 0 10-4 0m4 0a2 2 0 11-4 0m-10 0a2 2 0 10-4 0m4 0a2 2 0 11-4 0m8-8H4"/></svg> Véhicules</Link>
                                    <Link v-if="canViewVehicles" :href="route('admin.maintenances.index')" class="flex items-center gap-3 px-3 py-2 text-slate-300 text-sm hover:text-white hover:bg-slate-800 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg> Entretien</Link>
                                    <Link v-if="canViewAgences" :href="route('admin.agences.index')" class="flex items-center gap-3 px-3 py-2 text-slate-300 text-sm hover:text-white hover:bg-slate-800 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg> Agences</Link>
                                    <Link v-if="canViewVehicleSuggestion" :href="route('admin.settings.vehicleSuggestion.edit')" class="flex items-center gap-3 px-3 py-2 text-slate-300 text-sm hover:text-white hover:bg-slate-800 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg> Suggestion</Link>
                                </div>
                            </div>
                            <!-- Admin -->
                            <div v-if="canViewUsers || canViewRoles || canViewDomains">
                                <h3 class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Utilisateurs</h3>
                                <div class="space-y-1">
                                    <Link v-if="canViewUsers" :href="route('admin.users.index')" class="flex items-center gap-3 px-3 py-2 text-slate-300 text-sm hover:text-white hover:bg-slate-800 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg> Utilisateurs</Link>
                                    <Link v-if="canViewRoles" :href="route('admin.roles.index')" class="flex items-center gap-3 px-3 py-2 text-slate-300 text-sm hover:text-white hover:bg-slate-800 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg> Rôles</Link>
                                    <Link v-if="canViewDomains" :href="route('admin.domains.index')" class="flex items-center gap-3 px-3 py-2 text-slate-300 text-sm hover:text-white hover:bg-slate-800 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg> Domaines</Link>
                                </div>
                            </div>
                        </nav>

                        <div class="p-4 bg-slate-950 border-t border-slate-800 space-y-2">
                            <Link :href="route('dashboard')" class="flex items-center justify-center gap-2 w-full px-4 py-2 bg-slate-800 text-slate-200 text-xs font-bold rounded-lg border border-slate-700"><svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg> VUE CONDUCTEUR</Link>
                        </div>
                    </aside>
                </div>
            </transition>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col min-w-0 bg-gray-50 h-full overflow-hidden">
                <!-- Top Header Bar (Desktop Only) -->
                <header class="hidden lg:flex h-16 bg-white border-b border-gray-200 px-4 lg:px-8 items-center justify-between shrink-0">
                    <div class="flex items-center gap-6">
                        <h2 class="text-base font-semibold text-gray-800 leading-tight">
                            <slot name="header" />
                        </h2>
                    </div>

                    <div class="flex items-center gap-4">
                        <!-- Bouton Recherche (Style Pill Alignè à Droite) -->
                        <button 
                            @click="isSearchOpen = true"
                            class="hidden md:flex items-center gap-3 px-5 py-2 bg-gray-50/50 border border-gray-100 rounded-full text-gray-400 hover:text-indigo-600 hover:bg-white hover:border-indigo-200 transition-all group"
                        >
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            <span class="text-sm font-medium tracking-tight">{{ $t('search.placeholder') }}</span>
                            <span class="ml-2 text-[10px] font-bold bg-white border border-gray-100 px-2 py-1 rounded-lg leading-none shadow-sm group-hover:border-indigo-100">⌘K</span>
                        </button>
                        <div class="hidden sm:flex flex-col text-right" v-if="$page.props.auth && $page.props.auth.user">
                             <span class="text-sm font-bold text-gray-900 leading-none">{{ $page.props.auth.user.name }}</span>
                             <span class="text-[10px] text-gray-500 font-medium uppercase tracking-tighter mt-1">{{ $t('admin.role_admin') }}</span>
                        </div>

                        <!-- Language Switcher -->
                        <div class="relative flex items-center">
                            <Link 
                                v-if="$page.props.locale === 'en'"
                                :href="route('language.update')" method="post" as="button" :data="{ language: 'fr' }"
                                class="w-8 h-8 rounded bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-sm font-bold text-gray-700 transition"
                                :title="$t('lang.switch_to_fr')"
                            >FR</Link>
                            <Link 
                                v-else
                                :href="route('language.update')" method="post" as="button" :data="{ language: 'en' }"
                                class="w-8 h-8 rounded bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-sm font-bold text-gray-700 transition"
                                :title="$t('lang.switch_to_en')"
                            >EN</Link>
                        </div>

                        <Dropdown align="right" width="48" v-if="$page.props.auth && $page.props.auth.user">
                            <template #trigger>
                                <button class="w-10 h-10 rounded-full bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm hover:bg-indigo-100 transition-colors">
                                    {{ $page.props.auth.user.name.charAt(0) }}
                                </button>
                            </template>
                            <template #content>
                                <DropdownLink :href="route('profile.edit')">{{ $t('nav.profile') }}</DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">{{ $t('nav.logout') }}</DropdownLink>
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
                            <Link href="/terms" class="hover:text-gray-600 transition-colors">{{ $t('nav.terms').toUpperCase() }}</Link>
                            <Link href="/privacy" class="hover:text-gray-600 transition-colors">{{ $t('nav.privacy').toUpperCase() }}</Link>
                            <span v-if="page.props.appVersion" class="bg-gray-100 text-gray-500 px-1.5 rounded">v{{ page.props.appVersion }}</span>
                        </div>
                    </div>
                </footer>
            </div>
        </div>

        <!-- Global Search Search Modal (Command Palette) -->
        <CommandPalette 
            :is-open="isSearchOpen" 
            @close="isSearchOpen = false" 
        />
    </div>
</template>
