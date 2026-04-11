<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import debounce from 'lodash.debounce';

const props = defineProps({
    isOpen: Boolean
});

const emit = defineEmits(['close']);

const searchQuery = ref('');
const results = ref({
    vehicles: [],
    users: [],
    maintenances: [],
    agencies: []
});
const isLoading = ref(false);
const selectedIndex = ref(0);

// On aplatit les résultats pour la navigation au clavier
const flatResults = ref([]);

const search = debounce(async (query) => {
    if (query.length < 2) {
        results.value = { vehicles: [], users: [], maintenances: [], agencies: [] };
        flatResults.value = [];
        return;
    }

    isLoading.value = true;
    try {
        const response = await axios.get(route('admin.api.search'), {
            params: { q: query }
        });
        results.value = response.data;
        
        // Créer la liste plate pour la navigation au clavier
        flatResults.value = [
            ...response.data.vehicles.map(i => ({ ...i, type: 'vehicle' })),
            ...response.data.agencies.map(i => ({ ...i, type: 'agency' })),
            ...response.data.maintenances.map(i => ({ ...i, type: 'maintenance' })),
            ...response.data.users.map(i => ({ ...i, type: 'user' }))
        ];
        selectedIndex.value = 0;
    } catch (error) {
        console.error('Search error:', error);
    } finally {
        isLoading.value = false;
    }
}, 300);

watch(searchQuery, (newQuery) => {
    search(newQuery);
});

const navigateTo = (item) => {
    emit('close');
    searchQuery.value = '';
    router.get(item.url);
};

const onKeyDown = (e) => {
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        selectedIndex.value = (selectedIndex.value + 1) % flatResults.value.length;
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        selectedIndex.value = (selectedIndex.value - 1 + flatResults.value.length) % flatResults.value.length;
    } else if (e.key === 'Enter') {
        e.preventDefault();
        if (flatResults.value[selectedIndex.value]) {
            navigateTo(flatResults.value[selectedIndex.value]);
        }
    } else if (e.key === 'Escape') {
        emit('close');
    }
};

onMounted(() => {
    window.addEventListener('keydown', (e) => {
        if (props.isOpen) onKeyDown(e);
    });
});
</script>

<template>
    <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="isOpen" class="fixed inset-0 z-[100] flex items-start justify-center p-4 sm:p-6 lg:p-20 bg-slate-900/60 backdrop-blur-sm" @click.self="$emit('close')">
            <transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 -translate-y-4 scale-95"
                enter-to-class="opacity-100 translate-y-0 scale-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100 translate-y-0 scale-100"
                leave-to-class="opacity-0 -translate-y-4 scale-95"
            >
                <div class="bg-white w-full max-w-2xl rounded-[2.5rem] shadow-2xl overflow-hidden mt-10 border border-gray-100">
                    <!-- Barre de recherche -->
                    <div class="p-6 border-b border-gray-100 flex items-center gap-4 bg-gray-50/50">
                        <div class="w-10 h-10 rounded-2xl bg-white border border-gray-200 flex items-center justify-center text-indigo-600 shadow-sm">
                            <svg v-if="!isLoading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            <svg v-else class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        </div>
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Rechercher un véhicule, un agent, une facture..." 
                            class="bg-transparent border-none focus:ring-0 text-lg font-black text-gray-900 placeholder:text-gray-400 flex-1 p-0 px-2"
                            autofocus
                        />
                        <button @click="$emit('close')" class="text-[10px] font-black text-gray-400 uppercase tracking-widest bg-white border border-gray-200 px-3 py-2 rounded-xl hover:bg-gray-50 transition-colors shadow-sm">ESC</button>
                    </div>

                    <!-- Résultats -->
                    <div class="max-h-[60vh] overflow-y-auto p-4 custom-scrollbar bg-white">
                        <div v-if="flatResults.length > 0">
                            <!-- Véhicules -->
                            <div v-if="results.vehicles.length > 0" class="mb-6">
                                <h3 class="px-4 text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mb-3">Véhicules & Flotte</h3>
                                <div class="space-y-1">
                                    <button
                                        v-for="(v, idx) in results.vehicles"
                                        :key="'v-'+v.id"
                                        @click="navigateTo(v)"
                                        @mouseenter="selectedIndex = flatResults.findIndex(i => i.id === v.id && i.type === 'vehicle')"
                                        :class="selectedIndex === flatResults.findIndex(i => i.id === v.id && i.type === 'vehicle') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100 scale-[1.02]' : 'hover:bg-gray-50 text-gray-900'"
                                        class="w-full flex items-center justify-between p-4 rounded-2xl transition-all group text-left"
                                    >
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl" :class="selectedIndex === flatResults.findIndex(i => i.id === v.id && i.type === 'vehicle') ? 'bg-white/20' : 'bg-gray-100 text-gray-400'">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-1.1 0-2 .9-2 2v8c0 1.1.9 2 2 2h2m10 0a2 2 0 10-4 0m4 0a2 2 0 11-4 0m-10 0a2 2 0 10-4 0m4 0a2 2 0 11-4 0m8-8H4"/></svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-black tracking-tight" :class="selectedIndex === flatResults.findIndex(i => i.id === v.id && i.type === 'vehicle') ? 'text-white' : 'text-gray-900'">{{ v.title }}</p>
                                                <p class="text-[10px] font-bold uppercase tracking-widest mt-0.5" :class="selectedIndex === flatResults.findIndex(i => i.id === v.id && i.type === 'vehicle') ? 'text-indigo-100' : 'text-gray-400'">{{ v.subtitle }}</p>
                                            </div>
                                        </div>
                                        <svg class="w-5 h-5 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Agences -->
                            <div v-if="results.agencies.length > 0" class="mb-6">
                                <h3 class="px-4 text-[10px] font-black text-emerald-400 uppercase tracking-[0.2em] mb-3">Localisations & Agences</h3>
                                <div class="space-y-1">
                                    <button
                                        v-for="(a, idx) in results.agencies"
                                        :key="'a-'+a.id"
                                        @click="navigateTo(a)"
                                        @mouseenter="selectedIndex = flatResults.findIndex(i => i.id === a.id && i.type === 'agency')"
                                        :class="selectedIndex === flatResults.findIndex(i => i.id === a.id && i.type === 'agency') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-100 scale-[1.02]' : 'hover:bg-gray-50 text-gray-900'"
                                        class="w-full flex items-center justify-between p-4 rounded-2xl transition-all group text-left"
                                    >
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl" :class="selectedIndex === flatResults.findIndex(i => i.id === a.id && i.type === 'agency') ? 'bg-white/20' : 'bg-gray-100 text-gray-400'">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-black tracking-tight" :class="selectedIndex === flatResults.findIndex(i => i.id === a.id && i.type === 'agency') ? 'text-white' : 'text-gray-900'">{{ a.title }}</p>
                                                <p class="text-[10px] font-bold uppercase tracking-widest mt-0.5" :class="selectedIndex === flatResults.findIndex(i => i.id === a.id && i.type === 'agency') ? 'text-emerald-100' : 'text-gray-400'">{{ a.subtitle }}</p>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- Entretiens -->
                            <div v-if="results.maintenances.length > 0" class="mb-6">
                                <h3 class="px-4 text-[10px] font-black text-amber-500 uppercase tracking-[0.2em] mb-3">Maintenance & Technique</h3>
                                <div class="space-y-1">
                                    <button
                                        v-for="(m, idx) in results.maintenances"
                                        :key="'m-'+m.id"
                                        @click="navigateTo(m)"
                                        @mouseenter="selectedIndex = flatResults.findIndex(i => i.id === m.id && i.type === 'maintenance')"
                                        :class="selectedIndex === flatResults.findIndex(i => i.id === m.id && i.type === 'maintenance') ? 'bg-amber-500 text-white shadow-lg shadow-amber-100 scale-[1.02]' : 'hover:bg-gray-50 text-gray-900'"
                                        class="w-full flex items-center justify-between p-4 rounded-2xl transition-all group text-left"
                                    >
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl" :class="selectedIndex === flatResults.findIndex(i => i.id === m.id && i.type === 'maintenance') ? 'bg-white/20' : 'bg-gray-100 text-gray-400'">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-black tracking-tight" :class="selectedIndex === flatResults.findIndex(i => i.id === m.id && i.type === 'maintenance') ? 'text-white' : 'text-gray-900'">{{ m.title }}</p>
                                                <p class="text-[10px] font-bold uppercase tracking-widest mt-0.5" :class="selectedIndex === flatResults.findIndex(i => i.id === m.id && i.type === 'maintenance') ? 'text-amber-50' : 'text-gray-400'">{{ m.subtitle }}</p>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- Utilisateurs -->
                            <div v-if="results.users.length > 0">
                                <h3 class="px-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Collaborateurs</h3>
                                <div class="space-y-1">
                                    <button
                                        v-for="(u, idx) in results.users"
                                        :key="'u-'+u.id"
                                        @click="navigateTo(u)"
                                        @mouseenter="selectedIndex = flatResults.findIndex(i => i.id === u.id && i.type === 'user')"
                                        :class="selectedIndex === flatResults.findIndex(i => i.id === u.id && i.type === 'user') ? 'bg-slate-800 text-white shadow-lg scale-[1.02]' : 'hover:bg-gray-50 text-gray-900'"
                                        class="w-full flex items-center justify-between p-4 rounded-2xl transition-all group text-left"
                                    >
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl" :class="selectedIndex === flatResults.findIndex(i => i.id === u.id && i.type === 'user') ? 'bg-white/20' : 'bg-gray-100 text-gray-400'">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-black tracking-tight" :class="selectedIndex === flatResults.findIndex(i => i.id === u.id && i.type === 'user') ? 'text-white' : 'text-gray-900'">{{ u.title }}</p>
                                                <p class="text-[10px] font-bold uppercase tracking-widest mt-0.5" :class="selectedIndex === flatResults.findIndex(i => i.id === u.id && i.type === 'user') ? 'text-slate-400' : 'text-gray-400'">{{ u.subtitle }}</p>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- État vide ou initial -->
                        <div v-else class="py-20 flex flex-col items-center justify-center text-gray-300">
                            <div v-if="searchQuery.length === 0" class="text-center">
                                <div class="w-20 h-20 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-6 text-2xl border border-gray-100"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg></div>
                                <p class="text-xs font-black uppercase tracking-[0.2em] text-gray-400">Tapez pour explorer SparkOtto</p>
                                <div class="mt-8 flex flex-wrap justify-center gap-2">
                                    <span class="px-3 py-1 bg-gray-50 border border-gray-100 rounded-lg text-[10px] font-bold text-gray-400">Modèles</span>
                                    <span class="px-3 py-1 bg-gray-50 border border-gray-100 rounded-lg text-[10px] font-bold text-gray-400">Noms</span>
                                    <span class="px-3 py-1 bg-gray-50 border border-gray-100 rounded-lg text-[10px] font-bold text-gray-400">Plaques</span>
                                    <span class="px-3 py-1 bg-gray-50 border border-gray-100 rounded-lg text-[10px] font-bold text-gray-400">Emails</span>
                                </div>
                            </div>
                            <div v-else-if="!isLoading" class="text-center">
                                <div class="w-20 h-20 rounded-full bg-rose-50 flex items-center justify-center mx-auto mb-6 text-2xl border border-rose-100"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg></div>
                                <p class="text-xs font-black uppercase tracking-[0.2em] text-rose-400">Aucun résultat pour "{{ searchQuery }}"</p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="p-4 bg-gray-50 border-t border-gray-100 flex justify-between items-center px-8">
                        <div class="flex items-center gap-6">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-black bg-white border border-gray-200 px-2 py-1 rounded-lg leading-none shadow-sm text-gray-400">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                                </span>
                                <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none">Naviguer</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-black bg-white border border-gray-200 px-2 py-1 rounded-lg leading-none shadow-sm text-gray-400 font-mono">ENTER</span>
                                <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none">Ouvrir</span>
                            </div>
                        </div>
                        <div class="text-[9px] font-black text-indigo-500 uppercase tracking-[0.2em] flex items-center gap-2">
                            <span class="w-1.5 h-1.5 bg-indigo-500 rounded-full animate-pulse"></span>
                            Recherche Universelle
                        </div>
                    </div>
                </div>
            </transition>
        </div>
    </transition>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>
