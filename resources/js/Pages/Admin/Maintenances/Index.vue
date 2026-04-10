<template>
    <Head title="Maintenance Prédictive - Administration" />

    <AdminLayout>
      <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
              <h1 class="text-3xl font-bold text-gray-800">État du Parc (Maintenance)</h1>
            </div>

            <div class="overflow-x-auto relative shadow-md sm:rounded-lg border border-gray-200">
              <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                  <tr>
                    <th scope="col" class="py-3 px-6">Véhicule</th>
                    <th scope="col" class="py-3 px-6">Agence</th>
                    <th scope="col" class="py-3 px-6">KM Actuel</th>
                    <th scope="col" class="py-3 px-6">Prochain Service (Estimé)</th>
                    <th scope="col" class="py-3 px-6">Statut</th>
                    <th scope="col" class="py-3 px-6 text-center">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="v in vehicles" :key="v.id" class="bg-white border-b hover:bg-gray-50">
                    <td class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap">{{ v.modele }} ({{ v.immatriculation }})</td>
                    <td class="py-4 px-6">{{ v.agence ?? '—' }}</td>
                    <td class="py-4 px-6">{{ v.kilometrage }} km</td>
                    <td class="py-4 px-6">
                        <div class="font-medium text-gray-900">Dans {{ v.km_until_next }} km</div>
                        <div class="text-[11px] text-gray-400">Date limite : {{ v.date_next }}</div>
                    </td>
                    <td class="py-4 px-6">
                        <span v-if="v.status === 'overdue'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                            En retard
                        </span>
                        <span v-else-if="v.status === 'warning'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                            À prévoir
                        </span>
                        <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                            OK
                        </span>
                    </td>
                    <td class="py-4 px-6 text-center whitespace-nowrap">
                      <Link :href="route('admin.maintenances.show', v.id)" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-md text-xs transition duration-150">Gérer l'historique</Link>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div v-if="vehicles.length === 0" class="mt-6 text-center text-gray-500">
              Aucun véhicule disponible.
            </div>

          </div>
        </div>
      </div>
    </AdminLayout>
</template>

<script setup>
import { Link, usePage, Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue';

const { vehicles } = usePage().props
</script>