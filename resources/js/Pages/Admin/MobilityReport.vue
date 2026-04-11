<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';
import { Doughnut } from 'vue-chartjs';
import { ref, watch } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';

ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps({
    reports: {
        type: Array,
        default: () => []
    }
});

const defaultPeriod = () => ({ year: new Date().getFullYear(), month: 'all' });

const periods = ref(
    props.reports.length > 0 
        ? props.reports.map(r => ({ year: r.year, month: r.month }))
        : [defaultPeriod()]
);

const isLoading = ref(false);

const addPeriod = () => {
    periods.value.push(defaultPeriod());
};

const removePeriod = (index) => {
    if (periods.value.length > 1) {
        periods.value.splice(index, 1);
    }
};

const exportReport = (type) => {
    const periodsParam = JSON.stringify(periods.value);
    window.location.href = route('admin.mobility-report.export', { type, periods: periodsParam });
};

watch(periods, (newPeriods) => {
    router.get(
        route('admin.mobility-report'),
        { periods: JSON.stringify(newPeriods) },
        {
            preserveState: true,
            preserveScroll: true,
            onStart: () => isLoading.value = true,
            onFinish: () => isLoading.value = false,
        }
    );
}, { deep: true });

const getChartData = (report) => {
    const labels = report.vehicleEnergyStats.map(stat => stat.energie.toUpperCase());
    const data = report.vehicleEnergyStats.map(stat => stat.total);
    const backgroundColors = labels.map(label => {
        if (label === 'ELECTRIQUE') return '#10b981';
        if (label === 'HYBRIDE') return '#3b82f6';
        if (label === 'ESSENCE') return '#f59e0b';
        if (label === 'DIESEL') return '#ef4444';
        return '#6b7280';
    });

    return {
        labels,
        datasets: [{ data, backgroundColor: backgroundColors, borderWidth: 0 }]
    };
};

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: { usePointStyle: true, padding: 20 }
        }
    },
    cutout: '70%'
};
</script>

<template>
    <Head>
        <title>Rapport de Mobilité RSE – RH</title>
    </Head>

    <AdminLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Rapport de Mobilité Durable (RH)</h1>
                        <p class="mt-2 text-sm text-gray-500">Supervisez l'impact RSE de la flotte automobile et les économies de CO2 réalisées.</p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
                        <div class="flex space-x-2">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button title="Exporter les rapports" class="inline-flex justify-center items-center px-4 py-2 bg-gray-900 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-800 active:bg-gray-950 focus:outline-none focus:ring-2 focus:ring-gray-500 transition shadow-sm">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        Exporter PDF
                                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                </template>

                                <template #content>
                                    <button @click="exportReport('communication')" class="block w-full text-left px-4 py-3 text-sm leading-5 text-gray-700 hover:bg-emerald-50 focus:outline-none focus:bg-emerald-50 transition duration-150 ease-in-out font-medium border-b border-gray-100">
                                        <div class="flex items-center">
                                            <span class="text-emerald-500 mr-2">🎨</span>
                                            <span>Communication</span>
                                        </div>
                                    </button>
                                    <button @click="exportReport('technical')" class="block w-full text-left px-4 py-3 text-sm leading-5 text-gray-700 hover:bg-indigo-50 focus:outline-none focus:bg-indigo-50 transition duration-150 ease-in-out font-medium">
                                        <div class="flex items-center">
                                            <span class="text-indigo-500 mr-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg></span>
                                            <span>Technique</span>
                                        </div>
                                    </button>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </div>

                <!-- Comparer Periods -->
                <div class="flex flex-wrap items-center gap-4 mb-8">
                    <div v-for="(p, i) in periods" :key="i" class="bg-white rounded-xl shadow p-3 pr-10 relative flex items-center space-x-2 border border-gray-100">
                        <select v-model="p.year" class="border-gray-200 focus:border-indigo-500 rounded-lg text-sm font-medium py-1.5">
                            <option value="all">Toutes années</option>
                            <option v-for="y in 10" :key="y" :value="new Date().getFullYear() - y + 1">{{ new Date().getFullYear() - y + 1 }}</option>
                        </select>
                        <select v-model="p.month" :disabled="p.year === 'all'" class="border-gray-200 focus:border-indigo-500 rounded-lg text-sm font-medium py-1.5 disabled:opacity-50">
                            <option value="all">Mois entier</option>
                            <option value="1">Janv</option><option value="2">Févr</option><option value="3">Mars</option>
                            <option value="4">Avril</option><option value="5">Mai</option><option value="6">Juin</option>
                            <option value="7">Juil</option><option value="8">Août</option><option value="9">Sept</option>
                            <option value="10">Oct</option><option value="11">Nov</option><option value="12">Déc</option>
                        </select>
                        
                        <button v-if="periods.length > 1" @click="removePeriod(i)" class="absolute right-2 text-red-300 hover:text-red-500 transition p-1 hover:bg-red-50 rounded">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    
                    <button v-if="periods.length < 4" @click="addPeriod" class="h-10 px-4 rounded-xl border-2 border-dashed border-gray-300 text-gray-500 font-semibold text-sm hover:text-indigo-600 hover:border-indigo-600 hover:bg-indigo-50 transition">
                        + Ajouter comparaison
                    </button>
                </div>

                <!-- Loading State Overlay -->
                <div v-if="isLoading" class="fixed inset-0 bg-white/40 backdrop-blur-sm z-50 flex items-center justify-center pointer-events-none">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
                </div>

                <div class="grid gap-8 mb-8 items-start" :class="reports.length === 1 ? 'grid-cols-1' : 'grid-cols-1 xl:grid-cols-2'">
                    <div v-for="(report, rIndex) in reports" :key="'rep-'+rIndex" class="space-y-6 bg-gray-50/50 p-6 rounded-3xl border border-gray-100">
                        <div class="text-center font-extrabold text-2xl text-gray-900 bg-white shadow-sm rounded-xl py-3 border-t-4 border-indigo-500 mb-6">
                            {{ report.periodLabel }}
                        </div>
                        
                        <!-- KPIs -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Total CO2 -->
                            <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl shadow p-5 relative overflow-hidden group">
                                <div class="relative z-10 text-white">
                                    <h3 class="text-xs font-semibold text-emerald-100 uppercase tracking-wider mb-2">CO2 Global Économisé</h3>
                                    <div class="flex items-baseline space-x-2">
                                        <span class="text-4xl font-black tracking-tighter">{{ report.stats.total_co2_saved }}</span>
                                        <span class="text-sm font-medium text-emerald-100">kg</span>
                                    </div>
                                    <div v-if="report.year !== 'all'" class="mt-2 text-xs font-medium flex items-center space-x-1" :class="report.stats.delta_co2 >= 0 ? 'text-emerald-100' : 'text-red-200'">
                                        <svg v-if="report.stats.delta_co2 >= 0" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                        </svg>
                                        <svg v-else class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                                        </svg>
                                        <span>{{ report.stats.delta_co2 >= 0 ? '+' : '' }}{{ report.stats.delta_co2 }}%</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Carpools -->
                            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl shadow p-5 relative overflow-hidden group">
                                <div class="relative z-10 text-white">
                                    <h3 class="text-xs font-semibold text-indigo-100 uppercase tracking-wider mb-2">Trajets Covoiturés</h3>
                                    <div class="flex items-baseline space-x-2">
                                        <span class="text-4xl font-black tracking-tighter">{{ report.stats.total_carpools }}</span>
                                        <span class="text-sm font-medium text-indigo-100">trajets</span>
                                    </div>
                                    <div v-if="report.year !== 'all'" class="mt-2 text-xs font-medium flex items-center space-x-1" :class="report.stats.delta_carpools >= 0 ? 'text-indigo-100' : 'text-red-200'">
                                        <svg v-if="report.stats.delta_carpools >= 0" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                        </svg>
                                        <svg v-else class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                                        </svg>
                                        <span>{{ report.stats.delta_carpools >= 0 ? '+' : '' }}{{ report.stats.delta_carpools }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Agences and Chart -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Classement Agences -->
                            <div class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden flex flex-col">
                                <div class="p-4 border-b border-gray-100 flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center mr-3 text-emerald-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-gray-900">Top Agences</h3>
                                </div>
                                <div class="p-0 overflow-auto max-h-[300px]">
                                    <table class="min-w-full divide-y divide-gray-100">
                                        <thead class="bg-gray-50/50">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Agence</th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">CO2</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            <tr v-for="(agence, index) in report.agencesStats" :key="index" class="hover:bg-gray-50">
                                                <td class="px-4 py-3 text-sm text-gray-900">
                                                    <div class="flex items-center">
                                                        <span class="mr-2 text-gray-400 font-bold w-4">{{ index + 1 }}.</span>
                                                        {{ agence.name }}
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-right font-medium text-emerald-600 whitespace-nowrap">
                                                    {{ agence.co2_saved }} kg
                                                </td>
                                            </tr>
                                            <tr v-if="report.agencesStats.length === 0">
                                                <td colspan="2" class="px-4 py-6 text-center text-sm text-gray-500">Aucune donnée</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Typologie -->
                            <div class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden flex flex-col p-4">
                                <h3 class="text-sm font-bold text-gray-900 text-center mb-4">Typologie des véhicules</h3>
                                <div class="relative h-48 w-full flex items-center justify-center">
                                    <Doughnut v-if="report.vehicleEnergyStats.length > 0" :data="getChartData(report)" :options="chartOptions" />
                                    <div v-else class="text-sm text-gray-500">Aucune donnée</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
