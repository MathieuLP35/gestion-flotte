<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import axios from 'axios';
import useDate from '@/Composables/useDate';

const props = defineProps({
    reservation: Object,
});

const { formatDate } = useDate();

const messages = ref([]);
const newMessage = ref('');
const authUser = usePage().props.auth.user;

const scrollToBottom = async () => {
    await nextTick();
    const container = document.getElementById('chat-container');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
};

async function fetchMessages() {
    try {
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
        messages.value.push(response.data);
        newMessage.value = '';
        scrollToBottom();
    } catch (error) {
        console.error("Erreur lors de l'envoi du message:", error);
    }
}

onMounted(() => {
    fetchMessages();
    // Assuming Reverb/Echo is active
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
    <Head :title="'Détail : ' + reservation.destination" />

    <AuthenticatedLayout>
        <div class="py-10 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ $t('res.show_title') }}</h1>
                        <p class="mt-2 text-sm text-gray-600">{{ $t('res.show_subtitle') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Colonne gauche: Le "Billet" digital -->
                    <div class="lg:col-span-1 space-y-6">
                        
                        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden transform transition duration-200">
                            <div class="bg-sparkotto-purple p-6 text-white text-center rounded-t-2xl">
                                <span class="bg-white/20 text-white rounded-full px-3 py-1 text-xs font-semibold tracking-wide uppercase">
                                    {{ reservation.statut }}
                                </span>
                                <h2 class="mt-4 text-2xl font-bold">{{ reservation.depart }}</h2>
                                <div class="my-2">
                                    <svg class="h-6 w-6 mx-auto text-sparkotto-yellow opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                    </svg>
                                </div>
                                <h2 class="text-2xl font-bold">{{ reservation.destination }}</h2>
                            </div>
                            
                            <div class="p-6 space-y-4">
                                <div>
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">{{ $t('res.dates') }}</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $t('res.departure_date') }} {{ formatDate(reservation.date_debut) }}</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $t('res.return_date') }} {{ formatDate(reservation.date_fin) }}</p>
                                </div>

                                <div class="pt-4 border-t border-gray-100">
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">{{ $t('res.driver') }}</p>
                                    <div class="flex items-center space-x-3 mt-2">
                                        <div class="h-10 w-10 flex-shrink-0 bg-gray-100 text-gray-700 rounded-full flex items-center justify-center font-bold text-sm">
                                            {{ reservation.driver.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <p class="text-sm font-bold text-gray-900">{{ reservation.driver.name }}</p>
                                    </div>
                                </div>

                                <div class="pt-4 border-t border-gray-100">
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">{{ $t('res.vehicle') }}</p>
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-bold text-gray-900">{{ reservation.vehicle.modele }}</p>
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
                            </div>
                        </div>

                        <!-- Participants -->
                        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-6">
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-4 flex items-center">
                                <svg class="h-5 w-5 mr-2 text-sparkotto-purple" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                {{ $t('res.passengers') }}
                            </h3>
                            <ul class="space-y-3">
                                <li v-for="p in reservation.passengers.filter(p => p.statut === 'confirme')" :key="p.id" class="flex items-center">
                                    <div class="h-8 w-8 bg-gray-100 text-gray-600 rounded-full flex items-center justify-center font-bold text-xs mr-3">
                                        {{ p.user.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <span class="text-sm font-medium text-gray-800">{{ p.user.name }}</span>
                                </li>
                                <li v-if="reservation.passengers.filter(p => p.statut === 'confirme').length === 0" class="text-sm text-gray-500 italic">
                                    {{ $t('res.no_passengers') }}
                                </li>
                                <li v-if="reservation.places_reservees_materiel > 0" class="flex items-center pt-3 border-t border-gray-50 mt-3">
                                    <svg class="h-5 w-5 mr-3 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                    <span class="text-sm text-gray-600">+ {{ reservation.places_reservees_materiel }} {{ $t('res.seats') }} ({{ $t('admin.fleet') }})</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Colonne droite: La Messagerie -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-2xl shadow-card border border-gray-100 h-full flex flex-col overflow-hidden">
                            <div class="bg-gray-50 p-6 border-b border-gray-100">
                                <h2 class="text-xl font-bold text-gray-900 flex items-center">
                                    <svg class="h-5 w-5 mr-2 text-sparkotto-purple" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                    {{ $t('res.messaging') }}
                                </h2>
                            </div>

                            <div id="chat-container" class="flex-1 min-h-[400px] max-h-[600px] overflow-y-auto p-6 space-y-6 bg-white">
                                <div v-if="messages.length === 0" class="h-full flex flex-col items-center justify-center text-gray-500">
                                    <svg class="h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                                    <span>{{ $t('res.no_messages') }}</span>
                                </div>

                                <div v-for="message in messages" :key="message.id">
                                    <div v-if="message.user.id !== authUser.id" class="flex justify-start">
                                        <div class="bg-gray-100 rounded-2xl rounded-tl-sm px-5 py-3 max-w-sm">
                                            <p class="font-bold text-xs text-gray-500 mb-1">
                                                {{ message.user.name }}
                                                <span class="font-normal text-[10px] ml-2">{{ formatDate(message.created_at) }}</span>
                                            </p>
                                            <p class="text-sm text-gray-800">{{ message.body }}</p>
                                        </div>
                                    </div>

                                    <div v-else class="flex justify-end">
                                        <div class="bg-sparkotto-purple text-white rounded-2xl rounded-tr-sm px-5 py-3 max-w-sm shadow-sm">
                                            <p class="text-sm">{{ message.body }}</p>
                                            <p class="text-[10px] text-purple-200 text-right mt-1">{{ formatDate(message.created_at) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <form @submit.prevent="sendMessage" class="p-4 bg-gray-50 border-t border-gray-100 flex items-center space-x-2">
                                <input
                                    type="text"
                                    v-model="newMessage"
                                    :placeholder="$t('res.message_placeholder')"
                                    class="flex-1 bg-white border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-sparkotto-purple focus:border-sparkotto-purple shadow-sm transition"
                                />
                                <button type="submit" class="p-3 bg-sparkotto-purple hover:bg-sparkotto-purple-hover text-white rounded-xl shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-sparkotto-purple focus:ring-offset-2 flex-shrink-0">
                                    <svg class="h-5 w-5 transform rotate-90" fill="currentColor" viewBox="0 0 20 20"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" /></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
