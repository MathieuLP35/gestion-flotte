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

    if (window.Echo) {
        window.Echo.private(`reservation.${props.reservation.id}`)
            .listen('MessageSent', (e) => {
                if (e.message.user_id !== authUser.id) {
                    messages.value.push(e.message);
                    scrollToBottom();
                }
            });
    }
});

onUnmounted(() => {
    if (window.Echo) {
        window.Echo.leave(`reservation.${props.reservation.id}`);
    }
});

</script>

<template>
    <Head :title="'Gérer Trajet: ' + reservation.destination" />

    <AuthenticatedLayout>
        <div class="py-10 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ $t('res.edit_title') }}</h1>
                        <p class="mt-2 text-sm text-gray-600 truncate">{{ reservation.depart }} → {{ reservation.destination }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- Colonne de gauche : Détails & Carte -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Résumé -->
                        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-6">
                            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-4">{{ $t('res.edit_details') }}</h2>
                            <div class="space-y-4">
                                <div>
                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">{{ $t('res.vehicle') }}</span>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-bold text-gray-900">{{ reservation.vehicle.modele }}</span>
                                        <span v-if="reservation.vehicle.energie === 'electrique' || reservation.vehicle.energie === 'hybride'"
                                              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold"
                                              :class="{
                                                'bg-green-100 text-green-800': reservation.vehicle.energie === 'electrique',
                                                'bg-emerald-100 text-emerald-800': reservation.vehicle.energie === 'hybride'
                                              }">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>
                                            {{ reservation.vehicle.energie === 'electrique' ? $t('vehicle.electric') : $t('vehicle.hybrid') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="pt-3 border-t border-gray-100">
                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">{{ $t('res.dates') }}</span>
                                    <p class="text-sm text-gray-900">{{ $t('res.departure') }}: <span class="font-medium">{{ formatDate(reservation.date_debut) }}</span></p>
                                    <p class="text-sm text-gray-900">{{ $t('res.end_label') }} <span class="font-medium">{{ formatDate(reservation.date_fin) }}</span></p>
                                </div>
                                <div v-if="reservation.covoiturage" class="pt-3 border-t border-gray-100">
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-sparkotto-purple uppercase tracking-wider mb-1">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                        {{ $t('res.carpool_open') }}
                                    </span>
                                    <p v-if="reservation.places_reservees_materiel > 0" class="text-xs text-gray-500 italic mt-1">
                                        {{ $t('res.seats_blocked_for_material', { count: reservation.places_reservees_materiel }) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Carte Itinéraire -->
                        <div class="bg-white rounded-2xl shadow-card border border-gray-100 flex flex-col overflow-hidden">
                            <div class="bg-gray-50 border-b border-gray-100 p-4">
                                <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide flex items-center">
                                    <svg class="h-4 w-4 mr-2 text-sparkotto-purple" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c-1.105 0-2-.895-2-2m2 2c1.105 0 2-.895 2-2m-2 2V5m0 0C7.895 5 7 5.895 7 7m0 0A2 2 0 105 7m2 0C7 8.105 7.895 9 9 9m-2 0V3m0 0A2 2 0 103 3m2 0C5 1.895 5.895 1 7 1" /></svg>
                                    {{ $t('res.itinerary') }}
                                </h2>
                            </div>
                            <div class="flex-grow relative bg-gray-100">
                                <MapRoute
                                    :start-coords="[reservation.depart_latitude, reservation.depart_longitude]"
                                    :end-coords="[reservation.destination_latitude, reservation.destination_longitude]"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Colonne Centale & Droite : Passagers & Messages -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- Gestion des Passagers -->
                        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-6">
                            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                                <svg class="h-6 w-6 mr-2 text-sparkotto-purple" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                {{ $t('res.passengers') }}
                            </h2>
                            
                            <div v-if="reservation.passengers.length === 0" class="text-center bg-gray-50 rounded-xl p-8 border border-dashed border-gray-200">
                                <span class="text-sm text-gray-500">{{ $t('res.no_passenger_requests') }}</span>
                            </div>
                            
                            <div v-else class="space-y-3">
                                <div v-for="p in reservation.passengers" :key="p.id" class="bg-gray-50 border border-gray-100 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between">
                                    <div class="flex items-center mb-3 sm:mb-0">
                                        <div class="h-10 w-10 flex-shrink-0 bg-sparkotto-purple text-white rounded-full flex items-center justify-center font-bold text-sm mr-3">
                                            {{ p.user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900 text-sm">{{ p.user.name }}</p>
                                            <span class="inline-flex mt-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide"
                                                    :class="{
                                                    'bg-green-100 text-green-700': p.statut === 'confirme', 
                                                    'bg-yellow-100 text-yellow-700': p.statut === 'en_attente', 
                                                    'bg-red-100 text-red-700': p.statut === 'refuse'}">
                                                {{ p.statut }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <template v-if="p.statut === 'en_attente'">
                                            <button @click="updatePassengerStatus(p.id, 'confirme')" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-bold rounded-lg shadow-sm transition">{{ $t('res.accept') }}</button>
                                            <button @click="updatePassengerStatus(p.id, 'refuse')" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-bold rounded-lg shadow-sm transition">{{ $t('res.decline') }}</button>
                                        </template>
                                        <button v-if="p.statut === 'confirme'" @click="removePassenger(p.id)" class="px-4 py-2 bg-white border border-red-200 text-red-600 hover:bg-red-50 text-xs font-bold rounded-lg transition">{{ $t('res.remove') }}</button>
                                        <button v-if="p.statut === 'refuse'" @click="updatePassengerStatus(p.id, 'confirme')" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-bold rounded-lg shadow-sm transition">{{ $t('res.reevaluate') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Chat -->
                        <div class="bg-white rounded-2xl shadow-card border border-gray-100 h-96 flex flex-col overflow-hidden">
                            <div class="bg-gray-50 p-4 border-b border-gray-100">
                                <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide flex items-center">
                                    <svg class="h-4 w-4 mr-2 text-sparkotto-purple" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                    Discussion
                                </h2>
                            </div>

                            <div id="chat-container" class="flex-1 overflow-y-auto p-4 space-y-4 bg-white">
                                <div v-if="messages.length === 0" class="h-full flex items-center justify-center text-sm text-gray-400">{{ $t('res.no_messages_short') }}</div>
                                <div v-for="message in messages" :key="message.id">
                                    <div v-if="message.user.id !== authUser.id" class="flex justify-start">
                                        <div class="bg-gray-100 rounded-2xl rounded-tl-sm px-4 py-2 max-w-xs">
                                            <p class="font-bold text-[11px] text-gray-500 mb-0.5">{{ message.user.name }}</p>
                                            <p class="text-xs text-gray-800">{{ message.body }}</p>
                                        </div>
                                    </div>
                                    <div v-else class="flex justify-end">
                                        <div class="bg-sparkotto-purple text-white rounded-2xl rounded-tr-sm px-4 py-2 max-w-xs shadow-sm">
                                            <p class="text-xs">{{ message.body }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <form @submit.prevent="sendMessage" class="p-3 bg-gray-50 border-t border-gray-100 flex gap-2">
                                <input
                                    type="text"
                                    v-model="newMessage"
                                    placeholder="Écrire..."
                                    class="flex-1 bg-white border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-sparkotto-purple focus:border-sparkotto-purple shadow-sm transition"
                                />
                                <button type="submit" class="px-4 py-2 bg-sparkotto-purple hover:bg-sparkotto-purple-hover text-white text-sm font-bold rounded-xl shadow-sm transition-colors focus:outline-none">
                                    {{ $t('res.send') }}
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
