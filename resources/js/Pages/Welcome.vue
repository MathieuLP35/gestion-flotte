<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
</script>

<template>
    <PortalLayout>
        <Head title="Accueil" />

        <!-- Hero Section -->
        <div class="text-center py-12 bg-white rounded-lg shadow-md">
            <h1 class="text-4xl font-extrabold text-gray-800 mb-4">Bienvenue sur SparkOtto</h1>
            <p class="text-xl text-gray-600 mb-8">La solution tout-en-un pour gérer vos covoiturages, véhicules et réservations — simple, rapide, efficace.</p>
        </div>

        <!-- Features Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
            <div class="text-center p-6 bg-white rounded-lg shadow hover:shadow-lg transition">
                <div class="w-16 h-16 mx-auto bg-blue-100 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-xl font-semibold text-gray-800">Covoiturage</h3>
                <p class="mt-2 text-gray-600">Planifiez, partagez et suivez vos trajets en toute simplicité avec vos collègues.</p>
            </div>
            <div class="text-center p-6 bg-white rounded-lg shadow hover:shadow-lg transition">
                <div class="w-16 h-16 mx-auto bg-green-100 rounded-full flex items-center justify-center">
                    <svg version="1.1" id="Icons" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 32 32" xml:space="preserve" class="size-6"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M28,13h1c0.6,0,1-0.4,1-1s-0.4-1-1-1h-2.8L25,8c-0.8-1.8-2.6-3-4.6-3h-8.7C9.6,5,7.8,6.2,7,8l-1.3,3H3c-0.6,0-1,0.4-1,1 s0.4,1,1,1h1c-1.2,0.9-2,2.4-2,4v4c0,0.9,0.4,1.7,1,2.2V25c0,1.7,1.3,3,3,3h2c1.7,0,3-1.3,3-3v-1h10v1c0,1.7,1.3,3,3,3h2 c1.7,0,3-1.3,3-3v-1.8c0.6-0.5,1-1.3,1-2.2v-4C30,15.4,29.2,13.9,28,13z M27,18c0,0.6-0.4,1-1,1h-3c-0.6,0-1-0.4-1-1s0.4-1,1-1h3 C26.6,17,27,17.4,27,18z M6,17h3c0.6,0,1,0.4,1,1s-0.4,1-1,1H6c-0.6,0-1-0.4-1-1S5.4,17,6,17z M10.4,22l1.2-2.3 c0.5-1,1.5-1.7,2.7-1.7h3.5c1.1,0,2.2,0.6,2.7,1.7l1.2,2.3H10.4z M8.9,8.8C9.4,7.7,10.4,7,11.6,7h8.7c1.2,0,2.3,0.7,2.8,1.8l1.4,3.2 h-17L8.9,8.8z"></path> </g></svg>
                </div>
                <h3 class="mt-4 text-xl font-semibold text-gray-800">Véhicules</h3>
                <p class="mt-2 text-gray-600">Gérez votre parc automobile : kilométrage, entretien, disponibilité en un seul endroit.</p>
            </div>
            <div class="text-center p-6 bg-white rounded-lg shadow hover:shadow-lg transition">
                <div class="w-16 h-16 mx-auto bg-yellow-100 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-xl font-semibold text-gray-800">Réservations</h3>
                <p class="mt-2 text-gray-600">Suivez en temps réel les réservations, les passagers et les statuts sans stress, sans papier.</p>
            </div>
        </div>

        <!-- Why SparkOtto Section -->
        <div class="mt-12 bg-white py-12 rounded-lg shadow-md">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-4 text-center">Pourquoi choisir SparkOtto ?</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                    <div class="flex items-start">
                        <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-indigo-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Sécurité et contrôle</h3>
                            <p class="mt-2 text-gray-600">Vos données restent chez vous — pas dans le cloud. Contrôle total, conformité RGPD.</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-green-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Personnalisation</h3>
                            <p class="mt-2 text-gray-600">Adaptez SparkOtto à vos besoins — champs, workflows, rapports — tout est personnalisable.</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-blue-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.559.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.929.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.398.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Support et évolution</h3>
                            <p class="mt-2 text-gray-600">Mises à jour régulières, support technique dédié, documentation complète.</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-yellow-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Efficacité</h3>
                            <p class="mt-2 text-gray-600">Gagnez du temps — automatisations, rapports, notifications — tout est fait pour vous simplifier la vie.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>