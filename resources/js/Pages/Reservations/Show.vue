<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue'; // Imports pour le chat
import axios from 'axios'; // Import pour le chat
import useDate from '@/Composables/useDate';

// On reçoit la réservation du contrôleur
const props = defineProps({
    reservation: Object,
});

const { formatDate } = useDate();

// Logique pour le chat
const messages = ref([]);
const newMessage = ref('');
const authUser = usePage().props.auth.user;

async function fetchMessages() {
    try {
        // On charge les messages déjà passés par le contrôleur
        // (c'est plus efficace que de refaire un appel axios)
        messages.value = props.reservation.messages;
    } catch (error) {
        console.error("Erreur lors du chargement des messages:", error);
    }
}

async function sendMessage() {
    if (newMessage.value.trim() === '') return;
    try {
        const response = await axios.post(route('messages.store', props.reservation.id), {
            body: newMessage.value
        });
        messages.value.push(response.data);
        newMessage.value = '';
    } catch (error) {
        console.error("Erreur lors de l'envoi du message:", error);
    }
}

onMounted(() => {
    fetchMessages();
});
</script>

<template>
    <Head :title="'Trajet: ' + reservation.destination" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Trajet pour {{ reservation.destination }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="md:col-span-1 space-y-4">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900">Détails</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            <strong>Conducteur:</strong> {{ reservation.driver.name }}
                        </p>
                        <p class="mt-1 text-sm text-gray-600 flex items-center gap-2">
                            <span><strong>Véhicule:</strong> {{ reservation.vehicle.modele }}</span>
                            <span v-if="reservation.vehicle.energie === 'electrique' || reservation.vehicle.energie === 'hybride'" 
                                  class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold"
                                  :class="{
                                    'bg-green-100 text-green-800': reservation.vehicle.energie === 'electrique',
                                    'bg-emerald-100 text-emerald-800': reservation.vehicle.energie === 'hybride'
                                  }"
                                  title="Trajet écologique">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                                </svg>
                                <span v-if="reservation.vehicle.energie === 'electrique'">Électrique</span>
                                <span v-else>Hybride</span>
                            </span>
                        </p>
                        <p class="mt-1 text-sm text-gray-600">
                            <strong>Départ:</strong> {{ formatDate(reservation.date_debut) }}
                        </p>
                        <p class="mt-1 text-sm text-gray-600">
                            <strong>Fin:</strong> {{ formatDate(reservation.date_fin) }}
                        </p>
                        <p class="mt-1 text-sm text-gray-600">
                            <strong>Statut:</strong> 
                            <span class="px-2 py-1 rounded text-xs font-semibold"
                                :class="{
                                    'bg-yellow-100 text-yellow-800': reservation.statut === 'en attente',
                                    'bg-green-100 text-green-800': reservation.statut === 'validé',
                                    'bg-blue-100 text-blue-800': reservation.statut === 'en cours',
                                    'bg-orange-100 text-orange-800': reservation.statut === 'à retourner',
                                    'bg-red-100 text-red-800': reservation.statut === 'annulé',
                                    'bg-gray-100 text-gray-800': reservation.statut === 'terminé'
                                }">
                                {{ reservation.statut }}
                            </span>
                        </p>
                    </div>
                    
                    <!-- Section retour du véhicule -->
                    <div v-if="reservation.date_retour" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-3">Retour du véhicule</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            <strong>Date de retour:</strong> {{ formatDate(reservation.date_retour) }}
                        </p>
                        <p class="mt-1 text-sm text-gray-600">
                            <strong>Kilométrage final:</strong> {{ reservation.km_final }} km
                        </p>
                        <p class="mt-1 text-sm text-gray-600">
                            <strong>Emplacement:</strong> {{ reservation.emplacement_retour }}
                        </p>
                        <p class="mt-1 text-sm text-gray-600">
                            <strong>État:</strong> 
                            <span class="px-2 py-1 rounded text-xs font-semibold"
                                :class="{
                                    'bg-green-100 text-green-800': reservation.etat_vehicule === 'excellent',
                                    'bg-blue-100 text-blue-800': reservation.etat_vehicule === 'bon',
                                    'bg-yellow-100 text-yellow-800': reservation.etat_vehicule === 'moyen',
                                    'bg-red-100 text-red-800': reservation.etat_vehicule === 'mauvais'
                                }">
                                {{ reservation.etat_vehicule }}
                            </span>
                        </p>
                        <p v-if="reservation.notes_retour" class="mt-1 text-sm text-gray-600">
                            <strong>Notes:</strong> {{ reservation.notes_retour }}
                        </p>
                    </div>
                    
                    <!-- Bouton pour retourner le véhicule -->
                    <div v-else-if="reservation.user_id === authUser.id && (reservation.statut === 'validé' || reservation.statut === 'en cours' || reservation.statut === 'à retourner') && !reservation.date_retour" 
                         class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <Link 
                            :href="route('reservations.return.form', reservation.id)"
                            class="w-full flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-md shadow-md transition"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Retourner le véhicule
                        </Link>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900">Participants</h3>
                        <ul class="mt-2 space-y-1">
                            <li class="text-sm text-gray-700">
                                <strong>{{ reservation.driver.name }}</strong> (Conducteur)
                            </li>
                            <li v-for="p in reservation.passengers.filter(p => p.statut === 'confirme')" :key="p.id" class="text-sm text-gray-700">
                                {{ p.user.name }} (Passager)
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="md:col-span-2 space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">
                            Messagerie du Trajet
                        </h2>

                        <div class="h-96 overflow-y-auto border border-gray-200 rounded-md p-4 space-y-4">
                            <div v-if="messages.length === 0" class="text-gray-500 text-center">
                                Aucun message. Démarrez la conversation !
                            </div>
                            <div v-for="message in messages" :key="message.id">
                                <div v-if="message.user.id !== authUser.id" class="flex justify-start">
                                    <div class="bg-gray-100 rounded-lg px-4 py-2 max-w-xs">
                                        <p class="font-semibold text-sm">{{ message.user.name }}</p>
                                        <p>{{ message.body }}</p>
                                    </div>
                                </div>
                                <div v-else class="flex justify-end">
                                    <div class="bg-indigo-500 text-white rounded-lg px-4 py-2 max-w-xs">
                                        <p class="font-semibold text-sm">Moi</p>
                                        <p>{{ message.body }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="sendMessage" class="flex gap-2 mt-6">
                            <input 
                                type="text" 
                                v-model="newMessage"
                                placeholder="Écrivez votre message..."
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            />
                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-md shadow-md">
                                Envoyer
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>