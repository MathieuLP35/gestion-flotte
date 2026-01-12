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
                        <div class="relative">
                           {{ form }}
                            <label for="departure" class="block text-sm font-semibold text-gray-900 mb-2">Départ</label>
                            <input
                                type="text"
                                id="departure"
                                v-model="form.departure"
                                @input="fetchSuggestions(form.departure, 'departure')"
                                placeholder="Ex: Rennes, Bruz, 35000..."
                                class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                required
                            />
                            <div v-if="isLoadingDeparture" class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-b-xl shadow-md p-2 text-sm text-gray-500">
                                Recherche en cours...
                            </div>
                            <ul v-if="suggestionsDeparture.length > 0" class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-b-xl shadow-md max-h-60 overflow-y-auto z-10">
                                <li
                                    v-for="suggestion in suggestionsDeparture"
                                    :key="suggestion.label"
                                    @click="form.departureSelected = suggestion; form.departure = suggestion.label; suggestionsDeparture = []"
                                    class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm flex items-center justify-between"
                                >
                                    <span>{{ suggestion.label }}</span>
                                    <svg v-if="suggestion.source === 'nominatim'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.995 1.995 0 01-2.828 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.995 1.995 0 01-2.828 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    </svg>
                                </li>
                            </ul>
                            <div v-if="form.errors.departure" class="mt-2 text-sm text-red-600">
                                {{ form.errors.departure }}
                            </div>
                        </div>
                    </div>

                  <div>
                      <div class="relative">
                        <label for="destination" class="block text-sm font-semibold text-gray-900 mb-2">Destination</label>
                        <input
                            type="text"
                            id="destination"
                            v-model="form.destination"
                            @input="fetchSuggestions(form.destination, 'destination')"
                            placeholder="Ex: Pontivy, Nantes, 56000..."
                            class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                            required
                        />
                        <div v-if="isLoadingDestination" class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-b-xl shadow-md p-2 text-sm text-gray-500">
                            Recherche en cours...
                        </div>
                        <ul v-if="suggestionsDestination.length > 0" class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-b-xl shadow-md max-h-60 overflow-y-auto z-10">
                            <li
                                v-for="suggestion in suggestionsDestination"
                                :key="suggestion.label"
                                @click="form.destinationSelected = suggestion; form.destination = suggestion.label; suggestionsDestination = []"
                                class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm"
                            >
                                <span>{{ suggestion.label }}</span>
                                <svg v-if="suggestion.source === 'nominatim'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.995 1.995 0 01-2.828 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.995 1.995 0 01-2.828 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                </svg>
                            </li>
                        </ul>
                        <div v-if="form.errors.destination" class="mt-2 text-sm text-red-600">
                            {{ form.errors.destination }}
                        </div>
                      </div>
                  </div>

                    <div>
                      <label for="date_debut" class="block text-sm font-medium text-gray-700">Date de début</label>
                      <input type="date" v-model="form.date_debut" id="date_debut" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" required />
                      <div v-if="form.errors.date_debut" class="mt-2 text-sm text-red-600">
                        {{ form.errors.date_debut }}
                      </div>
                    </div>

                    <div>
                      <label for="date_fin" class="block text-sm font-medium text-gray-700">Date de fin</label>
                      <input type="date" v-model="form.date_fin" id="date_fin" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" required />
                      <div v-if="form.errors.date_fin" class="mt-2 text-sm text-red-600">
                        {{ form.errors.date_fin }}
                      </div>
                    </div>

                    <div>
                      <label for="vehicle" class="block text-sm font-medium text-gray-700">Véhicule</label>
                      <div v-if="suggestedVehicleInfo || calculatedDistance" class="mb-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-start">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 mt-0.5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                          </svg>
                          <div class="flex-1">
                            <p class="text-sm font-semibold text-green-800">
                              Véhicule suggéré pour votre trajet
                            </p>
                            <p v-if="calculatedDistance" class="text-xs text-green-700 mt-1">
                              Distance: {{ Math.round(calculatedDistance) }} km à vol d'oiseau
                            </p>
                            <p v-if="suggestedVehicleInfo" class="text-xs text-green-700 mt-1">
                              {{ suggestedVehicleInfo.modele }} ({{ suggestedVehicleInfo.energie }}) - {{ suggestedVehicleInfo.nbr_places }} places
                            </p>
                          </div>
                        </div>
                      </div>
                      <select v-model="form.vehicle_id" id="vehicle" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" required>
                        <option value="" disabled>Sélectionnez un véhicule</option>
                        <option v-for="v in vehicles" :key="v.id" :value="v.id">
                          {{ v.modele }} ({{ v.immatriculation }}) - {{ v.energie || 'essence' }} - {{ v.nbr_places }} places
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
                      <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                        <span>{{ resa.vehicle.modele }} - {{ resa.vehicle.immatriculation }}</span>
                        <span v-if="resa.vehicle.energie === 'electrique' || resa.vehicle.energie === 'hybride'" 
                              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold"
                              :class="{
                                'bg-green-100 text-green-800': resa.vehicle.energie === 'electrique',
                                'bg-emerald-100 text-emerald-800': resa.vehicle.energie === 'hybride'
                              }"
                              title="Trajet écologique">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                          </svg>
                          <span v-if="resa.vehicle.energie === 'electrique'">Électrique</span>
                          <span v-else>Hybride</span>
                        </span>
                      </h3>
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
import { useForm, usePage, Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, watch } from 'vue';
import axios from 'axios';
import debounce from 'lodash.debounce';
import useDate from '@/Composables/useDate';
import useGeocoding from "@/Composables/useGeocoding.js";

const props = defineProps({
    vehicles: Array,
    suggestedVehicle: Object,
    distance: Number,
});

const { formatDate } = useDate();
const { suggestionsDeparture, suggestionsDestination, isLoadingDeparture, isLoadingDestination, fetchSuggestions } = useGeocoding();

const suggestedVehicleInfo = ref(props.suggestedVehicle || null);
const calculatedDistance = ref(props.distance || null);

const form = useForm({
    vehicle_id: props.suggestedVehicle?.id || null,
    departure: '',
    destination: '',
    departureSelected: null,
    destinationSelected: null,
    date_debut: '',
    date_fin: '',
    is_carpool: false,
})

// Fonction pour obtenir la suggestion de véhicule
const fetchVehicleSuggestion = async () => {
    if (!form.departureSelected || !form.destinationSelected || !form.date_debut || !form.date_fin) {
        suggestedVehicleInfo.value = null;
        calculatedDistance.value = null;
        return;
    }

    try {
        const response = await axios.get(route('reservations.suggestVehicle'), {
            params: {
                depart_lat: form.departureSelected.lat,
                depart_lng: form.departureSelected.lng,
                dest_lat: form.destinationSelected.lat,
                dest_lng: form.destinationSelected.lng,
                date_debut: form.date_debut,
                date_fin: form.date_fin,
            }
        });

        if (response.data.suggestedVehicle) {
            suggestedVehicleInfo.value = response.data.suggestedVehicle;
            calculatedDistance.value = response.data.distance;
            // Pré-sélectionner le véhicule suggéré
            form.vehicle_id = response.data.suggestedVehicle.id;
        } else {
            suggestedVehicleInfo.value = null;
        }
    } catch (error) {
        console.error('Erreur lors de la suggestion de véhicule:', error);
        suggestedVehicleInfo.value = null;
        calculatedDistance.value = null;
    }
};

// Watcher pour déclencher la suggestion quand les lieux et dates sont sélectionnés
watch([() => form.departureSelected, () => form.destinationSelected, () => form.date_debut, () => form.date_fin], () => {
    if (form.departureSelected && form.destinationSelected && form.date_debut && form.date_fin) {
        fetchVehicleSuggestion();
    }
}, { deep: true });

function submitVehicleReservation() {

    form.errors.departure = "";
    form.errors.destination = "";

    if (!form.departureSelected) {
        form.errors.departure = "Vous n'avez pas sélectionnez de lieu de départ."
        return;
    }
    if (!form.destinationSelected) {
        form.errors.destination = "Vous n'avez pas sélectionnez de lieu de départ."
        return;
    }

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
