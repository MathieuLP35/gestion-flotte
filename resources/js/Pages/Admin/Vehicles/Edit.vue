<template>
    <Head title="Edition de véhicule" />

    <AdminLayout>
      <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 md:p-8">
              <h1 class="text-3xl font-bold text-gray-800 mb-6">
                Modifier le véhicule
              </h1>

              <form @submit.prevent="submit" class="space-y-6">
                <div>
                  <label for="modele" class="block text-sm font-medium text-gray-700">Modèle</label>
                  <input v-model="form.modele" type="text" id="modele" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required />
                  <div v-if="form.errors.modele" class="mt-2 text-sm text-red-600">
                    {{ form.errors.modele }}
                  </div>
                </div>

                <div>
                  <label for="immatriculation" class="block text-sm font-medium text-gray-700">Immatriculation</label>
                  <input v-model="form.immatriculation" type="text" id="immatriculation" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required />
                  <div v-if="form.errors.immatriculation" class="mt-2 text-sm text-red-600">
                    {{ form.errors.immatriculation }}
                  </div>
                </div>

                <div>
                  <label for="km_initial" class="block text-sm font-medium text-gray-700">Kilométrage initial</label>
                  <input v-model.number="form.km_initial" type="number" min="0" id="km_initial" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required />
                  <div v-if="form.errors.km_initial" class="mt-2 text-sm text-red-600">
                    {{ form.errors.km_initial }}
                  </div>
                </div>

                <div>
                  <label for="emplacement" class="block text-sm font-medium text-gray-700">Emplacement</label>
                  <input v-model="form.emplacement" type="text" id="emplacement" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required />
                  <div v-if="form.errors.emplacement" class="mt-2 text-sm text-red-600">
                    {{ form.errors.emplacement }}
                  </div>
                </div>

                <div>
                  <label for="nbr_places" class="block text-sm font-medium text-gray-700">Nombre de places</label>
                  <input v-model.number="form.nbr_places" type="number" min="1" id="nbr_places" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required />
                  <div v-if="form.errors.nbr_places" class="mt-2 text-sm text-red-600">
                    {{ form.errors.nbr_places }}
                  </div>
                </div>

                <div class="flex items-center">
                  <input v-model="form.en_maintenance" type="checkbox" id="maintenance" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" />
                  <label for="maintenance" class="ml-3 block text-sm font-medium text-gray-700">En maintenance</label>
                </div>
                <div v-if="form.errors.en_maintenance" class="mt-2 text-sm text-red-600">
                  {{ form.errors.en_maintenance }}
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
    </AdminLayout>
</template>

<script setup>
// 1. Plus besoin de 'reactive'. On importe 'useForm'.
import { usePage, useForm, Head  } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue';

// On peut déstructurer 'vehicle' directement des props
const { vehicle } = usePage().props

// 2. On initialise le formulaire avec 'useForm'
// Il prend les données de votre 'vehicle'
const form = useForm({
  modele: vehicle.modele || '',
  immatriculation: vehicle.immatriculation || '',
  km_initial: vehicle.km_initial || 0,
  emplacement: vehicle.emplacement || '',
  nbr_places: vehicle.nbr_places || 1,
  en_maintenance: vehicle.en_maintenance == 1 ? true : false || false,
})

function submit() {
  // 3. 'form' a ses propres méthodes .post(), .put(), .patch()
  // Il envoie automatiquement ses propres données.
  form.put(route('admin.vehicles.update', vehicle.id))
}
</script>