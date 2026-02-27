<template>
    <Head title="Ajout de véhicule" />

    <AdminLayout>
      <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 md:p-8">
              <h1 class="text-3xl font-bold text-gray-800 mb-6">
                Ajouter un nouveau véhicule
              </h1>

              <form @submit.prevent="submit" class="space-y-6">
                
                <div>
                  <label for="modele" class="block text-sm font-semibold text-gray-900 mb-2">Modèle</label>
                  <input v-model="form.modele" type="text" id="modele" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" required />
                  <div v-if="form.errors.modele" class="mt-2 text-sm text-red-600">
                    {{ form.errors.modele }}
                  </div>
                </div>

                <div>
                  <label for="immatriculation" class="block text-sm font-semibold text-gray-900 mb-2">Immatriculation</label>
                  <input v-model="form.immatriculation" type="text" id="immatriculation" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" required />
                  <div v-if="form.errors.immatriculation" class="mt-2 text-sm text-red-600">
                    {{ form.errors.immatriculation }}
                  </div>
                </div>

                <div>
                  <label for="km_initial" class="block text-sm font-semibold text-gray-900 mb-2">Kilométrage initial</label>
                  <input v-model.number="form.km_initial" type="number" min="0" id="km_initial" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" required />
                  <div v-if="form.errors.km_initial" class="mt-2 text-sm text-red-600">
                    {{ form.errors.km_initial }}
                  </div>
                </div>

                <div>
                  <label for="emplacement" class="block text-sm font-semibold text-gray-900 mb-2">Emplacement</label>
                  <input v-model="form.emplacement" type="text" id="emplacement" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" required />
                  <div v-if="form.errors.emplacement" class="mt-2 text-sm text-red-600">
                    {{ form.errors.emplacement }}
                  </div>
                </div>

                <div>
                  <label for="nbr_places" class="block text-sm font-semibold text-gray-900 mb-2">Nombre de places</label>
                  <input v-model.number="form.nbr_places" type="number" min="1" id="nbr_places" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" required />
                  <div v-if="form.errors.nbr_places" class="mt-2 text-sm text-red-600">
                    {{ form.errors.nbr_places }}
                  </div>
                </div>
              
                <div>
                  <label for="nbr_cles" class="block text-sm font-semibold text-gray-900 mb-2">Nombre de clés</label>
                  <input v-model.number="form.nbr_cles" type="number" min="1" max="5" id="nbr_cles" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" required />
                  <div v-if="form.errors.nbr_cles" class="mt-2 text-sm text-red-600">
                    {{ form.errors.nbr_cles }}
                  </div>
                </div>
                
                <div>
                  <label for="energie" class="block text-sm font-semibold text-gray-900 mb-2">Type d'énergie</label>
                  <select v-model="form.energie" id="energie" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" required>
                    <option value="essence">Essence</option>
                    <option value="diesel">Diesel</option>
                    <option value="hybride">Hybride</option>
                    <option value="electrique">Électrique</option>
                  </select>
                  <div v-if="form.errors.energie" class="mt-2 text-sm text-red-600">
                    {{ form.errors.energie }}
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
    </AdminLayout>
</template>

<script setup>
// 1. Importer 'useForm' (au lieu de 'reactive') et 'Head'
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

// 2. Initialiser le formulaire avec useForm
// Il remplace 'reactive' et gère l'état, les erreurs, etc.
const form = useForm({
  modele: '',
  immatriculation: '',
  km_initial: 0,
  emplacement: '',
  nbr_places: 1,
  nbr_cles: 2,
  energie: 'essence',
});

// 3. Définir la fonction de soumission
function submit() {
  // Utilise form.post (de useForm) qui s'occupe de tout
  form.post(route('admin.vehicles.store'), {
    // Bonus : Vider le formulaire après un succès
    onSuccess: () => form.reset(),
  });
}
</script>