<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

// 1. Récupérer les props du contrôleur
const props = defineProps({
    reservation: Object,
    vehicles: Array,
});

// 2. Initialiser le formulaire d'édition
// On inclut le champ `destination`
const form = useForm({
    vehicle_id: props.reservation.vehicle_id,
    destination: props.reservation.destination,
    date_debut: props.reservation.date_debut,
    date_fin: props.reservation.date_fin,
});

// 3. Fonction pour soumettre le formulaire principal
function submit() {
    form.put(route('reservations.update', props.reservation.id));
}

// 4. Fonction pour GÉRER les passagers (Accepter / Refuser)
// Appelle la route `passengers.update`
const updatePassengerStatus = (passengerId, newStatus) => {
    router.put(route('passengers.update', passengerId), {
        statut: newStatus
    }, {
        preserveScroll: true, // La page ne remontera pas
        onSuccess: () => {
            // Afficher un "toast" de succès serait idéal ici
        }
    });
};

// 5. Fonction pour RETIRER un passager
// Appelle la route `passengers.destroy`
const removePassenger = (passengerId) => {
    if (confirm("Voulez-vous vraiment retirer ce passager du trajet ?")) {
        router.delete(route('passengers.destroy', passengerId), {
            preserveScroll: true,
        });
    }
};

// ------------------------------------------
// NOUVEAU : LOGIQUE DE CHAT
// ------------------------------------------
const messages = ref([]); // La liste des messages
const newMessage = ref(''); // Le contenu du champ de texte
const authUser = usePage().props.auth.user; // L'utilisateur connecté

// Fonction pour charger les messages au démarrage
async function fetchMessages() {
    try {
        const response = await axios.get(route('messages.index', props.reservation.id));
        messages.value = response.data;
    } catch (error) {
        console.error("Erreur lors du chargement des messages:", error);
    }
}

// Fonction pour envoyer un nouveau message
async function sendMessage() {
    if (newMessage.value.trim() === '') return; // Ne pas envoyer de message vide

    try {
        const response = await axios.post(route('messages.store', props.reservation.id), {
            body: newMessage.value
        });
        
        // Ajouter le nouveau message (renvoyé par le serveur) à la liste
        messages.value.push(response.data);
        
        // Vider le champ de texte
        newMessage.value = '';
    } catch (error) {
        console.error("Erreur lors de l'envoi du message:", error);
    }
}

// Charger les messages quand le composant est monté
onMounted(() => {
    fetchMessages();
});

</script>

<template>
    <Head :title="'Gérer Trajet: ' + reservation.destination" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gérer le trajet : {{ reservation.destination }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 md:p-8">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            
                            <div class="space-y-6">
                                <h2 class="text-2xl font-bold text-gray-800 mb-6">
                                    Détails du Trajet
                                </h2>
                                <form @submit.prevent="submit" class="space-y-6">
                                
                                    <div>
                                        <label for="destination" class="block text-sm font-medium text-gray-700">Destination</label>
                                        <input type="text" v-model="form.destination" id="destination" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required />
                                        <div v-if="form.errors.destination" class="mt-2 text-sm text-red-600">
                                            {{ form.errors.destination }}
                                        </div>
                                    </div>

                                    <div>
                                        <label for="vehicle" class="block text-sm font-medium text-gray-700">Véhicule</label>
                                        <select v-model="form.vehicle_id" id="vehicle" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                            <option v-for="v in vehicles" :key="v.id" :value="v.id">
                                            {{ v.modele }} ({{ v.immatriculation }})
                                            </option>
                                        </select>
                                        <div v-if="form.errors.vehicle_id" class="mt-2 text-sm text-red-600">
                                            {{ form.errors.vehicle_id }}
                                        </div>
                                    </div>

                                    <div>
                                        <label for="date_debut" class="block text-sm font-medium text-gray-700">Date de début</label>
                                        <input type="datetime-local" v-model="form.date_debut" id="date_debut" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required />
                                        <div v-if="form.errors.date_debut" class="mt-2 text-sm text-red-600">
                                            {{ form.errors.date_debut }}
                                        </div>
                                    </div>

                                    <div>
                                        <label for="date_fin" class="block text-sm font-medium text-gray-700">Date de fin</label>
                                        <input type="datetime-local" v-model="form.date_fin" id="date_fin" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required />
                                        <div v-if="form.errors.date_fin" class="mt-2 text-sm text-red-600">
                                            {{ form.errors.date_fin }}
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                                        <button type="submit" :disabled="form.processing" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-md shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150" :class="{ 'opacity-50 cursor-not-allowed': form.processing }">
                                            {{ form.processing ? 'Sauvegarde...' : 'Mettre à jour' }}
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div class="space-y-6 md:border-l md:border-gray-200 md:pl-8">
                                <h2 class="text-2xl font-bold text-gray-800 mb-6">
                                    Gestion des Passagers
                                </h2>
                                
                                <div v-if="reservation.passengers.length === 0" class="text-center text-gray-500 p-4 border border-dashed rounded-md">
                                    Aucune demande de passager pour ce trajet.
                                </div>

                                <ul v-else class="space-y-4">
                                    <li v-for="p in reservation.passengers" :key="p.id" class="p-4 border border-gray-200 rounded-lg flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <span class="font-medium text-gray-900">{{ p.user.name }}</span>
                                            <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                :class="{ 
                                                    'bg-green-100 text-green-800': p.statut === 'confirme', 
                                                    'bg-yellow-100 text-yellow-800': p.statut === 'en_attente', 
                                                    'bg-red-100 text-red-800': p.statut === 'refuse' 
                                                }">
                                                {{ p.statut }}
                                            </span>
                                        </div>
                                        
                                        <div class="mt-3 sm:mt-0 sm:ml-4 sm:flex-shrink-0 space-x-2">
                                            <template v-if="p.statut === 'en_attente'">
                                                <button @click="updatePassengerStatus(p.id, 'confirme')" class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-md shadow-sm transition ease-in-out duration-150">Accepter</button>
                                                <button @click="updatePassengerStatus(p.id, 'refuse')" class="px-3 py-1 bg-yellow-600 hover:bg-yellow-700 text-white text-xs font-medium rounded-md shadow-sm transition ease-in-out duration-150">Refuser</button>
                                            </template>
                                            
                                            <template v-if="p.statut === 'confirme'">
                                                <button @click="removePassenger(p.id)" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded-md shadow-sm transition ease-in-out duration-150">Retirer</button>
                                            </template>

                                            <template v-if="p.statut === 'refuse'">
                                                <button @click="updatePassengerStatus(p.id, 'confirme')" class="px-3 py-1 bg-gray-400 hover:bg-gray-500 text-white text-xs font-medium rounded-md shadow-sm transition ease-in-out duration-150" title="Ré-accepter">Accepter</button>
                                            </template>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="mt-3 space-y-6 pt-6 border-t border-gray-200">
                            <h2 class="text-2xl font-bold text-gray-800 mb-6">
                                Messagerie du Trajet
                            </h2>

                            <div class="h-64 overflow-y-auto border border-gray-200 rounded-md p-4 space-y-4">
                                <div v-if="messages.length === 0" class="text-gray-500 text-center">
                                    Aucun message pour l'instant.
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

                            <form @submit.prevent="sendMessage" class="flex gap-2">
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
        </div>
    </AuthenticatedLayout>
</template>