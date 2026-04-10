<template>
    <Head :title="'Historique Maintenance - ' + vehicle.immatriculation" />

    <AdminLayout>
      <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <Link v-if="fromVehicle" :href="route('admin.vehicles.edit', vehicle.id)" class="text-sm font-medium text-indigo-600 hover:text-indigo-900 mb-2 inline-block">&larr; Retour à la fiche véhicule</Link>
                    <Link v-else :href="route('admin.maintenances.index')" class="text-sm font-medium text-indigo-600 hover:text-indigo-900 mb-2 inline-block">&larr; Retour à l'état du parc</Link>
                    <h1 class="text-3xl font-bold text-gray-800">Historique : {{ vehicle.modele }} ({{ vehicle.immatriculation }})</h1>
                </div>
                <div class="text-left md:text-right">
                    <p class="text-sm text-gray-500 mb-2">KM Actuel: <strong class="text-gray-900">{{ vehicle.kilometrage }}</strong></p>
                    <Link :href="route('admin.maintenances.create', { vehicle_id: vehicle.id })" class="inline-block px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-md shadow-md transition ease-in-out duration-150">
                        + Nouvelle Intervention
                    </Link>
                </div>
            </div>

            <!-- Dashboard Stats du Véhicule -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Statut -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500">Statut Maintenance</h3>
                    <p class="mt-2 text-2xl font-bold" :class="{
                        'text-red-600': vehicle.status === 'overdue',
                        'text-yellow-600': vehicle.status === 'warning',
                        'text-green-600': vehicle.status === 'ok'
                    }">
                        {{ 
                            vehicle.status === 'overdue' ? 'En retard !' : 
                            (vehicle.status === 'warning' ? 'À prévoir' : 'OK') 
                        }}
                    </p>
                </div>
                <!-- Prochain KM -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500">Prochaine révision (dans)</h3>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ vehicle.km_until_next }} km</p>
                    <p class="text-xs text-gray-400 mt-1">Intervalle de réglage : {{ vehicle.service_interval_km }} km</p>
                </div>
                <!-- Prochaine Date -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500">Date limite d'entretien</h3>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ vehicle.date_next }}</p>
                    <p class="text-xs text-gray-400 mt-1">Intervalle direct : {{ vehicle.service_interval_months }} mois</p>
                </div>
            </div>

            <!-- Historique -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Interventions passées</h2>
                
                <div v-if="maintenances.length > 0" class="overflow-x-auto relative sm:rounded-lg border border-gray-200">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3 px-6">Date</th>
                                <th scope="col" class="py-3 px-6">Type</th>
                                <th scope="col" class="py-3 px-6">Kilométrage</th>
                                <th scope="col" class="py-3 px-6">Coût HT</th>
                                <th scope="col" class="py-3 px-6 hidden md:table-cell">Notes</th>
                                <th scope="col" class="py-3 px-6 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="m in maintenances" :key="m.id" class="bg-white border-b hover:bg-gray-50">
                                <td class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap">
                                    {{ new Date(m.date).toLocaleDateString('fr-FR') }}
                                </td>
                                <td class="py-4 px-6 uppercase text-gray-800 font-semibold">{{ m.type }}</td>
                                <td class="py-4 px-6">{{ m.kilometrage }} km</td>
                                <td class="py-4 px-6 font-medium">{{ m.cost ? m.cost + ' €' : '—' }}</td>
                                <td class="py-4 px-6 hidden md:table-cell max-w-xs truncate" :title="m.notes">{{ m.notes || '—' }}</td>
                                <td class="py-4 px-6 text-center whitespace-nowrap">
                                    <Link :href="route('admin.maintenances.edit', m.id)" class="px-2 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-md text-xs text-center transition ease-in-out duration-150 mr-2">Modifier</Link>
                                    <button @click="deleteMaintenance(m.id)" class="px-2 py-1.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-md text-xs transition ease-in-out duration-150">Supprimer</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="mt-6 p-8 text-center border-2 border-dashed border-gray-200 rounded-xl">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">Aucune intervention enregistrée</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        L'échéance actuelle <span v-if="vehicle.date_next">(estimée au {{ vehicle.date_next }})</span> est calculée par défaut depuis la date d'achat ou ligne de départ du véhicule.
                    </p>
                    <div class="mt-6">
                        <Link :href="route('admin.maintenances.create', { vehicle_id: vehicle.id })" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                            + Ajouter la première intervention
                        </Link>
                    </div>
                </div>
            </div>

        </div>
      </div>
    </AdminLayout>
</template>

<script setup>
import { Link, usePage, router, Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { computed } from 'vue';

const { vehicle, maintenances } = usePage().props

const urlParams = new URLSearchParams(window.location.search);
const fromVehicle = urlParams.get('from_vehicle') === '1';

function deleteMaintenance(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette intervention de l\'historique ?')) {
        router.delete(route('admin.maintenances.destroy', id), {
            preserveScroll: true, 
        })
    }
}
</script>
