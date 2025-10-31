<template>
    <Head title="Edition d'une réservation" />

    <AuthenticatedLayout>
      <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 md:p-8">
              <h1 class="text-3xl font-bold text-gray-800 mb-6">
                Modifier la réservation
              </h1>

              <form @submit.prevent="submit" class="space-y-6">
                
                <div>
                  <label for="vehicle" class="block text-sm font-medium text-gray-700">Véhicule</label>
                  <select v-model="form.vehicle_id" id="vehicle" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    <option v-for="v in vehicles" :key="v.id" :value="v.id">
                      {{ v.modele }} ({{ v.immatriculation }})
                    </option>
                  </select>
                  <div v-if="form.errors.vehicle_id" class="mt-2 text-sm text-red-600">
                    {{ form.errors.vehicle_id }}
                  </div>
                </div>

                <div>
                  <label for="date_debut" class="block text-sm font-medium text-gray-700">Date de début</label>
                  <input type="datetime-local" v-model="form.date_debut" id="date_debut" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required />
                  <div v-if="form.errors.date_debut" class="mt-2 text-sm text-red-600">
                    {{ form.errors.date_debut }}
                  </div>
                </div>

                <div>
                  <label for="date_fin" class="block text-sm font-medium text-gray-700">Date de fin</label>
                  <input type="datetime-local" v-model="form.date_fin" id="date_fin" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required />
                  <div v-if="form.errors.date_fin" class="mt-2 text-sm text-red-600">
                    {{ form.errors.date_fin }}
                  </div>
                </div>

                <div>
                  <label for="statut" class="block text-sm font-medium text-gray-700">Statut</label>
                  <select v-model="form.statut" id="statut" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    <option value="en attente">En attente</option>
                    <option value="validé">Validé</option>
                    <option value="annulé">Annulé</option>
                  </select>
                  <div v-if="form.errors.statut" class="mt-2 text-sm text-red-600">
                    {{ form.errors.statut }}
                  </div>
                </div>

                <div class="flex items-center">
                  <input type="checkbox" v-model="form.covoiturage" id="covoiturage" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" />
                  <label for="covoiturage" class="ml-3 block text-sm font-medium text-gray-700">Covoiturage</label>
                </div>
                <div v-if="form.errors.covoiturage" class="mt-2 text-sm text-red-600">
                  {{ form.errors.covoiturage }}
                </div>

                <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                  <button type="submit" :disabled="form.processing" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-md shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150" :class="{ 'opacity-50 cursor-not-allowed': form.processing }">
                    {{ form.processing ? 'Mise à jour...' : 'Mettre à jour' }}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
</template>

<script setup>
// Votre script est déjà parfait. Je le recopie tel quel.
import { usePage, useForm, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const { reservation, vehicles } = usePage().props

function toDatetimeLocal(dateStr) {
  const dt = new Date(dateStr)
  dt.setMinutes(dt.getMinutes() - dt.getTimezoneOffset())
  return dt.toISOString().slice(0, 16)
}

const form = useForm({
  vehicle_id: reservation.vehicle_id,
  date_debut: toDatetimeLocal(reservation.date_debut),
  date_fin: toDatetimeLocal(reservation.date_fin),
  statut: reservation.statut,
  covoiturage: reservation.covoiturage,
})

function submit() {
  form.put(route('reservations.update', reservation.id))
}
</script>