<template>
    <Head title="Mes réservations" />

    <AuthenticatedLayout>
      <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            
            <div class="flex justify-between items-center mb-6">
              <h1 class="text-3xl font-bold text-gray-800">Mes réservations</h1>
              <Link :href="route('reservations.create')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-md shadow-md transition ease-in-out duration-150">
                Nouvelle réservation
              </Link>
            </div>

            <div class="overflow-x-auto relative shadow-md sm:rounded-lg border border-gray-200">
              <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                  <tr>
                    <th scope="col" class="py-3 px-6">Véhicule</th>
                    <th scope="col" class="py-3 px-6">Début</th>
                    <th scope="col" class="py-3 px-6">Fin</th>
                    <th scope="col" class="py-3 px-6">Statut</th>
                    <th scope="col" class="py-3 px-6">Covoiturage</th>
                    <th scope="col" class="py-3 px-6 text-center">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="r in reservations" :key="r.id" class="bg-white border-b hover:bg-gray-50">
                    <td class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap">{{ r.vehicle.modele }} ({{ r.vehicle.immatriculation }})</td>
                    <td class="py-4 px-6 whitespace-nowrap">{{ new Date(r.date_debut).toLocaleString() }}</td>
                    <td class="py-4 px-6 whitespace-nowrap">{{ new Date(r.date_fin).toLocaleString() }}</td>
                    <td class="py-4 px-6">
                      <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold"
                            :class="{
                              'bg-yellow-100 text-yellow-800': r.statut === 'en attente',
                              'bg-green-100 text-green-800': r.statut === 'validé',
                              'bg-red-100 text-red-800': r.statut === 'annulé',
                              'bg-gray-100 text-gray-800': !['en attente', 'validé', 'annulé'].includes(r.statut)
                            }">
                        {{ r.statut }}
                      </span>
                    </td>
                    <td class="py-4 px-6">{{ r.covoiturage ? 'Oui' : 'Non' }}</td>
                    <td class="py-4 px-6 text-center whitespace-nowrap">
                      <Link :href="route('reservations.edit', r.id)" class="px-3 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-md text-xs mr-2 transition ease-in-out duration-150">Modifier</Link>
                      <button @click="deleteReservation(r.id)" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-md text-xs transition ease-in-out duration-150">Supprimer</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div v-if="reservations.length === 0" class="mt-6 text-center text-gray-500">
              Aucune réservation trouvée.
            </div>

          </div>
        </div>
      </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue';
// 1. Importer 'router' pour la fonction de suppression
import { Link, usePage, Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const page = usePage();

// Votre 'computed' était parfaite !
const reservations = computed(() => page.props.reservations);

// 2. ⭐ CORRECTION : Ajout de la fonction de suppression manquante
function deleteReservation(id) {
  if (confirm('Êtes-vous sûr de vouloir supprimer cette réservation ?')) {
    router.delete(route('reservations.destroy', id), {
      preserveScroll: true, // Garde la position du scroll
    });
  }
}
</script>