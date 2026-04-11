<template>
    <Head :title="$t('res.create_title')" />

    <AuthenticatedLayout>
      <div class="py-10 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ $t('res.create_title') }}</h1>
                <p class="mt-2 text-sm text-gray-600">{{ $t('res.create_subtitle') }}</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
              
              <!-- Formulaire de création (Principal) -->
              <div class="lg:col-span-3">
                  <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-8">
                      <h2 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-4 mb-6 flex items-center">
                          <span class="bg-sparkotto-purple text-white w-6 h-6 rounded-full flex items-center justify-center text-xs mr-3">1</span>
                          {{ $t('res.enter_trip') }}
                      </h2>

                      <div v-if="form.errors.departure || form.errors.destination" class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg text-sm text-red-700">
                          <strong>{{ $t('res.check_search') }}</strong>
                          <ul class="list-disc pl-5 mt-1">
                              <li v-if="form.errors.departure">{{ form.errors.departure }}</li>
                              <li v-if="form.errors.destination">{{ form.errors.destination }}</li>
                          </ul>
                      </div>

                      <form @submit.prevent="submitVehicleReservation" class="space-y-6">
                          <!-- Lieux -->
                          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                              <div class="relative">
                                  <label for="departure" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">{{ $t('res.departure') }}</label>
                                  <input
                                      type="text"
                                      id="departure"
                                      v-model="form.departure"
                                      @input="form.departureSelected = null; fetchSuggestions(form.departure, 'departure')"
                                      placeholder="Ex: Rennes..."
                                      class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-900 focus:bg-white focus:ring-2 focus:ring-sparkotto-purple focus:border-sparkotto-purple transition"
                                      required
                                  />
                                  <div v-if="isLoadingDeparture" class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-b-lg shadow-md p-2 text-xs text-gray-500 mt-1">{{ $t('search.placeholder') }}</div>
                                  <ul v-if="suggestionsDeparture.length > 0" class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto z-20 mt-1">
                                      <li v-for="suggestion in suggestionsDeparture" :key="suggestion.label" @click="form.departureSelected = suggestion; form.departure = suggestion.label; suggestionsDeparture = []" class="px-4 py-3 hover:bg-gray-50 cursor-pointer text-sm truncate border-b border-gray-50 last:border-0">
                                          {{ suggestion.label }}
                                      </li>
                                  </ul>
                              </div>

                              <div class="relative">
                                  <label for="destination" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">{{ $t('res.destination') }}</label>
                                  <input
                                      type="text"
                                      id="destination"
                                      v-model="form.destination"
                                      @input="form.destinationSelected = null; fetchSuggestions(form.destination, 'destination')"
                                      placeholder="Ex: Nantes..."
                                      class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-900 focus:bg-white focus:ring-2 focus:ring-sparkotto-purple focus:border-sparkotto-purple transition"
                                      required
                                  />
                                  <div v-if="isLoadingDestination" class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-b-lg shadow-md p-2 text-xs text-gray-500 mt-1">{{ $t('search.placeholder') }}</div>
                                  <ul v-if="suggestionsDestination.length > 0" class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto z-20 mt-1">
                                      <li v-for="suggestion in suggestionsDestination" :key="suggestion.label" @click="form.destinationSelected = suggestion; form.destination = suggestion.label; suggestionsDestination = []" class="px-4 py-3 hover:bg-gray-50 cursor-pointer text-sm truncate border-b border-gray-50 last:border-0">
                                          {{ suggestion.label }}
                                      </li>
                                  </ul>
                              </div>
                          </div>

                          <!-- Dates -->
                          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                              <div>
                                  <label for="date_debut" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">{{ $t('res.date_start') }}</label>
                                  <input type="datetime-local" v-model="form.date_debut" id="date_debut" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-900 focus:bg-white focus:ring-2 focus:ring-sparkotto-purple focus:border-sparkotto-purple transition" required />
                                  <p v-if="form.errors.date_debut" class="mt-1 text-xs text-red-600">{{ form.errors.date_debut }}</p>
                              </div>
                              <div>
                                  <label for="date_fin" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">{{ $t('res.date_end') }}</label>
                                  <input type="datetime-local" v-model="form.date_fin" id="date_fin" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-900 focus:bg-white focus:ring-2 focus:ring-sparkotto-purple focus:border-sparkotto-purple transition" required />
                                  <p v-if="form.errors.date_fin" class="mt-1 text-xs text-red-600">{{ form.errors.date_fin }}</p>
                              </div>
                          </div>

                          <!-- Véhicule Suggéré & Sélection -->
                          <div class="pt-4">
                              <h2 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-4 mb-6 flex items-center">
                                  <span class="bg-sparkotto-purple text-white w-6 h-6 rounded-full flex items-center justify-center text-xs mr-3">2</span>
                                  {{ $t('res.your_vehicle') }}
                              </h2>
                              
                              <div v-if="suggestedVehicleInfo && Object.keys(suggestedVehicleInfo).length !== 0" class="mb-4 bg-green-50 border border-green-200 rounded-xl p-4 flex items-start">
                                  <svg class="h-6 w-6 text-green-600 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                  <div>
                                      <p class="text-sm font-bold text-green-900">{{ $t('res.vehicle_suggested') }} ({{ Math.round(calculatedDistance) }} km)</p>
                                      <p class="text-xs text-green-800 mt-1">{{ suggestedVehicleInfo.modele }} ({{ suggestedVehicleInfo.energie }}) - {{ suggestedVehicleInfo.nbr_places }} {{ $t('res.seats') }}</p>
                                  </div>
                              </div>

                              <label for="vehicle" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">{{ $t('res.vehicle_select') }}</label>
                              <select v-model="form.vehicle_id" id="vehicle" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-900 focus:bg-white focus:ring-2 focus:ring-sparkotto-purple focus:border-sparkotto-purple transition" required>
                                  <option value="" disabled>{{ $t('res.vehicle_select') }}</option>
                                  <option v-for="v in vehicles" :key="v.id" :value="v.id">
                                      {{ v.modele }} ({{ v.immatriculation }}) - {{ v.energie || 'essence' }} - {{ v.nbr_places }} {{ $t('res.seats') }}
                                  </option>
                              </select>
                              <p v-if="form.errors.vehicle_id" class="mt-1 text-xs text-red-600">{{ form.errors.vehicle_id }}</p>
                          </div>

                          <!-- Options & Partage -->
                          <div class="pt-4 border-t border-gray-100">
                              <label class="flex items-center cursor-pointer mb-2">
                                  <div class="relative">
                                      <input type="checkbox" v-model="form.is_carpool" id="covoiturage" class="sr-only">
                                      <div class="block bg-gray-200 w-10 h-6 rounded-full transition-colors duration-200" :class="{'bg-sparkotto-purple': form.is_carpool}"></div>
                                      <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition transform duration-200" :class="{'translate-x-4': form.is_carpool}"></div>
                                  </div>
                                  <div class="ml-3 text-sm font-bold text-gray-900">{{ $t('res.carpool_offer') }}</div>
                              </label>

                              <div v-if="form.is_carpool && selectedVehicle" class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-xl">
                                  <label for="places_materiel" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">{{ $t('res.seats_blocked') }}</label>
                                  <p class="text-xs text-gray-500 mb-3">{{ $t('res.seats_blocked_desc') }} ({{ selectedVehicle.nbr_places - 1 }} {{ $t('res.seats_blocked_max') }}).</p>
                                  <div class="flex items-center max-w-xs">
                                      <input type="number" id="places_materiel" v-model="form.places_reservees_materiel" min="0" :max="selectedVehicle.nbr_places - 1" class="w-full bg-white border border-gray-200 rounded-l-lg px-4 py-2 text-gray-900 focus:ring-2 focus:ring-sparkotto-purple focus:border-sparkotto-purple transition" />
                                      <span class="bg-gray-100 border border-l-0 border-gray-200 rounded-r-lg px-4 py-2 text-sm text-gray-600">{{ $t('res.seats') }}</span>
                                  </div>
                              </div>
                          </div>

                          <div class="pt-6 flex justify-end">
                              <button type="submit" :disabled="form.processing" class="w-full md:w-auto px-8 py-3 bg-sparkotto-purple hover:bg-sparkotto-purple-hover text-white text-sm font-bold rounded-xl shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-sparkotto-purple focus:ring-offset-2 flex items-center justify-center">
                                  <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                  {{ form.processing ? $t('res.saving') : $t('res.confirm_btn') }}
                              </button>
                          </div>
                      </form>
                  </div>
              </div>

              <!-- Colonne Latérale (Covoiturages correspondants) -->
              <div class="lg:col-span-2 space-y-6">
                  <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-6 sticky top-6">
                      <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-4">{{ $t('res.alternatives_title') }}</h2>
                      
                      <div v-if="isLoadingCarpools" class="text-center py-4 text-sm text-gray-500">
                          {{ $t('res.searching') }}
                      </div>
                      
                      <div v-else-if="matchingCarpools.length === 0" class="text-center py-8">
                          <svg class="h-10 w-10 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                          <p class="text-sm text-gray-500">{{ $t('res.no_carpool') }}</p>
                      </div>

                      <div v-else class="space-y-4 max-h-[600px] overflow-y-auto pr-1">
                          <div v-for="resa in matchingCarpools" :key="resa.id" class="bg-gray-50 rounded-xl border border-gray-200 p-4 transition hover:border-sparkotto-purple">
                              <h3 class="font-bold text-gray-900 text-sm mb-1">{{ resa.depart }} → {{ resa.destination }}</h3>
                              <p class="text-xs text-gray-600 mb-2 font-medium">{{ $t('res.by') }} {{ resa.driver.name }}</p>
                              
                              <div class="flex items-center text-xs text-gray-600 mb-1">
                                  <svg class="w-3.5 h-3.5 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                  {{ $t('res.go') }} {{ formatDate(resa.date_debut) }}
                              </div>
                              
                              <button @click="joinCarpool(resa.id)" class="mt-3 w-full px-4 py-2 bg-white border border-gray-300 hover:border-sparkotto-purple hover:text-sparkotto-purple font-bold text-gray-700 text-xs rounded-lg shadow-sm transition">
                                  {{ $t('res.join_trip') }}
                              </button>
                          </div>
                      </div>
                  </div>
              </div>

            </div>
        </div>
      </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { useForm, Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, watch, computed } from 'vue';
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
    places_reservees_materiel: 0,
})

const selectedVehicle = computed(() => {
    return props.vehicles.find(v => v.id === form.vehicle_id) || null;
});

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

        // The exact API response might return null object keys or undefined, ensuring robust check
        if (response.data && response.data.suggestedVehicle) {
            suggestedVehicleInfo.value = response.data.suggestedVehicle;
            calculatedDistance.value = response.data.distance;
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

watch([() => form.departureSelected, () => form.destinationSelected, () => form.date_debut, () => form.date_fin], () => {
    if (form.departureSelected && form.destinationSelected && form.date_debut && form.date_fin) {
        fetchVehicleSuggestion();
    }
}, { deep: true });

function submitVehicleReservation() {
    form.clearErrors();
    let hasError = false;

    if (!form.departureSelected) {
        form.setError('departure', 'Veuillez sélectionner une adresse valide dans la liste déroulante.');
        hasError = true;
    }
    if (!form.destinationSelected) {
        form.setError('destination', 'Veuillez sélectionner une adresse valide dans la liste déroulante.');
        hasError = true;
    }

    if (hasError) return;

    form.post(route('reservations.store'), {
        onSuccess: () => form.reset(),
    });
}

const matchingCarpools = ref([]);
const isLoadingCarpools = ref(false);

const fetchMatchingCarpools = async () => {
  if (form.destination.length < 3 || !form.date_debut || !form.date_fin) {
    matchingCarpools.value = [];
    return;
  }

  isLoadingCarpools.value = true;
  try {
    const payload = {
      date_debut: form.date_debut,
      date_fin: form.date_fin,
      departure: form.departure,
      destination: form.destination,
    };
    const response = await axios.post(route('reservations.checkCarpool'), payload);

    if (response.data.carpool_available) {
      matchingCarpools.value = response.data.reservations;
    } else {
      matchingCarpools.value = [];
    }
  } catch (error) {
    console.error("Erreur lors de la recherche de covoiturage.");
    matchingCarpools.value = [];
  } finally {
    isLoadingCarpools.value = false;
  }
};

watch(
  [() => form.departure, () => form.destination, () => form.date_debut, () => form.date_fin],
  debounce(() => {
    if (form.date_debut && form.date_fin) fetchMatchingCarpools();
    else matchingCarpools.value = [];
  }, 500)
);

const joinCarpool = (reservationId) => {
  router.post(route('passengers.store'), {
    reservation_id: reservationId
  });
};

</script>
