<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, nextTick } from 'vue'; // Ajout de onUnmounted et nextTick
import axios from 'axios';
import useDate from '@/Composables/useDate';

// On reçoit la réservation du contrôleur
const props = defineProps({
    reservation: Object,
});

const { formatDate } = useDate();

// --- LOGIQUE DE LA MESSAGERIE (TEMPS RÉEL) ---
const messages = ref([]);
const newMessage = ref('');
const authUser = usePage().props.auth.user;

// Fonction pour scroller automatiquement au bas du chat
const scrollToBottom = async () => {
    await nextTick();
    const container = document.getElementById('chat-container');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
};

async function fetchMessages() {
    try {
        // On initialise avec les messages passés dans les props
        messages.value = props.reservation.messages || [];
        scrollToBottom();
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

        // On ajoute notre propre message à la liste
        messages.value.push(response.data);
        newMessage.value = '';
        scrollToBottom();
    } catch (error) {
        console.error("Erreur lors de l'envoi du message:", error);
    }
}

onMounted(() => {
    fetchMessages();

    // CONNEXION AU CANAL REVERB
    // On écoute les nouveaux messages en direct
    window.Echo.private(`reservation.${props.reservation.id}`)
        .listen('MessageSent', (e) => {
            // Si le message vient de quelqu'un d'autre (conducteur ou autre passager)
            if (e.message.user_id !== authUser.id) {
                messages.value.push(e.message);
                scrollToBottom();
            }
        });
});

// On se déconnecte proprement en quittant la page
onUnmounted(() => {
    window.Echo.leave(`reservation.${props.reservation.id}`);
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
                                  }">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                                </svg>
                                <span>{{ reservation.vehicle.energie === 'electrique' ? 'Électrique' : 'Hybride' }}</span>
                            </span>
                        </p>
                        <p class="mt-1 text-sm text-gray-600"><strong>Départ:</strong> {{ formatDate(reservation.date_debut) }}</p>
                        <p class="mt-1 text-sm text-gray-600"><strong>Fin:</strong> {{ formatDate(reservation.date_fin) }}</p>
                        <p class="mt-1 text-sm text-gray-600">
                            <strong>Statut:</strong>
                            <span class="px-2 py-1 rounded text-xs font-semibold ml-1"
                                  :class="{
                                    'bg-yellow-100 text-yellow-800': reservation.statut === 'en attente',
                                    'bg-green-100 text-green-800': reservation.statut === 'validé',
                                    'bg-blue-100 text-blue-800': reservation.statut === 'en cours'
                                }">
                                {{ reservation.statut }}
                            </span>
                        </p>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900">Participants</h3>
                        <ul class="mt-2 space-y-1">
                            <li class="text-sm text-gray-700"><strong>{{ reservation.driver.name }}</strong> (Conducteur)</li>
                            <li v-for="p in reservation.passengers.filter(p => p.statut === 'confirme')" :key="p.id" class="text-sm text-gray-700">
                                {{ p.user.name }} (Passager)
                            </li>
                            <li v-if="reservation.places_reservees_materiel > 0" class="text-sm text-gray-500 italic mt-2">
                                + {{ reservation.places_reservees_materiel }} place(s) réservée(s) pour du matériel.
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="md:col-span-2 space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Messagerie du Trajet</h2>

                        <div id="chat-container" class="h-96 overflow-y-auto border border-gray-200 rounded-md p-4 space-y-4 bg-gray-50">
                            <div v-if="messages.length === 0" class="text-gray-500 text-center">Aucun message. Démarrez la conversation !</div>

                            <div v-for="message in messages" :key="message.id">
                                <div v-if="message.user.id !== authUser.id" class="flex justify-start">
                                    <div class="bg-white border border-gray-200 rounded-lg px-4 py-2 max-w-xs shadow-sm">
                                        <p class="font-semibold text-xs text-indigo-600">
                                            {{ message.user.name }}
                                            <span class="text-[10px] text-gray-400 ml-2 font-normal">{{ formatDate(message.created_at) }}</span>
                                        </p>
                                        <p class="text-sm text-gray-800">{{ message.body }}</p>
                                    </div>
                                </div>

                                <div v-else class="flex justify-end">
                                    <div class="bg-indigo-600 text-white rounded-lg px-4 py-2 max-w-xs shadow-md">
                                        <p class="text-sm">{{ message.body }}</p>
                                        <p class="text-[10px] text-indigo-200 text-right mt-1">{{ formatDate(message.created_at) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="sendMessage" class="flex gap-2 mt-6">
                            <input
                                type="text"
                                v-model="newMessage"
                                placeholder="Écrivez votre message..."
                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            />
                            <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-md shadow-md transition">
                                Envoyer
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
