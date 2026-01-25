<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminDashboardMap from '@/Components/AdminDashboardMap.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import useAdminPermissions from '@/Composables/useAdminPermissions';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';
import { Line, Doughnut, Bar } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Title, Tooltip, Legend, Filler);

defineOptions({ layout: AdminLayout });

const {
    canViewAgences,
    canViewRoles,
    canViewVehicles,
    canViewVehicleSuggestion,
    canViewUsers,
    canViewDomains,
} = useAdminPermissions();

const props = defineProps({
    agence: Object,
    stats: Object,
    recent_reservations: Array,
    chart_30j: Object,
    chart_12m: Object,
    map_reservations: Array,
});

const statusLabels = {
    'en attente': 'En attente',
    'validé': 'Validé',
    'en cours': 'En cours',
    'à retourner': 'À retourner',
    'terminé': 'Terminé',
    'annulé': 'Annulé',
};

const statusColors = {
    'en attente': 'bg-amber-100 text-amber-800 border-amber-200',
    'validé': 'bg-emerald-100 text-emerald-800 border-emerald-200',
    'en cours': 'bg-blue-100 text-blue-800 border-blue-200',
    'à retourner': 'bg-orange-100 text-orange-800 border-orange-200',
    'terminé': 'bg-slate-100 text-slate-700 border-slate-200',
    'annulé': 'bg-red-100 text-red-800 border-red-200',
};

const statusChartColors = ['#f59e0b', '#10b981', '#3b82f6', '#f97316', '#64748b', '#ef4444'];
const statusOrder = ['en attente', 'validé', 'en cours', 'à retourner', 'terminé', 'annulé'];

function formatDate(d) {
    if (!d) return '—';
    const dt = new Date(d);
    return dt.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

const evolution = (props.stats?.reservations_ce_mois ?? 0) - (props.stats?.reservations_mois_precedent ?? 0);
const evolutionPercent = props.stats?.reservations_mois_precedent
    ? Math.round((evolution / props.stats.reservations_mois_precedent) * 100)
    : 0;

// Données pour les graphiques
const chart30jData = computed(() => ({
    labels: props.chart_30j?.labels ?? [],
    datasets: [{
        label: 'Réservations',
        data: props.chart_30j?.data ?? [],
        borderColor: '#4f46e5',
        backgroundColor: 'rgba(79, 70, 229, 0.1)',
        fill: true,
        tension: 0.3,
    }],
}));

const chart12mData = computed(() => ({
    labels: props.chart_12m?.labels ?? [],
    datasets: [{
        label: 'Réservations',
        data: props.chart_12m?.data ?? [],
        backgroundColor: 'rgba(79, 70, 229, 0.7)',
        borderRadius: 4,
    }],
}));

const chartDoughnutData = computed(() => {
    const by = props.stats?.by_status ?? {};
    return {
        labels: statusOrder.map((k) => statusLabels[k] || k),
        datasets: [{
            data: statusOrder.map((k) => by[k] ?? 0),
            backgroundColor: statusChartColors,
            borderWidth: 1,
            borderColor: '#fff',
        }],
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: { mode: 'index', intersect: false },
    },
    scales: {},
};

const lineOptions = computed(() => ({
    ...chartOptions,
    scales: {
        x: { grid: { display: false } },
        y: { beginAtZero: true, ticks: { stepSize: 1 } },
    },
}));

const barOptions = computed(() => ({
    ...chartOptions,
    scales: {
        x: { grid: { display: false } },
        y: { beginAtZero: true, ticks: { stepSize: 1 } },
    },
}));

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'bottom' },
    },
};
</script>

<template>
    <Head title="Tableau de bord administration" />

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- En-tête -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Tableau de bord</h1>
                <p v-if="agence?.nom" class="mt-1 text-sm text-gray-500">Agence : {{ agence.nom }}</p>
                <p v-else class="mt-1 text-sm text-gray-500">Vue globale</p>
            </div>

            <!-- KPI -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 mb-8">
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Utilisateurs</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">{{ stats?.users_count ?? 0 }}</p>
                    <Link :href="route('admin.users.index')" class="mt-2 inline-block text-xs font-medium text-indigo-600 hover:text-indigo-800">Voir</Link>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Véhicules</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">{{ stats?.vehicles_count ?? 0 }}</p>
                    <Link :href="route('admin.vehicles.availability')" class="mt-2 inline-block text-xs font-medium text-indigo-600 hover:text-indigo-800">Voir</Link>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Réservations</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">{{ stats?.reservations_count ?? 0 }}</p>
                </div>
                <div v-if="!agence" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Agences</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">{{ stats?.agences_count ?? 0 }}</p>
                    <Link :href="route('admin.agences.index')" class="mt-2 inline-block text-xs font-medium text-indigo-600 hover:text-indigo-800">Voir</Link>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm" :class="{ 'border-amber-300 bg-amber-50/50': (stats?.vehicles_in_maintenance_count ?? 0) > 0 }">
                    <p class="text-sm font-medium text-gray-500">En maintenance</p>
                    <p class="mt-1 text-2xl font-bold" :class="(stats?.vehicles_in_maintenance_count ?? 0) > 0 ? 'text-amber-700' : 'text-gray-900'">{{ stats?.vehicles_in_maintenance_count ?? 0 }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm" :class="{ 'border-amber-300 bg-amber-50/50': (stats?.reservations_en_attente_count ?? 0) > 0 }">
                    <p class="text-sm font-medium text-gray-500">En attente</p>
                    <p class="mt-1 text-2xl font-bold" :class="(stats?.reservations_en_attente_count ?? 0) > 0 ? 'text-amber-700' : 'text-gray-900'">{{ stats?.reservations_en_attente_count ?? 0 }}</p>
                </div>
            </div>

            <!-- Graphiques : 30 jours + Doughnut -->
            <div class="grid gap-6 lg:grid-cols-3 mb-8">
                <div class="lg:col-span-2 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">Réservations sur 30 jours</h2>
                    <div class="h-[260px]">
                        <Line :data="chart30jData" :options="lineOptions" />
                    </div>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">Répartition par statut</h2>
                    <div class="h-[260px]">
                        <Doughnut :data="chartDoughnutData" :options="doughnutOptions" />
                    </div>
                </div>
            </div>

            <!-- 12 mois + Carte -->
            <div class="grid gap-6 lg:grid-cols-2 mb-8">
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">Réservations sur 12 mois</h2>
                    <div class="h-[260px]">
                        <Bar :data="chart12mData" :options="barOptions" />
                    </div>
                </div>
                <AdminDashboardMap :reservations="map_reservations || []" />
            </div>

            <!-- Résumé mois + Réservations par statut (pastilles) -->
            <div class="grid gap-6 lg:grid-cols-2 mb-8">
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="text-base font-semibold text-gray-900">Réservations ce mois</h2>
                    <div class="mt-3 flex items-baseline gap-3">
                        <span class="text-3xl font-bold text-gray-900">{{ stats?.reservations_ce_mois ?? 0 }}</span>
                        <span v-if="stats?.reservations_mois_precedent != null" class="text-sm" :class="evolution >= 0 ? 'text-emerald-600' : 'text-red-600'">
                            {{ evolution >= 0 ? '+' : '' }}{{ evolution }} ({{ evolution >= 0 ? '+' : '' }}{{ evolutionPercent }} % vs mois dernier)
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Mois dernier : {{ stats?.reservations_mois_precedent ?? 0 }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="text-base font-semibold text-gray-900">Réservations par statut</h2>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <span
                            v-for="(n, statut) in (stats?.by_status || {})"
                            :key="statut"
                            class="inline-flex items-center rounded-lg border px-3 py-1 text-sm font-medium"
                            :class="statusColors[statut] || 'bg-gray-100 text-gray-700 border-gray-200'"
                        >
                            {{ statusLabels[statut] || statut }} : {{ n }}
                        </span>
                        <span v-if="!stats?.by_status || Object.keys(stats.by_status).length === 0" class="text-sm text-gray-400">Aucune</span>
                    </div>
                </div>
            </div>

            <!-- Dernières réservations + Liens rapides -->
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-5 py-4 flex justify-between items-center">
                        <h2 class="text-base font-semibold text-gray-900">Dernières réservations</h2>
                        <Link :href="route('dashboard')" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Voir tout</Link>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <template v-if="recent_reservations?.length">
                            <div
                                v-for="r in recent_reservations"
                                :key="r.id"
                                class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition"
                            >
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-900 truncate">{{ r.depart }} → {{ r.destination }}</p>
                                    <p class="text-sm text-gray-500">{{ r.vehicle?.modele }} · {{ r.driver?.name }} · {{ formatDate(r.date_debut) }}</p>
                                </div>
                                <span
                                    class="ml-3 shrink-0 rounded-full border px-2.5 py-0.5 text-xs font-medium"
                                    :class="statusColors[r.statut] || 'bg-gray-100 text-gray-700'"
                                >
                                    {{ statusLabels[r.statut] || r.statut }}
                                </span>
                            </div>
                        </template>
                        <p v-else class="px-5 py-8 text-center text-gray-500">Aucune réservation</p>
                    </div>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="text-base font-semibold text-gray-900">Accès rapides</h2>
                    <nav class="mt-4 space-y-2">
                        <Link v-if="canViewVehicles" :href="route('admin.vehicles.availability')" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">Véhicules</Link>
                        <Link v-if="canViewUsers" :href="route('admin.users.index')" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">Utilisateurs</Link>
                        <Link v-if="canViewAgences && !agence" :href="route('admin.agences.index')" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">Agences</Link>
                        <Link v-if="canViewRoles" :href="route('admin.roles.index')" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">Rôles</Link>
                        <Link v-if="canViewDomains" :href="route('admin.domains.index')" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">Domaines</Link>
                        <Link v-if="canViewVehicleSuggestion" :href="route('admin.settings.vehicleSuggestion.edit')" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">Suggestion véhicule</Link>
                        <Link :href="route('dashboard')" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">Réservations (espace utilisateur)</Link>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</template>
