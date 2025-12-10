<template>
  <Head title="Liste des Véhicules" />

  <AdminLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          
          <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Liste des Véhicules</h1>
            <Link :href="route('admin.vehicles.create')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md shadow-md transition ease-in-out duration-150">
              Ajouter un véhicule
            </Link>
          </div>

          <div class="overflow-x-auto relative shadow-md sm:rounded-lg border border-gray-200">
            <table class="w-full text-sm text-left text-gray-500">
              <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                  <th scope="col" class="py-3 px-6">Modèle</th>
                  <th scope="col" class="py-3 px-6">Immatriculation</th>
                  <th scope="col" class="py-3 px-6">Kilométrage initial</th>
                  <th scope="col" class="py-3 px-6">Emplacement</th>
                  <th scope="col" class="py-3 px-6">Places</th>
                  <th scope="col" class="py-3 px-6">En maintenance</th>
                  <th scope="col" class="py-3 px-6 text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="vehicle in vehicles" :key="vehicle.id" class="bg-white border-b hover:bg-gray-50">
                  <td class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap">{{ vehicle.modele }}</td>
                  <td class="py-4 px-6">{{ vehicle.immatriculation }}</td>
                  <td class="py-4 px-6">{{ vehicle.km_initial }}</td>
                  <td class="py-4 px-6">{{ vehicle.emplacement }}</td>
                  <td class="py-4 px-6">{{ vehicle.nbr_places }}</td>
                  <td class="py-4 px-6">
                    <span :class="{'bg-green-100 text-green-800': !vehicle.en_maintenance, 'bg-red-100 text-red-800': vehicle.en_maintenance}" class="px-2.5 py-0.5 rounded-full text-xs font-semibold">
                      {{ vehicle.en_maintenance ? 'Oui' : 'Non' }}
                    </span>
                  </td>
                  <td class="py-4 px-6 text-center whitespace-nowrap">
                    <Link :href="route('admin.vehicles.edit', vehicle.id)" class="px-3 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-md text-xs mr-2 transition ease-in-out duration-150">Modifier</Link>
                    <button @click="deleteVehicle(vehicle.id)" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-md text-xs transition ease-in-out duration-150">Supprimer</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="vehicles.length === 0" class="mt-6 text-center text-gray-500">
            Aucun véhicule trouvé.
          </div>

        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { router, Link, usePage, Head } from '@inertiajs/vue3' 
import AdminLayout from '@/Layouts/AdminLayout.vue';

const { vehicles } = usePage().props

function deleteVehicle(id) {
  if (confirm('Confirmer la suppression ?')) {
    // Correction : On utilise 'router.delete'
    router.delete(route('admin.vehicles.destroy', id))
  }
}
</script>
