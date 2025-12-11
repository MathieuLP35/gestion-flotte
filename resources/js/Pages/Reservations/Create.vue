<template>
    <Head title="Nouvelle réservation" />

    <AuthenticatedLayout>
      <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 md:p-8">
              
              <h1 class="text-3xl font-bold text-gray-800 mb-6">
                Nouvelle réservation
              </h1>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <div class="space-y-6">
                  <h2 class="text-xl font-semibold text-gray-700">Option 1 : Réserver un véhicule</h2>
                  <form @submit.prevent="submitVehicleReservation" class="space-y-6">
                    
                    <div>
                      <label for="departure" class="block text-sm font-medium text-gray-700">Lieu de départ</label>
                      <input type="text" v-model="form.departure" id="departure" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required />
                      <div v-if="form.errors.departure" class="mt-2 text-sm text-red-600">
                        {{ form.errors.departure }}
                      </div>
                    </div>

                    <div>
                      <label for="destination" class="block text-sm font-medium text-gray-700">Destination</label>
                      <input type="text" v-model="form.destination" id="destination" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required />
                      <div v-if="form.errors.destination" class="mt-2 text-sm text-red-600">
                        {{ form.errors.destination }}
                      </div>
                    </div>

                    <div>
                      <label for="date_debut" class="block text-sm font-medium text-gray-700">Date de début</label>
                      <input type="date" v-model="form.date_debut" id="date_debut" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required />
                      <div v-if="form.errors.date_debut" class="mt-2 text-sm text-red-600">
                        {{ form.errors.date_debut }}
                      </div>
                    </div>

                    <div>
                      <label for="date_fin" class="block text-sm font-medium text-gray-700">Date de fin</label>
                      <input type="date" v-model="form.date_fin" id="date_fin" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required />
                      <div v-if="form.errors.date_fin" class="mt-2 text-sm text-red-600">
                        {{ form.errors.date_fin }}
                      </div>
                    </div>

                    <div>
                      <label for="vehicle" class="block text-sm font-medium text-gray-700">Véhicule</label>
                      <select v-model="form.vehicle_id" id="vehicle" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        <option value="" disabled>Sélectionnez un véhicule</option>
                        <option v-for="v in vehicles" :key="v.id" :value="v.id">
                          {{ v.modele }} ({{ v.immatriculation }})
                        </option>
                      </select>
                      <div v-if="form.errors.vehicle_id" class="mt-2 text-sm text-red-600">
                        {{ form.errors.vehicle_id }}
                      </div>
                    </div>

                    <div>
                      <label class="inline-flex items-center me-5 cursor-pointer">
                        <input type="checkbox" v-model="form.is_carpool" id="covoiturage" class="sr-only peer" checked>
                        <div class="relative w-9 h-5 bg-neutral-quaternary rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-600 dark:peer-checked:bg-green-600"></div>
                        <span class="select-none ms-3 text-sm font-medium text-heading">Je souhaite proposer ce trajet en covoiturage</span>
                      </label>
                    </div>

                    <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                      <button type="submit" :disabled="form.processing" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-md shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150" :class="{ 'opacity-50 cursor-not-allowed': form.processing }">
                        {{ form.processing ? 'Réservation...' : 'Réserver ce véhicule' }}
                      </button>
                    </div>
                  </form>
                </div>

                <div class="space-y-4 md:border-l md:pl-8">
                  <h2 class="text-xl font-semibold text-gray-700">Option 2 : Rejoindre un covoiturage</h2>
                  <p class="text-sm text-gray-600">
                    Les trajets correspondants à votre destination et date de début s'afficheront ici automatiquement.
                  </p>

                  <div v-if="isLoadingCarpools" class="text-sm text-gray-500">
                    Recherche de covoiturages en cours...
                  </div>

                  <div v-if="!isLoadingCarpools && matchingCarpools.length === 0 && form.destination.length >= 3" class="p-4 bg-gray-50 rounded-md">
                    <p class="text-sm text-gray-700">Aucun covoiturage trouvé pour cette destination à cette date.</p>
                  </div>

                  <ul v-if="matchingCarpools.length > 0" class="space-y-4">
                    <li v-for="resa in matchingCarpools" :key="resa.id" class="p-4 border rounded-lg shadow-sm">
                      <h2 class="text-lg font-semibold text-gray-800 mb-2">{{ resa.depart }} → {{ resa.destination }} </h2>
                      <h3 class="font-semibold text-gray-800">{{ resa.vehicle.modele }} - {{ resa.vehicle.immatriculation }}</h3>
                      <p class="text-sm text-gray-600"><strong>Conducteur:</strong> {{ resa.driver.name }}</p>
                      <p class="text-sm text-gray-600"><strong>Départ:</strong> {{ formatDate(resa.date_debut) }}</p>
                      <p class="text-sm text-gray-600"><strong>Retour:</strong> {{ formatDate(resa.date_fin) }}</p>
                      <button @click="joinCarpool(resa.id)" class="mt-3 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-md shadow-md transition ease-in-out duration-150">
                        Rejoindre ce trajet
                      </button>
                    </li>
                  </ul>

                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { useForm, usePage, Head, router } from '@inertiajs/vue3' // NOUVEAU : import `router`
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, watch } from 'vue'; // NOUVEAU : import `ref` et `watch`
import axios from 'axios'; // NOUVEAU
import debounce from 'lodash.debounce'; // NOUVEAU
import useDate from '@/Composables/useDate';

const { vehicles } = usePage().props

const { formatDate } = useDate();

// NOUVEAU : ajout de `destination` au formulaire
const form = useForm({
  vehicle_id: null, // Modifié pour démarrer à null
  departure: '', // NOUVEAU
  destination: '', // NOUVEAU
  date_debut: '',
  date_fin: '',
  is_carpool: false, // NOUVEAU
})

// NOUVEAU : renommé `submit` en `submitVehicleReservation`
function submitVehicleReservation() {
  form.post(route('reservations.store'), {
    onSuccess: () => form.reset(),
  })
}

// État pour la liste des covoiturages
const matchingCarpools = ref([]);
const isLoadingCarpools = ref(false);

// Fonction pour appeler l'API Laravel
const fetchMatchingCarpools = async () => {
  if (form.destination.length < 3 || !form.date_debut) {
    matchingCarpools.value = [];
    return;
  }

  isLoadingCarpools.value = true;
  try {
    const response = await axios.post(route('reservations.checkCarpool'), {
      date_debut: form.date_debut,
      date_fin: form.date_fin,
      departure: form.departure,
      destination: form.destination,
    });

    if (response.data.carpool_available) {
      matchingCarpools.value = response.data.reservations;
    } else {
      matchingCarpools.value = [];
    }
  } catch (error) {
    console.error("Erreur lors de la recherche de covoiturage. L'appel API a échoué.");
    
    if (error.response) {
        console.error("Statut HTTP:", error.response.status);
        console.error("Données de l'erreur:", error.response.data);
    } else if (error.request) {
        console.error("Aucune réponse reçue:", error.request);
    } else {
        console.error("Erreur de configuration Axios:", error.message);
    }
    
    matchingCarpools.value = [];
  } finally {
    isLoadingCarpools.value = false;
  }
};

// Watcher "intelligent" avec debounce
watch(
  [() => form.departure, () => form.destination, () => form.date_debut, () => form.date_fin],
  debounce(fetchMatchingCarpools, 500) // 500ms après que l'utilisateur ait fini de taper
);

// Fonction pour rejoindre un covoiturage (devient passager)
const joinCarpool = (reservationId) => {
  router.post(route('passengers.store'), {
    reservation_id: reservationId
  }, {
    // onSucces, le backend (PassengerController) va nous rediriger
    // vers le tableau de bord avec un message flash.
    onError: (errors) => {
      console.error(errors);
    }
  });
};

</script>