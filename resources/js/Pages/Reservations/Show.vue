<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue'; // Imports pour le chat
import axios from 'axios'; // Import pour le chat

// On reçoit la réservation du contrôleur
const props = defineProps({
    reservation: Object,
});

// ------------------------------------------
// LOGIQUE DE CHAT (identique à celle de Edit.vue)
// ------------------------------------------
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

// Helper pour formater les dates
const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString('fr-FR', {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
};
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
                        <p class="mt-1 text-sm text-gray-600">
                            <strong>Véhicule:</strong> {{ reservation.vehicle.modele }}
                        </p>
                        <p class="mt-1 text-sm text-gray-600">
                            <strong>Départ:</strong> {{ formatDate(reservation.date_debut) }}
                        </p>
                        <p class="mt-1 text-sm text-gray-600">
                            <strong>Fin:</strong> {{ formatDate(reservation.date_fin) }}
                        </p>
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