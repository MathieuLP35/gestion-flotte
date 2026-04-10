<template>
    <Head title="Édition d'une intervention" />

    <AdminLayout>
      <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 md:p-8">
              <h1 class="text-3xl font-bold text-gray-800 mb-6">
                Modifier l'intervention (Maintenance)
              </h1>

              <form @submit.prevent="submit" class="space-y-6">
                
                <div>
                  <label for="vehicle" class="block text-sm font-semibold text-gray-900 mb-2">Véhicule concerné</label>
                  <select v-model="form.vehicle_id" id="vehicle" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" required>
                    <option v-for="v in vehicles" :key="v.id" :value="v.id">
                      {{ v.modele }} ({{ v.immatriculation }})
                    </option>
                  </select>
                  <div v-if="form.errors.vehicle_id" class="mt-2 text-sm text-red-600">
                    {{ form.errors.vehicle_id }}
                  </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label for="date" class="block text-sm font-semibold text-gray-900 mb-2">Date de l'intervention</label>
                      <input v-model="form.date" type="date" id="date" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 transition" required />
                      <div v-if="form.errors.date" class="mt-2 text-sm text-red-600">{{ form.errors.date }}</div>
                    </div>

                    <div>
                      <label for="kilometrage" class="block text-sm font-semibold text-gray-900 mb-2">Kilométrage du véhicule</label>
                      <input v-model.number="form.kilometrage" type="number" min="0" id="kilometrage" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 transition" required/>
                      <div v-if="form.errors.kilometrage" class="mt-2 text-sm text-red-600">{{ form.errors.kilometrage }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label for="type" class="block text-sm font-semibold text-gray-900 mb-2">Type d'intervention</label>
                      <select v-model="form.type" id="type" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 transition" required>
                        <option value="revision">Révision générale (Vidange, etc.)</option>
                        <option value="pneus">Pneumatiques</option>
                        <option value="freins">Freinage</option>
                        <option value="ct">Contrôle Technique</option>
                        <option value="reparation">Réparation / Carrosserie</option>
                        <option value="autre">Autre intervention</option>
                      </select>
                      <p class="mt-1 text-xs text-gray-500">Seule une "Révision générale" relance l'échéance de maintenance prédictive.</p>
                      <div v-if="form.errors.type" class="mt-2 text-sm text-red-600">{{ form.errors.type }}</div>
                    </div>

                    <div>
                      <label for="cost" class="block text-sm font-semibold text-gray-900 mb-2">Coût usine (HT) - Optionnel</label>
                      <div class="relative">
                          <input v-model.number="form.cost" type="number" step="0.01" min="0" id="cost" placeholder="0.00" class="w-full border-2 border-gray-200 rounded-xl pl-4 pr-10 py-3 focus:ring-2 focus:ring-indigo-500 transition" />
                          <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-500 font-medium">€</div>
                      </div>
                      <div v-if="form.errors.cost" class="mt-2 text-sm text-red-600">{{ form.errors.cost }}</div>
                    </div>
                </div>

                <div>
                  <label for="notes" class="block text-sm font-semibold text-gray-900 mb-2">Notes / Description (Optionnel)</label>
                  <textarea v-model="form.notes" id="notes" rows="3" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 transition" placeholder="Détails, pièces changées, nom du garage..."></textarea>
                  <div v-if="form.errors.notes" class="mt-2 text-sm text-red-600">{{ form.errors.notes }}</div>
                </div>

                <div class="flex items-center justify-end pt-4 border-t border-gray-200 gap-3">
                  <Link :href="route('admin.maintenances.show', form.vehicle_id)" class="px-4 py-2 text-gray-600 hover:text-gray-900 font-medium">
                    Annuler
                  </Link>
                  <button type="submit" :disabled="form.processing" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-md shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150" :class="{ 'opacity-50 cursor-not-allowed': form.processing }">
                    {{ form.processing ? 'Mise à jour en cours...' : 'Mettre à jour' }}
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
import { useForm, usePage, Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue';

const { maintenance, vehicles } = usePage().props

const form = useForm({
  vehicle_id: maintenance.vehicle_id,
  date: maintenance.date,
  kilometrage: maintenance.kilometrage,
  type: maintenance.type,
  cost: maintenance.cost,
  notes: maintenance.notes || '',
})

function submit() {
  form.put(route('admin.maintenances.update', maintenance.id))
}
</script>