<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MapRoute from '@/Components/MapRoute.vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import useDate from '@/Composables/useDate';
import axios from 'axios';

// 1. Récupérer les props du contrôleur
const props = defineProps({
    reservation: Object,
    vehicles: Array,
});

const { formatDate } = useDate();

// Formulaire de modification du trajet
const form = useForm({
    vehicle_id: props.reservation.vehicle_id,
    destination: props.reservation.destination,
    date_debut: props.reservation.date_debut,
    date_fin: props.reservation.date_fin,
});

function submit() {
    form.put(route('reservations.update', props.reservation.id));
}

// 4. Gestion des Passagers
const updatePassengerStatus = (passengerId, newStatus) => {
    router.put(route('passengers.update', passengerId), {
        statut: newStatus
    }, {
        preserveScroll: true,
    });
};

const removePassenger = (passengerId) => {
    if (confirm("Voulez-vous vraiment retirer ce passager du trajet ?")) {
        router.delete(route('passengers.destroy', passengerId), {
            preserveScroll: true,
        });
    }
};

// -------------------------------------------------------------------------
// 6. GESTION DE LA MESSAGERIE (TEMPS RÉEL)
// -------------------------------------------------------------------------

const messages = ref([]);
const newMessage = ref('');
const authUser = usePage().props.auth.user;

// Fonction pour scroller en bas du chat
const scrollToBottom = async () => {
    await nextTick();
    const container = document.getElementById('chat-container');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
};

// Charger les messages au démarrage
async function fetchMessages() {
    try {
        const response = await axios.get(route('messages.index', props.reservation.id));
        messages.value = response.data;
        scrollToBottom();
    } catch (error) {
        console.error("Erreur lors du chargement des messages:", error);
    }
}

// Envoyer un nouveau message
async function sendMessage() {
    if (newMessage.value.trim() === '') return;

    try {
        const response = await axios.post(route('messages.store', props.reservation.id), {
            body: newMessage.value
        });

        // On ajoute notre propre message à la liste locale
        messages.value.push(response.data);
        newMessage.value = '';
        scrollToBottom();
    } catch (error) {
        console.error("Erreur lors de l'envoi du message:", error);
    }
}

onMounted(() => {
    fetchMessages();

    // ÉCOUTE DU TEMPS RÉEL
    // On s'abonne au canal privé de la réservation
    window.Echo.private(`reservation.${props.reservation.id}`)
        .listen('MessageSent', (e) => {
            // Si le message provient d'un autre utilisateur, on l'ajoute
            if (e.message.user_id !== authUser.id) {
                messages.value.push(e.message);
                scrollToBottom();
            }
        });
});

// Déconnexion du canal quand on quitte la page
onUnmounted(() => {
    window.Echo.leave(`reservation.${props.reservation.id}`);
});

</script>

<template>
    <Head :title="'Gérer Trajet: ' + reservation.destination" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gérer le trajet : {{ reservation.depart }} → {{ reservation.destination }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 md:p-8">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-6 mb-4">
                                <h2 class="text-2xl font-bold text-gray-800 mb-6">Détails du Trajet</h2>
                                <div class="space-y-6">
                                    <div class="flex items-center gap-2">
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
                                            {{ reservation.vehicle.energie === 'electrique' ? 'Électrique' : 'Hybride' }}
                                        </span>
                                    </div>
                                    <div><strong>Départ:</strong> {{ formatDate(reservation.date_debut) }}</div>
                                    <div><strong>Fin:</strong> {{ formatDate(reservation.date_fin) }}</div>
                                </div>
                            </div>

                            <div class="space-y-6 md:border-l md:border-gray-200 md:pl-8">
                                <h2 class="text-2xl font-bold text-gray-800 mb-6">Gestion des Passagers</h2>
                                <div v-if="reservation.passengers.length === 0" class="text-center text-gray-500 p-4 border border-dashed rounded-md">
                                    Aucune demande de passager.
                                </div>
                                <ul v-else class="space-y-4">
                                    <li v-for="p in reservation.passengers" :key="p.id" class="p-4 border border-gray-200 rounded-lg flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <span class="font-medium text-gray-900">{{ p.user.name }}</span>
                                            <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                  :class="{'bg-green-100 text-green-800': p.statut === 'confirme', 'bg-yellow-100 text-yellow-800': p.statut === 'en_attente', 'bg-red-100 text-red-800': p.statut === 'refuse'}">
                                                {{ p.statut }}
                                            </span>
                                        </div>
                                        <div class="mt-3 sm:mt-0 sm:ml-4 flex gap-2">
                                            <template v-if="p.statut === 'en_attente'">
                                                <button @click="updatePassengerStatus(p.id, 'confirme')" class="px-3 py-1 bg-green-600 text-white text-xs rounded-md">Accepter</button>
                                                <button @click="updatePassengerStatus(p.id, 'refuse')" class="px-3 py-1 bg-yellow-600 text-white text-xs rounded-md">Refuser</button>
                                            </template>
                                            <button v-if="p.statut === 'confirme'" @click="removePassenger(p.id)" class="px-3 py-1 bg-red-600 text-white text-xs rounded-md">Retirer</button>
                                            <button v-if="p.statut === 'refuse'" @click="updatePassengerStatus(p.id, 'confirme')" class="px-3 py-1 bg-gray-400 text-white text-xs rounded-md">Ré-accepter</button>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="mt-3 space-y-6 pt-6 border-t border-gray-200">
                            <h2 class="text-2xl font-bold text-gray-800 mb-6">Messagerie du Trajet</h2>

                            <div id="chat-container" class="h-64 overflow-y-auto border border-gray-200 rounded-md p-4 space-y-4 bg-gray-50">
                                <div v-if="messages.length === 0" class="text-gray-500 text-center">Aucun message.</div>
                                <div v-for="message in messages" :key="message.id">
                                    <div v-if="message.user.id !== authUser.id" class="flex justify-start">
                                        <div class="bg-white border border-gray-200 rounded-lg px-4 py-2 max-w-xs shadow-sm">
                                            <p class="font-semibold text-xs text-indigo-600">{{ message.user.name }}</p>
                                            <p class="text-sm text-gray-800">{{ message.body }}</p>
                                            <p class="text-[10px] text-gray-400 mt-1">{{ formatDate(message.created_at) }}</p>
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

                            <form @submit.prevent="sendMessage" class="flex gap-2">
                                <input
                                    type="text"
                                    v-model="newMessage"
                                    placeholder="Écrivez votre message..."
                                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                />
                                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-md shadow-md transition">
                                    Envoyer
                                </button>
                            </form>
                        </div>

                        <div class="mt-8 space-y-6 pt-6 border-t border-gray-200">
                            <h2 class="text-2xl font-bold text-gray-800 mb-6">Itinéraire du Trajet</h2>
                            <MapRoute
                                :start-coords="[reservation.depart_latitude, reservation.depart_longitude]"
                                :end-coords="[reservation.destination_latitude, reservation.destination_longitude]"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
