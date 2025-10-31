<template>
    <Head title="Ajout d'une maintenance" />

    <AuthenticatedLayout>
      <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 md:p-8">
              <h1 class="text-3xl font-bold text-gray-800 mb-6">
                Ajouter un seuil de maintenance
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
                  <label for="km_alert_threshold" class="block text-sm font-medium text-gray-700">Seuil kilométrique</label>
                  <input v-model.number="form.km_alert_threshold" type="number" min="0" id="km_alert_threshold" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required/>
                  <div v-if="form.errors.km_alert_threshold" class="mt-2 text-sm text-red-600">
                    {{ form.errors.km_alert_threshold }}
                  </div>
                </div>

                <div>
                  <label for="date_dernier_entretien" class="block text-sm font-medium text-gray-700">Date dernier entretien</label>
                  <input v-model="form.date_dernier_entretien" type="date" id="date_dernier_entretien" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                  <div v-if="form.errors.date_dernier_entretien" class="mt-2 text-sm text-red-600">
                    {{ form.errors.date_dernier_entretien }}
                  </div>
                </div>

                <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                  <button type="submit" :disabled="form.processing" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-md shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150" :class="{ 'opacity-50 cursor-not-allowed': form.processing }">
                    {{ form.processing ? 'Ajout en cours...' : 'Ajouter' }}
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
import { useForm, usePage, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const { vehicles } = usePage().props

const form = useForm({
  vehicle_id: vehicles.length ? vehicles[0].id : null,
  km_alert_threshold: 0,
  date_dernier_entretien: null,
})

function submit() {
  form.post(route('maintenances.store'), {
    onSuccess: () => form.reset('km_alert_threshold', 'date_dernier_entretien'),
  })
}
</script>