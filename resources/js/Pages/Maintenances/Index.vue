<template>
    <Head title="Maintenances" />

    <AuthenticatedLayout>
      <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            
            <div class="flex justify-between items-center mb-6">
              <h1 class="text-3xl font-bold text-gray-800">Seuils de maintenance</h1>
              <Link :href="route('maintenances.create')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-md shadow-md transition ease-in-out duration-150">
                Ajouter seuil
              </Link>
            </div>

            <div class="overflow-x-auto relative shadow-md sm:rounded-lg border border-gray-200">
              <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                  <tr>
                    <th scope="col" class="py-3 px-6">Véhicule</th>
                    <th scope="col" class="py-3 px-6">Seuil km</th>
                    <th scope="col" class="py-3 px-6">Dernier entretien</th>
                    <th scope="col" class="py-3 px-6 text-center">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="m in maintenances" :key="m.id" class="bg-white border-b hover:bg-gray-50">
                    <td class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap">{{ m.vehicle.modele }} ({{ m.vehicle.immatriculation }})</td>
                    <td class="py-4 px-6">{{ m.km_alert_threshold }}</td>
                    <td classs="py-4 px-6">{{ m.date_dernier_entretien ?? 'N/A' }}</td>
                    <td class="py-4 px-6 text-center whitespace-nowrap">
                      <Link :href="route('maintenances.edit', m.id)" class="px-3 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-md text-xs mr-2 transition ease-in-out duration-150">Modifier</Link>
                      <button @click="deleteMaintenance(m.id)" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-md text-xs transition ease-in-out duration-150">Supprimer</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div v-if="maintenances.length === 0" class="mt-6 text-center text-gray-500">
              Aucun seuil de maintenance trouvé.
            </div>

          </div>
        </div>
      </div>
    </AuthenticatedLayout>
</template>

<script setup>
// Votre script est déjà parfait. Je le recopie tel quel.
import { Link, usePage, router, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const { maintenances } = usePage().props

function deleteMaintenance(id) {
  if (confirm('Êtes-vous sûr de vouloir supprimer ce seuil ?')) {
    router.delete(route('maintenances.destroy', id), {
      preserveScroll: true, 
    })
  }
}
</script>