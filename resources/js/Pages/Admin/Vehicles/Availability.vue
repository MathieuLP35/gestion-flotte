<template>
  <Head title="Disponibilités des Véhicules" />

  <AdminLayout>
    <div class="py-12">
      <div class="max-w-full mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          
          <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between p-6 border-b">
            <h1 class="text-2xl font-bold text-gray-800">Gestion des Véhicules et Disponibilités</h1>
            <Link
              :href="route('admin.vehicles.create')"
              class="w-full md:w-auto inline-flex justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md shadow-md transition ease-in-out duration-150"
            >
              Ajouter un véhicule
            </Link>
          </div>


          <div class="flex flex-col lg:flex-row lg:h-[calc(100vh-200px)] min-h-screen lg:min-h-0">
            <!-- Sidebar avec la liste des véhicules -->
            <div class="w-full lg:w-96 lg:border-r border-b lg:border-b-0 border-gray-200 bg-gray-50 overflow-y-auto max-h-[50vh] lg:max-h-full shrink-0 shadow-inner lg:shadow-none">
              <div class="p-4">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Véhicules</h2>
                
                <!-- Barre de recherche -->
                <div class="mb-4">
                  <input
                    type="text"
                    v-model="searchQuery"
                    placeholder="Rechercher un véhicule..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                  />
                </div>

                <div class="space-y-2">
                  <div
                    v-for="vehicle in filteredVehicles"
                    :key="vehicle.id"
                    :class="{
                      'bg-indigo-600 text-white': selectedVehicle?.id === vehicle.id,
                      'bg-white text-gray-700': selectedVehicle?.id !== vehicle.id
                    }"
                    class="rounded-lg border border-gray-200 transition ease-in-out duration-150"
                  >
                    <button
                      @click="selectVehicle(vehicle.id)"
                      class="w-full text-left p-3 hover:bg-gray-100 transition ease-in-out duration-150"
                      :class="{
                        'hover:bg-indigo-700': selectedVehicle?.id === vehicle.id,
                        'hover:bg-gray-100': selectedVehicle?.id !== vehicle.id
                      }"
                    >
                      <div class="flex items-center justify-between">
                        <div class="flex-1">
                          <p class="font-medium flex items-center gap-2">
                            {{ vehicle.modele }}
                            <span v-if="vehicle.pending_reservations_count > 0" class="flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-yellow-500 rounded-full" title="Réservations en attente">
                              {{ vehicle.pending_reservations_count }}
                            </span>
                          </p>
                          <p class="text-xs opacity-75">{{ vehicle.immatriculation }}</p>
                        </div>
                        <div class="ml-2">
                          <span :class="{
                            'bg-green-100 text-green-800': !vehicle.en_maintenance && selectedVehicle?.id !== vehicle.id,
                            'bg-red-100 text-red-800': vehicle.en_maintenance && selectedVehicle?.id !== vehicle.id,
                            'bg-green-200 text-green-900': !vehicle.en_maintenance && selectedVehicle?.id === vehicle.id,
                            'bg-red-200 text-red-900': vehicle.en_maintenance && selectedVehicle?.id === vehicle.id
                          }" class="px-2 py-0.5 rounded-full text-xs font-semibold">
                            {{ vehicle.en_maintenance ? 'Maintenance' : 'Disponible' }}
                          </span>
                        </div>
                      </div>
                      <div class="mt-2 flex items-center gap-2">
                        <span class="text-xs opacity-75">{{ vehicle.nbr_places }} places</span>
                        <span v-if="vehicle.energie === 'electrique' || vehicle.energie === 'hybride'" 
                              class="inline-flex items-center gap-1 text-xs"
                              :class="{
                                'text-blue-600': vehicle.energie === 'electrique' && selectedVehicle?.id !== vehicle.id,
                                'text-green-600': vehicle.energie === 'hybride' && selectedVehicle?.id !== vehicle.id,
                                'text-blue-300': vehicle.energie === 'electrique' && selectedVehicle?.id === vehicle.id,
                                'text-green-300': vehicle.energie === 'hybride' && selectedVehicle?.id === vehicle.id
                              }">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                          </svg>
                        </span>
                      </div>
                    </button>
                    
                    <!-- Boutons d'action -->
                    <div class="px-3 pb-3 flex gap-2" @click.stop>
                      <Link 
                        :href="route('admin.vehicles.edit', vehicle.id)" 
                        class="flex-1 px-2 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-md text-xs text-center transition ease-in-out duration-150"
                      >
                        Modifier
                      </Link>
                      <button 
                        @click="deleteVehicle(vehicle.id)"
                        class="flex-1 px-2 py-1.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-md text-xs transition ease-in-out duration-150"
                      >
                        Supprimer
                      </button>
                    </div>
                  </div>
                  
                  <div v-if="filteredVehicles.length === 0" class="text-center text-gray-500 text-sm py-4">
                    Aucun véhicule trouvé
                  </div>
                </div>
              </div>
            </div>

            <!-- Zone principale avec le calendrier -->
            <div class="flex-1 overflow-y-auto p-6">
            <div v-if="page.props.flash?.success" class="p-3 mb-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm w-full">
              {{ page.props.flash.success }}
            </div>
              <div v-if="!selectedVehicle" class="flex items-center justify-center h-full text-gray-500">
                <div class="text-center">
                  <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                  </svg>
                  <p class="mt-4 text-lg font-medium">Sélectionnez un véhicule</p>
                  <p class="mt-2 text-sm">Choisissez un véhicule dans la liste pour voir son calendrier de disponibilités</p>
                </div>
              </div>

              <div v-else>
                <!-- Informations du véhicule sélectionné -->
                <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-3">
                    <div>
                      <h2 class="text-xl font-bold text-gray-800">{{ selectedVehicle.modele }}</h2>
                      <p class="text-sm text-gray-600">{{ selectedVehicle.immatriculation }}</p>
                    </div>
                    <span :class="{
                      'bg-green-100 text-green-800': !selectedVehicle.en_maintenance,
                      'bg-red-100 text-red-800': selectedVehicle.en_maintenance
                    }" class="px-3 py-1 rounded-full text-xs font-semibold">
                      {{ selectedVehicle.en_maintenance ? 'En maintenance' : 'Disponible' }}
                    </span>
                  </div>
                  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                    <div>
                      <span class="font-medium text-gray-700">Kilométrage:</span>
                      <span class="ml-2 text-gray-900">{{ selectedVehicle.km_initial }} km</span>
                    </div>
                    <div>
                      <span class="font-medium text-gray-700">Emplacement:</span>
                      <span class="ml-2 text-gray-900">{{ selectedVehicle.emplacement }}</span>
                    </div>
                    <div>
                      <span class="font-medium text-gray-700">Places:</span>
                      <span class="ml-2 text-gray-900">{{ selectedVehicle.nbr_places }}</span>
                    </div>
                    <div>
                      <span class="font-medium text-gray-700">Énergie:</span>
                      <span class="ml-2">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold"
                              :class="{
                                'bg-blue-100 text-blue-800': selectedVehicle.energie === 'electrique',
                                'bg-green-100 text-green-800': selectedVehicle.energie === 'hybride',
                                'bg-yellow-100 text-yellow-800': selectedVehicle.energie === 'essence',
                                'bg-gray-100 text-gray-800': selectedVehicle.energie === 'diesel' || !selectedVehicle.energie
                              }">
                          {{ selectedVehicle.energie ? selectedVehicle.energie.charAt(0).toUpperCase() + selectedVehicle.energie.slice(1) : 'Essence' }}
                        </span>
                      </span>
                    </div>
                    <div>
                      <span class="font-medium text-gray-700">Maintenance:</span>
                      <span class="ml-2">
                        <span :class="{
                          'bg-green-100 text-green-800': !selectedVehicle.en_maintenance,
                          'bg-red-100 text-red-800': selectedVehicle.en_maintenance
                        }" class="px-2.5 py-0.5 rounded-full text-xs font-semibold">
                          {{ selectedVehicle.en_maintenance ? 'Oui' : 'Non' }}
                        </span>
                      </span>
                    </div>
                  </div>
                </div>
                

                <!-- Créneaux en attente sous forme de liste -->
                <div v-if="pendingReservations.length > 0" class="mb-4">
                  <h3 class="text-sm font-semibold text-gray-800 mb-2 flex items-center gap-2">
                    Réservations en attente
                    <span class="bg-yellow-500 text-white text-xs px-2 py-0.5 rounded-full">{{ pendingReservations.length }}</span>
                  </h3>
                  <div class="space-y-2 max-h-40 overflow-y-auto pr-2 rounded-lg">
                    <div v-for="resa in pendingReservations" :key="resa.id" class="p-3 bg-white border border-yellow-300 rounded-lg shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                      <div>
                        <p class="font-medium text-sm text-gray-900">{{ resa.depart }} → {{ resa.destination }}</p>
                        <p class="text-xs text-gray-600">Du {{ formatDate(resa.date_debut) }} au {{ formatDate(resa.date_fin) }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">Par: <span class="font-medium">{{ resa.driver?.name || 'Inconnu' }}</span> | {{ resa.passengers?.length || 0 }} passager(s)</p>
                      </div>
                      <div class="flex gap-2 shrink-0">
                        <button v-if="page.props.auth.permissions.includes('reservations.validate')" @click="updateStatus(resa.id, 'validé')" class="px-2 py-1 bg-green-100 text-green-700 hover:bg-green-200 text-xs font-semibold rounded outline-none focus:ring-2 focus:ring-green-400 transition">
                          Valider
                        </button>
                        <button v-if="page.props.auth.permissions.includes('reservations.validate')" @click="updateStatus(resa.id, 'annulé')" class="px-2 py-1 bg-red-100 text-red-700 hover:bg-red-200 text-xs font-semibold rounded outline-none focus:ring-2 focus:ring-red-400 transition">
                          Refuser
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Légende des événements -->
                <div class="mb-4 p-3 bg-white border border-gray-200 rounded-lg shadow-sm">
                  <h3 class="text-xs font-semibold text-gray-700 mb-2">Légende des statuts</h3>
                  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2">
                    <div class="flex items-center gap-1.5">
                      <div class="w-3 h-3 rounded" style="background-color: #fbbf24; border: 1px solid #f59e0b;"></div>
                      <span class="text-xs text-gray-700">En attente</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                      <div class="w-3 h-3 rounded" style="background-color: #10b981; border: 1px solid #059669;"></div>
                      <span class="text-xs text-gray-700">Validé</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                      <div class="w-3 h-3 rounded" style="background-color: #3b82f6; border: 1px solid #2563eb;"></div>
                      <span class="text-xs text-gray-700">En cours</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                      <div class="w-3 h-3 rounded" style="background-color: #f97316; border: 1px solid #ea580c;"></div>
                      <span class="text-xs text-gray-700">À retourner</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                      <div class="w-3 h-3 rounded" style="background-color: #6b7280; border: 1px solid #4b5563;"></div>
                      <span class="text-xs text-gray-700">Terminé</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                      <div class="w-3 h-3 rounded" style="background-color: #ef4444; border: 1px solid #dc2626;"></div>
                      <span class="text-xs text-gray-700">Annulé</span>
                    </div>
                  </div>
                </div>

                <!-- Calendrier -->
                <div class="mt-4 -mx-2 sm:mx-0">
                  <div class="rounded-lg border border-gray-200 bg-white overflow-x-auto">
                    <div id="calendar" class="min-w-[320px] sm:min-w-0"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- Modal pour afficher les détails de la réservation -->
    <div v-if="showModal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-start justify-center pt-10 sm:pt-20 px-4 pb-10" @click="showModal = false">
      <div class="relative w-full max-w-lg p-5 border shadow-2xl rounded-xl bg-white" @click.stop>
        <div class="mt-3">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-900">Détails de la réservation</h3>
            <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          
          <div v-if="selectedReservation" class="space-y-3">
            <div>
              <p class="text-sm font-medium text-gray-700">Trajet:</p>
              <p class="text-sm text-gray-900">{{ selectedReservation.depart }} → {{ selectedReservation.destination }}</p>
            </div>
            
            <div>
              <p class="text-sm font-medium text-gray-700">Statut:</p>
              <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold"
                    :class="{
                      'bg-yellow-100 text-yellow-800': selectedReservation.statut === 'en attente',
                      'bg-green-100 text-green-800': selectedReservation.statut === 'validé',
                      'bg-blue-100 text-blue-800': selectedReservation.statut === 'en cours',
                      'bg-orange-100 text-orange-800': selectedReservation.statut === 'à retourner',
                      'bg-gray-100 text-gray-800': selectedReservation.statut === 'terminé',
                      'bg-red-100 text-red-800': selectedReservation.statut === 'annulé',
                    }">
                {{ selectedReservation.statut }}
              </span>
            </div>
            
            <div>
              <p class="text-sm font-medium text-gray-700">Conducteur:</p>
              <p class="text-sm text-gray-900">{{ selectedReservation.driver?.name || 'N/A' }}</p>
            </div>
            
            <div>
              <p class="text-sm font-medium text-gray-700">Passagers:</p>
              <p class="text-sm text-gray-900">{{ selectedReservation.passengers?.length || 0 }}</p>
            </div>
            
            <div>
              <p class="text-sm font-medium text-gray-700">Covoiturage:</p>
              <p class="text-sm text-gray-900">{{ selectedReservation.covoiturage ? 'Oui' : 'Non' }}</p>
            </div>
            
            <div>
              <p class="text-sm font-medium text-gray-700">Départ:</p>
              <p class="text-sm text-gray-900">{{ new Date(selectedReservation.date_debut).toLocaleString('fr-FR') }}</p>
            </div>
            
            <div>
              <p class="text-sm font-medium text-gray-700">Retour:</p>
              <p class="text-sm text-gray-900">{{ new Date(selectedReservation.date_fin).toLocaleString('fr-FR') }}</p>
            </div>
          </div>
          
          <div class="mt-6 flex justify-between items-center">
            <div v-if="selectedReservation?.statut === 'en attente' && page.props.auth.permissions.includes('reservations.validate')" class="flex gap-2">
              <button @click="updateReservationStatus('validé')" class="px-3 py-2 bg-green-600 text-white text-sm font-semibold rounded-md hover:bg-green-700 transition ease-in-out duration-150">
                Valider
              </button>
              <button @click="updateReservationStatus('annulé')" class="px-3 py-2 bg-red-600 text-white text-sm font-semibold rounded-md hover:bg-red-700 transition ease-in-out duration-150">
                Annuler
              </button>
            </div>
            <button @click="showModal = false" class="px-4 py-2 bg-gray-600 text-white font-semibold rounded-md hover:bg-gray-700 ml-auto transition ease-in-out duration-150">
              Fermer
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { onMounted, ref, watch, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import frLocale from '@fullcalendar/core/locales/fr';
import useDate from '@/Composables/useDate';

const { formatDate } = useDate();
const page = usePage();
const props = defineProps({
  vehicles: Array,
  selectedVehicle: Object,
  reservations: Array,
});

let calendar = ref(null);
const showModal = ref(false);
const selectedReservation = ref(null);
const searchQuery = ref('');

const filteredVehicles = computed(() => {
  if (!searchQuery.value) {
    return props.vehicles;
  }
  const query = searchQuery.value.toLowerCase();
  return props.vehicles.filter(vehicle => 
    vehicle.modele.toLowerCase().includes(query) ||
    vehicle.immatriculation.toLowerCase().includes(query) ||
    vehicle.emplacement.toLowerCase().includes(query)
  );
});

const pendingReservations = computed(() => {
  return props.reservations.filter(r => r.statut === 'en attente');
});

const selectVehicle = (vehicleId) => {
  router.get(route('admin.vehicles.availability'), { vehicle_id: vehicleId }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const deleteVehicle = (vehicleId) => {
  if (confirm('Confirmer la suppression de ce véhicule ?')) {
    router.delete(route('admin.vehicles.destroy', vehicleId), {
      preserveState: false,
      onSuccess: () => {
        // Si le véhicule supprimé était sélectionné, désélectionner
        if (props.selectedVehicle?.id === vehicleId) {
          router.get(route('admin.vehicles.availability'), {}, {
            preserveState: false,
          });
        }
      }
    });
  }
};


const updateStatus = (resaId, status) => {
  if (confirm(`Êtes-vous sûr de vouloir ${status === 'validé' ? 'valider' : 'annuler'} cette réservation ?`)) {
    router.put(route('admin.reservations.updateStatus', resaId), {
      statut: status
    }, {
      preserveScroll: true,
      onSuccess: () => {
        if (selectedReservation.value?.id === resaId) {
          showModal.value = false;
        }
      }
    });
  }
};

const updateReservationStatus = (status) => {
  if (!selectedReservation.value) return;
  updateStatus(selectedReservation.value.id, status);
};

const renderCalendar = () => {
  if (!props.selectedVehicle) return;

  const calendarEl = document.getElementById('calendar');
  if (!calendarEl) return;

  // Détruire le calendrier existant s'il existe
  if (calendar.value) {
    calendar.value.destroy();
  }

  // Formater les réservations pour FullCalendar
  const events = props.reservations.map(reservation => {
    // Déterminer la couleur selon le statut
    let backgroundColor = '#6b7280'; // gris par défaut
    let borderColor = '#4b5563';
    
    switch (reservation.statut) {
      case 'en attente':
        backgroundColor = '#fbbf24'; // jaune
        borderColor = '#f59e0b';
        break;
      case 'validé':
        backgroundColor = '#10b981'; // vert
        borderColor = '#059669';
        break;
      case 'en cours':
        backgroundColor = '#3b82f6'; // bleu
        borderColor = '#2563eb';
        break;
      case 'à retourner':
        backgroundColor = '#f97316'; // orange
        borderColor = '#ea580c';
        break;
      case 'terminé':
        backgroundColor = '#6b7280'; // gris
        borderColor = '#4b5563';
        break;
      case 'annulé':
        backgroundColor = '#ef4444'; // rouge
        borderColor = '#dc2626';
        break;
    }

    return {
      id: reservation.id,
      title: `${reservation.depart} → ${reservation.destination}`,
      start: reservation.date_debut,
      end: reservation.date_fin,
      backgroundColor: backgroundColor,
      borderColor: borderColor,
      textColor: '#ffffff',
      extendedProps: {
        statut: reservation.statut,
        driver: reservation.driver?.name || 'N/A',
        passengersCount: reservation.passengers?.length || 0,
        covoiturage: reservation.covoiturage ? 'Oui' : 'Non',
      },
    };
  });

  calendar.value = new Calendar(calendarEl, {
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    locale: frLocale,
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay',
    },
    events: events,
    eventClick: function(info) {
      // Afficher les détails de la réservation dans un modal
      const reservation = props.reservations.find(r => r.id === parseInt(info.event.id));
      if (reservation) {
        selectedReservation.value = reservation;
        showModal.value = true;
      }
    },
    height: '600px',
    weekends: true,
    editable: false,
    selectable: false,
  });

  calendar.value.render();
};

onMounted(() => {
  renderCalendar();
});

watch(() => props.selectedVehicle, () => {
  renderCalendar();
}, { deep: true });

watch(() => props.reservations, () => {
  renderCalendar();
}, { deep: true });
</script>

<style>
/* Styles pour FullCalendar */
.fc {
  font-family: inherit;
}

.fc-event {
  cursor: pointer;
}

.fc-event:hover {
  opacity: 0.8;
}

/* FullCalendar mobile responsiveness */
@media (max-width: 640px) {
  .fc .fc-toolbar {
    flex-direction: column;
    gap: 0.5rem;
  }
}
</style>
