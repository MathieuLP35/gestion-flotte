<template>
  <Head :title="`Calendrier - ${vehicle.modele}`" />

  <AdminLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          
          <div class="flex justify-between items-center mb-6">
            <div>
              <h1 class="text-2xl font-bold text-gray-800">Calendrier de disponibilités</h1>
              <p class="text-sm text-gray-600 mt-1">
                {{ vehicle.modele }} - {{ vehicle.immatriculation }}
              </p>
            </div>
            <Link :href="route('admin.vehicles.availability')" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md shadow-md transition ease-in-out duration-150">
              Retour aux véhicules
            </Link>
          </div>

          <div class="mb-4 p-4 bg-gray-50 rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <span class="text-sm font-medium text-gray-700">Kilométrage:</span>
                <span class="ml-2 text-gray-900">{{ vehicle.km_initial }} km</span>
              </div>
              <div>
                <span class="text-sm font-medium text-gray-700">Emplacement:</span>
                <span class="ml-2 text-gray-900">{{ vehicle.emplacement }}</span>
              </div>
              <div>
                <span class="text-sm font-medium text-gray-700">Places:</span>
                <span class="ml-2 text-gray-900">{{ vehicle.nbr_places }}</span>
              </div>
            </div>
            <div class="mt-2">
              <span class="text-sm font-medium text-gray-700">Statut:</span>
              <span :class="{
                'bg-green-100 text-green-800': !vehicle.en_maintenance,
                'bg-red-100 text-red-800': vehicle.en_maintenance
              }" class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-semibold">
                {{ vehicle.en_maintenance ? 'En maintenance' : 'Disponible' }}
              </span>
            </div>
          </div>

          <!-- Légende des événements -->
          <div class="mb-6 p-4 bg-white border border-gray-200 rounded-lg shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Légende des statuts</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
              <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded" style="background-color: #fbbf24; border: 1px solid #f59e0b;"></div>
                <span class="text-xs text-gray-700">En attente</span>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded" style="background-color: #10b981; border: 1px solid #059669;"></div>
                <span class="text-xs text-gray-700">Validé</span>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded" style="background-color: #3b82f6; border: 1px solid #2563eb;"></div>
                <span class="text-xs text-gray-700">En cours</span>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded" style="background-color: #f97316; border: 1px solid #ea580c;"></div>
                <span class="text-xs text-gray-700">À retourner</span>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded" style="background-color: #6b7280; border: 1px solid #4b5563;"></div>
                <span class="text-xs text-gray-700">Terminé</span>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded" style="background-color: #ef4444; border: 1px solid #dc2626;"></div>
                <span class="text-xs text-gray-700">Annulé</span>
              </div>
            </div>
          </div>

          <div id="calendar" class="mt-6"></div>

        </div>
      </div>
    </div>

    <!-- Modal pour afficher les détails de la réservation -->
    <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="showModal = false">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" @click.stop>
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
          
          <div class="mt-4 flex justify-end">
            <button @click="showModal = false" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
              Fermer
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import frLocale from '@fullcalendar/core/locales/fr';

const props = defineProps({
  vehicle: Object,
  reservations: Array,
});

let calendar = ref(null);
const showModal = ref(false);
const selectedReservation = ref(null);

onMounted(() => {
  const calendarEl = document.getElementById('calendar');
  
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
    height: 'auto',
    weekends: true,
    editable: false,
    selectable: false,
  });

  calendar.value.render();
});
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
</style>
